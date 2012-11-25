<?php

class SimpleEcommCartShortcodeManager {
  
  public $manualIsOn;
  
  /**
   * Short code for displaying shopping cart including the number of items in the cart and links to view cart and checkout
   */
  public function shoppingCart($attrs) {
    $cartPage = get_page_by_path('store/cart');
    $checkoutPage = get_page_by_path('store/checkout');
    $cart = SimpleEcommCartSession::get('SimpleEcommCartCart');
    if(is_object($cart) && $cart->countItems()) {
      ?>
      <div id="SimpleEcommCartscCartContents">
        <a id="SimpleEcommCartscCartLink" href='<?php echo get_permalink($cartPage->ID) ?>'>
        <span id="SimpleEcommCartscCartCount"><?php echo $cart->countItems(); ?></span>
        <span id="SimpleEcommCartscCartCountText"><?php echo $cart->countItems() > 1 ? ' items' : ' item' ?></span> 
        <span id="SimpleEcommCartscCartCountDash">&ndash;</span>
        <span id="SimpleEcommCartscCartPrice"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL . 
          number_format($cart->getSubTotal() - $cart->getDiscountAmount(), 2); ?>
        </span></a>
        <a id="SimpleEcommCartscViewCart" href='<?php echo get_permalink($cartPage->ID) ?>'>View Cart</a>
        <span id="SimpleEcommCartscLinkSeparator"> | </span>
        <a id="SimpleEcommCartscCheckout" href='<?php echo get_permalink($checkoutPage->ID) ?>'>Check out</a>
      </div>
      <?php
    }
    else {
      $emptyMessage = isset($attrs['empty_msg']) ? $attrs['empty_msg'] : 'Your cart is empty';
      echo "<p id=\"SimpleEcommCartscEmptyMessage\">$emptyMessage</p>";
    }
  }

  public static function showCartButton($attrs, $content) {
    $product = new SimpleEcommCartProduct();
    $product->loadFromShortcode($attrs);
    return SimpleEcommCartButtonManager::getCartButton($product, $attrs, $content);
  }
  
  public static function showCartAnchor($attrs, $content) {
    $product = new SimpleEcommCartProduct();
    $product->loadFromShortcode($attrs);
    $options = isset($attrs['options']) ? $attrs['options'] : '';
    $urlOptions = isset($attrs['options']) ? '&options=' . urlencode($options) : '';
    
    $iCount = true;
    $iKey = $product->getInventoryKey($options);
    if($product->isInventoryTracked($iKey)) {
      $iCount = $product->getInventoryCount($iKey);
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] iCount: $iCount === iKey: $iKey");
    }
    else {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not tracking inventory for: $iKey");
    }
    
    if($iCount) {
      $id = $product->id;
      $class = isset($attrs['class']) ? $attrs['class'] : '';
      $cartPage = get_page_by_path('store/cart');
      $cartLink = get_permalink($cartPage->ID);
      $joinChar = (strpos($cartLink, '?') === FALSE) ? '?' : '&';
      

      $data = array(
        'url' => $cartLink . $joinChar . "task=add-to-cart-anchor&simpleecommcartItemId=${id}${urlOptions}",
        'text' => $content,
        'class' => $class
      );

      $view = SimpleEcommCartCommon::getView('views/cart-button-anchor.php', $data);
    }
    else {
      $view = $content;
    }
    
