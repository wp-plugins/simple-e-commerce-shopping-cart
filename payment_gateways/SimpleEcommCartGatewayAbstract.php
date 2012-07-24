<?php
abstract class SimpleEcommCartGatewayAbstract {

  protected $_errors;
  protected $_jqErrors;
  protected $_billing;
  protected $_shipping;
  protected $_payment;
  protected $_taxRate;

  /**
   * Return an array of available credit card types where the keys are the display values and the values 
   * are the submitted values.
   * 
   * @return array
   */
  public abstract function getCreditCardTypes();
  
  /**
   * Prepare the gateway to process a transaction.
   * Perhaps api credentials need to be set or other pre-sales setup
   * 
   * @param decimal Total amount to charge to credit card
   */
  public abstract function initCheckout($total);
  
  /**
   * Attempt to process a sales
   */
  public abstract function doSale();
  
  /**
   * Return a description explaining the results from an attempt to process a transaction through a gateway.
   * This information may be the reason for a transaction decline or a transaction failure
   */
  public abstract function getTransactionResponseDescription();
  
  public function __construct() {
    $this->_billing = array(
      'firstName' => '',
      'lastName' => '',
      'address' => '',
      'address2' => '',
      'city' => '',
      'state' => '',
      'zip' => '',
      'country' => ''
    );
    
    $this->_payment = array(
      'cardType' => '',
      'cardNumber' => '',
      'cardExpirationMonth' => '',
      'cardExpirationYear' => '',
      'securityId' => '',
      'phone' => '',
      'email' => '',
      'password' => '',
      'password2' => ''
    );
    
    $this->_shipping = array(
      'firstName' => '',
      'lastName' => '',
      'address' => '',
      'address2' => '',
      'city' => '',
      'state' => '',
      'zip' => '',
      'country' => ''
    );
  }
  
  public function getErrors() {
    if(!is_array($this->_errors)) {
      $this->_errors = array();
    }
    return $this->_errors;
  }

  public function getJqErrors() {
    if(!is_array($this->_jqErrors)) {
      $this->_jqErrors = array();
    }
    return $this->_jqErrors;
  }

  public function clearErrors() {
    $this->_errors = array();
    $this->_jqErrors = array();
  }

  public function setBilling($b) {
    if(!(isset($b['state']) && !empty($b['state']))) {
      //$b['state'] = trim($b['state_text']);
    }
    unset($b['state_text']);
    
    $this->_billing = $b;
    $skip = array('address2', 'billing-state_text');
    foreach($b as $key => $value) {
      if(!in_array($key, $skip)) {
        $value = trim($value);
        if($value == '') {
          $keyName = ucwords(preg_replace('/([A-Z])/', " $1", $key));
          $this->_errors['Billing ' . $keyName] = __('Billing ','simpleecommcart') . $keyName . __(' required','simpleecommcart');
          $this->_jqErrors[] = "billing-$key";
        }
      }
    }
  } 

  public function setPayment($p) {
    
    // Remove all non-numeric characters from card number
    if(isset($p['cardNumber'])) {
      $cardNumber = $p['cardNumber'];
      $p['cardNumber'] = preg_replace('/\D/', '', $cardNumber);
    }
    
    $this->_payment = $p;
    
    foreach($p as $key => $value) {
      $value = trim($value);
      if($value == '') {
        $keyName = preg_replace('/([A-Z])/', " $1", $key);
        $this->_errors['Payment ' . $keyName] = __('Payment ','simpleecommcart') . $keyName . __(' required','simpleecommcart');
        $this->_jqErrors[] = "payment-$key";
      }
    }
    if(strlen($p['cardNumber']) > 0 && strlen($p['cardNumber']) < 13) {
      $this->_errors['Payment Card Number'] = __('Invalid credit card number','simpleecommcart');
      $this->_jqErrors[] = "payment-cardNumber";
    } 

    // For subscription accounts
    if(isset($p['password'])) {
      if($p['password'] != $p['password2']) {
        $this->_errors['Password'] = __("Passwords do not match","simpleecommcart");
        $this->_jqErrors[] = 'payment-password';
      }
    }
  }

  public function setShipping($s) {
    if(!(isset($s['state']) && !empty($s['state']))) {
      //$s['state'] = trim($s['state_text']);
    }
    unset($s['state_text']);
    
    $this->_shipping = $s;
    $skip = array('address2', 'shipping-state_text');
    foreach($s as $key => $value) {
      if(!in_array($key, $skip)) {
        $value = trim($value);
        if($value == '') {
          $keyName = preg_replace('/([A-Z])/', " $1", $key);
          $this->_errors['Shipping ' . $keyName] = __('Shipping ','simpleecommcart') . $keyName . __(' required','simpleecommcart');
          $this->_jqErrors[] = "shipping-$key";
        }
      }
    }
  }

  public function getShipping() {
    return count($this->_shipping) ? $this->_shipping : $this->_billing;
  }

  public function getBilling() {
    return $this->_billing;
  }

