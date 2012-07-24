<?php
$supportedGateways = array (
  'SimpleEcommCartAuthorizeNet',
  'SimpleEcommCartPayPalPro',
  'SimpleEcommCartManualGateway'
);
 
$errors = array();
$createAccount = false;
$gateway = $data['gateway']; // Object instance inherited from SimpleEcommCartGatewayAbstract 

if($_SERVER['REQUEST_METHOD'] == "POST") {
  $cart = SimpleEcommCartSession::get('SimpleEcommCartCart');
  
  
  $account = false;
  if($cart->hasMembershipProducts() || $cart->hasSpreedlySubscriptions()) {
    // Set up a new SimpleEcommCartAccount and start by pre-populating the data or load the logged in account
    if($accountId = SimpleEcommCartCommon::isLoggedIn()) {
      $account = new SimpleEcommCartAccount($accountId);
    }
    else {
      $account = new SimpleEcommCartAccount();
      if(isset($_POST['account'])) {
        $acctData = SimpleEcommCartCommon::postVal('account');
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] New Account Data: " . print_r($acctData, true));
        $account->firstName = $acctData['first_name'];
        $account->lastName = $acctData['last_name'];
        $account->email = $acctData['email'];
        $account->username = $acctData['username'];
        $account->password = md5($acctData['password']);
        $errors = $account->validate();
        $jqErrors = $account->getJqErrors();
        if($acctData['password'] != $acctData['password2']) {
          $errors[] = __("Passwords do not match","simpleecommcart");
          $jqErrors[] = 'account-password';
          $jqErrors[] = 'account-password2';
        }
        if(count($errors) == 0) { $createAccount = true; } // An account should be created and the account data is valid
      }
    }
  }
  
  $gatewayName = SimpleEcommCartCommon::postVal('simpleecommcart-gateway-name');
  SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] CHECKOUT: with gateway: $gatewayName");
  
  if(in_array($gatewayName, $supportedGateways)) {
    $gateway->validateCartForCheckout();
    
    $gateway->setBilling(SimpleEcommCartCommon::postVal('billing'));
    $gateway->setPayment(SimpleEcommCartCommon::postVal('payment'));
    
    if(isset($_POST['sameAsBilling'])) {
		if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipping())
		{
			$gateway->setShipping(SimpleEcommCartCommon::postVal('billing'));
		} 
    }
    elseif(isset($_POST['shipping'])) {
		if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipping())
		{
      		$gateway->setShipping(SimpleEcommCartCommon::postVal('shipping'));
	  	}
    }

    if(count($errors) == 0) {
      $errors = $gateway->getErrors();     // Error info for server side error code
      $jqErrors = $gateway->getJqErrors(); // Error info for client side error code
    }
    
    if(count($errors) == 0 || 1) {
      // Calculate final billing amounts
      $taxLocation = $gateway->getTaxLocation();
      $tax = $gateway->getTaxAmount();
      $total = SimpleEcommCartSession::get('SimpleEcommCartCart')->getGrandTotal() + $tax;
      $subscriptionAmt = SimpleEcommCartSession::get('SimpleEcommCartCart')->getSubscriptionAmount();
      $oneTimeTotal = $total - $subscriptionAmt;
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Tax: $tax | Total: $total | Subscription Amount: $subscriptionAmt | One Time Total: $oneTimeTotal");

      // Throttle checkout attempts
      if(!SimpleEcommCartSession::get('SimpleEcommCartCheckoutThrottle')) {
        SimpleEcommCartSession::set('SimpleEcommCartCheckoutThrottle', SimpleEcommCartCheckoutThrottle::getInstance(), true);
      }

      if(!SimpleEcommCartSession::get('SimpleEcommCartCheckoutThrottle')->isReady($gateway->getCardNumberTail(), $oneTimeTotal)) {
        $errors[] = "You must wait " . SimpleEcommCartSession::get('SimpleEcommCartCheckoutThrottle')->getTimeRemaining() . " more seconds before trying to checkout again.";
      }
    }
    
    
    // Charge credit card for one time transaction using Authorize.net API
    if(count($errors) == 0 && !SimpleEcommCartSession::get('SimpleEcommCartInventoryWarning')) {
      
      // =============================
      // = Start Spreedly Processing =
      // =============================
      
      if(SimpleEcommCartSession::get('SimpleEcommCartCart')->hasSpreedlySubscriptions()) {
        
        $accountErrors = $account->validate();
        if(count($accountErrors) == 0) {
          $account->save(); // Save account data locally which will create an account id and/or update local values
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account data validated and saved for account id: " . $account->id);
          
          try {
            $spreedlyCard = new SpreedlyCreditCard();
            $spreedlyCard->hydrateFromCheckout();
            $subscriptionId = SimpleEcommCartSession::get('SimpleEcommCartCart')->getSpreedlySubscriptionId();
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] About to create a new spreedly account subscription: Account ID: $account->id | Subscription ID: $subscriptionId");
            $accountSubscription = new SimpleEcommCartAccountSubscription();
            $accountSubscription->createSpreedlySubscription($account->id, $subscriptionId, $spreedlyCard);
          }
          catch(SpreedlyException $e) {
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Failed to checkout: " . $e->getCode() . ' ' . $e->getMessage());
            $errors['spreedly failed'] = $e->getMessage();
            $accountSubscription->refresh();
            if(empty($accountSubscription->subscriberToken)) {
              SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] About to delete local account after spreedly failure: " . print_r($account->getData(), true));
              $account->deleteMe();
            }
            else {
              // Set the subscriber token in the session for repeat attempts to create the subscription
              SimpleEcommCartSession::set('SimpleEcommCartSubscriberToken', $account->subscriberToken);
            }
          }
          
        }
        else {
          $errors = $account->getErrors();
          $jqErrors = $account->getJqErrors();
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation failed. " . print_r($errors, true));
        }
      }
      
      // ===========================
      // = End Spreedly Processing =
      // ===========================
       
      
       
      if(count($errors) == 0) {
         
        $gatewayName = get_class($gateway);
        $gateway->initCheckout($oneTimeTotal);
        if($oneTimeTotal > 0 || $gatewayName == 'SimpleEcommCartManualGateway') {
          $transactionId = $gateway->doSale();
        }
        else {
          // Do not attempt to charge $0.00 transactions to live gateways
          $transactionId = $transId = 'MT-' . SimpleEcommCartCommon::getRandString();
        }
        
        if($transactionId) {
          // Set order status based on SimpleEcommCart settings
          $statusOptions = SimpleEcommCartCommon::getOrderStatusOptions();
          $status = $statusOptions[0];
          
          // Check for account creation
          $accountId = 0;
          if($createAccount) { $account->save(); }
          if($mp = SimpleEcommCartSession::get('SimpleEcommCartCart')->getMembershipProduct()) { 
            $account->attachMembershipProduct($mp, $account->firstName, $account->lastName);
            $accountId = $account->id;
          }

          // Save the order locally
          $orderId = $gateway->saveOrder($total, $tax, $transactionId, $status, $accountId);

          
          SimpleEcommCartSession::drop('SimpleEcommCartSubscriberToken');
          SimpleEcommCartSession::set('order_id', $orderId);
          $receiptLink = SimpleEcommCartCommon::getPageLink('store/receipt');
          $newOrder = new SimpleEcommCartOrder($orderId);
          
          // Send email receipts
          //SimpleEcommCartCommon::sendEmailReceipts($orderId);
          SimpleEcommCartCommon::sendEmailOnPurchase($orderId);
          
		  // Send buyer to receipt page
          //$receiptVars = strpos($receiptLink, '?') ? '&' : '?';
          //$receiptVars .= "ouid=" . $newOrder->ouid;
          //header("Location: " . $receiptLink . $receiptVars);
		  
		  
		  //clear cart
		  SimpleEcommCartSession::drop('SimpleEcommCartCart');
		  //Send buyer to landing page  
		  $landing_page_id = 4;
		  if(SimpleEcommCartSetting::getValue('landing_page')!= NULL)
		  {
				$landing_page_id = SimpleEcommCartSetting::getValue('landing_page');
		  }
		  $landing_page_link = get_permalink($landing_page_id);
		  header("Location: " .$landing_page_link); 
        }
        else {
          // Attempt to discover reason for transaction failure
          $errors['Could Not Process Transaction'] = $gateway->getTransactionResponseDescription();
        }
      }
      
    }
    
  } // End if supported gateway 
} // End if POST


