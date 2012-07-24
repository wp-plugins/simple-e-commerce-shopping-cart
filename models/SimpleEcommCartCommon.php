<?php
class SimpleEcommCartCommon {

 public static function isDateValid($str)
 {
  $stamp = strtotime($str);
  if (!is_numeric($stamp))
     return FALSE;
 
  //checkdate(month, day, year)
  if ( checkdate(date('m', $stamp), date('d', $stamp), date('Y', $stamp)) )
  {
     return TRUE;
  }
  return FALSE;
 }
  /**
   * Return the string to use as the input id while keeping track of 
   * how many times a product is rendered to make sure there are no 
   * conflicting input ids.
   *
   * @param int $id - The databse id for the product
   * @return string
   */
  public static function getButtonId($id) {
    global $simpleecommcartCartButtons;

    $idSuffix = '';

    if(!is_array($simpleecommcartCartButtons)) {
      $simpleecommcartCartButtons = array();
    }

    if(in_array($id, array_keys($simpleecommcartCartButtons))) {
      $simpleecommcartCartButtons[$id] += 1;
    }
    else {
      $simpleecommcartCartButtons[$id] = 1;
    }

    if($simpleecommcartCartButtons[$id] > 1) {
      $idSuffix = '_' . $simpleecommcartCartButtons[$id];
    }

    $id .= $idSuffix;

    return $id;
  }
 
  /**
   * Strip all non numeric characters, then format the phone number.
   * 
   * Phone numbers are formatted as follows:
   *  7 digit phone numbers: 266-1789
   *  10 digit phone numbers: (804) 266-1789
   * 
   * @return string
   */
  public static function formatPhone($phone) {
  	$phone = preg_replace("/[^0-9]/", "", $phone);
  	if(strlen($phone) == 7)
  		return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
  	elseif(strlen($phone) == 10)
  		return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
  	else
  		return $phone;
  }

  public function isRegistered() {
    $setting = new SimpleEcommCartSetting();
    $orderNumber = SimpleEcommCartSetting::getValue('order_number');
    $isRegistered = ($orderNumber !== false) ? true : false;
    return $isRegistered;
  }
  
  public static function activePromotions() {
    $active = false;
    //$promo = new SimpleEcommCartPromotion();
    //if($promo->getOne()) {
      //$active = true;
    //}
	if(SimpleEcommCartSetting::getValue('use_coupons_on_checkout') == 1)
	{
		$active = true;
	}
	
    return $active;
  }
  
  public static function showValue($value) {
    echo isset($value)? $value : '';
  }
  
