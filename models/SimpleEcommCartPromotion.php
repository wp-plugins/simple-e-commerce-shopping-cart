<?php
class SimpleEcommCartPromotion extends SimpleEcommCartModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = SimpleEcommCartCommon::getTableName('promotions');
    parent::__construct($id);
  }
  
  public function getAmountDescription() {
    $amount = 'not set'; 
		
    if($this->id > 0) {
		if($this->active=='0')
		{
			$amount="";
		}
		else
		{
			 if($this->type == 'dollar') {
		        //$amount = SIMPLEECOMMCART_CURRENCY_SYMBOL . number_format($this->amount, 2, '.', ',') . ' off';
				$amount = SIMPLEECOMMCART_CURRENCY_SYMBOL . number_format(SimpleEcommCartSession::get('SimpleEcommCartCart')->getDiscountAmount(), 2, '.', ',') . ' off';
		     }
		     elseif($this->type == 'percentage') {
		        //$amount = number_format($this->amount, 0) . '% off';
				$amount = SIMPLEECOMMCART_CURRENCY_SYMBOL . number_format(SimpleEcommCartSession::get('SimpleEcommCartCart')->getDiscountAmount(), 2, '.', ',') . ' off';
				
				
		     }
		}
     
    }
    return $amount;
  }
  
  public function getMinOrderDescription() {
    $min = $this->minOrder;
    if($min > 0) {
      $min = SIMPLEECOMMCART_CURRENCY_SYMBOL . $min;
    }
    else {
      $min = "Apply to all orders";
    }
    return $min;
  }
  
  public function save() {
    $this->_data['code'] = strtoupper($this->_data['code']);
	$errors = $this->validate();
    if(count($errors) == 0) {
       parent::save(); 
    }
    if(count($errors)) {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] " . get_class($this) . " save errors: " . print_r($errors, true));
      $this->setErrors($errors);
      $errors = print_r($errors, true);
      throw new SimpleEcommCartException('Coupon save failed: ' . $errors, 66103);
    } 
  }
  
  public function loadByCode($code) {
    $loaded = false;
    $sql = "SELECT * from $this->_tableName where code = %s";
    $sql = $this->_db->prepare($sql, $code);
    if($data = $this->_db->get_row($sql, ARRAY_A)) {
      $this->setData($data);
      $loaded = true;
    }
    return $loaded;
  }
  
  public function discountTotal($total) { 
  	if($this->active=='0') return $total;
	
    if($total >= $this->minOrder) {
      if($this->type == 'dollar') {
	    if($total>$this->amount) 
        	$total = $total - $this->amount;
      }
      elseif($this->type == 'percentage') {
        $total = $total * ((100 - $this->amount)/100);
      }
    }
    // SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Calculated discount total: $total");
    return $total;
  }
 public function validate() {
    $errors = array();
    
    // Verify that the item number is present
    if(empty($this->code)) {
      $errors['code'] = "Coupon Code is required";
    } 
    return $errors;
  }
  public static function redeemCoupon($code)
  {
  	  //need to implement this
	   SimpleEcommCartCommon::log("Coupon Code:".$code);
	   $promo = new SimpleEcommCartPromotion();
	   $promo->loadByCode($code);
	   if($promo!=NULL)
	   {
	   		$promo->redemption_count = $promo->redemption_count +1; 
	   		$promo->save();
	   }
  }
}