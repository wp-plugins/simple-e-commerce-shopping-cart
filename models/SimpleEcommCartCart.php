<?php
class SimpleEcommCartCart {
  
  /**
   * An array of SimpleEcommCartCartItem objects
   */
  private $_items;
  
  private $_promotion;
  private $_promoStatus;
  private $_shippingMethodId;
  
  
  public function __construct($items=null) {
    if(is_array($items)) {
      $this->_items = $items;
    }
    else {
      $this->_items = array();
    }
    $this->_promoStatus = 0;
    $this->_setDefaultShippingMethodId();
     
  }
  
  /**
   * Add an item to the shopping cart when an Add To Cart button is clicked.
   * Combine the product options, check inventory, and add the item to the shopping cart.
   * If the inventory check fails redirect the user back to the referring page.
   * This function assumes that a form post triggered the call.
   */
  public function addToCart() {
    $itemId = SimpleEcommCartCommon::postVal('simpleecommcartItemId');
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Adding item to cart: $itemId");

    $options = '';
    if(isset($_POST['options_1'])) {
      $options = SimpleEcommCartCommon::postVal('options_1');
    }
    if(isset($_POST['options_2'])) {
      $options .= '~' . SimpleEcommCartCommon::postVal('options_2');
    }
    
    if(isset($_POST['item_quantity'])) {
      $itemQuantity = ($_POST['item_quantity'] > 0) ? round($_POST['item_quantity'],0) : 1;
    }
    else{
      $itemQuantity = 1;
    }
    
    if(isset($_POST['item_user_price'])){
      $sanitizedPrice = preg_replace("/[^0-9\.]/","",$_POST['item_user_price']);
      SimpleEcommCartSession::set("userPrice_$itemId",$sanitizedPrice);
    }

    if(SimpleEcommCartProduct::confirmInventory($itemId, $options)) {
      $this->addItem($itemId, $itemQuantity, $options);
    }
    else {
      SimpleEcommCartCommon::log("Item not added due to inventory failure");
      wp_redirect($_SERVER['HTTP_REFERER']);
    }
	
	//set default shipping and billing country
	if(!SimpleEcommCartSession::get('simpleecommcart_shipping_country'))
	{
		if($this->requireShipTo())
		{
			$home_country = SimpleEcommCartSetting::getValue('home_country'); 
			$country='';
			foreach(SimpleEcommCartCommon::getCountries(true) as $code => $name) {
				$country = "$code~$name" ;
				break;
			}
			if( $home_country != '00~All Countries') $country = $home_country;
			SimpleEcommCartSession::set('simpleecommcart_shipping_country', $country); 
		} 
	}
	if(!SimpleEcommCartSession::get('simpleecommcart_billing_country'))
	{
		if($this->requireBillTo()){ 
			$home_country = SimpleEcommCartSetting::getValue('home_country'); 
			$country='';
			foreach(SimpleEcommCartCommon::getCountries(true) as $code => $name) {
				$country = "$code~$name" ;
				break;
			}
			if( $home_country != '00~All Countries') $country = $home_country;
			SimpleEcommCartSession::set('simpleecommcart_billing_country', $country); 
		}
	}
	
	//reset promotion 
	$this->_promotion = null; 
  }
  
  public function updateCart() {
    if(SimpleEcommCartCommon::postVal('updateCart') == 'Calculate Shipping') {
      SimpleEcommCartSession::set('simpleecommcart_shipping_zip', SimpleEcommCartCommon::postVal('shipping_zip'));
      SimpleEcommCartSession::set('simpleecommcart_shipping_country_code', SimpleEcommCartCommon::postVal('shipping_country_code'));
    }
	if(SimpleEcommCartCommon::postVal('updateCart') == 'Calculate Tax & Shipping' ||
		SimpleEcommCartCommon::postVal('updateCart') == 'Calculate Tax' || 
		SimpleEcommCartCommon::postVal('updateCart') == 'Calculate Shipping') {
		SimpleEcommCartSession::set('simpleecommcart_shipping_country', SimpleEcommCartCommon::postVal('shipping_country'));
		if(SimpleEcommCartCommon::postVal('shipping_country')=='US~United States')
		{
			SimpleEcommCartSession::set('simpleecommcart_shipping_state', SimpleEcommCartCommon::postVal('shipping_state_usa'));
		}
		else
		{
			SimpleEcommCartSession::set('simpleecommcart_shipping_state', SimpleEcommCartCommon::postVal('shipping_state_canada'));
		}
		SimpleEcommCartSession::set('simpleecommcart_billing_country', SimpleEcommCartCommon::postVal('billing_country'));
		if(SimpleEcommCartCommon::postVal('billing_country')=='US~United States')
		{
			SimpleEcommCartSession::set('simpleecommcart_billing_state', SimpleEcommCartCommon::postVal('billing_state_usa'));
		}
		else
		{
			SimpleEcommCartSession::set('simpleecommcart_billing_state', SimpleEcommCartCommon::postVal('billing_state_canada'));
		}
		
		SimpleEcommCartSession::set('simpleecommcart_shipping_variations', SimpleEcommCartCommon::postVal('shipping_variations'));
	}
	
	if(SimpleEcommCartCommon::postVal('updateCart') == 'UPDATE CART') 
	{
		//reset promotion 
		$this->_promotion = null;
	}
	
	if(SimpleEcommCartCommon::postVal('updateCart') == 'CLEAR CART') 
	{
		 //clear cart 
		 foreach($this->_items as $item) {
     		$this->removeItemByProductId($item->getProductId()) ;
    	 } 
		 $this->_promotion = null;
		 return;
	}
	
    $this->_setShippingMethodFromPost();
    $this->_updateQuantitiesFromPost();
    $this->_setCustomFieldInfoFromPost();
    $this->_setPromoFromPost();
    
	
	
    SimpleEcommCartSession::touch();
    do_action('simpleecommcart_after_update_cart', $this);
  }
  