// Show inventory warning if there is one
if(SimpleEcommCartSession::get('SimpleEcommCartInventoryWarning')) {
  echo SimpleEcommCartSession::get('SimpleEcommCartInventoryWarning');
  SimpleEcommCartSession::drop('SimpleEcommCartInventoryWarning');
}


// Build checkout form action URL
$checkoutPage = get_page_by_path('store/checkout');
$ssl = SimpleEcommCartSetting::getValue('auth_force_ssl');
$url = get_permalink($checkoutPage->ID);
if(SimpleEcommCartCommon::isHttps()) {
  $url = str_replace('http:', 'https:', $url);
}

// Determine which gateway is in use
$gatewayName = get_class($data['gateway']);

// Make it easier to get to payment, billing, and shipping data
$p = $gateway->getPayment();
$b = $gateway->getBilling();
$s = $gateway->getShipping();

$billingCountryCode =  (isset($b['country']) && !empty($b['country'])) ? $b['country'] : SimpleEcommCartCommon::getHomeCountryCode();
$shippingCountryCode = (isset($s['country']) && !empty($s['country'])) ? $s['country'] : SimpleEcommCartCommon::getHomeCountryCode();

// Include the HTML markup for the checkout form
if($_SERVER['REQUEST_METHOD'] == 'POST') {
  include_once(SIMPLEECOMMCART_PATH . '/views/checkout-form.php');  
}
else {
  include(SIMPLEECOMMCART_PATH . '/views/checkout-form.php');
}

// Include the client side javascript validation                 
include_once(SIMPLEECOMMCART_PATH . '/views/client/checkout.php'); 