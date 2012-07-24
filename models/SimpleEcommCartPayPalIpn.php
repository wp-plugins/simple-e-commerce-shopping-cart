<?php
class SimpleEcommCartPayPalIpn {
  
  /**
   * Validate the PayPal IPN by posting back the data sent from PayPal.
   * 
   * Create a request that contains exactly the same IPN variables and values in the 
   * same order, preceded with cmd=_notify-validate.
   */
  public function validate($rawPost) {
    $isValid = false;
    SimpleEcommCartCommon::log("Validate PayPal Post Data: \n" . print_r($rawPost, true));
    
    // Looking for local test IPNs
    if($rawPost['test_ipn'] == '66') {
      $isValid = true;
    }
    else {
      $postdata = '';
      foreach($rawPost as $i => $v) {
      	$postdata .= $i.'='.urlencode(stripslashes($v)).'&';
      }
      $postdata .= 'cmd=_notify-validate';
      SimpleEcommCartCommon::log("PayPal Validation Post Back: $postdata");

      $web = parse_url(SimpleEcommCartCommon::getPayPalUrl());
      if ($web['scheme'] == 'https') { 
      	$web['port'] = 443;  
      	$ssl = 'ssl://'; 
      } 
      else { 
      	$web['port'] = 80;
      	$ssl = ''; 
      }
      $fp = @fsockopen($ssl.$web['host'], $web['port'], $errnum, $errstr, 30);

      if(!$fp) { 
      	$socketError =  "Socket error --> " . $ssl . $web['host'] . ' -- ' . $web['port'] . ' -- ' . $errnum .' -- ' . $errstr . ' -- 30';
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PayPal IPN Validation Socket Error: $socketError");
        echo "IPN Socket Failure: $socketError";
      } 
      else {
      	fputs($fp, "POST ".$web['path']." HTTP/1.1\r\n");
      	fputs($fp, "Host: ".$web['host']."\r\n");
      	fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
      	fputs($fp, "Content-length: ".strlen($postdata)."\r\n");
      	fputs($fp, "Connection: close\r\n\r\n");
      	fputs($fp, $postdata . "\r\n\r\n");

      	while(!feof($fp)) { 
      		$info[] = @fgets($fp, 1024); 
      	}
      	fclose($fp);
      	$infoString = implode(',', $info);

      	if (eregi('VERIFIED', $infoString)) {
      		// PayPal Verification Success'
      		$isValid = true;
      		SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PayPal IPN Validation succeeded: $infoString");
      	} 
      	else {
      		// PayPal Verification Failed
      		SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PayPal Validation failed: $infoString");
      	}
      }
    }
    
    return $isValid;
  }
  
  public function saveCartOrder($rawPost) {
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Calling SimpleEcommCartPayPalIpn::saveCartOrder: " . print_r($rawPost, true));
    if($rawPost['mc_gross'] >= 0) {
		  $decodedPost = $this->_decodeRawPost($rawPost);
		  $paypal = new SimpleEcommCartPayPalStandard();
		  SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] About to hit SimpleEcommCartPayPalStandard::saveOrder");
		  $paypal->saveOrder($decodedPost);
		}
  }
  
  /**
   * Set active flag to zero and status to canceled.
   * The active_until date is not changed. Therefore, a canceled subscription
   * will remain active until the amount of time paid for has expired.
   * 
   * @param array $rawPost The IPN post data from PayPal
   * @return int or false The id of the subscription that was canceled or false if cancelation failed
   */
  public function cancelSubscription($rawPost) {
    $canceledId = false;
    $decodedPost = $this->_decodeRawPost($rawPost);
    $subId = $decodedPost['recurring_payment_id'];
    $sub = new SimpleEcommCartAccountSubscription();
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] IPN request to cancel subscription $subId");
    if($sub->loadByPayPalBillingProfileId($subId)) {
      $sub->active = 0;
      $sub->status = 'canceled';
      $sub->save();
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Canceled subscription $subId via IPN request.");
      $canceledId = $sub->id;
    }
    return $canceledId;
  }
  
  public function suspendSubscription() {
    
  }
  
  public function expireSubscription() {
    
  }
  
  /**
   * recurring_payment_id=I-S5CFTV70NRTH
   */
  public function logRecurringPayment($data) {
    $decodedPost = $this->_decodeRawPost($data);
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Handling IPN request for logging recurring payment: " . print_r($decodedPost, true));
    $payment = new SimpleEcommCartPayPalRecurringPayment();
    $payment->log($decodedPost);
  }
  
  /**
   * URL decode the raw post and return and array of key/value pairs
   * 
   * @return array
   */
  protected function _decodeRawPost(array $rawPost) {
    foreach($rawPost as $key => $val) {
	    $decodedPost[$key] = stripslashes(urldecode($val));
	  }
	  return $decodedPost;
  }
  
}