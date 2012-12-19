<?php
class SimpleEcommCartProduct extends SimpleEcommCartModelAbstract {
  
  protected $_creditAmount;
  
  public function __construct($id=null) {
    $this->_tableName = SimpleEcommCartCommon::getTableName('products');
    parent::__construct($id);
    $this->_creditAmount = 0;
  }
  
  public function getOptions() {
    $opt1 = $this->_buildOptionList(1);
    $opt2 = $this->_buildOptionList(2);
	
	$opt='';
	if(strlen($opt1)>0)
	{
		$opt.='<br>'.$opt1 .'<br>';
	}
	if(strlen($opt2)>0)
	{
		$opt.=$opt2;
	}
    return $opt;
  }
  
  public function loadByDuid($duid) {
    $itemsTable = SimpleEcommCartCommon::getTableName('order_items');
    $sql = "SELECT product_id from $itemsTable where duid = '$duid'";
    $id = $this->_db->get_var($sql);
    $this->load($id);
    return $this->id;
  }
  
  public function loadByItemNumber($itemNumber) {
    $itemNumber = $this->_db->escape($itemNumber);
    $sql = "SELECT id from $this->_tableName where item_number = '$itemNumber'";
    $id = $this->_db->get_var($sql);
    $this->load($id);
    return $this->id;
  }

  public function loadFromShortcode($attrs) {
    if(is_array($attrs)) {
      if(isset($attrs['item'])) {
        $this->loadByItemNumber($attrs['item']);
      }
      else {
        $id = $attrs['id'];
        $this->load($id);
      }
    }
    return $this->id;
  }

  public function countDownloadsForDuid($duid) {
    $downloadsTable = SimpleEcommCartCommon::getTableName('downloads');
    $sql = "SELECT count(*) from $downloadsTable where duid='$duid'";
    return $this->_db->get_var($sql);
  }
  
  /**
   * Return the quantity of inventory in stock for the product with the given id and variation description.
   * 
   * The variation descriptins is a ~ separated string of options. The price info may be in the variation string but
   * will be stripped out before calculating the iKey.
   * 
   * @param int $id
   * @param string $variation
   * @return int Quantity of inventory in stock
   */
  public static function checkInventoryLevelForProduct($id, $variation='') {
    // Build varation ikey string component
    if(!empty($variation)) {
      $variation = self::scrubVaritationsForIkey($variation);
    }
    
    $p = new SimpleEcommCartProduct($id);
    $ikey = $p->getInventoryKey($variation);
    $count = $p->getInventoryCount($ikey);
    //SimpleEcommCartCommon::log("Check Inventory Level For Product: $ikey = $count");
    return $count;
  }
  
  public static function decrementInventory($id, $variation='', $qty=1) {
    SimpleEcommCartCommon::log("Decrementing Inventory: line " . __LINE__);
    // Build varation ikey string component
    if(!empty($variation)) {
      $variation = self::scrubVaritationsForIkey($variation);
    }
    
    $p = new SimpleEcommCartProduct($id);
    $ikey = $p->getInventoryKey($variation);
    $count = $p->getInventoryCount($ikey);
    $newCount = $count - $qty;
    if($newCount < 0) {
      $newCount = 0;
    }
    
    $p->setInventoryLevel($ikey, $newCount);
  }
  