  public static function getView($filename, $data=null) {

    $unregistered = '';
    if(strpos($filename, 'admin') !== false) {
      if(SIMPLEECOMMCART_PRO && !self::isRegistered()) {
        $hardCoded = '';
        $settingsUrl = get_bloginfo('wpurl') . '/wp-admin/admin.php?page=simpleecommcart-settings';
        if(SIMPLEECOMMCART_ORDER_NUMBER !== false) {
          $hardCoded = "<br/><br/><em>An invalid order number has be hard coded<br/> into the main simpleecommcart.php file.</em>";
        }
        $unregistered = '
          <div class="unregistered">
            This is not a registered copy of SimpleEcommCart.<br/>
            Please <a href="' . $settingsUrl . '">enter your order number</a> or
            <a href="http://simpleecommcartbasic.wordpress.com//pricing">buy a license for your site.</a> ' . $hardCoded . '
          </div>
        ';
      }
    }

    $customView = false;
    $themeDirectory = get_stylesheet_directory();
    $approvedOverrideFiles = array(
                                   "views/cart.php",
                                   "views/cart-button.php",
                                   "views/account-login.php",
                                   "views/checkout-form.php",
                                   "views/cart-sidebar.php",
                                   "views/cart-sidebar-advanced.php",
                                   "views/receipt.php",
                                   "views/receipt_print_version.php",
                                   "pro/views/terms.php"
                             );
    $overrideDirectory = $themeDirectory."/simpleecommcart-templates";
    $userViewFile = $overrideDirectory."/$filename";
    
    //SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Override: $overrideDirectory\nUser view file: $userViewFile");
    
    if(file_exists($userViewFile) && in_array($filename,$approvedOverrideFiles)) {
      // File exists, make sure it's not empty
      if(filesize($userViewFile)>10) {
        // It's not empty
        $customView = true;
        $customViewPath = $userViewFile;
      }
      else{
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] User file was empty: $userViewFile");
      }
    }
    else{
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] File exists: ".var_export(file_exists($userViewFile),true)."\n");
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Approved Override: ".var_export(in_array($filename,$approvedOverrideFiles),true));
    }
  
    // Check for override and confirm we have a registered plugin
    if($customView && SIMPLEECOMMCART_PRO && self::isRegistered()) {
      // override is present
      $filename = $customViewPath;
    }
    else {
      // no override, render standard view
      $filename = SIMPLEECOMMCART_PATH . "/$filename";
    }
    
    ob_start();
    include $filename;
    $contents = ob_get_contents();
    ob_end_clean();

    return $unregistered . $contents;
  }
  
  public static function getTableName($name, $prefix='simpleecommcart_'){
      global $wpdb;
      return $wpdb->prefix . $prefix . $name;
  }
  
  public static function getTablePrefix(){
      global $wpdb;
      return $wpdb->prefix . "simpleecommcart_";
  }
  
  /**
   * If SIMPLEECOMMCART_DEBUG is defined as true and a log file exists in the root of the SimpleEcommCart plugin directory, log the $data
   */
  public static function log($data) {
    
    if(defined('SIMPLEECOMMCART_DEBUG') && SIMPLEECOMMCART_DEBUG) {
      $tz = '- Server time zone ' . date('T');
      $date = date('m/d/Y g:i:s a', self::localTs());
      $header = strpos($_SERVER['REQUEST_URI'], 'wp-admin') ? "\n\n======= ADMIN REQUEST =======\n[LOG DATE: $date $tz]\n" : "\n\n[LOG DATE: $date $tz]\n";
      $filename = SIMPLEECOMMCART_PATH . "/log.txt"; 
      if(file_exists($filename) && is_writable($filename)) {
        file_put_contents($filename, $header . $data, FILE_APPEND);
      }
    }
    
  }

  public static function getRandNum($numChars = 7) {
    $id = '';
		mt_srand((double)microtime()*1000000);
		for ($i = 0; $i < $numChars; $i++) { 
			$id .= chr(mt_rand(ord(0), ord(9)));
		}
		return $id;
	}
	
	public static function getRandString($length = 14) {
	  $string = '';
    $chrs = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for($i=0; $i<$length; $i++) {
      $loc = mt_rand(0, strlen($chrs)-1);
      $string .= $chrs[$loc];
    }
	  return $string;
	} 
  
  public static function camel2human($val) {
    $val = strtolower(preg_replace('/([A-Z])/', ' $1', $val));
    return $val;
  }
  
  /**
   * Return the account id if the visitor is logged in, otherwise false.
   * This function has nothing to do with feature levels or subscription status
   * 
   * @return int or false
   */
  public static function isLoggedIn() {
    $isLoggedIn = false;
    if(SimpleEcommCartSession::get('SimpleEcommCartAccountId') && is_numeric(SimpleEcommCartSession::get('SimpleEcommCartAccountId')) && SimpleEcommCartSession::get('SimpleEcommCartAccountId') > 0) {
      $isLoggedIn = SimpleEcommCartSession::get('SimpleEcommCartAccountId');
    }
    return $isLoggedIn;
  }

  
  public static function awardCommission($orderId, $referrer) {
    global $wpdb;
    if (!empty($referrer)) {
      $order = new SimpleEcommCartOrder($orderId);
      if($order->id > 0) {
        $subtractAmount = 0;
        $discount = $order->discountAmount;
        foreach($order->getItems() as $item) {
          $price = $item->product_price * $item->quantity;

          if($price > $discount) {
            $subtractAmount = $discount;
            $discount = 0;
          }
          else {
            $subtractAmount = $price;
            $discount = $discount - $price;
          }

          if($subtractAmount > 0) {
            $price = $price - $subtractAmount;
          }
          
          // Transaction if for commission is the id in th order items table
          $txn_id = $item->id;
          $sale_amount = $price;
          $item_id = $item->item_number;
          $buyer_email = $order->email;

          // Make sure commission has not already been granted for this transaction
          $aff_sales_table = $wpdb->prefix . "affiliates_sales_tbl";
          $txnCount = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM $aff_sales_table where txn_id = %s", $txn_id));
          if($txnCount < 1) {
            wp_aff_award_commission($referrer,$sale_amount,$txn_id,$item_id,$buyer_email);
          }
        }
        
      }
    }
  }
  
  /**
   * Return true if the email address is not empty and has a valid format
   * 
   * @param string $email The email address to validate
   * @return boolean Empty or invalid email addresses return false, otherwise true
   */
  public static function isValidEmail($email) {
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Checking to see if email address is valid: $email");
    $pattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}\b/';
    $isValid = false;
    if(!empty($email) && strlen($email) > 3) {
      if(preg_match($pattern, $email)) {
        $isValid = true;
      }
      else {
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Email validation failed because address is invalid: $email");
      }
    }
    else {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Email validation failed because address is empty: $email");
    }
    return $isValid;
  }
  
  public static function isEmailUnique($email, $exceptId=0) {
    global $wpdb;
    $accounts = self::getTableName('accounts');
    $sql = "SELECT count(*) as c from $accounts where email = %s and id != %d";
    $sql = $wpdb->prepare($sql, $email, $exceptId);
    $count = $wpdb->get_var($sql);
    $isUnique = $count == 0;
    return $isUnique;
  }
  
  
  /**
   * Configure mail for use with either standard wp_mail or when using the WP Mail SMTP plugin
   */
  public static function mail($to, $subject, $msg, $headers=null) {
    //Disable mail headers if the WP Mail SMTP plugin is in use.
    //if(function_exists('wp_mail_smtp_activate')) { $headers = null; }
    return wp_mail($to, $subject, $msg, $headers);
  }
  
  /**
   * Send email receipt and copies thereof.
   * Return true if all the emails that were supposed to be sent got sent.
   * Note that just because the email was sent does not mean the receipient received it.
   * All sorts of things can go awry after the email leaves the server before it is in the
   * recipient's inbox. 
   * 
   * @param int $orderId
   * @return bool
   */
  public static function sendEmailReceipts($orderId) {
    $isSent = false;
    $newOrder = new SimpleEcommCartOrder($orderId);
    $msg = self::getEmailReceiptMessage($newOrder);
    $to = $newOrder->email;
    $subject = SimpleEcommCartSetting::getValue('receipt_subject');
    
    $headers = 'From: '. SimpleEcommCartSetting::getValue('receipt_from_name') .' <' . SimpleEcommCartSetting::getValue('receipt_from_address') . '>' . "\r\n\\";
    $msgIntro = SimpleEcommCartSetting::getValue('receipt_intro');    
    
    if($newOrder) {
      $isSent = self::mail($to, $subject, $msg, $headers);
      if(!$isSent) {
        self::log("Mail not sent to: $to");
      }

      $others = SimpleEcommCartSetting::getValue('receipt_copy');
      if($others) {
        $list = explode(',', $others);
        $msg = "THIS IS A COPY OF THE RECEIPT\n\n$msg";
        foreach($list as $e) {
          $e = trim($e);
          $isSent = wp_mail($e, $subject, $msg, $headers);
          if(!$isSent) {
            self::log("Mail not sent to: $e");
          }
          else {
            self::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Receipt also mailed to: $e");
          }
        }
      } 
    }
    return $isSent;
  }
   
  public static function sendEmailOnPurchase($orderId) {
  self::log("inside:sendEmailOnPurchase");
    $isSent = false;
    $newOrder = new SimpleEcommCartOrder($orderId);
    
	if($newOrder->payment_status == 'Pending')
	{
		self::sendEmailOnPending($orderId);
		return;
	}
	else if($newOrder->payment_status == 'Refund')
	{
		self::sendEmailOnRefund($orderId);
		return;
	}

    $to = $newOrder->email;
    $subject = SimpleEcommCartSetting::getValue('email_sent_on_purchase_subject');
    
    $headers = 'From: '. SimpleEcommCartSetting::getValue('email_from_name') .' <' . SimpleEcommCartSetting::getValue('email_from_address') . '>' . "\r\n\\";
	
    $msg= self::replaceEmailTags($orderId, SimpleEcommCartSetting::getValue('email_sent_on_purchase_body'));
	
	$msg = $msg."\r\n\r\n".SimpleEcommCartSetting::getValue('email_signature_text');
	
    if($newOrder) {
	   self::log("Will send purchase email now");
      $isSent = self::mail($to, $subject, $msg, $headers);
      if(!$isSent) {
        self::log("Mail not sent to: $to");
      }
    }
    return $isSent;
  }
   
  public static function sendEmailOnPending($orderId) {
    $isSent = false;
    $newOrder = new SimpleEcommCartOrder($orderId);
    //$msg = self::getEmailReceiptMessage($newOrder);
    $to = $newOrder->email;
    $subject = SimpleEcommCartSetting::getValue('email_sent_when_order_pending_subject');
    
    $headers = 'From: '. SimpleEcommCartSetting::getValue('email_from_name') .' <' . SimpleEcommCartSetting::getValue('email_from_address') . '>' . "\r\n\\";
      
    $msg= self::replaceEmailTags($orderId, SimpleEcommCartSetting::getValue('email_sent_when_order_pending_body'));
    $msg = $msg."\r\n\r\n".SimpleEcommCartSetting::getValue('email_signature_text');
	if($newOrder) {
      $isSent = self::mail($to, $subject, $msg, $headers);
      if(!$isSent) {
        self::log("Mail not sent to: $to");
      }
    }
    return $isSent;
  }
  
  public static function sendEmailOnShipped($orderId) {
    $isSent = false;
    $newOrder = new SimpleEcommCartOrder($orderId);
     
    $to = $newOrder->email;
    $subject = SimpleEcommCartSetting::getValue('email_sent_when_shipped_subject');
    
    $headers = 'From: '. SimpleEcommCartSetting::getValue('email_from_name') .' <' . SimpleEcommCartSetting::getValue('email_from_address') . '>' . "\r\n\\";
     
    $msg= self::replaceEmailTags($orderId, SimpleEcommCartSetting::getValue('email_sent_when_shipped_body'));
    $msg = $msg."\r\n\r\n".SimpleEcommCartSetting::getValue('email_signature_text');
	if($newOrder) {
      $isSent = self::mail($to, $subject, $msg, $headers);
      if(!$isSent) {
        self::log("Mail not sent to: $to");
      }
    }
    return $isSent;
  }

  public static function sendEmailOnRefund($orderId) {
      $isSent = false;
    $newOrder = new SimpleEcommCartOrder($orderId);
    //$msg = self::getEmailReceiptMessage($newOrder);
    $to = $newOrder->email;
    $subject = SimpleEcommCartSetting::getValue('email_sent_on_refund_subject');
    
    $headers = 'From: '. SimpleEcommCartSetting::getValue('email_from_name') .' <' . SimpleEcommCartSetting::getValue('email_from_address') . '>' . "\r\n\\"; 
	   
    $msg= self::replaceEmailTags($orderId, SimpleEcommCartSetting::getValue('email_sent_on_refund_body'));
	$msg = $msg."\r\n\r\n".SimpleEcommCartSetting::getValue('email_signature_text');
	
    if($newOrder) {
      $isSent = self::mail($to, $subject, $msg, $headers);
      if(!$isSent) {
        self::log("Mail not sent to: $to");
      }
    }
	
    return $isSent;
  }

  public static function replaceEmailTags($orderId, $emailBody)
  {
  	/*
  	{first_name} – First name of the buyer
	{last_name} – Last name of the buyer
	{payer_email}- Buyer email address
	{product_details} – Lists the item name (with variation), quantity, currency and price of every purchased item.
	{product_name} – Name of the purchased products (comma separated)
	{product_link_digital_items_only} – List of purchased digital items with encrypted download links (The item is only listed if the product has a downloadable file)
	{shipping_info} – Buyer’s shipping address
	{product_specific_instructions} – Add the product specific instructions (e.g password for a PDF file) specified in the product to the email body.
	{purchase_date} – The date of the purchase.
	{transaction_id} – The unique transaction ID of the purchase.
	{purchase_amt} – The amount paid for the current transaction.
	{total_tax} – Total tax amount for this transaction.
	{total_shipping} – Total shipping amount for this transaction.
	{total_minus_total_tax} – The total amount minus the total tax.
	*/
	
	$order = new SimpleEcommCartOrder($orderId);
    	
	$first_name = $order->bill_first_name;
	$last_name = $order->bill_last_name;
	$payer_email = $order->email;
	$product_details = '';
	$product_name = '';
	$product_link_digital_items_only='';
	
	$product = new SimpleEcommCartProduct();
	foreach($order->getItems() as $item) 
	{
      $product->load($item->product_id); 
	  
	  $product_details.="Product Name: ". $item->description."\n";
	  $product_details.= "Quantity: ".$item->quantity."\n";
	  $product_details.= "Price(".SimpleEcommCartSetting::getValue('SIMPLEECOMMCART_CURRENCY_SYMBOL_text')."): ".SIMPLEECOMMCART_CURRENCY_SYMBOL.($item->product_price * $item->quantity)."\n";
	  $product_details.= "\n";
	  
	  $product_name.= ','.$item->description;
	  
	  if($product->isDigital())	
	  {
	  	$receiptPage = get_page_by_path('store/receipt');
    	$link = get_permalink($receiptPage->ID);
    	if(strstr($link,"?")){
      		$link .= "&duid=" . $item->duid;
    	}
    	else{
      		$link .= "?duid=" . $item->duid;
    	} 
		$product_link_digital_items_only.= "\n".$item->description.": ".$link."\n";
	  }
	}
	$product_name = substr($product_name,1); 
	$shipping_info=$order->ship_address;
	$product_specific_instructions = '';
	$purchase_date = $order->ordered_on.'';
	$transaction_id= $order->trans_id.'';
	$purchase_amt = SIMPLEECOMMCART_CURRENCY_SYMBOL.$order->total.'';
	$total_tax = SIMPLEECOMMCART_CURRENCY_SYMBOL.$order->tax.'';
	$total_shipping = SIMPLEECOMMCART_CURRENCY_SYMBOL.$order->shipping.'';
	$total_minus_total_tax = SIMPLEECOMMCART_CURRENCY_SYMBOL.($order->total - $order->tax) .'';
	
	
	$emailBody = str_replace("{first_name}", $first_name, $emailBody);
	$emailBody = str_replace("{last_name}", $last_name, $emailBody);
	$emailBody = str_replace("{payer_email}", $payer_email, $emailBody);
	$emailBody = str_replace("{product_details}", $product_details, $emailBody);
	$emailBody = str_replace("{product_name}", $product_name, $emailBody);
	$emailBody = str_replace("{product_link_digital_items_only}", $product_link_digital_items_only, $emailBody);
	$emailBody = str_replace("{shipping_info}", $shipping_info, $emailBody);
	$emailBody = str_replace("{product_specific_instructions}", $product_specific_instructions, $emailBody);
	$emailBody = str_replace("{purchase_date}", $purchase_date, $emailBody);
	$emailBody = str_replace("{transaction_id}", $transaction_id, $emailBody);
	$emailBody = str_replace("{purchase_amt}", $purchase_amt, $emailBody);
	$emailBody = str_replace("{total_tax}", $total_tax, $emailBody);
	$emailBody = str_replace("{total_shipping}", $total_shipping, $emailBody);
	$emailBody = str_replace("{total_minus_total_tax}", $total_minus_total_tax, $emailBody);
	
	self::log('Email Body \n'.$emailBody);
	
	return $emailBody;
  }
  public static function checkAndSendStockNotification($ikey)
  { 
  	$p = new SimpleEcommCartProduct();
	$product_id = $p->getProductId($ikey) ;
	 
	if($product_id == NULL) return;
	 
	$p->load($product_id);
	if($p->isDigital())
	{
		return;
	}
	
  	$count = $p->getInventoryCount($ikey);
	
	$product_display_name = $p->name;
	
	$pieces = explode(" ", $ikey);
	if(count($pieces)==2)
	{
		$product_display_name.='('.$pieces[1].')';
	}
	else if(count($pieces)==3)
	{
		$product_display_name.='('.$pieces[1].','.$pieces[2].')';
	}
	
	$low_stock_notification = SimpleEcommCartSetting::getValue("low_stock_notification");
	if($low_stock_notification == '1')
	{
		$low_stock_threshhold =  SimpleEcommCartSetting::getValue("low_stock_threshhold")+0;
		if($count <= $low_stock_threshhold)
		{
			//will send low stock notification
			self::log("will send low stock notification");
			
			$to = SimpleEcommCartSetting::getValue('email_from_address');
    		$subject = 'Stock is low for - '.$product_display_name;
    
    		$headers = 'From: '. SimpleEcommCartSetting::getValue('email_from_name') .' <' . SimpleEcommCartSetting::getValue('email_from_address') . '>' . "\r\n\\";
    		$msg = 'Stock is low for - '.$product_display_name.'\r\n Current Stock: '.$count; 
			$isSent = self::mail($to, $subject, $msg, $headers);
      		if(!$isSent) {
        		self::log("Low Stock Notification Mail not sent to: $to");
      		}
		}
	}
	
	
	$out_of_stock_notification = SimpleEcommCartSetting::getValue("out_of_stock_notification");
	if($out_of_stock_notification=='1')
	{
		$out_of_stock_threshhold =  SimpleEcommCartSetting::getValue("out_of_stock_threshhold")+0;
		if($count <= $out_of_stock_threshhold)
		{
			//will send out of stock notification 
			self::log("will send out of stock notification");
			
			$to = SimpleEcommCartSetting::getValue('email_from_address');
    		$subject = 'Out of stock - '.$product_display_name;
    
    		$headers = 'From: '. SimpleEcommCartSetting::getValue('email_from_name') .' <' . SimpleEcommCartSetting::getValue('email_from_address') . '>' . "\r\n\\";
    		$msg = 'Out of stock - '.$product_display_name.'\r\n Current Stock: '.$count; 
			$isSent = self::mail($to, $subject, $msg, $headers);
      		if(!$isSent) {
        		self::log("Out of stock Notification Mail not sent to: $to");
      		}
		}
	}
	
	
	
  }
  public static function randomString($numChars = 7) {
		$letters = "";
		mt_srand((double)microtime()*1000000);
		for ($i = 0; $i < $numChars; $i++) { 
			$randval = chr(mt_rand(ord("a"), ord("z")));
			$letters .= $randval;
		}
		return $letters;
	}
	
	public static function isValidDate($val) {
	  $isValid = false;
		if(preg_match("/\d{1,2}\/\d{1,2}\/\d{4}/", $val)) {
			list($month, $day, $year) = split("/", $val);
			if(is_numeric($month) && is_numeric($day) && is_numeric($year) ) {
				if($month > 12 || $month < 1) {
					$isValid = false;
				}
				elseif($day > 31 || $day < 1) {
					$isValid = false;
				}
				elseif($year < 1900) {
					$isValid = false;
				}
				else {
					$isValid = true;
				}
			}
		}
		return $isValid;
	}

  /**
   * Strip slashes and escape sequences from POST values and returened the scrubbed value.
   * If the key is not set, return false.
   */
  public static function postVal($key) {
    $value = false;
    if(isset($_POST[$key])) {
      $value = self::deepTagClean($_POST[$key]);
    }
    return $value;
  }
  
  public static function deepTagClean(&$data) {
    if(is_array($data)) {
      foreach($data as $key => $value) {
        if(is_array($value)) {
          $data[$key] = self::deepTagClean($value);
        }
        else {
          $value = strip_tags($value);
          $data[$key] = preg_replace('/[<>\\\\\/]/', '', $value);
        }
      }
    }
    else {
      $data= strip_tags($data);
      $data = preg_replace('/[<>\\\\\/]/', '', $data);;
    }
    return $data;
  }
  

  /**
   * Strip slashes and escape sequences from GET values and returened the scrubbed value.
   * If the key is not set, return false.
   */
  public static function getVal($key) {
    $value = false;
    if(isset($_GET[$key])) {
      $value = strip_tags($_GET[$key]);
      $value = preg_replace('/[<>\\\\\/]/', '', $value);
    }
    return $value;
  }
  
  /**
   * Get home country code from cart settings or return US if no setting exists
   * 
   * @return string
   */
  public static function getHomeCountryCode() {
    if($homeCountry = SimpleEcommCartSetting::getValue('home_country')) {
      list($homeCountryCode, $dummy) = explode('~', $homeCountry); 
    }
    else {
      $homeCountryCode = 'US';
    }
    return $homeCountryCode;
  }
  
  public static function getCountryName($code) {
    $countries = self::getCountries(true);
    return $countries[$code];
  }

  public static function getCountries($all=false) {
    $countries = array( 
      'AD'=>'Andorra',
	'AE'=>'United Arab Emirates',
	'AF'=>'Afghanistan',
	'AG'=>'Antigua and Barbuda',
	'AI'=>'Anguilla',
	'AL'=>'Albania',
	'AM'=>'Armenia',
	'AN'=>'Netherlands Antilles',
	'AO'=>'Angola',
	'AQ'=>'Antarctica',
	'AR'=>'Argentina',
	'AS'=>'American Samoa',
	'AT'=>'Austria',
	'AU'=>'Australia',
	'AW'=>'Aruba',
	'AX'=>'Aland Islands',
	'AZ'=>'Azerbaijan',
	'BA'=>'Bosnia and Herzegovina',
	'BB'=>'Barbados',
	'BD'=>'Bangladesh',
	'BE'=>'Belgium',
	'BF'=>'Burkina Faso',
	'BG'=>'Bulgaria',
	'BH'=>'Bahrain',
	'BI'=>'Burundi',
	'BJ'=>'Benin',
	'BM'=>'Bermuda',
	'BN'=>'Brunei Darussalam',
	'BO'=>'Bolivia',
	'BR'=>'Brazil',
	'BS'=>'Bahamas',
	'BT'=>'Bhutan',
	'BV'=>'Bouvet Island',
	'BW'=>'Botswana',
	'BY'=>'Belarus',
	'BZ'=>'Belize',
	'CA'=>'Canada',
	'CC'=>'Cocos (Keeling) Islands',
	'CD'=>'Democratic Republic of the Congo',
	'CF'=>'Central African Republic',
	'CG'=>'Congo',
	'CH'=>'Switzerland',
	'CI'=>"Cote D'Ivoire (Ivory Coast)",
	'CK'=>'Cook Islands',
	'CL'=>'Chile',
	'CM'=>'Cameroon',
	'CN'=>'China',
	'CO'=>'Colombia',
	'CR'=>'Costa Rica',
	'CS'=>'Serbia and Montenegro',
	'CU'=>'Cuba',
	'CV'=>'Cape Verde',
	'CX'=>'Christmas Island',
	'CY'=>'Cyprus',
	'CZ'=>'Czech Republic',
	'DE'=>'Germany',
	'DJ'=>'Djibouti',
	'DK'=>'Denmark',
	'DM'=>'Dominica',
	'DO'=>'Dominican Republic',
	'DZ'=>'Algeria',
	'EC'=>'Ecuador',
	'EE'=>'Estonia',
	'EG'=>'Egypt',
	'EH'=>'Western Sahara',
	'ER'=>'Eritrea',
	'ES'=>'Spain',
	'ET'=>'Ethiopia',
	'FI'=>'Finland',
	'FJ'=>'Fiji',
	'FK'=>'Falkland Islands (Malvinas)',
	'FM'=>'Federated States of Micronesia',
	'FO'=>'Faroe Islands',
	'FR'=>'France',
	'FX'=>'France, Metropolitan',
	'GA'=>'Gabon',
	'GB'=>'Great Britain (UK)',
	'GD'=>'Grenada',
	'GE'=>'Georgia',
	'GF'=>'French Guiana',
	'GH'=>'Ghana',
	'GI'=>'Gibraltar',
	'GL'=>'Greenland',
	'GM'=>'Gambia',
	'GN'=>'Guinea',
	'GP'=>'Guadeloupe',
	'GQ'=>'Equatorial Guinea',
	'GR'=>'Greece',
	'GS'=>'S. Georgia and S. Sandwich Islands',
	'GT'=>'Guatemala',
	'GU'=>'Guam',
	'GW'=>'Guinea-Bissau',
	'GY'=>'Guyana',
	'HK'=>'Hong Kong',
	'HM'=>'Heard Island and McDonald Islands',
	'HN'=>'Honduras',
	'HR'=>'Croatia (Hrvatska)',
	'HT'=>'Haiti',
	'HU'=>'Hungary',
	'ID'=>'Indonesia',
	'IE'=>'Ireland',
	'IL'=>'Israel',
	'IN'=>'India',
	'IO'=>'British Indian Ocean Territory',
	'IQ'=>'Iraq',
	'IR'=>'Iran',
	'IS'=>'Icelandv',
	'IT'=>'Italy',
	'JM'=>'Jamaica',
	'JO'=>'Jordan',
	'JP'=>'Japan',
	'KE'=>'Kenya',
	'KG'=>'Kyrgyzstan',
	'KH'=>'Cambodia',
	'KI'=>'Kiribativ',
	'KM'=>'Comoros',
	'KN'=>'Saint Kitts and Nevis',
	'KP'=>'Korea (North)',
	'KR'=>'Korea (South)',
	'KW'=>'Kuwait',
	'KY'=>'Cayman Islands',
	'KZ'=>'Kazakhstan',
	'LA'=>'Laos',
	'LB'=>'Lebanon',
	'LC'=>'Saint Lucia',
	'LI'=>'Liechtenstein',
	'LK'=>'Sri Lanka',
	'LR'=>'Liberia',
	'LS'=>'Lesotho',
	'LT'=>'Lithuania',
	'LU'=>'Luxembourg',
	'LV'=>'Latvia',
	'LY'=>'Libya',
	'MA'=>'Morocco',
	'MC'=>'Monaco',
	'MD'=>'Moldova',
	'MG'=>'Madagascar',
	'MH'=>'Marshall Islands',
	'MK'=>'Macedonia',
	'ML'=>'Mali',
	'MM'=>'Myanmar',
	'MN'=>'Mongolia',
	'MO'=>'Macao',
	'MP'=>'Northern Mariana Islands',
	'MQ'=>'Martinique',
	'MR'=>'Mauritania',
	'MS'=>'Montserrat',
	'MT'=>'Malta',
	'MU'=>'Mauritius',
	'MV'=>'Maldives',
	'MW'=>'Malawi',
	'MX'=>'Mexico',
	'MY'=>'Malaysia',
	'MZ'=>'Mozambique',
	'NA'=>'Namibia',
	'NC'=>'New Caledonia',
	'NE'=>'Niger',
	'NF'=>'Norfolk Island',
	'NG'=>'Nigeria',
	'NI'=>'Nicaragua',
	'NL'=>'Netherlands',
	'NO'=>'Norway',
	'NP'=>'Nepal',
	'NR'=>'Nauru',
	'NU'=>'Niue',
	'NZ'=>'New Zealand (Aotearoa)',
	'OM'=>'Oman',
	'PA'=>'Panama',
	'PE'=>'Peru',
	'PF'=>'French Polynesia',
	'PG'=>'Papua New Guinea',
	'PH'=>'Philippines',
	'PK'=>'Pakistan',
	'PL'=>'Poland',
	'PM'=>'Saint Pierre and Miquelon',
	'PN'=>'Pitcairn',
	'PR'=>'Puerto Rico',
	'PS'=>'Palestinian Territory',
	'PT'=>'Portugal',
	'PW'=>'Palau',
	'PY'=>'Paraguay',
	'QA'=>'Qatar',
	'RE'=>'Reunion',
	'RO'=>'Romania',
	'RU'=>'Russian Federation',
	'RW'=>'Rwanda',
	'SA'=>'Saudi Arabia',
	'SB'=>'Solomon Islands',
	'SC'=>'Seychelles',
	'SD'=>'Sudan',
	'SE'=>'Sweden',
	'SG'=>'Singapore',
	'SH'=>'Saint Helena',
	'SI'=>'Slovenia',
	'SJ'=>'Svalbard and Jan Mayen',
	'SK'=>'Slovakia',
	'SL'=>'Sierra Leone',
	'SM'=>'San Marino',
	'SN'=>'Senegal',
	'SO'=>'Somalia',
	'SR'=>'Suriname',
	'ST'=>'Sao Tome and Principe',
	'SU'=>'USSR (former)',
	'SV'=>'El Salvador',
	'SY'=>'Syria',
	'SZ'=>'Swaziland',
	'TC'=>'Turks and Caicos Islands',
	'TD'=>'Chad',
	'TF'=>'French Southern Territories',
	'TG'=>'Togo',
	'TH'=>'Thailand',
	'TJ'=>'Tajikistan',
	'TK'=>'Tokelau',
	'TL'=>'Timor-Leste',
	'TM'=>'Turkmenistan',
	'TN'=>'Tunisia',
	'TO'=>'Tonga',
	'TP'=>'East Timor',
	'TR'=>'Turkey',
	'TT'=>'Trinidad and Tobago',
	'TV'=>'Tuvalu',
	'TW'=>'Taiwan',
	'TZ'=>'Tanzania',
	'UA'=>'Ukraine',
	'UG'=>'Uganda',
	'UK'=>'United Kingdom',
	'UM'=>'United States Minor Outlying Islands',
	'US'=>'United States',
	'UY'=>'Uruguay',
	'UZ'=>'Uzbekistan',
	'VA'=>'Vatican City State (Holy See)',
	'VC'=>'Saint Vincent and the Grenadines',
	'VE'=>'Venezuela',
	'VG'=>'Virgin Islands (British)',
	'VI'=>'Virgin Islands (U.S.)',
	'VN'=>'Viet Nam',
	'VU'=>'Vanuatu',
	'WF'=>'Wallis and Futuna',
	'WS'=>'Samoa',
	'YE'=>'Yemen',
	'YT'=>'Mayotte',
	'YU'=>'Yugoslavia (former)',
	'ZA'=>'South Africa',
	'ZM'=>'Zambia',
	'ZR'=>'Zaire (former)',
	'ZW'=>'Zimbabwe');
    
    // Put home country at the top of the list
    $setting = new SimpleEcommCartSetting();
    $home_country = SimpleEcommCartSetting::getValue('home_country');
    if($home_country) {
      list($code, $name) = explode('~', $home_country);
      $countries = array_merge(array($code => $name), $countries);
    }
    else {
      $countries = array_merge(array('US' => 'United States'), $countries);
    }

    $customCountries = self::getCustomCountries();
    
    if($all) {
      if(is_array($customCountries)) {
        foreach($customCountries as $code => $name) {
          unset($countries[$code]);
        }
        foreach($countries as $code => $name) {
          $customCountries[$code] = $name;
        }
        $countries = $customCountries;
      }
    }
    else {
      $international = SimpleEcommCartSetting::getValue('international_sales');
      if($international) {
        if($customCountries) {
          //SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Got some custom countries: " . print_r($customCountries, true));
          $countries = $customCountries;
        }
      }
      else {
        $countries = array_slice($countries, 0, 1, true); 
      }
    }
    
    
    
    return $countries;
  }

  public static function getCustomCountries() {
    $list = false;
    $setting = new SimpleEcommCartSetting();
    $countries = SimpleEcommCartSetting::getValue('countries');
    if($countries) {
      $countries = explode(',', $countries);
      foreach($countries as $c) {
        list($code, $name) = explode('~', $c);
        $list[$code] = $name;
      }
    }
    return $list;
  }
  
  public static function getPayPalCurrencyCodes() {
    $currencies = array(
      'United States Dollar' => 'USD',
      'Australian Dollar' => 'AUD',
      'Canadian Dollar' => 'CAD',
      'Czech Koruna' => 'CZK',
      'Danish Krone' => 'DKK',
      'Euro' => 'EUR',
      'Hong Kong Dollar' => 'HKD',
      'Hungarian Forint' => 'HUF',
      'Israeli New Sheqel' => 'ILS',
      'Japanese Yen' => 'JPY',
      'Malaysian Ringgit' => 'MYR',
      'Mexican Peso' => 'MXN',
      'Norwegian Krone' => 'NOK',
      'New Zealand Dollar' => 'NZD',
      'Philippine Peso' => 'PHP',
      'Polish Zloty' => 'PLN',
      'Pound Sterling' => 'GBP',
      'Singapore Dollar' => 'SGD',
      'Swedish Krona' => 'SEK',
      'Swiss Franc' => 'CHF',
      'Taiwan New Dollar' => 'TWD',
      'Thai Baht' => 'THB'
    );
    return $currencies;
  }

  
  public static function getZones($code='all') {
    $zones = array();
    
    $au = array();
    $au['NSW'] = 'New South Wales';
    $au['NT'] = 'Northern Territory';
    $au['QLD'] = 'Queensland';
    $au['SA'] = 'South Australia';
    $au['TAS'] = 'Tasmania';
    $au['VIC'] = 'Victoria';
    $au['WA'] = 'Western Australia';
    $zones['AU'] = $au;
    
    $br = array();
    $br['Acre'] = 'Acre';
		$br['Alagoas'] = 'Alagoas';
		$br['Amapa'] = 'Amapa';
		$br['Amazonas'] = 'Amazonas';
		$br['Bahia'] = 'Bahia';
		$br['Ceara'] = 'Ceara';
		$br['Distrito Federal'] = 'Distrito Federal';
		$br['Espirito Santo'] = 'Espirito Santo';
		$br['Goias'] = 'Goias';
		$br['Maranhao'] = 'Maranhao';
		$br['Mato Grosso'] = 'Mato Grosso';
		$br['Mato Grosso do Sul'] = 'Mato Grosso do Sul';
		$br['Minas Gerais'] = 'Minas Gerais';
		$br['Para'] = 'Para';
		$br['Paraiba'] = 'Paraiba';
		$br['Parana'] = 'Parana';
		$br['Pernambuco'] = 'Pernambuco';
		$br['Piaui'] = 'Piaui';
		$br['Rio de Janeiro'] = 'Rio de Janeiro';
		$br['Rio Grande do Norte'] = 'Rio Grande do Norte';
		$br['Rio Grande do Sul'] = 'Rio Grande do Sul';
		$br['Rondonia'] = 'Rondonia';
		$br['Roraima'] = 'Roraima';
		$br['Santa Catarina'] = 'Santa Catarina';
		$br['Sao Paulo'] = 'Sao Paulo';
		$br['Sergipe'] = 'Sergipe';
		$br['Tocantins'] = 'Tocantins';
    $zones['BR'] = $br;
    
    $ca = array();
    $ca['AB'] = 'Alberta';
    $ca['BC'] = 'British Columbia';
    $ca['MB'] = 'Manitoba';
    $ca['NB'] = 'New Brunswick';
    $ca['NF'] = 'Newfoundland';
    $ca['NT'] = 'Northwest Territories';
    $ca['NS'] = 'Nova Scotia';
    $ca['NU'] = 'Nunavut';
    $ca['ON'] = 'Ontario';
    $ca['PE'] = 'Prince Edward Island';
    $ca['PQ'] = 'Quebec';
    $ca['SK'] = 'Saskatchewan';
    $ca['YT'] = 'Yukon Territory';
    $zones['CA'] = $ca;
    
    $us = array();
    $us['AL'] = 'Alabama';
    $us['AK'] = 'Alaska ';
    $us['AZ'] = 'Arizona';
    $us['AR'] = 'Arkansas';
    $us['CA'] = 'California ';
    $us['CO'] = 'Colorado';
    $us['CT'] = 'Connecticut';
    $us['DE'] = 'Delaware';
    $us['DC'] = 'D. C.';
    $us['FL'] = 'Florida';
    $us['GA'] = 'Georgia ';
    $us['HI'] = 'Hawaii';
    $us['ID'] = 'Idaho';
    $us['IL'] = 'Illinois';
    $us['IN'] = 'Indiana';
    $us['IA'] = 'Iowa';
    $us['KS'] = 'Kansas';
    $us['KY'] = 'Kentucky';
    $us['LA'] = 'Louisiana';
    $us['ME'] = 'Maine';
    $us['MD'] = 'Maryland';
    $us['MA'] = 'Massachusetts';
    $us['MI'] = 'Michigan';
    $us['MN'] = 'Minnesota';
    $us['MS'] = 'Mississippi';
    $us['MO'] = 'Missouri';
    $us['MT'] = 'Montana';
    $us['NE'] = 'Nebraska';
    $us['NV'] = 'Nevada';
    $us['NH'] = 'New Hampshire';
    $us['NJ'] = 'New Jersey';
    $us['NM'] = 'New Mexico';
    $us['NY'] = 'New York';
    $us['NC'] = 'North Carolina';
    $us['ND'] = 'North Dakota';
    $us['OH'] = 'Ohio';
    $us['OK'] = 'Oklahoma';
    $us['OR'] = 'Oregon';
    $us['PA'] = 'Pennsylvania';
    $us['RI'] = 'Rhode Island';
    $us['SC'] = 'South Carolina';
    $us['SD'] = 'South Dakota';
    $us['TN'] = 'Tennessee';
    $us['TX'] = 'Texas';
    $us['UT'] = 'Utah';
    $us['VT'] = 'Vermont';
    $us['VA'] = 'Virginia';
    $us['WA'] = 'Washington';
    $us['WV'] = 'West Virginia';
    $us['WI'] = 'Wisconsin';
    $us['WY'] = 'Wyoming';
    $us['AE'] = 'Armed Forces';
    $zones['US'] = $us;
    
    switch ($code) {
      case 'AU':
        $zones = $zones['AU'];
        break;
  	  case 'BR':
    		$zones = $zones['BR'];
  		  break;
      case 'CA':
        $zones = $zones['CA'];
        break;
      case 'US':
        $zones = $zones['US'];
        break;
    }
    
    return $zones;
  }
  


  /**
   * Return a link to the "view cart" page
   */
  public static function getPageLink($path) {
    $page = get_page_by_path($path);
    $link = get_permalink($page->ID);
    if($_SERVER['SERVER_PORT'] == '443') {
      $link = str_replace('http://', 'https://', $link);
    }
    return $link;
  }

  /**
   * Make sure path ends in a trailing slash by looking for trailing slash and add if necessary
   */
  public static function endSlashPath($path) {
    if(stripos(strrev($path), '/') !== 0) {
      $path .= '/';
    }
    return $path;
  }
  
  public static function localTs($timestamp=null) {
    $timestamp = isset($timestamp) ? $timestamp : time();
    if(date('T') == 'UTC') {
      $timestamp += (get_option( 'gmt_offset' ) * 3600 );
    }
    return $timestamp;
  }

  /**
   * Return an array of order status options
   * If no options have been set by the user, 'new' is the only returned option
   */
  public static function getOrderStatusOptions() {
    $statuses = array();
    $setting = new SimpleEcommCartSetting();
    $opts = SimpleEcommCartSetting::getValue('status_options');
    if(!empty($opts)) {
      $opts = explode(',', $opts);
      foreach($opts as $o) {
        $statuses[] = trim($o);
      }
    }
    if(count($statuses) == 0) {
      $statuses[] = 'new';
    }
    return $statuses;
  }

  public function getPromoMessage() {
    $promo = SimpleEcommCartSession::get('SimpleEcommCartCart')->getPromotion();
    $promoMsg = "none";
    if($promo) {
      $promoMsg = $promo->code . ' (-' . SIMPLEECOMMCART_CURRENCY_SYMBOL . number_format(SimpleEcommCartSession::get('SimpleEcommCartCart')->getDiscountAmount(), 2) . ')';
    }
    return $promoMsg;
  }

  public function showErrors($errors, $message=null) {
    $out = "<div id='simpleecommcartErrors' class='SimpleEcommCartError'>";
    if(empty($message)) {
      $out .= "<p><b>" . __("We're sorry.<br/>Your order could not be completed for the following reasons","simpleecommcart") . ":</b></p>";
    }
    else {
      $out .= $message;
    }
    $out .= '<ul>';
    if(is_array($errors)) {
      foreach($errors as $key => $value) {
        $value = strtolower($value);
        $out .= "<li>$value</li>";
      }
    }
    else {
      $out .= "<li>$errors</li>";
    }
    $out .= "</ul></div>";
    return $out;
  }
  
  public function getJqErrorScript(array $jqErrors) {
    $script = '
<script type="text/javascript">
var $jq = jQuery.noConflict();
$jq(document).ready(function() { 
_script_here_ 
});
</script>';

    if(count($jqErrors)) {
      $lines = '';
      foreach($jqErrors as $val) {
        $lines .= "  \$jq('#$val').addClass('errorField);\n";
      }
    }
    $lines  = rtrim($lines, "\n");
    $script = str_replace('_script_here_', $lines, $script);
    return $script;
  }

  
  public static function getEmailReceiptMessage($order) {
    $product = new SimpleEcommCartProduct();
    
    $msg = __("ORDER NUMBER","simpleecommcart") . ": " . $order->trans_id . "\n\n";
    $hasDigital = false;
    foreach($order->getItems() as $item) {
      $product->load($item->product_id);
      if($hasDigital == false) {
        $hasDigital = $product->isDigital();
      }
      $price = $item->product_price * $item->quantity;
      // $msg .= "Item: " . $item->item_number . ' ' . $item->description . "\n";
      $msg .= __("Item","simpleecommcart") . ": " . $item->description . "\n";
      if($item->quantity > 1) {
        $msg .= __("Quantity","simpleecommcart") . ": " . $item->quantity . "\n";
      }
      $msg .= __("Item Price","simpleecommcart") . ": " . SIMPLEECOMMCART_CURRENCY_SYMBOL_TEXT . number_format($item->product_price, 2) . "\n";
      $msg .= __("Item Total","simpleecommcart") . ": " . SIMPLEECOMMCART_CURRENCY_SYMBOL_TEXT . number_format($item->product_price * $item->quantity, 2) . "\n\n";
      
      if($product->isGravityProduct()) {
        $msg .= SimpleEcommCartGravityReader::displayGravityForm($item->form_entry_ids, true);
      }
    }

    if($order->shipping_method != 'None' && $order->shipping_method != 'Download') {
      $msg .= __("Shipping","simpleecommcart") . ": " . SIMPLEECOMMCART_CURRENCY_SYMBOL_TEXT . $order->shipping . "\n";
    }

    if(!empty($order->coupon) && $order->coupon != 'none') {
      $msg .= __("Coupon","simpleecommcart") . ": " . $order->coupon . "\n";
    }

    if($order->tax > 0) {
      $msg .= __("Tax","simpleecommcart") . ": " . SIMPLEECOMMCART_CURRENCY_SYMBOL_TEXT . number_format($order->tax, 2) . "\n";
    }

    $msg .= "\n" . __("TOTAL","simpleecommcart") . ": " . SIMPLEECOMMCART_CURRENCY_SYMBOL_TEXT . number_format($order->total, 2) . "\n";

    if($order->shipping_method != 'None' && $order->shipping_method != 'Download') {
      $msg .= "\n\n" . __("SHIPPING INFORMATION","simpleecommcart") . "\n\n";

      $msg .= $order->ship_first_name . ' ' . $order->ship_last_name . "\n";
      $msg .= $order->ship_address . "\n";
      if(!empty($order->ship_address2)) {
        $msg .= $order->ship_address2 . "\n";
      }
      $msg .= $order->ship_city . ' ' . $order->ship_state . ' ' . $order->ship_zip . "\n" . $order->ship_country . "\n";

      $msg .= "\n" . __("Delivery via","simpleecommcart") . ": " . $order->shipping_method . "\n";
    }


    $msg .= "\n\n" . __("BILLING INFORMATION","simpleecommcart") . "\n\n";

    $msg .= $order->bill_first_name . ' ' . $order->bill_last_name . "\n";
    $msg .= $order->bill_address . "\n";
    if(!empty($order->bill_address2)) {
      $msg .= $order->bill_address2 . "\n";
    }
    $msg .= $order->bill_city . ' ' . $order->bill_state . ' ' . $order->bill_zip . "\n" . $order->bill_country . "\n";

    if(!empty($order->phone)) {
      $phone = self::formatPhone($order->phone);
      $msg .= "\n" . __("Phone","simpleecommcart") . ": $phone\n";
    }
    
    if(!empty($order->email)) {
      $msg .= __("Email","simpleecommcart") . ': ' . $order->email . "\n";
    }

    $receiptPage = get_page_by_path('store/receipt');
    $link = get_permalink($receiptPage->ID);
    if(strstr($link,"?")){
      $link .= '&ouid=' . $order->ouid;
    }
    else{
      $link .= '?ouid=' . $order->ouid;
    }

    if($hasDigital) {
      $msg .= "\n" . __('DOWNLOAD LINK','simpleecommcart') . "\n" . __('Click the link below to download your order.','simpleecommcart') . "\n$link";
    }
    else {
      $msg .= "\n" . __('VIEW RECEIPT ONLINE','simpleecommcart') . "\n" . __('Click the link below to view your receipt online.','simpleecommcart') . "\n$link";
    }
    
    $setting = new SimpleEcommCartSetting();
    $msgIntro = SimpleEcommCartSetting::getValue('email_sent_on_purchase_body');
    $msg = $msgIntro . " \n----------------------------------\n\n" . $msg;
    
    return $msg;
  }
  
  /**
   * Return the WP_CONTENT_URL taking into account HTTPS and the possibility that WP_CONTENT_URL may not be defined
   * 
   * @return string
   */
  public static function getWpContentUrl() {
    $wpurl = WP_CONTENT_URL;
    if(empty($wpurl)) {
      $wpurl = get_bloginfo('wpurl') . '/wp-content';
    }
    if(self::isHttps()) {
      $wpurl = str_replace('http://', 'https://', $wpurl);
    }
    return $wpurl;
  }
  
  /**
   * Return the WordPress URL taking into account HTTPS
   */
  public static function getWpUrl() {
    $wpurl = get_bloginfo('wpurl');
    if(self::isHttps()) {
      $wpurl = str_replace('http://', 'https://', $wpurl);
    }
    return $wpurl;
  }
  
  /**
   * Detect if request occurred over HTTPS and, if so, return TRUE. Otherwise return FALSE.
   * 
   * @return boolean
   */
  public static function isHttps() {
    $isHttps = false;
    if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
      $isHttps = true;
    }
    return $isHttps;
  }
  
  
  public static function getCurrentPageUrl() {
    $protocol = 'http://';
    if(self::isHttps()) {
      $protocol = 'https://';
    }
    $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    return $url;
  }
  
  /**
   * Attach a string of name/value pairs to a URL for the current page
   * This function looks for the presence of a ? and appropriately appends the new parameters.
   * Return a URL for the current page with the appended params.
   * 
   * @return string
   */
  public function appendQueryString($nvPairs) {
    $url = self::getCurrentPageUrl();
    $url .= strpos($url, '?') ? '&' : '?';
    $url .= $nvPairs;
    return $url;
  }
  
  /**
   * Replace the query string for the current page url
   * 
   * @param string Name value pairs formatted as name1=value1&name2=value2
   * @return string The URL to the current page with the given query string
   */
  public function replaceQueryString($nvPairs=false) {
    $url = explode('?', self::getCurrentPageUrl());
    $url = $url[0];
    if($nvPairs) {
      $url .= '?' . $nvPairs;
    }
    return $url;
  }
  

  
  public static function serializeSimpleXML(SimpleXMLElement $xmlObj) {
    return serialize($xmlObj->asXML());
  }
  
  public static function unserializeSimpleXML($str) {
    return simplexml_load_string(unserialize($str));
  }
  
  /**
   * Return either the live or the sandbox PayPal URL based on whether or not paypal_sandbox is set.
   */
  public static function getPayPalUrl() {
    $paypalUrl='https://www.paypal.com/cgi-bin/webscr';
    if(SimpleEcommCartSetting::getValue('paypal_sandbox')) {
      $paypalUrl='https://www.sandbox.paypal.com/cgi-bin/webscr';
    }
    return $paypalUrl;
  }
  
  public function curl($url, $method='GET') {
    $method = strtoupper($method);
    
    // Make sure curl is installed?
    if (!function_exists('curl_init')){ 
      throw new SimpleEcommCartException('cURL is not installed!');
    }

    // create a new curl resource
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if($method == 'POST') {
      curl_setopt($ch, CURLOPT_POST, true);
    }
    
    $output = curl_exec($ch);

    // close the curl resource, and free system resources
    curl_close($ch);

    return $output;
  }
  
  public static function downloadFile($path) {
    
    // Validate the $path
    if(!strpos($path, '://')) {
      if($productFolder = SimpleEcommCartSetting::getValue('product_folder')) {
        if(strpos($path, $productFolder) === 0) {
          // Erase and close all output buffers
          while (@ob_end_clean());

          // Get the name of the file to be downloaded
          $fileName = basename($path);
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Download file name: $fileName");

          // This is required for IE, otherwise Content-disposition is ignored
          if(ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
          }

          $bytes = 'unknown';
          if(substr($path, 0, 4) == 'http') {
            $bytes = SimpleEcommCartCommon::remoteFileSize($path);
          }
          else {
            $bytes = filesize($path);
          }
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Download file size: $bytes");

          ob_start();
          header("Pragma: public");
          header("Expires: 0");
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Cache-Control: private",false);
          header("Content-Type: application/octet-stream;");
          header("Content-Disposition: attachment; filename=\"".$fileName."\";" );
          header("Content-Transfer-Encoding: binary");
          header("Content-Length: $bytes");

          //open the file and stream download
          if($fp = fopen($path, 'rb')) {
            while(!feof($fp)) {
              //reset time limit for big files
              @set_time_limit(0);
              echo fread($fp, 1024*8);
              flush();
              ob_flush();
            }
            fclose($fp);
          }
          else {
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] fopen failed to open path: $path");
          }
        }
        else {
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to download file because the requested file is not in the path defined by the product folder settings: $path");
        }
      }
      else {
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to download file because the product folder is not set.");
      }
    }
    
  }
   
   public static function downloadTmpFile($path) {
    
    // Validate the $path
    if(!strpos($path, '://')) {
      if($productFolder = SimpleEcommCartSetting::getValue('tmp_folder')) {
        if(strpos($path, $productFolder) === 0) {
          // Erase and close all output buffers
          while (@ob_end_clean());

          // Get the name of the file to be downloaded
          $fileName = basename($path);
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Download file name: $fileName");

          // This is required for IE, otherwise Content-disposition is ignored
          if(ini_get('zlib.output_compression')) {
            ini_set('zlib.output_compression', 'Off');
          }

          $bytes = 'unknown';
          if(substr($path, 0, 4) == 'http') {
            $bytes = SimpleEcommCartCommon::remoteFileSize($path);
          }
          else {
            $bytes = filesize($path);
          }
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Download file size: $bytes");

          ob_start();
          header("Pragma: public");
          header("Expires: 0");
          header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
          header("Cache-Control: private",false);
          header("Content-Type: application/octet-stream;");
          header("Content-Disposition: attachment; filename=\"".$fileName."\";" );
          header("Content-Transfer-Encoding: binary");
          header("Content-Length: $bytes");

          //open the file and stream download
          if($fp = fopen($path, 'rb')) {
            while(!feof($fp)) {
              //reset time limit for big files
              @set_time_limit(0);
              echo fread($fp, 1024*8);
              flush();
              ob_flush();
            }
            fclose($fp);
          }
          else {
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] fopen failed to open path: $path");
          }
        }
        else {
          SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to download file because the requested file is not in the path defined by the product folder settings: $path");
        }
      }
      else {
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to download file because the product folder is not set.");
      }
    }
    
  }
  public static function remoteFileSize($remoteFile) {
    $ch = curl_init($remoteFile);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
    $data = curl_exec($ch);
    curl_close($ch);
    $contentLength = 'unknown';
    if ($data !== false) {
      if (preg_match('/Content-Length: (\d+)/', $data, $matches)) {
        $contentLength = (int)$matches[1];
      }
    }
    return $contentLength;
  }
  
  public static function onlyUsingPayPalStandard() {
    $onlyPayPalStandard = false;
    if(SimpleEcommCartSetting::getValue('paypal_email')) {
      $onlyPayPalStandard = true;
    }
    
    if(SimpleEcommCartSetting::getValue('auth_username') || SimpleEcommCartSetting::getValue('paypalpro_api_username')) {
      $onlyPayPalStandard = false;
    }
    
    return $onlyPayPalStandard;
  }
  
  /**
   * Convert an array into XML
   * 
   * Example use: echo arrayToXml($products,'products');
   * 
   * @param array $array       - The array you wish to convert into a XML structure.
   * @param string $name       - The name you wish to enclose the array in, the 'parent' tag for XML.
   * @param string $space      - The xml namespace
   * @param bool $standalone   - This will add a document header to identify this solely as a XML document.
   * @param bool $beginning    - INTERNAL USE... DO NOT USE!
   * @param int $nested        - INTERNAL USE... DO NOT USE! The nest level for pretty formatting
   * @return Gives a string output in a XML structure
  */
  public static function arrayToXml($array, $name, $space='', $standalone=false, $beginning=true, $nested=0) {
    $output = '';
    if ($beginning) {
      if($standalone) header("content-type:text/xml;charset=utf-8");
      if(!isset($output)) { $output = ''; }
      if($standalone) $output .= '<'.'?'.'xml version="1.0" encoding="UTF-8"'.'?'.'>' . "\n";
      if(!empty($space)) {
        $output .= '<' . $name . ' xmlns="' . $space . '">' . "\n";
      }
      else {
        $output .= '<' . $name . '>' . "\n";
      }
      $nested = 0;
    }

    // This is required because XML standards do not allow a tag to start with a number or symbol, you can change this value to whatever you like:
    $ArrayNumberPrefix = 'ARRAY_NUMBER_';

     foreach ($array as $root=>$child) {
      if (is_array($child)) {
        $output .= str_repeat(" ", (2 * $nested)) . '  <' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . "\n";
        $nested++;
        $output .= self::arrayToXml($child,NULL,NULL,NULL,FALSE, $nested);
        $nested--;
        $tag = is_string($root) ? $root : $ArrayNumberPrefix . $root;
        $tag = array_shift(explode(' ', $tag));
        $output .= str_repeat(" ", (2 * $nested)) . '  </' . $tag . '>' . "\n";
      }
      else {
        if(!isset($output)) { $output = ''; }
        $tag = is_string($root) ? $root : $ArrayNumberPrefix . $root;
        $tag = array_shift(explode(' ', $tag));
        $output .= str_repeat(" ", (2 * $nested)) . '  <' . (is_string($root) ? $root : $ArrayNumberPrefix . $root) . '>' . $child . '</' . $tag . '>' . "\n";
      }
    }
    
    $name = array_shift(explode(' ', $name));
    if ($beginning) $output .= '</' . $name . '>';

    return $output;
  }
  
  public static function testResult($passed, $msg='') {
    $trace = debug_backtrace();
    $func = $trace[1]['function'];
    $line = $trace[0]['line'];
    $file = $trace[1]['file'];
    $out = $passed ? "<font color=\"green\">SUCCESS: $func</font>\n" : "<font color=\"red\">FAILED: $func (Line: $line)\nFile: $file</font>\n";
    if(!empty($msg)) { $out .= $msg . "\n"; }
    echo $out . "\n";
  }
  
  public static function showReportData(){
    global $wpdb;
    $orders = SimpleEcommCartCommon::getTableName('orders');
    $reportData = array();
    
    $sql = "SELECT sum(`total`) from $orders";
    $lifetimeTotal = $wpdb->get_var($sql);
    $reportData[] = array("Total Sales","total_sales",$lifetimeTotal);
    
    $sql = "SELECT count('id') from $orders";
    $totalOrders = $wpdb->get_var($sql);
    $reportData[] = array("Total Orders","total_orders",$totalOrders);
    
    $sql = "SELECT ordered_on from $orders order by id asc LIMIT 1";
    $firstSaleDate = $wpdb->get_var($sql);
    $reportData[] = array("First Sale","first_sale",$firstSaleDate);
    
    $sql = "SELECT ordered_on from $orders order by id desc LIMIT 1";
    $lastSaleDate = $wpdb->get_var($sql);
    $reportData[] = array("Last Sale","last_sale",$lastSaleDate);
    
    $postTypes = get_post_types('','names');
    foreach($postTypes as $postType){
      if(!in_array($postType,array("post","page","attachment","nav_menu_item","revision"))){
        $customPostTypes[] = $postType;
      }
    }
    $customPostTypes = (empty($customPostTypes)) ? "none" : implode(',',$customPostTypes);
    $reportData[] = array("Custom Post Types","custom_post_types",$customPostTypes);
    
    $output = "First Sale: " . $firstSaleDate . "<br>";
    $output .= "Last Sale: " . $lastSaleDate . "<br>";
    $output .= "Total Orders: " . $totalOrders . "<br>";
    $output .= "Total Sales: " . $lifetimeTotal . "<br>";
    $output .= "Custom Post Types: " . $customPostTypes . "<br>";
    $output .= "WordPress Version: " . get_bloginfo("version") . "<br>";
    $output .= (SIMPLEECOMMCART_PRO) ? "SimpleEcommCart Version: Pro " . SimpleEcommCartSetting::getValue('version') . "<br>" : "SimpleEcommCart Version: " .SimpleEcommCartSetting::getValue('version') . "<br>";
    $output .= "PHP Version: " . phpversion() . "<br>";
    
    
    //$output .= ": " . "" . "<br>";
    
    return $output;
  }
}
