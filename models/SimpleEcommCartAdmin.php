<?php

class SimpleEcommCartAdmin {
  
  public function productsPage() {
    $data = array();
    $subscriptions = array('0' => 'None');
    
    if(class_exists('SpreedlySubscription')) {
      $spreedlySubscriptions = SpreedlySubscription::getSubscriptions();
      foreach($spreedlySubscriptions as $s) {
        $subs[(int)$s->id] = (string)$s->name;
      }
      if(count($subs)) {
        asort($subs);
        foreach($subs as $id => $name) {
          $subscriptions[$id] = $name;
        }
      }
    }
    else {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not loading Spreedly data because Spreedly class has not been loaded");
    }
    
    if(class_exists('SimpleEcommCartPayPalSubscription')) {
      $ppsub = new SimpleEcommCartPayPalSubscription();
      $data['ppsubs'] = $ppsub->getModels('where id>0', 'order by name');
    }
    
    $data['subscriptions'] = $subscriptions;
    $view = SimpleEcommCartCommon::getView('admin/products.php', $data);
    echo $view; 
  }
  
  public function settingsPage() {
    $view = SimpleEcommCartCommon::getView('admin/settings.php');
    echo $view;
  }
  
  public function ordersPage() {
    if($_SERVER['REQUEST_METHOD'] == 'GET' && SimpleEcommCartCommon::getVal('task') == 'delete') {
      $order = new SimpleEcommCartOrder($_GET['id']);
      $order->deleteMe();
      $view = SimpleEcommCartCommon::getView('admin/orders.php'); 
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && SimpleEcommCartCommon::postVal('task') == 'update order status') {
      $order = new SimpleEcommCartOrder($_POST['order_id']);
      $order->updateStatus(SimpleEcommCartCommon::postVal('status'));
      $view = SimpleEcommCartCommon::getView('admin/orders.php');
    } 
	elseif($_SERVER['REQUEST_METHOD'] == 'POST' && SimpleEcommCartCommon::postVal('task') == 'update delivery status') {
      $order = new SimpleEcommCartOrder($_POST['order_id']);
      $order->updateDeliveryStatus(SimpleEcommCartCommon::postVal('delivery_status'));
	  
	  if(SimpleEcommCartCommon::postVal('delivery_status')=='Pending')
	  {
	  	 SimpleEcommCartCommon::sendEmailOnPending($_POST['order_id']);
	  }
	  else if(SimpleEcommCartCommon::postVal('delivery_status')=='Shipped')
	  {
	  	 SimpleEcommCartCommon::sendEmailOnShipped($_POST['order_id']);
	  }
      //$view = SimpleEcommCartCommon::getView('admin/orders.php');
	  
	  //$order = new SimpleEcommCartOrder($_POST['order_id']);
      $order->updatePaymentStatus(SimpleEcommCartCommon::postVal('payment_status'));
      
	  if(SimpleEcommCartCommon::postVal('payment_status')=='Refund')
	  {
	  	 SimpleEcommCartCommon::sendEmailOnRefund($_POST['order_id']);
	  }
	  else if(SimpleEcommCartCommon::postVal('payment_status')=='Complete')
	  {
	  	 SimpleEcommCartCommon::sendEmailOnPurchase($_POST['order_id']);
	  }
	 
	  $view = SimpleEcommCartCommon::getView('admin/orders.php');
    } 
	elseif($_SERVER['REQUEST_METHOD'] == 'POST' && SimpleEcommCartCommon::postVal('task') == 'update payment status') {
     
    }
    else {
      $view = SimpleEcommCartCommon::getView('admin/orders.php'); 
    }

    echo $view;
  }

  public function inventoryPage() {
    $view = SimpleEcommCartCommon::getView('admin/inventory.php');
    echo $view; 
  }

  public function promotionsPage() {
    $view = SimpleEcommCartCommon::getView('admin/promotions.php');
    echo $view;
  }

  public function shippingPage() {
    $view = SimpleEcommCartCommon::getView('admin/shipping.php');
    echo $view;
  }


  public function reportsPage() {
    $view = SimpleEcommCartCommon::getView('admin/reports.php');
    echo $view;
  }
  
  public function SimpleEcommCartHelp() {
    $setting = new SimpleEcommCartSetting();
    define('HELP_URL', "http://simpleecommcartbasic.wordpress.com//simpleecommcart-help/?order_number=".SimpleEcommCartSetting::getValue('order_number'));
    $view = SimpleEcommCartCommon::getView('admin/help.php');
    echo $view;
  }
  