  public static function scrubVaritationsForIkey($variation='') {
    if(!empty($variation)) {
      $variations = explode('~', $variation);
      $options = array();
      foreach($variations as $opt) {
        $options[] = trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt));
      }
      $variation = strtolower(str_replace('~', ',', str_replace(' ', '', implode(',', $options))));
    }
    return $variation;
  }
  
  public static function confirmInventory($id, $variation='', $desiredQty=1) {
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Confirming Inventory:\n$id | $variation | $desiredQty");
    $ok = true;
    $setting = new SimpleEcommCartSetting();
    $trackInventory = SimpleEcommCartSetting::getValue('track_inventory');
    if($trackInventory == 1) {
      $p = new SimpleEcommCartProduct($id);
      $variation = self::scrubVaritationsForIkey($variation);
      $ikey = $p->getInventoryKey($variation);
      if($p->isInventoryTracked($ikey)) {
        $qty = self::checkInventoryLevelForProduct($id, $variation);
		
		//check for out of stock threshhold
		$out_of_stock_notification = SimpleEcommCartSetting::getValue("out_of_stock_notification");
		if($out_of_stock_notification=='1')
		{
			$out_of_stock_threshhold =  SimpleEcommCartSetting::getValue("out_of_stock_threshhold")+0;
			if($qty <= $out_of_stock_threshhold) return false;
		}
		
        if($qty < $desiredQty) {
          $ok = false;
        }
      }
      else {
        SimpleEcommCartCommon::log("Inventory not tracked: $ikey");
      }
    }
    return $ok;
  }
  
  /**
   * Return an array of option names having stripped off any price variations
   * 
   * @param int $optNumber The option group number
   * @return array
   */
  public function getOptionNames($optNumber=1) {
    $names = array();
    $optionName = "options_$optNumber";
    $opts = split(',', $this->$optionName);
    foreach($opts as $opt) {
      $name = $opt;
      if(strpos($opt, '$')) {
        $name = trim(preg_replace('/\s*([+-])[^$]*\$.*$/', '', $opt));
      }
      
      if(!empty($name)) {
        $names[] = $name;
      }
    }
    return $names;
  }
  
  public function getAllOptionCombinations() {
    $combos = array();
    $opt1 = $this->getOptionNames(1);
    $opt2 = $this->getOptionNames(2);
    if(count($opt1)) {
      foreach($opt1 as $first) {
        if(count($opt2)) {
          foreach($opt2 as $second) {
            $combos[] = "$first, $second";
          }
        }
        else {
          $combos[] = "$first";
        }
      }
    }
    return $combos;
  }
  
  /**
   * Return the primary key used in the ikey table. 
   * This is the product name + variation name without price difference information in all lowercase with no spaces.
   * Only letters and numbers are used.
   * 
   * @param string The variation name without the price difference
   * @return string
   */
  public function getInventoryKey($variationName='') {
    /*$key = strtolower($this->id . $this->name . $variationName);
    $key = str_replace(' ', '', $key);
    $key = preg_replace('/\W/', '', $key);
    return $key;*/
	
	$key=$this->id;
	if($variationName != '')
	{
		$key=$key.'_'.str_replace(',', '_', $variationName);
	}
	
	return $key;
  }
  
  public function insertInventoryData() {
    $keys = array();
    $combos = $this->getAllOptionCombinations();
    if(count($combos)) {
      foreach($combos as $c) {
        $key = $this->getInventoryKey($c);
        $keys[] = $key;
      }
    }
    else {
      // There are no product variations
      $key = $this->getInventoryKey();
      $keys[] = $key;
    }
    
    foreach($keys as $key) {
      $inventory = SimpleEcommCartCommon::getTableName('inventory');
      
      // Only insert new rows
      $sql = "SELECT ikey from $inventory where ikey = %s";
      $stmt = $this->_db->prepare($sql, $key);
      $foundKey = $this->_db->get_var($stmt);
      if(!$foundKey) {
        $sql = "INSERT into $inventory (ikey, track, product_id, quantity) VALUES (%s,%d,%d,%d)";
        $stmt = $this->_db->prepare($sql, $key, 0, $this->id, 0);
        $this->_db->query($stmt);
      }
      
    }
    
    // Delete obsolete inventory rows
    $keyList = implode("','", $keys);
    $sql = "DELETE from $inventory where product_id=$this->id and ikey not in ('$keyList')";
    $this->_db->query($sql);
  }
  
  public function updateInventoryFromPost($ikey) {
    $inventory = SimpleEcommCartCommon::getTableName('inventory');
    $track = SimpleEcommCartCommon::postVal("track_$ikey");
    $qty = SimpleEcommCartCommon::postVal("qty_$ikey");
    $sql = "UPDATE $inventory set track=%d, quantity=%d where ikey=%s";
    $sql = $this->_db->prepare($sql, $track, $qty, $ikey);
    $this->_db->query($sql);
	
	SimpleEcommCartCommon::checkAndSendStockNotification($ikey);
  }
  
  public function setInventoryLevel($ikey, $qty) {
    $inventory = SimpleEcommCartCommon::getTableName('inventory');
    $sql = "UPDATE $inventory set quantity=%d where ikey=%s";
    $sql = $this->_db->prepare($sql, $qty, $ikey);
    $this->_db->query($sql);
	
	
	SimpleEcommCartCommon::checkAndSendStockNotification($ikey);
  }
  
  public function getInventoryCount($ikey) {
    $inventory = SimpleEcommCartCommon::getTableName('inventory');
    $sql = "SELECT quantity from $inventory where ikey=%s";
    $sql = $this->_db->prepare($sql, $ikey);
    $count = $this->_db->get_var($sql);
    return $count;
  }
   public function getProductId($ikey) {
    $inventory = SimpleEcommCartCommon::getTableName('inventory');
    $sql = "SELECT product_id from $inventory where ikey=%s";
    $sql = $this->_db->prepare($sql, $ikey);
    $product_id = $this->_db->get_var($sql);
    return $product_id;
  }
  public function getInventoryNamesAndCounts() {
    $counts = array();
    $ikeyList = $this->getInventoryKeyList();
    foreach($ikeyList as $comboName => $ikey) {
      if($this->isInventoryTracked($ikey)) {
        $counts[$comboName] = $this->getInventoryCount($ikey);
      }
      else {
        $counts[$comboName] = 'in stock';
      }
    }
    return $counts;
  }
  
  /**
   * Return an array of all inventory keys for this product
   */
  public function getInventoryKeyList() {
    $ikeyList = array();
    $combos = $this->getAllOptionCombinations();
    if(count($combos)) {
      foreach($combos as $c) {
        $k = $this->getInventoryKey($c);
        $n = $this->name . ': ' . $c;
        $ikeyList[$n] = $k;
      }
    }
    else {
      $ikeyList[$p->name] = $p->getInventoryKey();
    }
    
    return $ikeyList;
  }
  
  /**
   * Return true if this product is available in any variation for purchase.
   * 
   * If inventory is not tracked or if any variations of the product are in stock, true is returned.
   * Otherwise, false is returned.
   * 
   * @return boolean
   */
  public function isAvailable() {
    $isAvailable = false;
    $inventory = SimpleEcommCartCommon::getTableName('inventory');
    $sql = "SELECT count(*) from $inventory where product_id=$this->id";
    $found = $this->_db->get_var($sql);
    if($found) { 
		$out_of_stock_notification = SimpleEcommCartSetting::getValue("out_of_stock_notification");
		if($out_of_stock_notification=='1')
		{
			 $quantities = $this->getInventoryDataForSpecificProduct($this->id) ;
	  		 $false_count=0;
			 foreach($quantities as $iq)
			 {  
				$out_of_stock_threshhold =  SimpleEcommCartSetting::getValue("out_of_stock_threshhold")+0;
				if($iq->quantity <= $out_of_stock_threshhold)
				{
					$false_count++;
				} 
			 }
			 if($false_count==count($quantities))
			 {
			 	 $isAvailable = false;
			 }
			 else
			 {
			 	$isAvailable = true;
			 }
		}
		else
		{
			$sql = "SELECT sum(quantity) from $inventory where track=1 and product_id=$this->id";
		      $qty = $this->_db->get_var($sql);
		      if(is_numeric($qty) && $qty > 0) {
		        $isAvailable = true;
		      }
		      else {
		        $sql = "SELECT count(*) as c from $inventory where track=0 and product_id=$this->id";
		        $notTracked = $this->_db->get_var($sql);
		        if($notTracked > 0) {
		          $isAvailable = true;
		        }
		      }
		} 
      
    }
    else {
      // Inventory table hasn't been refreshed so ignore inventory tracking for this product
      $isAvailable = true;
    }
    return $isAvailable;
  }
  
  public function isInventoryTracked($ikey) {
    $inventory = SimpleEcommCartCommon::getTableName('inventory');
    $sql = "SELECT track from $inventory where ikey=%s";
    $sql = $this->_db->prepare($sql, $ikey);
    $track = $this->_db->get_var($sql);
    //SimpleEcommCartCommon::log("Is inventory tracked query: $sql");
    $isTracked = ($track == 1) ? true : false;
    return $isTracked;
  }
  
  public function pruneInventory(array $ikeyList) {
    $inventory = SimpleEcommCartCommon::getTableName('inventory');
    $list = "'" . implode("','", $ikeyList) . "'";
    $sql = "DELETE from $inventory where ikey not in ($list)";
    $this->_db->query($sql);
    //SimpleEcommCartCommon::log("Prune Inventory: $sql");
  }
  public function deleteInventoryByiKey($ikey) {
    $inventory = SimpleEcommCartCommon::getTableName('inventory'); 
    $sql = "DELETE FROM $inventory where ikey='$ikey'"; 
    $this->_db->query($sql);
  }
  public function deleteAllInventoryData($id) {
   	$inventory = SimpleEcommCartCommon::getTableName('inventory'); 
    $sql = "DELETE FROM $inventory where product_id='$id'"; 
    $this->_db->query($sql);
  }
  
 private function _buildOptionList($optNumber) {
	$select = '';
	if($optNumber==1)
	{
		$variation1s=array_filter(explode("|",$this->variation1_variations));
		$prices1s=array_filter(explode("|",$this->variation1_prices));
		$signs1s=array_filter(explode("|",$this->variation1_signs));
		
		if(count($variation1s)>0)
		{
			$select = "\n".$this->variation1_name.":<select name=\"options_$optNumber\" id=\"options_$optNumber\">";
			$i=0;
			foreach($variation1s as $v) {
				$price = $prices1s[$i];
                                if(  $prices1s[$i] == "" || $prices1s[$i] == 0 ){
                                  $price = 0;
                                }
								if($price>0) $opt= trim($v.' '.$signs1s[$i].' '.SIMPLEECOMMCART_CURRENCY_SYMBOL.$price);
								else $opt= trim($v);
				$select .= "\n\t<option value=\"" . htmlentities($opt) . "\">$opt</option>";
				$i++;
			}
			$select .= "\n</select>";
		}
	}
	else if($optNumber==2)
	{
		$variation2s=array_filter(explode("|",$this->variation2_variations));
		$prices2s=array_filter(explode("|",$this->variation2_prices));
		$signs2s=array_filter(explode("|",$this->variation2_signs));
		if(count($variation2s)>0)
		{
			$select = "\n".$this->variation2_name.":<select name=\"options_$optNumber\" id=\"options_$optNumber\">";
			$i=0;
			foreach($variation2s as $v) {
				
				$price = $prices2s[$i];
                                if(  $prices2s[$i] == "" || $prices2s[$i] == 0 ){
                                  $price = 0;
                                }
								
				if($price>0) $opt= trim($v.' '.$signs2s[$i].' '.SIMPLEECOMMCART_CURRENCY_SYMBOL.$price);
				else $opt= trim($v);
				
				$select .= "\n\t<option value=\"" . htmlentities($opt) . "\">$opt</option>";
				$i++;
			}
			$select .= "\n</select>"; 
		}
	} 
    return $select;
  }

  public function isDigital() {
    $isDigital = false;
    if(strlen($this->downloadPath) > 2 || strlen($this->digital_prdoduct_url) > 2) {
      $isDigital = true;
    }
    return $isDigital;
  }
  
  public function isShipped() {
    $isShipped = false;
    if($this->shipped > 0) {
      $isShipped = true;
    }
    return $isShipped;
  }