    return $view;
  }

  public function showCart($attrs, $content) {
    if(isset($_REQUEST['simpleecommcart-task']) && $_REQUEST['simpleecommcart-task'] == 'remove-attached-form') {
      $entryId = $_REQUEST['entry'];
      if(is_numeric($entryId)) {
        SimpleEcommCartSession::get('SimpleEcommCartCart')->detachFormEntry($entryId);
      }
    }
    $view = SimpleEcommCartCommon::getView('views/cart.php', $attrs);
    return $view;
  }

  public function showReceipt($attrs) {
    $view = SimpleEcommCartCommon::getView('views/receipt.php', $attrs);
    return $view;
  }

  public function paypalCheckout($attrs) {
    if(SimpleEcommCartSession::get('SimpleEcommCartCart')->countItems() > 0) {
      if(!SimpleEcommCartSession::get('SimpleEcommCartCart')->hasSubscriptionProducts() && !SimpleEcommCartSession::get('SimpleEcommCartCart')->hasMembershipProducts()) {
        if(SimpleEcommCartSession::get('SimpleEcommCartCart')->getGrandTotal()) {
          $view = SimpleEcommCartCommon::getView('views/paypal-checkout.php', $attrs);
          return $view;
        } 
      }
    }
  }
 
  public function authCheckout($attrs) {
    if(SimpleEcommCartSession::get('SimpleEcommCartCart')->countItems() > 0) {
      $gatewayName = SimpleEcommCartCommon::postVal('simpleecommcart-gateway-name');
      

      if(!SimpleEcommCartSession::get('SimpleEcommCartCart')->hasPayPalSubscriptions()) {
        require_once(SIMPLEECOMMCART_PATH . "/advanced/payment_gateways/SimpleEcommCartAuthorizeNet.php");

        if(SimpleEcommCartSession::get('SimpleEcommCartCart')->getGrandTotal() > 0) {
          $authnet = new SimpleEcommCartAuthorizeNet();
          $view = $this->_buildCheckoutView($authnet);
          return $view;
        }
        
      }
      else {
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not rendering Authorize.net checkout form because the cart contains a PayPal subscription");
      }
    }
  }
 
  public function processIPN($attrs) {
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartPayPalIpn.php");
    require_once(SIMPLEECOMMCART_PATH . "/payment_gateways/SimpleEcommCartPayPalStandard.php");
    $ipn = new SimpleEcommCartPayPalIpn();
    if($ipn->validate($_POST)) {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Working with  IPN transaction type: " . $_POST['txn_type']);
      switch($_POST['txn_type']) { 
        case 'cart':              // Payment received for multiple items; source is Express Checkout or the PayPal Shopping Cart.
          $ipn->saveCartOrder($_POST);
          break;
        case 'recurring_payment':
          $ipn->logRecurringPayment($_POST);
          break;
        case 'recurring_payment_profile_cancel':
          $ipn->cancelSubscription($_POST);
          break;
        default:
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] IPN transaction type not implemented: " . $_POST['txn_type']);
      }
    }
    else {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] IPN verification failed");
    }
  }

 public function checkoutSelect($attrs)
 { 
	return $this->paypalCheckout($attrs);  
 }
 public function termsAndCondition($attrs)
 {
 	$view = SimpleEcommCartCommon::getView('views/terms_condition.php', $attrs);
    return $view;
 }
 public function redirectToPreviousPage($attrs)
 {
 	 header("Location: " .$_SERVER['HTTP_REFERER']); 
 }
 
  public function simpleecommcartTests() {
    $view = SimpleEcommCartCommon::getView('tests/tests.php');
    $view = "<pre>$view</pre>";
    return $view;
  }

  public function clearCart() {
    SimpleEcommCartSession::drop('SimpleEcommCartCart');
  }
  
  public function accountLogin($attrs) {
    $account = new SimpleEcommCartAccount();
    if($accountId = SimpleEcommCartCommon::isLoggedIn()) {
      $account->load($accountId);
    }
    
    $data = array('account' => $account);
    
    // Look for password reset task
    if(isset($_POST['simpleecommcart-task']) && $_POST['simpleecommcart-task'] == 'account-reset') {
      $data['resetResult'] = $account->passwordReset();
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Attempted to reset password: " . $data['resetResult']->message);
    }
    
    // Build the account login view
    $view = SimpleEcommCartCommon::getView('views/account-login.php', $data);
    
    if(isset($_POST['simpleecommcart-task']) && $_POST['simpleecommcart-task'] == 'account-login') {
      if($account->login($_POST['login']['username'], $_POST['login']['password'])) {
        SimpleEcommCartSession::set('SimpleEcommCartAccountId', $account->id);
        
        // Send logged in user to the appropriate page after logging in
        $url = SimpleEcommCartCommon::getCurrentPageUrl();
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account Login: " . print_r($attrs, true));
        if(isset($attrs['url']) && !empty($attrs['url'])) {
          if('stay' != strtolower($attrs['url'])) {
            $url = $attrs['url'];
          }
        }
        else {
          // Locate logged in user home page
          $pgs = get_posts('numberposts=1&post_type=any&meta_key=simpleecommcart_member&meta_value=home');
          if(count($pgs)) {
            $url = get_permalink($pgs[0]->ID);
          }
        }
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Redirecting after login to: $url");
        wp_redirect($url);
        exit();
      }
      else {
        $view .= "<p class='SimpleEcommCartError'>Login failed</p>";
      }
    }
    
    return $view;
  }
  
  /**
   * Unset the SimpleEcommCartAccountId from the session and redirect to $attr['url'] if the url attribute is provided.
   * If no redirect url is provided, look for the page with the custom field simpleecommcart_member=logout
   * If no custom field is set then redirect to the current page after logging out
   */
  public function accountLogout($attrs) {
    $url = SimpleEcommCartCommon::getCurrentPageUrl();
    if(isset($attrs['url']) && !empty($attrs['url'])) {
      $url = $attrs['url'];
    }
    else {
      $url = SimpleEcommCartProCommon::getLogoutUrl();
    }
    SimpleEcommCartAccount::logout($url);
  }
  
  public function accountLogoutLink($attrs) {
    $url = SimpleEcommCartCommon::replaceQueryString('simpleecommcart-task=logout');
    $linkText = isset($attrs['text']) ? $attrs['text'] : 'Log out';
    $link = "<a href='$url'>$linkText</a>";
    return $link;
  }
  
  /**
   * Return the Spreedly url to manage the subscription or the
   * PayPal url to cancel the subscription. 
   * If the visitor is not logged in, return false.
   * You can pass in text for the link and a custom return URL
   * 
   * $attr = array(
   *   text => 'The link text for the subscription management link'
   *   return => 'Customize the return url for the spreedly page'
   * )
   * 
   * @return string Spreedly subscription management URL
   */
  public function accountInfo($attrs) {
    if(SimpleEcommCartCommon::isLoggedIn()) {
      $data = array();
      $account = new SimpleEcommCartAccount(SimpleEcommCartSession::get('SimpleEcommCartAccountId'));
      if(isset($_POST['simpleecommcart-task']) && $_POST['simpleecommcart-task'] == 'account-update') {
        $login = $_POST['login'];
        if($login['password'] == $login['password2']) {
          $account->firstName = $login['first_name'];
          $account->lastName = $login['last_name'];
          $account->email = $login['email'];
          $account->password = empty($login['password']) ? $account->password : md5($login['password']);
          $account->username = $login['username'];
          $errors = $account->validate();
          if(count($errors) == 0) {
            $account->save();
            if($account->isSpreedlyAccount()) {
              SpreedlySubscriber::updateRemoteAccount($account->id, array('email' => $account->email));
            }
            $data['message'] = 'Your account is updated';
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account was updated: " . print_r($account->getData, true));
          }
          else {
            $data['errors'] = $account->getErrors();
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Account validation failed: " . print_r($data['errors'], true));
          }
        }
        else {
          $data['errors'] = "Account not updated. The passwords entered did not match";
        }
      }
      
      $data['account'] = $account;
      $data['url'] = false;
      
      if($account->isSpreedlyAccount()) {
        $accountSub = $account->getCurrentAccountSubscription();
        $text = isset($attrs['text']) ? $attrs['text'] : 'Manage your subscription.';
        $returnUrl = isset($attrs['return']) ? $attrs['return'] : null;
        $url = $accountSub->getSubscriptionManagementLink($returnUrl);
        $data['url'] = $url;
        $data['text'] = $text;
      }
      
      $view = SimpleEcommCartCommon::getView('views/account-info.php', $data);
      return $view;
    }
    else {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to view account subscription short code but account holder is not logged into SimpleEcommCart.");
    }
  }
  
  public function cancelPayPalSubscription($attrs) {
    $link = '';
    if(SimpleEcommCartCommon::isLoggedIn()) {
      $account = new SimpleEcommCartAccount(SimpleEcommCartSession::get('SimpleEcommCartAccountId'));
      if($account->isPayPalAccount()) {
        
        // Look for account cancelation request
        if(isset($_GET['simpleecommcart-task']) && $_GET['simpleecommcart-task'] == 'CancelRecurringPaymentsProfile') {
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Caught task: CancelPaymentsProfileStatus");
          $sub = new SimpleEcommCartAccountSubscription($account->getCurrentAccountSubscriptionId());
          $profileId = $sub->paypalBillingProfileId;
          $note = "Your subscription has been canceled per your request.";
          $action = "Cancel";
          $pp = new SimpleEcommCartPayPalPro();
          $pp->ManageRecurringPaymentsProfileStatus($profileId, $action, $note);
          $url = str_replace('simpleecommcart-task=CancelRecurringPaymentsProfile', '', SimpleEcommCartCommon::getCurrentPageUrl());
          $link = "We sent a cancelation request to PayPal. It may take a minute or two for the cancelation process to complete and for your account status to be changed.";
        }
        elseif($subId = $account->getCurrentAccountSubscriptionId()) {
          $sub = new SimpleEcommCartAccountSubscription($subId);
          if($sub->status == 'active') {
            $url = $sub->getSubscriptionManagementLink();
            $text = isset($attrs['text']) ? $attrs['text'] : 'Cancel your subscription';
            $link = "<a id='SimpleEcommCartCancelPayPalSubscription' href=\"$url\">$text</a>";
          }
          else {
            $link = "Your account is $sub->status but will remain active until " . date('m/d/Y', strtotime($sub->activeUntil));
          }
        }
        
        
        
      }
    }
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Cancel paypal account link for logged in user: $link");
    
    return $link;
  }
  
  public function currentSubscriptionPlanName() {
    $name = 'You do not have an active subscription';
    if(SimpleEcommCartCommon::isLoggedIn()) {
      $account = new SimpleEcommCartAccount(SimpleEcommCartSession::get('SimpleEcommCartAccountId'));
      if($subId = $account->getCurrentAccountSubscriptionId()) {
        $sub = new SimpleEcommCartAccountSubscription($subId);
        $name = $sub->subscriptionPlanName;
      }
    }
    return $name;
  }
  
  public function currentSubscriptionFeatureLevel() {
    $level = 'No access';
    if(SimpleEcommCartCommon::isLoggedIn()) {
      $account = new SimpleEcommCartAccount(SimpleEcommCartSession::get('SimpleEcommCartAccountId'));
      if($subId = $account->getCurrentAccountSubscriptionId()) {
        $sub = new SimpleEcommCartAccountSubscription($subId);
        $level = $sub->featureLevel;
      }
    }
    return $level;
  }

  public function spreedlyListener() {
    if(isset($_POST['subscriber_ids'])) {
      $ids = explode(',', $_POST['subscriber_ids']);
      foreach($ids as $id) {
        try {
          $subscriber = SpreedlySubscriber::find($id);
          $subscriber->updateLocalAccount();
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Updated local account id: $id");
        }
        catch(SpreedlyException $e) {
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] I heard that subscriber $id was changed but I can't do anything about it. " . $e->getMessage());
        }
        
      }
    }
    else {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] This is not a valid call to the spreedly listener.");
    }
    
    ob_clean();
    header('HTTP/1.1 200 OK');
    die();
  }
  
  public function showTo($attrs, $content='null') {
    $isAllowed = false;
    if(SimpleEcommCartCommon::isLoggedIn()) {
      $account = new SimpleEcommCartAccount();
      if($account->load(SimpleEcommCartSession::get('SimpleEcommCartAccountId'))) {
        if($account->isActive() && in_array($account->getFeatureLevel(), explode(',', $attrs['level']))) {
          $isAllowed = true;
        }
      }
    }
    $content = $isAllowed ? $content : '';

    return do_shortcode($content);
  }
  
  
  public function hideFrom($attrs, $content='null') {
    $isAllowed = true;
    if(SimpleEcommCartCommon::isLoggedIn()) {
      $account = new SimpleEcommCartAccount();
      if($account->load(SimpleEcommCartSession::get('SimpleEcommCartAccountId'))) {
        if($account->isActive() && in_array($account->getFeatureLevel(), explode(',', $attrs['level']))) {
          $isAllowed = false;
        }
      }
    }
    $content = $isAllowed ? $content : '';

    return do_shortcode($content);
  }
  
  public function gravityFormToCart($entry) {
    if(SIMPLEECOMMCART_PRO) {
      $formId = SimpleEcommCartGravityReader::getGravityFormIdForEntry($entry['id']);
      if($formId) {
        $productId = SimpleEcommCartProduct::getProductIdByGravityFormId($formId);
        if($productId > 0) {
          $product = new SimpleEcommCartProduct($productId);
          $qty = $product->gravityCheckForEntryQuantity($entry);
          $options = $product->gravityGetVariationPrices($entry);
          SimpleEcommCartSession::get('SimpleEcommCartCart')->addItem($productId, $qty, $options, $entry['id']);
          $cartPage = get_page_by_path('store/cart');
          $cartPageLink = get_permalink($cartPage->ID);
          SimpleEcommCartSession::set('SimpleEcommCartLastPage', $_SERVER['HTTP_REFERER']);
          wp_redirect($cartPageLink);
          exit;
        }
      }
    }
  }
  
  public function zendeskRemoteLogin() {
    if(SimpleEcommCartCommon::isLoggedIn() && isset($_GET['timestamp'])) {
      $account = new SimpleEcommCartAccount(SimpleEcommCartSession::get('SimpleEcommCartAccountId'));
      if($account) {
        ZendeskRemoteAuth::login($account);
      }
    }
  }
  
  public function downloadFile($attrs) {
    $link = false;
    if(isset($attrs['path'])) {
      $path = urlencode($attrs['path']);
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] encoded $path");
      $nvp = 'task=member_download&path=' . $path;
      $url = SimpleEcommCartCommon::replaceQueryString($nvp);
      
      if(SimpleEcommCartCommon::isLoggedIn()) {
        $link = '<a class="SimpleEcommCartDownloadFile" href="' . $url . '">' . $attrs['text'] . '</a>';
      }
      else {
        $link = $attrs['text'];
      }
    }
    return $link;
  }
  
  public function termsOfService($attrs) {
    if(SIMPLEECOMMCART_PRO) {
      $attrs = array("location"=>"SimpleEcommCartShortcodeTOS");
      $view = SimpleEcommCartCommon::getView('/advanced/views/terms.php', $attrs);
      return $view;
    }
  }
  
  protected function _buildCheckoutView($gateway) {
    $ssl = SimpleEcommCartSetting::getValue('auth_force_ssl');
    if($ssl == 'yes') {
      if(!SimpleEcommCartCommon::isHttps()) {
        $sslUrl = "https://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        wp_redirect($sslUrl);
        exit();
      }
    }
    
    if(!SimpleEcommCartSession::get('SimpleEcommCartCart')->requirePayment()) {
      require_once(SIMPLEECOMMCART_PATH . "/payment_gateways/SimpleEcommCartManualGateway.php");
      $gateway = new SimpleEcommCartManualGateway();
    }
    
    $view = SimpleEcommCartCommon::getView('views/checkout.php', array('gateway' => $gateway));
    return $view;
  } 
 public function storeHome($attrs)
 {
 	  $view = SimpleEcommCartCommon::getView('views/store-home.php', $attrs);
      return $view;
 }
 
 public function storeCategory($attrs)
 { 
 	 $view = SimpleEcommCartCommon::getView('views/store-category.php', $attrs);
     return $view;
 }
}