  public function paypalSubscriptions() {
    $data = array();
    if(SIMPLEECOMMCART_PRO) {
      $sub = new SimpleEcommCartPayPalSubscription();
      $data['subscription'] = $sub;

      if($_SERVER['REQUEST_METHOD'] == 'POST' && SimpleEcommCartCommon::postVal('simpleecommcart-action') == 'save paypal subscription') {
        $subData = SimpleEcommCartCommon::postVal('subscription');
        $sub->setData($subData);
        $errors = $sub->validate();
        if(count($errors) == 0) {
          $sub->save();
          $sub->clear();
          $data['subscription'] = $sub;
        }
        else {
          $data['errors'] = $sub->getErrors();
          $data['jqErrors'] = $sub->getJqErrors();
        }
      }
      else {
        if(SimpleEcommCartCommon::getVal('task') == 'edit' && isset($_GET['id'])) {
          $sub->load(SimpleEcommCartCommon::getVal('id'));
          $data['subscription'] = $sub;
        }
        elseif(SimpleEcommCartCommon::getVal('task') == 'delete' && isset($_GET['id'])) {
          $sub->load(SimpleEcommCartCommon::getVal('id'));
          $sub->deleteMe();
          $sub->clear();
          $data['subscription'] = $sub;
        }
      }

      $data['plans'] = $sub->getModels('where is_paypal_subscription>0', 'order by name');
      $view = SimpleEcommCartCommon::getView('pro/admin/paypal-subscriptions.php', $data);
      echo $view;
    }
    else {
      echo '<h2>PayPal Subscriptions</h2><p class="description">This feature is only available in <a href="http://simpleecommcart.com">SimpleEcommCart Professional</a>.</p>';
    }
    
  }
  
  public function accountsPage() {
    $data = array();
    if(SIMPLEECOMMCART_PRO) {
      $data['plan'] = new SimpleEcommCartAccountSubscription();
      $data['activeUntil'] = '';
      $account = new SimpleEcommCartAccount();

      if(isset($_REQUEST['simpleecommcart-action']) && $_REQUEST['simpleecommcart-action'] == 'delete_account') {
        // Look for delete request
        if(isset($_REQUEST['accountId']) && is_numeric($_REQUEST['accountId'])) {
          $account = new SimpleEcommCartAccount($_REQUEST['accountId']);
          $account->deleteMe();
          $account->clear();
        }
      }
      elseif(isset($_REQUEST['accountId']) && is_numeric($_REQUEST['accountId'])) {
        // Look in query string for account id
        $account = new SimpleEcommCartAccount();
        $account->load($_REQUEST['accountId']);
        $data['plan'] = $account->getCurrentAccountSubscription(true); // Return even if plan is expired
        if(date('Y', strtotime($data['plan']->activeUntil)) <= 1970) {
          $data['activeUntil'] = '';
        }
        else {
          $data['activeUntil'] = date('m/d/Y', strtotime($data['plan']->activeUntil));
        }
        
      }

      if($_SERVER['REQUEST_METHOD'] == 'POST' && SimpleEcommCartCommon::postVal('simpleecommcart-action') == 'save account') {
        $acctData = $_POST['account'];

        // Format or unset password
        if(empty($acctData['password'])) {
          unset($acctData['password']);
        }
        else {
          $acctData['password'] = md5($acctData['password']);
        }

        // Strip HTML tags on notes field
        $acctData['notes'] = strip_tags($acctData['notes'], '<a><strong><em>');

        $planData = $_POST['plan'];
        $planData['active_until'] = date('Y-m-d 00:00:00', strtotime($planData['active_until']));

        // Updating an existing account
        if($acctData['id'] > 0) {
          $account = new SimpleEcommCartAccount($acctData['id']);
          $account->setData($acctData);
          $errors = $account->validate();

          $sub = new SimpleEcommCartAccountSubscription($planData['id']);
          $sub->setData($planData);

          if(count($errors) == 0) {
            $account->save();
            $sub->save();
            $account->clear();
            $sub->clear();
          }
          else {
            $data['errors'] = $errors;
            $data['plan'] = $sub;
            $data['activeUntil'] = date('m/d/Y', strtotime($sub->activeUntil));
          }
        }
        else {
          // Creating a new account
          $account = new SimpleEcommCartAccount();
          $account->setData($acctData);
          $errors = $account->validate();
          if(count($errors) == 0) {
            $account->save();
            $sub = new SimpleEcommCartAccountSubscription();
            $planData['account_id'] = $account->id;
            $sub->setData($planData);
            $sub->billingFirstName = $account->firstName;
            $sub->billingLastName = $account->lastName;
            $sub->billingInterval = 'Manual';
            $sub->save();
            $account->clear();
          }
        }

      }

      $data['url'] = SimpleEcommCartCommon::replaceQueryString('page=simpleecommcart-accounts');
      $data['account'] = $account;
      $data['accounts'] = $account->getModels('where id>0', 'order by last_name');
    }
    
    
    $view = SimpleEcommCartCommon::getView('admin/accounts.php', $data);
    echo $view;
  }

  public function taxPage() {
    $view = SimpleEcommCartCommon::getView('admin/tax.php');
    echo $view;
  }
}