public function isTaxed() {
    $isTaxed = false;
    if($this->taxable  > 0) {
      $isTaxed = true;
    }
    return $isTaxed;
  }

  /**
   * Return the shipping rate for this product for the given shipping method
   */
  public function getShippingPrice($methodId) {
    $methodId = (isset($methodId) && is_numeric($methodId)) ? $methodId : 0;
    // Look to see if there is a specific setting for this product and the given shipping method
    $ratesTable = SimpleEcommCartCommon::getTableName('shipping_rates');
    $sql = "SELECT shipping_rate from $ratesTable where product_id = " . $this->id . " and shipping_method_id = $methodId";
    $rate = $this->_db->get_var($sql);
    if($rate === NULL) {
      // If no specific rate is set, return the default rate for the given shipping method
      $shippingMethods = SimpleEcommCartCommon::getTableName('shipping_methods');
      $sql = "SELECT default_rate from $shippingMethods where id=$methodId";
      $rate = $this->_db->get_var($sql);
    }
    return $rate;
  }
  
  public function getBundleShippingPrice($methodId) {
    $methodId = (isset($methodId) && is_numeric($methodId)) ? $methodId : 0;
    $ratesTable = SimpleEcommCartCommon::getTableName('shipping_rates');
    $shippingMethods = SimpleEcommCartCommon::getTableName('shipping_methods');
    
    // Look to see if there is a specific bundle rate for this product and the given shipping method
    $sql = "SELECT shipping_bundle_rate from $ratesTable where product_id = " . $this->id . " and shipping_method_id = $methodId";
    $rate = $this->_db->get_var($sql);
    if($rate === NULL) {
      // If no specific rate is set, return the default bundle rate for the given shipping method
      $sql = "SELECT default_bundle_rate from $shippingMethods where id=$methodId";
      $rate = $this->_db->get_var($sql);
      return $rate;
    }
    return $rate;
  }
  
  public function isMembershipProduct() {
    $isMembershipProduct = false;
    if($this->isMembershipProduct == 1) {
      $isMembershipProduct = true;
    }
    return $isMembershipProduct;
  }
  
  public function isSubscription() {
    $isSub = false;
    if(SIMPLEECOMMCART_PRO) {
      if($this->isSpreedlySubscription() || $this->isPayPalSubscription()) {
        $isSub = true;
      }
    }
    return $isSub;
  }
  
  public function isSpreedlySubscription() {
    $isSub = false;
    if(SIMPLEECOMMCART_PRO && (is_numeric($this->spreedlySubscriptionId) && $this->spreedlySubscriptionId > 0)) {
      $isSub = true;
    }
    return $isSub;
  }
  
  public function isPayPalSubscription() {
    $isPayPalSubscription = false;
    if(SIMPLEECOMMCART_PRO && $this->isPaypalSubscription == 1) {
      $isPayPalSubscription = true;
    }
    return $isPayPalSubscription;
  }
  
  public static function getProductIdByGravityFormId($id) {
    global $wpdb;
    $products = SimpleEcommCartCommon::getTableName('products');
    $sql = "SELECT id from $products where gravity_form_id = %d";
    $query = $wpdb->prepare($sql, $id);
    $productId = $wpdb->get_var($query);
    return $productId;
  }
  
  public static function getNonSubscriptionProducts() {
    global $wpdb;
    $subscriptions = array();
    $product = new SimpleEcommCartProduct();
    $products = $product->getModels();
    foreach($products as $p) {
      if(!$p->isSubscription()) {
        $subscriptions[] = $p;
      }
    }
    return $subscriptions;
  }
  
  public static function getSubscriptionProducts() {
    global $wpdb;
    $subscriptions = array();
    $product = new SimpleEcommCartProduct();
    $products = $product->getModels();
    foreach($products as $p) {
      if($p->isSubscription()) {
        $subscriptions[] = $p;
      }
    }
    return $subscriptions;
  }
  
  public static function getSpreedlyProducts() {
    global $wpdb;
    $subscriptions = array();
    $product = new SimpleEcommCartProduct();
    $products = $product->getModels("where spreedly_subscription_id > 0");
    foreach($products as $p) {
      if($p->isSpreedlySubscription()) {
        $subscriptions[] = $p;
      }
    }
    return $subscriptions;
  }
  
  public static function getMembershipProducts() {
    global $wpdb;
    $memberships = array();
    $product = new SimpleEcommCartProduct();
    $products = $product->getModels('where is_membership_product=1');
    foreach($products as $p) {
      if($p->isMembershipProduct()) {
        $memberships[] = $p;
      }
    }
    return $memberships;
  }
  
  /**
   * Return the pricing for PayPal or Spreedly subscription plan.
   * The PayPal pricing takes precedence over the Spreedly pricing, 
   * but they should both be the same. If the $showAll paramter is 
   * true then a detailed price summary of all attached subscriptions
   * is returned.
   * 
   * @return string
   */
  public function getRecurringPriceSummary() {
    $priceSummary = "No recurring pricing";
    $paypalPriceSummary = false;
    $spreedlyPriceSummary = false;
    
    if($this->isPayPalSubscription()) {
      if(class_exists('SimpleEcommCartPayPalSubscription')) {
        $subscription = new SimpleEcommCartPayPalSubscription($this->id);
        $priceSummary = $subscription->getPriceDescription();
      }
    }
    elseif($this->isSpreedlySubscription()) {
      if(class_exists('SpreedlySubscription')) {
        if($this->isSubscription()) {
          $subscription = new SpreedlySubscription();
          $subscription->load($this->spreedlySubscriptionId);
          $priceSummary = $subscription->getPriceDescription();
        }
      }
    }
    
    return $priceSummary;
  }
  
  /**
   * Return true if only one subscription is attached or if both attached subscriptions are 
   * for the same amount.
   * 
   * @return boolean
   */
  public function subscriptionMismatch() {
    $ok = false;
    if($this->isSpreedlySubscription() && $this->isPayPalSubscription()) {
      if(class_exists(SpreedlySubscription) && class_exists(SimpleEcommCartPayPalSubscription)) {
        $spreedly = new SpreedlySubscription();
        $spreedly->load($this->spreedlySubscriptionId);
        $paypal = new SimpleEcommCartPayPalSubscription($this->id);
        $paypalPrice = number_format($paypal->price, 2, '.', '');
        $paypalInterval = $paypal->billingInterval; 
        $paypalUnits = $paypal->billingIntervalUnit;
        $pp = $paypalPrice . '|' . $paypalInterval . '|' . $paypalUnits;
        
        $spreedlyPrice = number_format($spreedly->price, 2, '.', '');
        $spreedlyInterval = $spreedly->durationQuantity;
        $spreedlyUnits = $spreedly->durationUnits;
        $sp = $spreedlyPrice . '|' . $spreedlyInterval . '|' . $spreedlyUnits;
        
        
        $this->chargeLaterDurationQuantity . '&nbsp;' . $this->chargeLaterDurationUnits;
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Comparing: $pp <--> $sp" );
        if($pp != $sp) {
          $ok = true;
        }
      }
    }
    return $ok;
  }
  
  public function hasFreeTrial() {
    $hasFreeTrial = false;
    if($this->isSubscription()) {
      $subscription = new SpreedlySubscription();
      $subscription->load($this->spreedlySubscriptionId);
      $hasFreeTrial = $subscription->hasFreeTrial();
    }
    return $hasFreeTrial;
  }
  
  /**
   * Return the number of sales for the given month
   * 
   * @param int $month An integer between 1 and 12 inclusive
   * @param int $year The four digit year
   * @return int
   */
  public function getSalesForMonth($month, $year) {
    $orders = SimpleEcommCartCommon::getTableName('orders');
    $orderItems = SimpleEcommCartCommon::getTableName('order_items');
    $start = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year));
    $end = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year . ' +1 month'));
    $sql = "SELECT sum(oi.quantity) as num 
      from 
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = %s and
        oi.order_id = o.id and
        o.ordered_on >= '$start' and 
        o.ordered_on < '$end'
      ";
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  public function getSalesTotal() {
    $orders = SimpleEcommCartCommon::getTableName('orders');
    $orderItems = SimpleEcommCartCommon::getTableName('order_items');
    $sql = "SELECT sum(oi.quantity) as num 
      from 
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = %s and
        oi.order_id = o.id
      ";
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  public function getIncomeTotal() {
    $orders = SimpleEcommCartCommon::getTableName('orders');
    $orderItems = SimpleEcommCartCommon::getTableName('order_items');
    $sql = "SELECT sum(oi.product_price * oi.quantity) as num 
      from 
        $orders as o, 
        $orderItems as oi 
      where
        oi.product_id = %s and
        oi.order_id = o.id
      ";
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  public function getIncomeForMonth($month, $year) {
    $orders = SimpleEcommCartCommon::getTableName('orders');
    $orderItems = SimpleEcommCartCommon::getTableName('order_items');
    $start = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year));
    $end = date('Y-m-d 00:00:00', strtotime($month . '/1/' . $year . ' +1 month'));
    
    $sql = "SELECT sum(oi.product_price * oi.quantity) as total
      FROM
        $orders as o,
        $orderItems as oi
      WHERE
        oi.product_id = %s and
        oi.order_id = o.id and
        o.ordered_on >= '$start' and 
        o.ordered_on < '$end'
      ";
       
    $query = $this->_db->prepare($sql, $this->id);
    $total = $this->_db->get_var($query);
    return $total;
  }
  
  public function validate() {
    $errors = array();
    
	 // Verify that the Product name is present
    if(empty($this->name)) {
      $errors['name'] = "Product name is required";
    }
	
	 // Verify that the Product price is present
    if(empty($this->price)) {
      $errors['price'] = "Product price is required";
    }
	
    // Verify that the item number is present
    //if(empty($this->item_number)) {
      //$errors['item_number'] = "Item number is required";
    //}
    
    if(empty($this->spreedlySubscriptionId))  {
      $this->spreedlySubscriptionId = 0;
    }
    
    // Verify that no other products have the same item number
    if(empty($errors)) {
      $sql = "SELECT count(*) from $this->_tableName where item_number = %s and id != %d";
      $sql = $this->_db->prepare($sql, $this->item_number, $this->id);
      $count = $this->_db->get_var($sql);
      if($count > 0) {
        $errors['item_number'] = "The item number must be unique";
      }
    }
    
    // Verify that if the product has been saved and there is a download path that there is a file located at the path
    if(!empty($this->download_path)) {
      $dir = SimpleEcommCartSetting::getValue('product_folder');
      if(!file_exists($dir . DIRECTORY_SEPARATOR . $this->download_path)) {
        $errors['download_file'] = "There is no file available at the download path: " . $this->download_path;
      }
    }

    return $errors;
  }
  
  /**
   * Check the gravity form entry for the quantity field.
   * Return the quanity in the field, or 1 if no quantity can be found.
   * 
   * @return int
   * @access public
   */
  public function gravityCheckForEntryQuantity($gfEntry) {
    $qty = 1;
    $qtyId = $this->gravity_form_qty_id;
    if($qtyId > 0) {
      if(isset($gfEntry[$qtyId]) && is_numeric($gfEntry[$qtyId])) {
        $qty = $gfEntry[$qtyId];
        unset($gfEntry[$qtyId]);
      }
    }
    return $qty;
  }
  
  public function gravityGetVariationPrices($gfEntry) {
    $options = array();
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Gravity Forms Entry:  " . print_r($gfEntry, true));
    foreach($gfEntry as $id => $value) {
      if($id != 'source_url') {
        $exp = '/[+-]\s*\$\d/';
        if(preg_match($exp, $value)) {
          $options[] = $value;
        }
      }
    }
    $options = implode('~', $options);
    return $options;
  }
  
  public function isGravityProduct() {
    $isGravity = false;
    if($this->gravity_form_id > 0) {
      $isGravity = true;
    }
    return $isGravity;
  }
  
  public function handleFileUpload() {
    // Check for file upload
    if(strlen($_FILES['product']['tmp_name']['upload']) > 2) {
      $dir = SimpleEcommCartSetting::getValue('product_folder');
      if($dir) {
        $filename = preg_replace('/\s/', '_', $_FILES['product']['name']['upload']);
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        $src = $_FILES['product']['tmp_name']['upload'];
        if(move_uploaded_file($src, $path)) {
          $_POST['product']['download_path'] = $filename;
        }
        else {
          $this->addError('File Upload', __("Unable to upload file","simpleecommcart"));
          $msg = "Could not upload file from $src to $path\n". print_r($_FILES, true);
          throw new SimpleEcommCartException($msg, 66101);
        }
      }
    }
	
	//check for product image upload 
	if(strlen($_FILES['product']['tmp_name']['image_upload']) > 2) {
      $dir = SimpleEcommCartSetting::getValue('product_folder');
      if($dir) {
        $filename = preg_replace('/\s/', '_', $_FILES['product']['name']['image_upload']);
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        $src = $_FILES['product']['tmp_name']['image_upload'];
        if(move_uploaded_file($src, $path)) {
          $_POST['product']['product_image_path'] = $filename;
        }
        else {
          $this->addError('Product Image File Upload', __("Unable to upload file","simpleecommcart"));
          $msg = "Could not upload file from $src to $path\n". print_r($_FILES, true);
          throw new SimpleEcommCartException($msg, 66101);
        }
      }
    }
	//product_image_path
	
	//check for custom button image upload
	if(strlen($_FILES['product']['tmp_name']['button_image_upload']) > 2) {
      $dir = SimpleEcommCartSetting::getValue('product_folder');
      if($dir) {
        $filename = preg_replace('/\s/', '_', $_FILES['product']['name']['button_image_upload']);
        $path = $dir . DIRECTORY_SEPARATOR . $filename;
        $src = $_FILES['product']['tmp_name']['button_image_upload'];
        if(move_uploaded_file($src, $path)) {
          $_POST['product']['button_image_path'] = $filename;
        }
        else {
          $this->addError('Custom Button Image File Upload', __("Unable to upload file","simpleecommcart"));
          $msg = "Could not upload file from $src to $path\n". print_r($_FILES, true);
          throw new SimpleEcommCartException($msg, 66101);
        }
      }
    }
  }
  
  /**
   * Return the price to charge at checkout.
   * For subscriptions this may also include the first recurring payment if the recurring start number is 0. 
   * This function will return the exact product price if:
   *  - The product is not a subscription product
   *  - The product is a subscription with a free trial period
   */
  public function getCheckoutPrice() {
    $price = $this->price;
    if($this->isSpreedlySubscription()) {
      if(!$this->hasFreeTrial()) {
        $subscription = new SpreedlySubscription();
        $subscription->load($this->spreedlySubscriptionId);
        $price += $subscription->price;
      }
    }
    elseif($this->isPayPalSubscription()) {
      $price = $this->setupFee;
      $plan = $this->getPayPalSubscription();
      if($plan->startRecurringNumber == 0) {
        if($plan->offerTrial) {
          $price += $plan->trialPrice;
        }
        else {
          $price += $plan->price;
        }
      }
    }
    return $price;
  }
  
  /**
   * Return a description of the subscription rate such as $10 / 1 month
   * 
   * @return string
   */
  public function getSubscriptionPriceSummary() {
    $desc = '';
    if($this->isSpreedlySubscription()) {
      $subscription = new SpreedlySubscription();
      $subscription->load($this->spreedlySubscriptionId);
      $desc = $subscription->getPriceDescription();
    }
    return $desc;
  }
  
  public function getPriceDescription($priceDifference=0) {
    if($this->id > 0) {
      if($this->isSpreedlySubscription()) {
        $price = $this->price + $priceDifference;

        if($price > 0) {
          $priceDescription = SIMPLEECOMMCART_CURRENCY_SYMBOL . number_format($price, 2);
        }

        if($this->hasFreeTrial()) {
          $priceDescription = "Free Trial";
        }
        else {
          if($price > 0) { $priceDescription .= ' (one time) +<br/> '; }
          $priceDescription .= $this->getSubscriptionPriceSummary();
        }
        
        $proRated = $this->getProRateInfo();
        if(is_object($proRated) && $proRated->amount > 0) {
          $proRatedInfo = $proRated->description . ':&nbsp;' . $proRated->money;
          $priceDescription .= '<br/>' . $proRatedInfo;
        }
        
      }
      elseif($this->isPayPalSubscription()) {
        $plan = new SimpleEcommCartPayPalSubscription($this->id);
        $priceDescription = '';
        if($plan->offerTrial) {
          $priceDescription .= $plan->getTrialPriceDescription();
        }
        else {
          $priceDescription .= $plan->getPriceDescription();
        }
      }
      else {
        // SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product custom price description: $this->priceDes");
        if(!empty($this->priceDescription)) {
          $priceDescription = $this->priceDescription;
        }
        else {
          //$priceDescription = SIMPLEECOMMCART_CURRENCY_SYMBOL . number_format($this->price + $priceDifference, 2);
		  $priceDescription = $this->priceDescription;
        }
      }
    }
    return $priceDescription;
  }
  
  /**
   * Return information about pro-rated credit or false if there is none.
   * 
   * Returns a standard object:
   *   $data->description = The description of the credit
   *   $data->amount = The monetary amount of the credit
   *   $data->money = The formated monetary amount of the credit
   * 
   * return object or false
   */
  public function getProRateInfo() {
    $data = false;
    if($this->isSpreedlySubscription()) {
      if(SimpleEcommCartCommon::isLoggedIn() && SimpleEcommCartSession::get('SimpleEcommCartCart')) {
        if($subscriptionId = SimpleEcommCartSession::get('SimpleEcommCartCart')->getSpreedlySubscriptionId()) {
          try {
            $invoiceData = array(
              'subscription-plan-id' => $subscriptionId,
              'subscriber' => array(
                'customer-id' => SimpleEcommCartSession::get('SimpleEcommCartAccountId')
              )
            );
            $invoice = new SpreedlyInvoice();
            $invoice->createFromArray($invoiceData);
            $this->_creditAmount = abs((float)$invoice->invoiceData->{'line-items'}->{'line-item'}[1]->amount);

            $data = new stdClass();
            $data->description = $invoice->invoiceData->{'line-items'}->{'line-item'}[1]->description;
            $data->amount = $this->_creditAmount;
            $data->money = SIMPLEECOMMCART_CURRENCY_SYMBOL . number_format($this->_creditAmount, 2);

            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] PRICE DESCRIPTON CREDIT: $proRated");
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Spreedly Invoice: " . print_r($invoice->invoiceData, true));
          }
          catch(SpreedlyException $e) {
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Unable to locate spreedly customer: " . SimpleEcommCartSession::get('SimpleEcommCartAccountId'));
          }
        }
      }
    }
    
    return $data;
  }
  
  /**
   * Return the SimpleEcommCartPayPalSubscription associated with this products paypal subscription id.
   * If no paypal subscription is attached to this product, return false.
   * 
   * @return SimpleEcommCartPayPalSubscription
   */
  public function getPayPalSubscription() {
    $sub = false;
    if($this->isPayPalSubscription()) {
      if(class_exists('SimpleEcommCartPayPalSubscription')) {
        $sub = new SimpleEcommCartPayPalSubscription($this->id);
      }
    }
    return $sub;
  }
  
  /**
   * Override base class save method by validating the data before and after saving.
   * Return the product id of the saved product.
   * Throw SimpleEcommCartException if the save fails.
   * 
   * @return int The product id
   * @throws SimpleEcommCartException on save failure
   */
  public function save() {
    $errors = $this->validate();
    if(count($errors) == 0) {
      $productId = parent::save();
      $errors = $this->validate();
    }
    if(count($errors)) {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . get_class($this) . " save errors: " . print_r($errors, true));
      $this->setErrors($errors);
      $errors = print_r($errors, true);
      throw new SimpleEcommCartException('Product save failed: ' . $errors, 66102);
    }
    return $productId;
  }
  
  
  
  
  // written by Dipankar Biswas
  public function saveInventoryData($quantities)
  {
  	  //delete all inventory items first
  	  $sql = "DELETE from ".SimpleEcommCartCommon::getTableName('inventory')." ";
      $this->_db->query($sql); 
	  
	  foreach($quantities as $key => $value)
	  {
	  	  $items=array_filter(explode("_",$key));
		  $product_id=$items[0];
	  	  $sql = "INSERT INTO ".SimpleEcommCartCommon::getTableName('inventory')." (ikey,product_id,quantity,track) values('$key',$product_id,$value,'1')";
      $this->_db->query($sql); 
	  }
  }
  public function updateBulkPrice($data,$checks)
  {
  	  if($data["selection"]=="all")
	  {
	  	$allproducts=$this->getNonSubscriptionProducts();
		foreach($allproducts as $p)
		{
			$basePrice = $p->price;
			if($data["type"]=="$")
			{
				if($data["increment"]=="increase")
				{
					$basePrice += $data["amount"];
				}
				else
				{
					$basePrice -= $data["amount"];
				}
			}
			else
			{
				if($data["increment"]=="increase")
				{
					$basePrice += ($basePrice * $data["amount"])/100.0;
				}
				else
				{
					$basePrice -= ($basePrice * $data["amount"])/100.0;
				}
			}
			if($basePrice>0)
			{
				$p->price=$basePrice;
				$p->save();
				$p->clear();
			}
			
		}
	  }
	  else
	  {
	  	foreach($checks as $key => $value)
	  	{
			if($value=="on")
			{
				 $items=array_filter(explode("_",$key));
		  		 $product_id=$items[1];
				 $p=new SimpleEcommCartProduct($product_id);
				 $basePrice = $p->price;
					if($data["type"]=="$")
					{
						if($data["increment"]=="increase")
						{
							$basePrice += $data["amount"];
						}
						else
						{
							$basePrice -= $data["amount"];
						}
					}
					else
					{
						if($data["increment"]=="increase")
						{
							$basePrice += ($basePrice * $data["amount"])/100.0;
						}
						else
						{
							$basePrice -= ($basePrice * $data["amount"])/100.0;
						}
					}
					if($basePrice>0)
					{
						$p->price=$basePrice;
						$p->save();
						$p->clear();
					}
					
			}
	  	}
	  }
  }
  
  
  public function getInventoryData()
  {
  	$sql="SELECT ikey,quantity from ".SimpleEcommCartCommon::getTableName('inventory')." ";
    $sql = $this->_db->prepare($sql,null);
	$data = $this->_db->get_results($sql,OBJECT_K);
	 
	
	$quantities=array();
	
	foreach($data as $item)
	{
		 $key= $item->ikey;
		 $val=$item->quantity;
		 $quantities[$key]=$val;
	}
	return $quantities;
  }
  public function getInventoryDataForSpecificProduct($id)
  {
  	$sql="SELECT ikey,quantity from ".SimpleEcommCartCommon::getTableName('inventory')." where product_id=".$id."";
    $sql = $this->_db->prepare($sql,null);
	$data = $this->_db->get_results($sql,OBJECT_K);
	 
	return $data;
  }
  /**
     Method to calculate how many individual product sold
  */
  public function getProductSoldQnty() {
    $orders = SimpleEcommCartCommon::getTableName('orders');
    $orderItems = SimpleEcommCartCommon::getTableName('order_items');
    $sql = "SELECT sum(oi.quantity) as num
      from
        $orders as o,
        $orderItems as oi
      where
        oi.product_id = %s and
        oi.order_id = o.id
      ";
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
  
  public function getMaximumProductId()
  { 
    $sql = "SELECT MAX( id ) FROM ".SimpleEcommCartCommon::getTableName('products');
    $query = $this->_db->prepare($sql, $this->id);
    $num = $this->_db->get_var($query);
    return $num;
  }
}