  public function getPayment() {
    return $this->_payment;
  }

  /**
   * Return and array with state and zip of shipping location
   * array('state' => 'XX', 'zip' => 'YYYYY');
   *
   * @return array
   */
  public function getTaxLocation() {
    $ship = $this->getShipping();
    $taxLocation = array (
      'state' => $ship['state'],
      'zip' => $ship['zip']
    );
    return $taxLocation;
  }
  
  /**
   * Return the last $lenght digits of the credit card number or false 
   * if the number is not set or is shorter than the requested length.
   * 
   * @param int (optional) The number of digits to return
   * @return int | false
   */
  public function getCardNumberTail($length=4) {
    $tail = false;
    if(isset($this->_payment['cardNumber']) && strlen($this->_payment['cardNumber']) >= $length) {
      $tail = substr($this->_payment['cardNumber'], -1 * $length);
    }
    return $tail;
  }

  /**
   * Return true if the order should be taxed
   *
   * @return boolean
   */
  public function isTaxed() {
    $s = $this->getShipping();
    if(count($s)) {
      $taxRate = new SimpleEcommCartTaxRate();
      $isTaxed = $taxRate->loadByZip($s['zip']);
      if($isTaxed == false) {
        $isTaxed = $taxRate->loadByState($s['state']);
      }
      $this->_taxRate = $taxRate;
      return $isTaxed;
    }
    else {
      throw new Exception(__('Unable to determine tax rate because shipping data is unavailable','simpleecommcart'));
    }
  }

  public function taxShipping() {
    if(!isset($this->_taxRate)) {
      $this->isTaxed();
    }
    $taxShipping = (isset($this->tax_shipping) && $this->tax_shipping == 1) ? true : false;
    return $taxShipping;
  }

  public function getTaxAmount() {
    $tax = 0;
    if($this->isTaxed()) {
      $taxable = SimpleEcommCartSession::get('SimpleEcommCartCart')->getTaxableAmount();
      if($this->taxShipping()) {
        $taxable += SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingCost();
      }
      $tax = number_format($taxable * ($this->_taxRate->rate/100), 2, '.', '');
    }
    return $tax;
  }

  /**
   * Store order in database after successful transaction is processed
   */
  public function saveOrder($total, $tax, $transId, $status, $accountId=0) {
    $address = $this->getShipping();
    $b = $this->getBilling();
    $p = $this->getPayment();

    $orderInfo['ship_first_name'] = $address['firstName'];
    $orderInfo['ship_last_name'] = $address['lastName'];
    $orderInfo['ship_address'] = $address['address'];
    $orderInfo['ship_address2'] = $address['address2'];
    $orderInfo['ship_city'] = $address['city'];
    $orderInfo['ship_state'] = $address['state'];
    $orderInfo['ship_zip'] = $address['zip'];
    $orderInfo['ship_country'] = SimpleEcommCartCommon::getCountryName($address['country']);

    $orderInfo['bill_first_name'] = $b['firstName'];
    $orderInfo['bill_last_name'] = $b['lastName'];
    $orderInfo['bill_address'] = $b['address'];
    $orderInfo['bill_address2'] = $b['address2'];
    $orderInfo['bill_city'] = $b['city'];
    $orderInfo['bill_state'] = $b['state'];
    $orderInfo['bill_zip'] = $b['zip'];
    $orderInfo['bill_country'] = SimpleEcommCartCommon::getCountryName($b['country']);

    $orderInfo['phone'] = preg_replace("/[^0-9]/", "", $p['phone']);
    $orderInfo['email'] = $p['email'];
    $orderInfo['coupon'] = SimpleEcommCartCommon::getPromoMessage();
    $orderInfo['tax'] = $tax;
    $orderInfo['shipping'] = SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingCost();
    $orderInfo['subtotal'] = SimpleEcommCartSession::get('SimpleEcommCartCart')->getSubTotal();
    $orderInfo['total'] = preg_replace("/[^0-9\.]/", "", $total);
    $orderInfo['trans_id'] = $transId;
    $orderInfo['status'] = $status;
    $orderInfo['ordered_on'] = date('Y-m-d H:i:s', SimpleEcommCartCommon::localTs());
    $orderInfo['shipping_method'] = SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingMethodName();
    $orderInfo['account_id'] = $accountId;
    
	$orderInfo['payment_method'] = "Manual";
	
	$orderId = SimpleEcommCartSession::get('SimpleEcommCartCart')->storeOrder($orderInfo);  
    return $orderId;
  }
  
  /**
   * Make sure there is at least one product in the cart.
   * Return true if the cart is valid, otherwise false.
   * 
   * @return boolean
   */
  public function validateCartForCheckout() {
    $isValid = true;
    $itemCount = SimpleEcommCartSession::get('SimpleEcommCartCart')->countItems();
    if($itemCount < 1) {
      $this->_errors['Invalid Cart'] = __('There must be at least one item in the cart.','simpleecommcart');
      $isValid = false;
    }
    return $isValid;
  }
  
  
}
