<?php
class SimpleEcommCartCheckoutThrottle {
  
  /**
   * The last four digits of the credit card that was to be charged
   * @var integer|string
   */
  private $_digits;
  
  /**
   * The total amount of the charge
   * @var decimal
   */
  private $_amount;
  
  /**
   * The unix timestamp of when the charge was attempted. 
   * @var integer
   */
  private $_time;
  
  /**
   * The time remaining until another checkout attempt can be made
   * @var int
   */
  private $_timeRemaing;
  
  /**
   * The instance of this singleton class
   * @var SimpleEcommCartCheckoutThrottle
   */
  private static $_instance = false;
  
  
  private function __construct() {}
  
  
  protected function logCheckout($digits, $amount) {
    $this->_digits = $digits;
    $this->_amount = $amount;
    $this->_time = time();
  }
  
  
  public static function getInstance() {
    if(self::$_instance === false) {
      self::$_instance = new SimpleEcommCartCheckoutThrottle;
    }
    return self::$_instance;
  }
  
  
  /**
   * Return true if it has been more than 10 seconds since the last checkout attempt using the same billing information
   * 
   * @param int The last four digits of the credit card being charged
   * @param decimal The amount being charged
   * @param int (optoinal) The minimum number of seconds between checkout attempts
   * @return boolean
   */
  public function isReady($digits, $amount, $throttle=10) {
    $isReady = false;
    $diff = time() - $this->_time;
    if($diff > $throttle) {
      $this->logCheckout($digits, $amount);
      $isReady = true;
      $this->_timeRemaing = 0;
    }
    else {
      $this->_timeRemaing = $throttle - $diff;
    }
    return $isReady;
  }
  
  public function getTimeRemaining() {
    return $this->_timeRemaing;
  }
  
}