  public function addItem($id, $qty=1, $optionInfo='', $formEntryId=0) {
    $optionInfo = $this->_processOptionInfo($optionInfo);
    $product = new SimpleEcommCartProduct($id);
    
    if($product->id > 0) {
      
      $newItem = new SimpleEcommCartCartItem($product->id, $qty, $optionInfo->options, $optionInfo->priceDiff);
      
      if( ($product->isSubscription() || $product->isMembershipProduct()) && ($this->hasSubscriptionProducts() || $this->hasMembershipProducts() )) {
        // Make sure only one subscription can be added to the cart. Spreedly only allows one subscription per subscriber.
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] about to remove membership item");
        $this->removeMembershipProductItem();
      }
      
      if($product->isGravityProduct()) {
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product being added is a Gravity Product: $formEntryId");
        if($formEntryId > 0) {
          $newItem->addFormEntryId($formEntryId);
          $this->_items[] = $newItem;
        }
      }
      else {
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Product being added is NOT a Gravity Product");
        $isNew = true;
        $newItem->setQuantity($qty);
        foreach($this->_items as $item) {
          if($item->isEqual($newItem)) {
            $isNew = false;
            $newQuantity = $item->getQuantity() + $qty;
            $item->setQuantity($newQuantity);
            if($formEntryId > 0) {
              $item->addFormEntryId($formEntryId);
            }
            break;
          }
        }
        if($isNew) {
          $this->_items[] = $newItem;
        }
      }
      
      
      SimpleEcommCartSession::touch();
      do_action('simpleecommcart_after_add_to_cart', $product, $qty);
    }
    
  }
  
  public function removeItem($itemIndex) {
    if(isset($this->_items[$itemIndex])) {
      $product = $this->_items[$itemIndex]->getProduct();
      $this->_items[$itemIndex]->detachAllForms();
      if(count($this->_items) <= 1) {
        $this->_items = array();
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Reset the cart items array");
      }
      else {
        unset($this->_items[$itemIndex]);
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Did not reset the cart items array because the cart contains more than just a membership item");
      }
      SimpleEcommCartSession::touch();
      do_action('simpleecommcart_after_remove_item', $this, $product);
    }
  }
  
  public function removeItemByProductId($productId) {
    foreach($this->_items as $index => $item) {
      if($item->getProductId() == $productId) {
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Removing item at index: $index");
        $this->removeItem($index);
      }
    }
  }
  
  public function removeMembershipProductItem() {
    foreach($this->_items as $item) {
      if($item->isMembershipProduct() || $item->isSubscription()) {
        $productId = $item->getProductId();
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Removing membership item with product id: $productId");
        $this->removeItemByProductId($productId);
      }
    }
  }
  
  public function setPriceDifference($amt) {
    if(is_numeric($amt)) {
      $this->_priceDifference = $amt;
    }
  }
  
  public function setItemQuantity($itemIndex, $qty) {
    if(is_numeric($qty)) {
      if(isset($this->_items[$itemIndex])) {
        if($qty == 0) {
          unset($this->_items[$itemIndex]);
        }
        else {
          $this->_items[$itemIndex]->setQuantity($qty);
        }
      }
    }
  }
  
  public function setCustomFieldInfo($itemIndex, $info) {
    if(isset($this->_items[$itemIndex])) {
      $this->_items[$itemIndex]->setCustomFieldInfo($info);
    }
  }
  
  /**
   * Return the number of items in the shopping cart.
   * This count includes multiples of the same product so the returned value is the sum 
   * of all the item quantities for all the items in the cart.
   * 
   * @return int
   */
  public function countItems() {
    $count = 0;
    foreach($this->_items as $item) {
      $count += $item->getQuantity();
    }
    return $count;
  }
  
  public function getItems() {
    return $this->_items;
  }
  
  public function getItem($itemIndex) {
    $item = false;
    if(isset($this->_items[$itemIndex])) {
      $item = $this->_items[$itemIndex];
    }
    return $item;
  }
  
  public function setItems($items) {
    if(is_array($items)) {
      $this->_items = $items;
    }
  }
  
  public function getSubTotal() {
    $total = 0;
    foreach($this->_items as $item) {
      $total += $item->getProductPrice() * $item->getQuantity();
    }
    return $total;
  }
  
  public function getSubscriptionAmount() {
    $amount = 0;
    if($subId = $this->getSpreedlySubscriptionId()) {
      $subscription = new SpreedlySubscription();
      $subscription->load($subId);
      if(!$subscription->hasFreeTrial()) {
        $amount = (float) $subscription->amount;
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Subscription amount for subscription id: $subId = " . $subscription->amount);
      }
    }
    return $amount;
  }
  
  /**
   * Return the subtotal without including any of the subscription prices
   * 
   * @return float
   */
  public function getNonSubscriptionAmount() {
    $total = 0;
    foreach($this->_items as $item) {
      if(!$item->isSubscription()) {
        $total += $item->getProductPrice() * $item->getQuantity();
      }
      elseif($item->isPayPalSubscription()) {
        $total += $item->getProductPrice();
      }
    }
    return $total;
  }
  
  public function getTaxableAmount() {
    $total = 0;
    $p = new SimpleEcommCartProduct();
    foreach($this->_items as $item) {
      $p->load($item->getProductId());
      if($p->taxable == 1) {
        $total += $item->getProductPrice() * $item->getQuantity();
      }
    }
    $discount = $this->getDiscountAmount();
    if($discount > $total) {
      $total = 0;
    }
    else {
      $total = $total - $discount;
    }
    return $total;
  }

  public function getTax($state='All Sales', $zip=null) {
    $tax = 0;
    
	$tax_settings = unserialize(SimpleEcommCartSetting::getValue('tax_settings')); 
	 	
	//flat rate
	$flat_rate = $tax_settings["flat_rate"];
	$logic = $tax_settings["logic"]; 
	
	if($logic == '1')
	{
		//Apply flat tax rate on all the products 
		$p = new SimpleEcommCartProduct();
		$taxable = 0;
		foreach($this->_items as $item) {
			$p->load($item->getProductId());
			$taxable += $item->getProductPrice() * $item->getQuantity(); 
		}
		$taxable = $taxable - $this->getDiscountAmount();
		$tax = number_format($taxable * ($flat_rate/100), 2, '.', '');
	}
	else if($logic == '2')
	{
		//Apply flat tax rate on products which have product specific tax
		$p = new SimpleEcommCartProduct();
		$taxable = 0;
		foreach($this->_items as $item) {
			$p->load($item->getProductId());
			if($p->taxable == 1) {
				$discountSingle = $this->getDiscountAmountForSingleProduct($item->getProductId());
			 
				$taxable += $item->getProductPrice() * $item->getQuantity() - $discountSingle;
			}
		} 
		//echo '$taxable:'.$taxable;
		$tax = number_format($taxable * ($flat_rate/100), 2, '.', ''); 
	} 
	if($tax<0)$tax=0;  
   	return $tax;
  }
 
  /**
   * Return an array of the shipping methods where the keys are names and the values are ids
   * 
   * @return array of shipping names and ids
   */
  public function getShippingMethods() {
    $method = new SimpleEcommCartShippingMethod();
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate asc');
    $ship = array();
    foreach($methods as $m) {
      $ship[$m->name] = $m->id;
    }
    return $ship;
  }

  public function getCartWeight() {
    $weight = 0;
    foreach($this->_items as $item) {
      $weight += $item->getWeight()  * $item->getQuantity();
    }
    return $weight;
  }
  
  public function getShippingCost($methodId=null) {
    $setting = new SimpleEcommCartSetting();
    $shipping = null;
    if(!$this->requireShipping()) { 
      $shipping = 0; 
    } 
    
	else
	{
		$shipping = $this->getFlatRate();
	} 
	
    return number_format($shipping, 2, '.', '');
  }
  public function isInternationalShipping()
  {
  	$shipping_country = SimpleEcommCartSession::get('simpleecommcart_shipping_country');
	$home_country=SimpleEcommCartSetting::getValue('home_country');
	if($shipping_country == $home_country)
	{
		return false;
	}
	return true;
  }
  public function getFlatRate()
  {
  	$flat_rate=0;
	
	$hasMultipleTypeProduct = (count($this->_items) > 1)?true:false;
	
	if(SimpleEcommCartSetting::getValue('shipping_options_flat_rate_option') == '1')
	{
		//Charge a Flat rate from the Individual Product configuration 
		if(!$hasMultipleTypeProduct)
		{
			//single type product in the cart
			foreach($this->_items as $item) 
		 	{
		 		$quantity = $item->getQuantity();
				$product = $item->getProduct();
			
				if($product->isDigital())continue; // skip digital products
				 
				if($this->isInternationalShipping())
				{
					//apply international
					if($quantity> 1)
					{
						$flat_rate+=$product->single_sihipping_cost_international+
							($product->multiple_sihipping_cost_international)*($quantity - 1);
					}
					else
					{
						$flat_rate+=$product->single_sihipping_cost_international;
					}
				}
				else
				{
					//apply local
					if($quantity> 1)
					{
						$flat_rate+=$product->single_sihipping_cost + 
							($product->multiple_sihipping_cost)*($quantity - 1);
					}
					else
					{
						$flat_rate+=$product->single_sihipping_cost;
					}
				}
			
		 	}
		}
		else
		{
			//multiple type products in the cart
			$higest_single_shpping_cost_local = 0; 
			$higest_single_shpping_cost_product_id_for_local = 0;
			$higest_single_shpping_cost_international=0;
			$higest_single_shpping_cost_product_id_for_international=0;
			foreach($this->_items as $item) 
		 	{ 
				$product = $item->getProduct();
				
				if($product->isDigital())continue; // skip digital products
				
				if($product->single_sihipping_cost>$higest_single_shpping_cost_local)
				{
					$higest_single_shpping_cost_local = $product->single_sihipping_cost;
					$higest_single_shpping_cost_product_id_for_local = $product->id;
				}
				
				if($product->single_sihipping_cost_international>$higest_single_shpping_cost_international)
				{
					$higest_single_shpping_cost_international = $product->single_sihipping_cost_international;
					$higest_single_shpping_cost_product_id_for_international = $product->id;
				}
			}
			
			foreach($this->_items as $item) 
		 	{
		 		$quantity = $item->getQuantity();
				$product = $item->getProduct();
				
				if($product->isDigital())continue; // skip digital products
				
				if($this->isInternationalShipping())
				{
					//apply international
					if($product->id == $higest_single_shpping_cost_product_id_for_international)
					{ 
						$flat_rate+=$product->single_sihipping_cost_international + 
								($product->multiple_sihipping_cost_international)*($quantity - 1); 
					}
					else
					{
						$flat_rate+= $product->multiple_sihipping_cost_international * $quantity; 
					}
				}
				else
				{
					//apply local
					if($product->id == $higest_single_shpping_cost_product_id_for_local)
					{ 
						$flat_rate+=$product->single_sihipping_cost + 
								($product->multiple_sihipping_cost)*($quantity - 1); 
					}
					else
					{
						$flat_rate+= $product->multiple_sihipping_cost * $quantity; 
					} 
				}
			
		 	}
			
		}
		 
	}
	else if(SimpleEcommCartSetting::getValue('shipping_options_flat_rate_option') == '2')
	{
		//Charge a Flat rate regardless the number of Items and location 
		
		//check for physical product
		$physical_product_found=false;
		foreach($this->_items as $item) 
		{ 
			 $product = $item->getProduct();
			 if($product->isDigital() == false)
			 {
			 	$physical_product_found=true;
				break;
			 }
		}
		 
		if($physical_product_found) 
		{
			if($this->isInternationalShipping())
			{
				$flat_rate += SimpleEcommCartSetting::getValue('shipping_options_flat_rate_option2_international');
			}
			else
			{ 
				$flat_rate += SimpleEcommCartSetting::getValue('shipping_options_flat_rate_option2_local');
			}
		} 
	}
	else if(SimpleEcommCartSetting::getValue('shipping_options_flat_rate_option') == '3')
	{
		//Charge a Flat rate for all individual product in the cart 
		if($this->isInternationalShipping())
		{
			foreach($this->_items as $item) 
			{
			 	$product = $item->getProduct();
			 	if($product->isDigital())continue;
			 	if(!$product->isShipped())continue;  
			 	$flat_rate += SimpleEcommCartSetting::getValue('shipping_options_flat_rate_option3_international') * $item->getQuantity();
			}
		}
		else
		{
			foreach($this->_items as $item) 
			{
				$product = $item->getProduct();
			 	if($product->isDigital())continue;
				if(!$product->isShipped())continue;  
			 	$flat_rate += SimpleEcommCartSetting::getValue('shipping_options_flat_rate_option3_local') * $item->getQuantity();
			} 
		}
		 
	}
	return $flat_rate;
  }
    
  function isDateValid($str)
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
  public function applyPromotion($code) {
    $code = strtoupper($code);
	$promotion = new SimpleEcommCartPromotion();
	if($promotion->loadByCode($code))
	{
		/*
		if(is_object($this->_promotion) && $this->_promotion->minOrder > $this->getNonSubscriptionAmount())
		{
			// Order total not high enough for promotion to apply
			$this->_promoStatus = -1;
			$this->_promotion = null;
		}
		else {
			$this->_promotion = $promotion;
			$this->_promoStatus = 1;
		}
		SimpleEcommCartSession::touch();
		*/	
		if($promotion->active=='1')
		{
			$applicable_for_products_in_the_cart = false;
			if($promotion->apply_for_all_products=='1')
			{
				$applicable_for_products_in_the_cart = true;
			}
			else
			{ 
				$product_ids = explode(",", $promotion->products);
				$found = 0;
				foreach($product_ids as $id )
				{  
    				foreach($this->_items as $item) 
					{ 
      					if($item->getProductId() == $id)
						{ 
							$found++;
						}
    				}
				}
				
				//if($found >= count($product_ids))
				if($found >0)
				{
					$applicable_for_products_in_the_cart = true;
				}
			}
		 
			$has_redemption_limit = false;
			if($promotion->redemption_limit > 0 &&
			$promotion->redemption_limit > $promotion->redemption_count)
			{
				$has_redemption_limit = true;
			} 
			 
			//echo 'isDateValid(start_date)'.$this->isDateValid($promotion->start_date).'<br>';
			//echo 'isDateValid(expiry_date)'.$this->isDateValid($promotion->expiry_date).'<br>';
			
			if($this->isDateValid($promotion->start_date) == false)
			{ 
				if($this->isDateValid($promotion->expiry_date) == true)
				{  
					$expiry_time = strtotime( $promotion->expiry_date );
					$expiry_date = date( "Y-m-d", $expiry_time);
					$today = date("Y-m-d",current_time('timestamp',0));
					  
					//echo 'expire_date:'.$expiry_date.'<br>';
				    //echo 'today:'.$today.'<br>';
				
						if($expiry_date > $today)
						{ 
							$not_expired =true;
						}
						else
						{
							$not_expired =false;
						}
				} 
				else if($this->isDateValid($promotion->expiry_date) == false)
				{ 
					$not_expired =true;
				}
			}
			else
			{
				$start_time = strtotime( $promotion->start_date );
				$start_date = date("Y-m-d",$start_time);
				$today = date("Y-m-d",current_time('timestamp',0));
				 
				//echo 'start_date:'.$start_date.'<br>';
				//echo 'today:'.$today.'<br>';
				//echo  '$start_date > $today:'.($start_date > $today).'<br>';
				//echo  '$start_date < $today:'.($start_date < $today).'<br>';
				//echo  '$start_date == $today:'.($start_date == $today).'<br>';
				//echo  '$start_date <= $today:'.($start_date <= $today).'<br>';
				if($start_date <= $today)
				{
					//cho '$start_date <= $today';
					
					if($this->isDateValid($promotion->expiry_date) == false)
					{
						$not_expired =true;
					} 
					else
					{
						$expiry_time = strtotime( $promotion->expiry_date );
						$expiry_date = date("Y-m-d",$expiry_time);
						
						if($expiry_date > $today)
						{ 
							$not_expired =true;
						}
						else
						{
							$not_expired =false;
						}
					}
				}
				else
				{
					//echo '!($start_date <= $today)';
					$not_expired =false;
				}
			}
			
			$valid_option=false;
			if($promotion->optional_option1_value>0)
			{
				if($promotion->optional_option1 == '1')
				{
					//product quantity
					 
					$product_ids = explode(",", $promotion->products);
					
					$found = 0;
					foreach($product_ids as $id )
					{  
    					foreach($this->_items as $item) 
						{ 
      						if($item->getProductId() == $id)
							{
								$qantity=$item->getQuantity();
								if($promotion->optional_option1_condition=='1')
								{
									//equal to
									if($qantity==$promotion->optional_option1_value)
									{
										$found++;
									}
								}
								else if($promotion->optional_option1_condition=='2')
								{
									//greater than
									if($qantity>$promotion->optional_option1_value)
									{
										$found++;
									} 
								}
								else if($promotion->optional_option1_condition=='3')
								{
									//less than
									if($qantity<$promotion->optional_option1_value)
									{
										$found++;
									} 
								}
							}
    					}
					}
					
					if($found >= count($product_ids)) $valid_option=true;
					else $valid_option=false;
				}
				else if($promotion->optional_option1 == '2')
				{
					//total product quantity
					$qantity=0;
					  
    				foreach($this->_items as $item) 
					{  
						$qantity+=$item->getQuantity(); 	 
    				}
					  
					if($promotion->optional_option1_condition=='1')
					{
						//equal to (at least)
						if($qantity==$promotion->optional_option1_value)
						{
							$valid_option=true;
						}
						else{
							$valid_option=false;
						}
					}
					else if($promotion->optional_option1_condition=='2')
					{
						//greater than
						if($qantity>$promotion->optional_option1_value)
						{
							$valid_option=true;
						}
						else{
							$valid_option=false;
						}
					}
					else if($promotion->optional_option1_condition=='3')
					{
						//less than
						if($qantity<$promotion->optional_option1_value)
						{
							$valid_option=true;
						}
						else{
							$valid_option=false;
						}
					}
				}
				else if($promotion->optional_option1 == '3')
				{
					//sub total cart amount
					$qantity=0;
					  
    				foreach($this->_items as $item) 
					{  
						$qantity+=$item->getQuantity() * $item->getProductPrice(); 	 
    				}
					 
					//product quantity
					if($promotion->optional_option1_condition=='1')
					{
						//equal to
						if($qantity==$promotion->optional_option1_value)
						{
							$valid_option=true;
						}
						else{
							$valid_option=false;
						}
					}
					else if($promotion->optional_option1_condition=='2')
					{
						//greater than
						if($qantity>$promotion->optional_option1_value)
						{
							$valid_option=true;
						}
						else{
							$valid_option=false;
						}
					}
					else if($promotion->optional_option1_condition=='3')
					{
						//less than
						if($qantity<$promotion->optional_option1_value)
						{
							$valid_option=true;
						}
						else{
							$valid_option=false;
						}
					}
				}
			}
			else
			{
				$valid_option= true;
			}
			
			//echo 'applicable_for_products_in_the_cart:'.$applicable_for_products_in_the_cart.'<br>';
			//echo 'has_redemption_limit:'.$has_redemption_limit.'<br>';
			//echo 'not_expired:'.$not_expired.'<br>';
			//echo 'valid_option:'.$valid_option.'<br>';
			
			if($applicable_for_products_in_the_cart && $has_redemption_limit && $not_expired && $valid_option)
			{
				if($promotion->redemption_limit > $promotion->redemption_count)
				{
					$this->_promotion = $promotion;
					$this->_promoStatus = 1;
				}
				else
				{
					$this->_promoStatus = -1;
					$this->_promotion = null;
				}
			}
			else
			{
				$this->_promoStatus = -1;
				$this->_promotion = null;
			} 
		}
		else
		{
			$this->_promoStatus = -1;
			$this->_promotion = null;
		}
		SimpleEcommCartSession::touch();
	}
	else {
		$this->_promoStatus = -1;
		$this->_promotion = null;
	}
  }
  
  public function getPromotion() {
    $promotion = false;
    if(is_a($this->_promotion, 'SimpleEcommCartPromotion')) {
      $promotion = $this->_promotion;
    }
    return $promotion;
  }
  
  public function getPromoMessage() {
    $message = '&nbsp;';
    if($this->_promoStatus == -1) {
      $message = 'Invalid coupon code';
    }
    elseif($this->_promoStatus == -2) {
      $message = 'Order total not high enough for promotion to apply';
    }
    if($this->_promoStatus < 0) {
      $this->_promoStatus = 0;
    }
    return $message;
  }
  
  public function resetPromotionStatus() {
    if(is_a($this->_promotion, 'SimpleEcommCartPromotion')) {
      if($this->_promotion->minOrder > $this->getSubTotal()) {
        // Order total not high enough for promotion to apply
        $this->_promoStatus = -2;
        $this->_promotion = null;
      }
      else {
        $this->_promoStatus = 1;
      }
    }
  }
  
  public function clearPromotion() {
    $this->_promotion = '';
    $this->_promoStatus = 0;
  }
  
  public function getPromoStatus() {
    return $this->_promoStatus;
  }
  
  public function getDiscountAmount() {
    $discount = 0;
    if(is_a($this->_promotion, 'SimpleEcommCartPromotion')) {
		$total = 0;  
		if($this->_promotion->apply_for_all_products == '0')
		{
			$product_ids = explode(",", $this->_promotion->products);
			foreach($product_ids as $id )
			{  
    			foreach($this->_items as $item) 
				{ 
      				if($item->getProductId() == $id)
					{
						$total += $item->getProductPrice() * $item->getQuantity(); 
					}
				}
			} 
		}
		else
		{
			foreach($this->_items as $item) { 
				$total += $item->getProductPrice() * $item->getQuantity(); 
			} 
		} 
		$discountedTotal = $this->_promotion->discountTotal($total);
		 
		if($discountedTotal<0)
		{
			$discount = 0;
		}
		else 
		{ 
			if($total>$discountedTotal)
			{
				$discount = number_format($total - $discountedTotal, 2, '.', '');
			}
			else
			{
				$discount = $discountedTotal;
			}
		}
		 
		if($discount<0)$discount=0;
      // SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting discount Total: $total -- Discounted Total: $discountedTotal -- Discount: $discount");
    }
	
    return $discount;
  }
  public function getDiscountAmountForSingleProduct($product_id) {
    $discount = 0;
    if(is_a($this->_promotion, 'SimpleEcommCartPromotion')) {
		$total = 0;  
		 
		if($this->_promotion->apply_for_all_products == '0')
		{ 
			$product_ids = explode(",", $this->_promotion->products);
			foreach($product_ids as $id )
			{  
    			foreach($this->_items as $item) 
				{ 
      				if($item->getProductId() == $product_id && $item->getProductId() == $id)
					{
						$total += $item->getProductPrice() * $item->getQuantity(); 
					}
				}
			} 
		}
		else
		{
			foreach($this->_items as $item) { 
			    if($item->getProductId() == $product_id)
				{
					$total += $item->getProductPrice() * $item->getQuantity(); 
				}
			} 
		} 
		
		//echo  '$total:'.$total; 
		$discountedTotal = 0;
		if($this->_promotion->type == 'dollar')
		{
			$subtotal = 0; 
			
			if($this->_promotion->apply_for_all_products == '0')
			{
				$product_ids = explode(",", $this->_promotion->products);
				foreach($this->_items as $item) 
				{ 
					foreach($product_ids as $id ) 
					{ 
	      				if($item->getProductId() == $id)
						{
							$subtotal += $item->getProductPrice() * $item->getQuantity(); 
						}
					}
				}
			}
			else
			{
				foreach($this->_items as $item) 
				{ 
					$subtotal += $item->getProductPrice() * $item->getQuantity(); 
				}
			}
			
			
			$discountedTotal = $total - (($this->_promotion->amount / $subtotal) * $total);
		}
		elseif($this->_promotion->type == 'percentage') 
		{
        	$discountedTotal = $this->_promotion->discountTotal($total);
      	}
		
		if($discountedTotal<0)
		{
			$discount=0;
		}
		else 
		{
			if($total>$discountedTotal)
				$discount = number_format($total - $discountedTotal, 2, '.', '');
			else  
				$discount = $discountedTotal;
		}
      // SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting discount Total: $total -- Discounted Total: $discountedTotal -- Discount: $discount");
    }
	
	if($discount<0)$discount=0;
    return $discount;
  }
  /**
   * Return the entire cart total including shipping costs and discounts.
   * An optional paramater can be provided to specify whether or not subscription items 
   * are included in the total.
   * 
   * @param boolean $includeSubscriptions
   * @return float 
   */
  public function getGrandTotal($includeSubscriptions=true) {
    if($includeSubscriptions) {
      $total = $this->getSubTotal() + $this->getShippingCost() - $this->getDiscountAmount();
    }
    else {
      $total = $this->getNonSubscriptionAmount() + $this->getShippingCost() - $this->getDiscountAmount();
    }
	
	$total=$total+0; 
    $total = ($total < 0) ? 0 : $total;
    return $total; 
  }
  
  public function storeOrder($orderInfo) {
  	
	if($orderInfo['payment_method']=='Manual')
	{
		$orderInfo['payment_status'] = 'Pending';
		$orderInfo['delivery_status'] = 'Pending';
	}
	else	
	{
		$orderInfo['payment_status'] = 'Complete';
		
		if($this->isAllDigital())
		{
			$orderInfo['delivery_status'] = 'Complete';
		}
		else if($this->isAllPhysical())
		{
			$orderInfo['delivery_status'] = 'Pending';
		}
		else
		{
			$orderInfo['delivery_status'] = 'Pending';
		}
	}
	
	SimpleEcommCartCommon::log('TAX____:'.$orderInfo['tax']);
  	
	 
	//////////////////////////////////////
	 
    $order = new SimpleEcommCartOrder();
    $orderInfo['trans_id'] = (empty($orderInfo['trans_id'])) ? 'MT-' . SimpleEcommCartCommon::getRandString() : $orderInfo['trans_id'];
    $orderInfo['ip'] = $_SERVER['REMOTE_ADDR'];
    $orderInfo['discount_amount'] = $this->getDiscountAmount();
    $order->setInfo($orderInfo);
    $order->setItems($this->getItems());
    $orderId = $order->save();
    
    $orderInfo['id'] = $orderId; 
	
    do_action('simpleecommcart_after_order_saved', $orderInfo);
     
    return $orderId;
  }
  
  /**
   * Return true if all products are digital
   */
  public function isAllDigital() {
    $allDigital = true;
    foreach($this->getItems() as $item) {
      if(!$item->isDigital()) {
        $allDigital = false;
        break;
      }
    }
    return $allDigital;
  }
  
   public function isAllNoShipping() {
    $allNoShipping = true;
    foreach($this->getItems() as $item) {
      if($item->isShipped()) {
        $allNoShipping = false;
        break;
      }
    }
    return $allNoShipping;
  }
   public function isAllNoTax() {
    $allNoTax = true;
    foreach($this->getItems() as $item) {
      if($item->isTaxed()) {
        $allNoTax = false;
        break;
      }
    }
    return $allNoTax;
  }
  /**
   * Return true if all products are Physical
   */
  public function isAllPhysical() {
    $allPhysical = true;
    foreach($this->getItems() as $item) {
      if($item->isDigital()) {
        $allPhysical = false;
        break;
      }
    }
    return $allPhysical;
  }
  
  public function hasMembershipProducts() {
    foreach($this->getItems() as $item) {
      if($item->isMembershipProduct()) {
        return true;
      }
    }
    return false;
  }
  
  public function hasSubscriptionProducts() {
    foreach($this->getItems() as $item) {
      if($item->isSubscription()) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Return true if the cart only contains PayPal subscriptions
   */
  public function hasPayPalSubscriptions() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return true;
      }
    }
    return false;
  }
  
  public function hasSpreedlySubscriptions() {
    foreach($this->getItems() as $item) {
      if($item->isSpreedlySubscription()) {
        return true;
      }
    }
    return false;
  }
  
  /**
   * Return the spreedly subscription id for the subscription product in the cart 
   * or false if there are no spreedly subscriptions in the cart. With Spreedly 
   * subscriptions, there may be only one subscription product in the cart.
   */
  public function getSpreedlySubscriptionId() {
    foreach($this->getItems() as $item) {
      if($item->isSpreedlySubscription()) {
        return $item->getSpreedlySubscriptionId();
      }
    }
    return false;
  }
  
  /**
   * Return the CartItem that holds the membership product.
   * If there is no membership product in the cart, return false.
   * 
   * @return SimpleEcommCartCartItem
   */
  public function getMembershipProductItem() {
    foreach($this->getItems() as $item) {
      if($item->isMembershipProduct()) {
        return $item;
      }
    }
    return false;
  }
  
  /**
   * Return the membership product in the cart. 
   * Only one membership or subscription type item may be in the cart at any given time. 
   * Note that this function returns the actual SimpleEcommCartProduct not the SimpleEcommCartCartItem.
   * If there is no membership product in the cart, return false.
   * 
   * @return SimpleEcommCartProduct
   */
  public function getMembershipProduct() {
    $product = false;
    if($item = $this->getMembershipProductItem()) {
      $product = new SimpleEcommCartProduct($item->getProductId());
    }
    return $product;
  }
  
  public function getPayPalSubscriptionId() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $item->getPayPalSubscriptionId();
      }
    }
    return false;
  }
  
  /**
   * Return the SimpleEcommCartCartItem with the PayPal subscription
   * 
   * @return SimpleEcommCartCartItem
   */
  public function getPayPalSubscriptionItem() {
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $item;
      }
    }
    return false;
  }
  
  /**
   * Return the index in the cart of the PayPal subscription item.
   * This number is used to know the location of the item in the cart
   * when creating the payment profile with PayPal.
   * 
   * @return int
   */
  public function getPayPalSubscriptionIndex() {
    $index = 0;
    foreach($this->getItems() as $item) {
      if($item->isPayPalSubscription()) {
        return $index;
      }
      $index++;
    }
    return false;
  }
  
  /**
   * Return false if none of the items in the cart are shipped
   */
  public function requireShipping() {
    $ship = false;
    foreach($this->getItems() as $item) {
      if($item->isShipped()) {
        $ship = true;
        break;
      }
    }
    return $ship;
  }
  public function requireShippingVariation()
  {
   	$shippingVariation = false;
	if( SimpleEcommCartSetting::getValue('shipping_variation_show') == '1')
	{
		$shippingVariation=true;
	}
	return $shippingVariation;
  }
  public function requireShippingAndBillingAddress()
  {
   	$shipAndBill = true;
	if($this->requireShipTo() ||  $this->requireBillTo())
	{
		$shipAndBill=true;
	}
	else
	{
		$shipAndBill=false;
	}
	return $shipAndBill;
  }
  public function requireShipTo()
  {
   	$shipTo = false;
	
	$tax_settings = unserialize(SimpleEcommCartSetting::getValue('tax_settings')); 
	if($tax_settings["option"] == '2' && $tax_settings["logic"] == '4')
	{
		 $shipTo = true;
	}
	else
	{
		 if($this->isAllDigital() || $this->isAllNoShipping()) 
		 {
		 	$shipTo = false;
		 }
		 else
		 {
		 	$shipTo = true;
		 }
	}
	 
	return $shipTo;
  }
  public function requireBillTo()
  {
   	$billTo = true;
	
	$tax_settings = unserialize(SimpleEcommCartSetting::getValue('tax_settings')); 
	if($tax_settings["logic"]  == '3')
	{
		$billTo = true;
	}
	else
	{
		$billTo = false;
	} 
	 
	return $billTo;
  }

  public function getCalculateTaxOrShippingButtonText()
  {
  	$button_text = "";
	
	$shipTo = false; 
	if($this->isAllDigital() || $this->isAllNoShipping()) 
	{
		$shipTo = false;
	}
	else
	{
		$shipTo = true;
	}
	
	$tax = false;
	$tax_settings = unserialize(SimpleEcommCartSetting::getValue('tax_settings')); 
	if($tax_settings["option"] == '3')
	{
		//product specific tax
		if(!$this->isAllNoTax())
		{
			$tax = true;
		}
	}
	else if($tax_settings["option"] == '1')
	{
		//Flat Rate 
		if($tax_settings["logic"]== '1')
		{
			$tax = true;
		}
		else
		{
			if(!$this->isAllNoTax())
			{
				$tax = true;
			}
		}
	}
	else
	{
		$tax = true;
	}
	 
	if($tax == true)
	{
		if($shipTo == false)
		{
			$button_text = "Calculate Tax";
		}
		else
		{
			//$button_text="Calculate Tax & Shipping";
			$button_text="Calculate Shipping";
		}
	}
	else
	{
		if($shipTo == true)
		{
			$button_text="Calculate Shipping";
		}
		else
		{
			$button_text="N/A";
		}
	} 
	return $button_text;
  }
  public function requireCalculateTaxOrShippingButton()
  {
  	$require_button  = true;
	
	$shipTo = false; 
	if($this->isAllDigital() || $this->isAllNoShipping()) 
	{
		$shipTo = false;
	}
	else
	{
		$shipTo = true;
	}
	
	$tax = false;
	/*$tax_settings = unserialize(SimpleEcommCartSetting::getValue('tax_settings')); 
	if($tax_settings["option"] == '3')
	{
		//product specific tax
		if(!$this->isAllNoTax())
		{
			$tax = true;
		}
	}
	else if($tax_settings["option"] == '1')
	{
		//Flat Rate 
		if($tax_settings["logic"]== '1')
		{
			$tax = true;
		}
		else
		{
			if(!$this->isAllNoTax())
			{
				$tax = true;
			}
		}
	}
	else
	{
		$tax = true;
	}*/
	 
	 
	if($tax == false && $shipTo == false)
	{ 
		$require_button=false;
	} 
	else
	{
		$require_button=true;
	}
	
	return $require_button;
  }
  public function requirePayment() {
    $requirePayment = true;
    if($this->getGrandTotal() < 0.01) {
      // Look for free trial subscriptions that require billing
      if($subId = $this->getSpreedlySubscriptionId()) {
        $sub = new SpreedlySubscription($subId);
        if('free_trial' == strtolower((string)$sub->planType)) {
          $requirePayment = false;
        }
      }
    }
    return $requirePayment;
  }

  public function setShippingMethod($id) {
    $method = new SimpleEcommCartShippingMethod();
    if($method->load($id)) {
      $this->_shippingMethodId = $id;
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Set shipping method id to: $id");
    }
  }

  public function getShippingMethodId() {
    if($this->_shippingMethodId < 1) {
      $this->_setDefaultShippingMethodId();
    }
    return $this->_shippingMethodId;
  }

  public function getShippingMethodName() {
    // Look for live rates
    if(SimpleEcommCartSession::get('SimpleEcommCartLiveRates')) {
      $rate = SimpleEcommCartSession::get('SimpleEcommCartLiveRates')->getSelected();
      return $rate->service;
    }
    // Not using live rates
    else {
      if($this->isAllDigital()) {
        return 'Download';
      }
      elseif(!$this->requireShipping()) {
        return 'None';
      }
      else {
        if($this->_shippingMethodId < 1) {
          $this->_setDefaultShippingMethodId();
        }
        $method = new SimpleEcommCartShippingMethod($this->_shippingMethodId);
        return $method->name;
      }
    }
    
  }
  
  public function detachFormEntry($entryId) {
    foreach($this->_items as $index => $item) {
      $entries = $item->getFormEntryIds();
      if(in_array($entryId, $entries)) {
        $item->detachFormEntry($entryId);
        $qty = $item->getQuantity();
        if($qty == 0) {
          $this->removeItem($index);
        }
      }
    }
  }
  
  public function checkCartInventory() {
    $alert = '';
    foreach($this->_items as $itemIndex => $item) {
      if(!SimpleEcommCartProduct::confirmInventory($item->getProductId(), $item->getOptionInfo(), $item->getQuantity())) {
        SimpleEcommCartCommon::log("Unable to confirm inventory when checking cart.");
        $qtyAvailable = SimpleEcommCartProduct::checkInventoryLevelForProduct($item->getProductId(), $item->getOptionInfo());
        if($qtyAvailable > 0) {
          $alert .= '<p>We are not able to fulfill your order for <strong>' .  $item->getQuantity() . '</strong> ' . $item->getFullDisplayName() . "  because 
            we only have <strong>$qtyAvailable in stock</strong>.</p>";
        }
        else {
          $alert .= '<p>We are not able to fulfill your order for <strong>' .  $item->getQuantity() . '</strong> ' . $item->getFullDisplayName() . "  because 
            it is <strong>out of stock</strong>.</p>";
        }
        
        if($qtyAvailable > 0) {
          $item->setQuantity($qtyAvailable);
        }
        else {
          $this->removeItem($itemIndex);
        }
        
      }
    }
    
    if(!empty($alert)) {
      $alert = "<div class='SimpleEcommCartUnavailable'><h1>Inventory Restriction</h1> $alert <p>Your cart has been updated based on our available inventory.</p>";
      $alert .= '<input type="button" name="close" value="Ok" class="SimpleEcommCartButtonSecondary modalClose" /></div>';
    }
    
    return $alert;
  }
  
  public function getLiveRates() {
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Call to getLiveRates");
    if(!SIMPLEECOMMCART_PRO) { return false; }
    
    $weight = SimpleEcommCartSession::get('SimpleEcommCartCart')->getCartWeight();
    $zip = SimpleEcommCartSession::get('simpleecommcart_shipping_zip') ? SimpleEcommCartSession::get('simpleecommcart_shipping_zip') : false;
    $countryCode = SimpleEcommCartSession::get('simpleecommcart_shipping_country_code') ? SimpleEcommCartSession::get('simpleecommcart_shipping_country_code') : SimpleEcommCartCommon::getHomeCountryCode();
    
    // Make sure _liveRates is a SimpleEcommCartLiveRates object
    if(get_class($this->_liveRates) != 'SimpleEcommCartLiveRates') {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] WARNING: \$this->_liveRates is not a SimpleEcommCartLiveRates object so we're making it one now.");
      $this->_liveRates = new SimpleEcommCartLiveRates();
    }
    
    // Return the live rates from the session if the zip, country code, and cart weight are the same
    if(SimpleEcommCartSession::get('SimpleEcommCartLiveRates') && get_class($this->_liveRates) == 'SimpleEcommCartLiveRates') {
      $cartWeight = $this->getCartWeight();
      $this->_liveRates = SimpleEcommCartSession::get('SimpleEcommCartLiveRates');
      
      $liveWeight = $this->_liveRates->weight;
      $liveZip = $this->_liveRates->toZip;
      $liveCountry = $this->_liveRates->getToCountryCode();
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] 
        $liveWeight == $weight
        $liveZip == $zip
        $liveCountry == $countryCode
      ");
      
      if($this->_liveRates->weight == $weight && $this->_liveRates->toZip == $zip && $this->_liveRates->getToCountryCode() == $countryCode) {
        SimpleEcommCartCommon::log("Using Live Rates from the session: " . $this->_liveRates->getSelected()->getService());
        return SimpleEcommCartSession::get('SimpleEcommCartLiveRates'); 
      }
    }

    if($this->getCartWeight() > 0 && SimpleEcommCartSession::get('simpleecommcart_shipping_zip') && SimpleEcommCartSession::get('simpleecommcart_shipping_country_code')) {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Clearing current live shipping rates and recalculating new rates.");
      $this->_liveRates->clearRates();
      $this->_liveRates->weight = $weight;
      $this->_liveRates->toZip = $zip;
      $method = new SimpleEcommCartShippingMethod();
      
      // Get USPS shipping rates
      if(SimpleEcommCartSetting::getValue('usps_username')) {
        $this->_liveRates->setToCountryCode($countryCode);
        $rates = ($countryCode == 'US') ? $this->getUspsRates() : $this->getUspsIntlRates($countryCode);
        $uspsServices = $method->getServicesForCarrier('usps');
        foreach($rates as $name => $price) {
          $price = number_format($price, 2, '.', '');
          if(in_array($name, $uspsServices)) {
            $this->_liveRates->addRate('USPS', 'USPS ' . $name, $price);
          }
        }
      }

      // Get UPS Live Shipping Rates
      if(SimpleEcommCartSetting::getValue('ups_apikey')) {
        $rates = $this->getUpsRates();
        foreach($rates as $name => $price) {
          $this->_liveRates->addRate('UPS', $name, $price);
        }
      }
      
    }
    else {
      $this->_liveRates->clearRates();
      $this->_liveRates->weight = 0;
      $this->_liveRates->toZip = $zip;
      $this->_liveRates->setToCountryCode($countryCode);
      $this->_liveRates->addRate('SYSTEM', 'Free Shipping', '0.00');
    }
    
    SimpleEcommCartSession::set('SimpleEcommCartLiveRates', $this->_liveRates);
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Dump live rates: " . print_r($this->_liveRates, true));
    return $this->_liveRates;
  }
  
  /**
   * Return a hash where the keys are service names and the values are the service rates.
   * @return array 
   */
  public function getUspsRates() {
    $usps = new SimpleEcommCartUsps();
    $weight = $this->getCartWeight();
    $fromZip = SimpleEcommCartSetting::getValue('usps_ship_from_zip');
    $toZip = SimpleEcommCartSession::get('simpleecommcart_shipping_zip');
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting rates for USPS: $fromZip > $toZip > $weight");
    $rates = $usps->getRates($fromZip, $toZip, $weight);
    return $rates;
  }
  
  public function getUspsIntlRates($countryCode) {
    $usps = new SimpleEcommCartUsps();
    $weight = $this->getCartWeight();
    $value = $this->getSubTotal();
    $zipOrigin = SimpleEcommCartSetting::getValue('usps_ship_from_zip');
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Getting rates for USPS Intl: $zipOrigin > $countryCode > $value > $weight");
    $rates = $usps->getIntlRates($zipOrigin, $countryCode, $value, $weight);
    return $rates;
  }
  
  /**
   * Return a hash where the keys are service names and the values are the service rates.
   * @return array 
   */
  public function getUpsRates() {
    $ups = new SimpleEcommCartUps();
    $weight = SimpleEcommCartSession::get('SimpleEcommCartCart')->getCartWeight();
    $zip = SimpleEcommCartSession::get('simpleecommcart_shipping_zip');
    $countryCode = SimpleEcommCartSession::get('simpleecommcart_shipping_country_code');
    $rates = $ups->getAllRates($zip, $countryCode, $weight);
    return $rates;
  }
  
  protected function _setDefaultShippingMethodId() {
    // Set default shipping method to the cheapest method
    $method = new SimpleEcommCartShippingMethod();
    $methods = $method->getModels("where code IS NULL or code = ''", 'order by default_rate asc');
    if(is_array($methods) && count($methods) && get_class($methods[0]) == 'SimpleEcommCartShippingMethod') {
      $this->_shippingMethodId = $methods[0]->id;
    }
  }
  
  protected function _setShippingMethodFromPost() {
    // Not using live rates
    if(isset($_POST['shipping_method_id'])) {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not using live shipping rates");
      $shippingMethodId = $_POST['shipping_method_id'];
      $this->setShippingMethod($shippingMethodId);
    }
    // Using live rates
    elseif(isset($_POST['live_rates'])) {
      if(SimpleEcommCartSession::get('SimpleEcommCartLiveRates')) {
        SimpleEcommCartSession::get('SimpleEcommCartLiveRates')->setSelected($_POST['live_rates']);
        // SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] This LIVE RATE is now set: " . SimpleEcommCartSession::get('SimpleEcommCartLiveRates')->getSelected()->getService());
        // SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Using live shipping rates to set shipping method from post: " . $_POST['live_rates']);
      }
    }
  }
  
  protected function _updateQuantitiesFromPost() {
    $qtys = SimpleEcommCartCommon::postVal('quantity');
    if(is_array($qtys)) {
      foreach($qtys as $itemIndex => $qty) {
        $item = $this->getItem($itemIndex);
        if(!is_null($item) && get_class($item) == 'SimpleEcommCartCartItem') {
          
          if($qty == 0){
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Customer specified quantity of 0 - remove item.");
            $this->removeItem($itemIndex);
          }
          
          if(SimpleEcommCartProduct::confirmInventory($item->getProductId(), $item->getOptionInfo(), $qty)) {
            $this->setItemQuantity($itemIndex, $qty);
          }
          else {
            $qtyAvailable = SimpleEcommCartProduct::checkInventoryLevelForProduct($item->getProductId(), $item->getOptionInfo());
            $this->setItemQuantity($itemIndex, $qtyAvailable);
            if(!SimpleEcommCartSession::get('SimpleEcommCartInventoryWarning')) { SimpleEcommCartSession::set('SimpleEcommCartInventoryWarning', ''); }
            $inventoryWarning = SimpleEcommCartSession::get('SimpleEcommCartInventoryWarning');
            $inventoryWarning .= '<p>The quantity for ' . $item->getFullDisplayName() . " could not be changed to $qty because we only have $qtyAvailable in stock.</p>";
            SimpleEcommCartSession::set('SimpleEcommCartInventoryWarning', $inventoryWarning);
            SimpleEcommCartCommon::log("Quantity available ($qtyAvailable) cannot meet desired quantity ($qty) for product id: " . $item->getProductId());
          }
        }
      }
    }
  }
  
  protected function _setCustomFieldInfoFromPost() {
    // Set custom values for individual products in the cart
    $custom = SimpleEcommCartCommon::postVal('customFieldInfo');
    if(is_array($custom)) {
      foreach($custom as $itemIndex => $info) {
        $this->setCustomFieldInfo($itemIndex, $info);
      }
    }
  }
  
  protected function _setPromoFromPost() {
    if(isset($_POST['couponCode']) && $_POST['couponCode'] != '') {
      $couponCode = SimpleEcommCartCommon::postVal('couponCode');
      $this->applyPromotion($couponCode);
    }
    else {
      $this->resetPromotionStatus();
    }
  }
  
  /**
   * Return a stdClass object with the price difference and a CSV list of options.
   *   $optionResult->priceDiff
   *   $optionResult->options
   * @return object
   */
  /*protected function _processOptionInfo($optionInfo) {
    $optionInfo = trim($optionInfo);
    $priceDiff = 0;
    $options = explode('~', $optionInfo);
    $optionList = array();
    foreach($options as $opt) {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Working with option: $opt");
      if(preg_match('/\+\s*\$/', $opt)) {
        $opt = preg_replace('/\+\s*\$/', '+$', $opt);
        list($opt, $pd) = explode('+$', $opt);
        $optionList[] = trim($opt);
        $priceDiff += $pd;
      }
      elseif(preg_match('/-\s*\$/', $opt)) {
        $opt = preg_replace('/-\s*\$/', '-$', $opt);
        list($opt, $pd) = explode('-$', $opt);
        $optionList[] = trim($opt);
        $pd = trim($pd);
        $priceDiff -= $pd;
      }
      else {
        $optionList[] = trim($opt);
      }
    }
    $optionResult = new stdClass();
    $optionResult->priceDiff = $priceDiff;
    $optionResult->options = implode(', ', $optionList);
    return $optionResult;
  }
*/  
 protected function _processOptionInfo($optionInfo) {
    $optionInfo = trim($optionInfo);
    $priceDiff = 0;
    $options = explode('~', $optionInfo);
    $optionList = array();
    foreach($options as $opt) {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Working with option: $opt");
	  $pprice = '/\+\s*\\'.SIMPLEECOMMCART_CURRENCY_SYMBOL.'/';
	  $pcurrency = '+'.SIMPLEECOMMCART_CURRENCY_SYMBOL;
	  $mprice = '/-\s*\\'.SIMPLEECOMMCART_CURRENCY_SYMBOL.'/';
	  $mcurrency = '-'.SIMPLEECOMMCART_CURRENCY_SYMBOL;
      if(preg_match($pprice, $opt)) {
        $opt = preg_replace($pprice, $pcurrency, $opt);
        list($opt, $pd) = explode($pcurrency, $opt);
        $optionList[] = trim($opt);
        $priceDiff += $pd;
      }
      elseif(preg_match($mprice, $opt)) {
        $opt = preg_replace($mprice, $mcurrency, $opt);
        list($opt, $pd) = explode($mcurrency, $opt);
        $optionList[] = trim($opt);
        $pd = trim($pd);
        $priceDiff -= $pd;
      }
      else {
        $optionList[] = trim($opt);
      }
    }
    $optionResult = new stdClass();
    $optionResult->priceDiff = $priceDiff;
    $optionResult->options = implode(', ', $optionList);
    return $optionResult;
  }
}
