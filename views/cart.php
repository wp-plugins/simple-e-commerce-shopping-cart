<?php 
SimpleEcommCartSession::get('SimpleEcommCartCart')->resetPromotionStatus();
$items = SimpleEcommCartSession::get('SimpleEcommCartCart')->getItems();
$shippingMethods = SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingMethods();
$shipping = SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingCost(); 
$promotion = SimpleEcommCartSession::get('SimpleEcommCartCart')->getPromotion();
$product = new SimpleEcommCartProduct();
$subtotal = SimpleEcommCartSession::get('SimpleEcommCartCart')->getSubTotal();
$discountAmount = SimpleEcommCartSession::get('SimpleEcommCartCart')->getDiscountAmount();
$cartPage = get_page_by_path('store/cart');
$checkoutPage = get_page_by_path('store/checkout');
$setting = new SimpleEcommCartSetting();

 
// Try to return buyers to the last page they were on when the click to continue shopping
if(SimpleEcommCartSetting::getValue('continue_shopping') == 1){
  // force the last page to be store home
  $lastPage = SimpleEcommCartSetting::getValue('store_url') ? SimpleEcommCartSetting::getValue('store_url') : get_bloginfo('url');
  SimpleEcommCartSession::set('SimpleEcommCartLastPage', $lastPage);
}
else{
  if(isset($_SERVER['HTTP_REFERER']) && isset($_POST['task']) && $_POST['task'] == "addToCart"){
    $lastPage = $_SERVER['HTTP_REFERER'];
    SimpleEcommCartSession::set('SimpleEcommCartLastPage', $lastPage);
	
	if($_POST['hascartwidget']=='yes')
	{
		header("Location: " .SimpleEcommCartSession::get('SimpleEcommCartLastPage')); 
	}
  }
  if(!SimpleEcommCartSession::get('SimpleEcommCartLastPage')) {
      // If the last page is not set, use the store url
      $lastPage = SimpleEcommCartSetting::getValue('store_url') ? SimpleEcommCartSetting::getValue('store_url') : get_bloginfo('url');
      SimpleEcommCartSession::set('SimpleEcommCartLastPage', $lastPage);
  }
}

$fullMode = true;
if(isset($data['mode']) && $data['mode'] == 'read') {
  $fullMode = false;
}

$tax = 0;
if(isset($data['tax']) && $data['tax'] > 0) {
  $tax = $data['tax'];
}
else {
  // Check to see if all sales are taxed
  $tax = SimpleEcommCartSession::get('SimpleEcommCartCart')->getTax('All Sales');
}

$cartImgPath = SimpleEcommCartSetting::getValue('cart_images_url');
if($cartImgPath && stripos(strrev($cartImgPath), '/') !== 0) {
  $cartImgPath .= '/';
}
if($cartImgPath) {
  $continueShoppingImg = $cartImgPath . 'continue-shopping.png';
}

if(count($items)): ?>

<?php if(SimpleEcommCartSession::get('SimpleEcommCartInventoryWarning') && $fullMode): ?>
  <div class="SimpleEcommCartUnavailable">
    <h1><?php _e( 'Inventory Restriction' , 'simpleecommcart' ); ?></h1>
    <?php 
      echo SimpleEcommCartSession::get('SimpleEcommCartInventoryWarning');
      SimpleEcommCartSession::drop('SimpleEcommCartInventoryWarning');
    ?>
    <input type="button" name="close" value="Ok" id="close" class="SimpleEcommCartButtonSecondary modalClose" />
  </div>
<?php endif; ?>


<?php if(SimpleEcommCartSession::get('SimpleEcommCartZipWarning')): ?>
  <div id="SimpleEcommCartZipWarning" class="SimpleEcommCartUnavailable">
    <h2><?php _e( 'Please Provide Your Zip Code' , 'simpleecommcart' ); ?></h2>
    <p><?php _e( 'Before you can checkout, please provide the zip code for where we will be shipping your order and click' , 'simpleecommcart' ); ?> "<?php _e( 'Calculate Shipping' , 'simpleecommcart' ); ?>".</p>
    <?php 
      SimpleEcommCartSession::drop('SimpleEcommCartZipWarning');
    ?>
    <input type="button" name="close" value="Ok" id="close" class="SimpleEcommCartButtonSecondary modalClose" />
  </div>
<?php elseif(SimpleEcommCartSession::get('SimpleEcommCartShippingWarning')): ?>
  <div id="SimpleEcommCartShippingWarning" class="SimpleEcommCartUnavailable">
    <h2><?php _e( 'No Shipping Service Selected' , 'simpleecommcart' ); ?></h2>
    <p><?php _e( 'We cannot process your order because you have not selected a shipping method. If there are no shipping services available, we may not be able to ship to your location.' , 'simpleecommcart' ); ?></p>
    <?php SimpleEcommCartSession::drop('SimpleEcommCartShippingWarning'); ?>
    <input type="button" name="close" value="Ok" id="close" class="SimpleEcommCartButtonSecondary modalClose" />
  </div>
<?php endif; ?>

<?php if(SimpleEcommCartSession::get('SimpleEcommCartSubscriptionWarning')): ?>
  <div id="SimpleEcommCartSubscriptionWarning" class="SimpleEcommCartUnavailable">
    <h2><?php _e( 'Too Many Subscriptions' , 'simpleecommcart' ); ?></h2>
    <p><?php _e( 'Only one subscription may be purchased at a time.' , 'simpleecommcart' ); ?></p>
    <?php 
      SimpleEcommCartSession::drop('SimpleEcommCartSubscriptionWarning');
    ?>
    <input type="button" name="close" value="Ok" id="close" class="SimpleEcommCartButtonSecondary modalClose" />
  </div>
<?php endif; ?>

<?php 
  if($accountId = SimpleEcommCartCommon::isLoggedIn()) {
    $account = new SimpleEcommCartAccount($accountId);
    if($sub = $account->getCurrentAccountSubscription()) {
      if($sub->isPayPalSubscription() && SimpleEcommCartSession::get('SimpleEcommCartCart')->hasPayPalSubscriptions()) {
        ?>
        <p id="SimpleEcommCartSubscriptionChangeNote"><?php _e( 'Your current subscription will be canceled when you purchase your new subscription.' , 'simpleecommcart' ); ?></p>
        <?php
      }
    }
  } 
?>

<form id='SimpleEcommCartCartForm' action="" method="post">
  <input type='hidden' name='task' value='updateCart' />
  <table id='viewCartTable'>
    <tr>
      <th><?php _e('Product','simpleecommcart') ?></th>
      <th colspan="1"><?php _e( 'Quantity' , 'simpleecommcart' ); ?></th>
      <th>&nbsp;</th>
      <th><?php _e( 'Item Price' , 'simpleecommcart' ); ?></th>
      <th><?php _e( 'Subtotal' , 'simpleecommcart' ); ?></th>
    </tr>
  
    <?php foreach($items as $itemIndex => $item): ?>
      <?php 
        $product->load($item->getProductId());
        $price = $item->getProductPrice() * $item->getQuantity();
      ?>
      <tr>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?> >
          <?php #echo $item->getItemNumber(); ?>
          <?php echo $item->getFullDisplayName(); ?>
          <?php echo $item->getCustomField($itemIndex, $fullMode); ?>
        </td>
        <?php if($fullMode): ?>
          <?php
            $removeItemImg = SIMPLEECOMMCART_URL . '/images/remove-item.png';
            if($cartImgPath) {
              $removeItemImg = $cartImgPath . 'remove-item.png';
            }
          ?>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?> colspan="2">
          
          <?php if($item->isSubscription() || $item->isMembershipProduct() || $product->is_user_price==1): ?>
            <span class="subscriptionOrMembership"><?php echo $item->getQuantity() ?></span>
          <?php else: ?>
            <input type='text' name='quantity[<?php echo $itemIndex ?>]' value='<?php echo $item->getQuantity() ?>' class="itemQuantity"/>
          <?php endif; ?>
          
          <?php $removeLink = get_permalink($cartPage->ID); ?>
          <?php $taskText = (strpos($removeLink, '?')) ? '&task=removeItem&' : '?task=removeItem&'; ?>
          <a href='<?php echo $removeLink . $taskText ?>itemIndex=<?php echo $itemIndex ?>' title='Remove item from cart'><img src='<?php echo $removeItemImg ?>' alt="Remove Item" /></a>
          
        </td>
        <?php else: ?>
          <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?> colspan="2"><?php echo $item->getQuantity() ?></td>
        <?php endif; ?>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?>><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($item->getProductPrice(), 2); ?></td>
        <td <?php if($item->hasAttachedForms()) { echo "class=\"noBottomBorder\""; } ?>><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($price, 2) ?></td>
      </tr>
      <?php if($item->hasAttachedForms()): ?>
        <tr>
          <td colspan="5">
            <a href='#' class="showEntriesLink" rel="<?php echo 'entriesFor_' . $itemIndex ?>"><?php _e( 'Show Details' , 'simpleecommcart' ); ?> <?php #echo count($item->getFormEntryIds()); ?></a>
            <div id="<?php echo 'entriesFor_' . $itemIndex ?>" class="showGfFormData" style="display: none;">
              <?php echo $item->showAttachedForms($fullMode); ?>
            </div>
          </td>
        </tr>
      <?php endif;?>
    <?php endforeach; ?>
   
    <?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipping()): ?>
      
      <?php if(SIMPLEECOMMCART_PRO && SimpleEcommCartSetting::getValue('use_live_rates')): ?>
        <?php $zipStyle = "style=''"; ?>
        
        <?php if($fullMode): ?>
          <?php if(SimpleEcommCartSession::get('simpleecommcart_shipping_zip')): ?> 
            <?php $zipStyle = "style='display: none;'"; ?>
            <tr id="shipping_to_row">
              <th colspan="5" class="alignRight">
                <?php _e( 'Shipping to' , 'simpleecommcart' ); ?> <?php echo SimpleEcommCartSession::get('simpleecommcart_shipping_zip'); ?> 
                <?php
                  if(SimpleEcommCartSetting::getValue('international_sales')) {
                    echo SimpleEcommCartSession::get('simpleecommcart_shipping_country_code');
                  }
                ?>
                (<a href="#" id="change_shipping_zip_link">change</a>)
                &nbsp;
                <?php
                  $liveRates = SimpleEcommCartSession::get('SimpleEcommCartCart')->getLiveRates();
                  $rates = $liveRates->getRates();
                  SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] LIVE RATES: " . print_r($rates, true));
                  $selectedRate = $liveRates->getSelected();
                  $shipping = SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingCost();
                ?>
                <select name="live_rates" id="live_rates">
                  <?php foreach($rates as $rate): ?>
                    <option value='<?php echo $rate->service ?>' <?php if($selectedRate->service == $rate->service) { echo 'selected="selected"'; } ?>>
                      <?php 
                        if($rate->rate !== false) {
                          echo "$rate->service: \$$rate->rate";
                        }
                        else {
                          echo "$rate->service";
                        }
                      ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </th>
            </tr>
          <?php endif; ?>
        
          <tr id="set_shipping_zip_row" <?php echo $zipStyle; ?>>
            <th colspan="5" class="alignRight"><?php _e( 'Enter Your Zip Code' , 'simpleecommcart' ); ?>:
              <input type="text" name="shipping_zip" value="" id="shipping_zip" size="5" />
              
              <?php if(SimpleEcommCartSetting::getValue('international_sales')): ?>
                <select name="shipping_country_code">
                  <?php
                    $customCountries = SimpleEcommCartCommon::getCustomCountries();
                    foreach($customCountries as $code => $name) {
                      echo "<option value='$code'>$name</option>\n";
                    }
                  ?>
                </select>
              <?php else: ?>
                <input type="hidden" name="shipping_country_code" value="<?php echo SimpleEcommCartCommon::getHomeCountryCode(); ?>" id="shipping_country_code">
              <?php endif; ?>
              
              <input type="submit" name="updateCart" value="Calculate Shipping" id="shipping_submit" class="SimpleEcommCartButtonSecondary" />
            </th>
          </tr>
        <?php else:  // Cart in read mode ?>
          <tr>
            <th colspan="5" class='alignRight'>
              <?php
                $liveRates = SimpleEcommCartSession::get('SimpleEcommCartCart')->getLiveRates();
                if($liveRates && SimpleEcommCartSession::get('simpleecommcart_shipping_zip') && SimpleEcommCartSession::get('simpleecommcart_shipping_country_code')) {
                  $selectedRate = $liveRates->getSelected();
                  echo __("Shipping to", "simpleecommcart") . " " . SimpleEcommCartSession::get('simpleecommcart_shipping_zip') . " " . __("via","simpleecommcart") . " " . $selectedRate->service;
                }
              ?>
            </th>
          </tr>
        <?php endif; // End cart in read mode ?>
        
      <?php  else: ?>
        <?php if(count($shippingMethods) > 1 && $fullMode): ?>
       <!-- <tr>
          <th colspan='5' class="alignRight"><?php _e( 'Shipping Method' , 'simpleecommcart' ); ?>: &nbsp;
            <select name='shipping_method_id' id='shipping_method_id'>
              <?php foreach($shippingMethods as $name => $id): ?>
              <option value='<?php echo $id ?>' 
               <?php echo ($id == SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingMethodId())? 'selected' : ''; ?>><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </th>
        </tr>-->
        <?php elseif(!$fullMode): ?>
      <!--  <tr>
          <th colspan='5' class="alignRight"><?php _e( 'Shipping Method' , 'simpleecommcart' ); ?>: 
            <?php 
              $method = new SimpleEcommCartShippingMethod(SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingMethodId());
              echo $method->name;
            ?>
          </th>
        </tr>-->
        <?php endif; ?>
      <?php endif; ?>
    <?php endif; ?>
    
	<?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShippingAndBillingAddress()): ?>
		<?php if($fullMode): ?>
	 		<tr id="shipping_and_billing_to_row" >
	        	<th colspan="5" class="alignRight">
					 
					<table style="border-bottom:none;">
					<?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipTo()): ?>
						<tr  style="border-bottom:none;">
							<td>
								Ship to: 
								<select title="country" id="shipping_country" name="shipping_country">
				                  <?php  
				                    foreach(SimpleEcommCartCommon::getCountries(false) as $code => $name) 
									{ 
									  if($name=='All Countries')continue;
									  $selected='';
									  if(SimpleEcommCartSession::get('simpleecommcart_shipping_country')=="$code~$name")
									  {
									  	$selected='selected="selected"';
									  }
									 
				                      echo "<option value=\"$code~$name\" $selected>$name</option>";
				                    }
				                  ?>
				                </select>
								
								 
								<input  type="hidden" id="hdnShippingState" value="<?php echo SimpleEcommCartSession::get('simpleecommcart_shipping_state') ?>"/>
								<select title="shipping state" name="shipping_state_usa" id="shipping_state_usa">
				                  <option value="">&nbsp;</option> 
				                    <option value="AL">Alabama</option>
				                    <option value="AK">Alaska</option>
				                    <option value="AZ">Arizona</option>
				                    <option value="AR">Arkansas</option>
				                    <option value="CA">California</option>
				                    <option value="CO">Colorado</option>
				                    <option value="CT">Connecticut</option>
				                    <option value="DC">D. C.</option>
				                    <option value="DE">Delaware</option>
				                    <option value="FL">Florida</option>
				                    <option value="GA">Georgia</option>
				                    <option value="HI">Hawaii</option>
				                    <option value="ID">Idaho</option>
				                    <option value="IL">Illinois</option>
				                    <option value="IN">Indiana</option>
				                    <option value="IA">Iowa</option>
				                    <option value="KS">Kansas</option>
				                    <option value="KY">Kentucky</option>
				                    <option value="LA">Louisiana</option>
				                    <option value="ME">Maine</option>
				                    <option value="MD">Maryland</option>
				                    <option value="MA">Massachusetts</option>
				                    <option value="MI">Michigan</option>
				                    <option value="MN">Minnesota</option>
				                    <option value="MS">Mississippi</option>
				                    <option value="MO">Missouri</option>
				                    <option value="MT">Montana</option>
				                    <option value="NE">Nebraska</option>
				                    <option value="NV">Nevada</option>
				                    <option value="NH">New Hampshire</option>
				                    <option value="NJ">New Jersey</option>
				                    <option value="NM">New Mexico</option>
				                    <option value="NY">New York</option>
				                    <option value="NC">North Carolina</option>
				                    <option value="ND">North Dakota</option>
				                    <option value="OH">Ohio</option>
				                    <option value="OK">Oklahoma</option>
				                    <option value="OR">Oregon</option>
				                    <option value="PA">Pennsylvania</option>
				                    <option value="RI">Rhode Island</option>
				                    <option value="SC">South Carolina</option>
				                    <option value="SD">South Dakota</option>
				                    <option value="TN">Tennessee</option>
				                    <option value="TX">Texas</option>
				                    <option value="UT">Utah</option>
				                    <option value="VT">Vermont</option>
				                    <option value="VA">Virginia</option>
				                    <option value="WA">Washington</option>
				                    <option value="WV">West Virginia</option>
				                    <option value="WI">Wisconsin</option>
				                    <option value="WY">Wyoming</option> 
				                </select>
								 
								<select title="shipping state" name="shipping_state_canada" id="shipping_state_canada">
				                  <option value="">&nbsp;</option>  
				                    <option value="AB">Alberta</option>
				                    <option value="BC">British Columbia</option>
				                    <option value="MB">Manitoba</option>
				                    <option value="NB">New Brunswick</option>
				                    <option value="NF">Newfoundland</option>
				                    <option value="NT">Northwest Territories</option>
				                    <option value="NS">Nova Scotia</option>
				                    <option value="NU">Nunavut</option>
				                    <option value="ON">Ontario</option>
				                    <option value="PE">Prince Edward Island</option>
				                    <option value="PQ">Quebec</option>
				                    <option value="SK">Saskatchewan</option>
				                    <option value="YT">Yukon Territory</option> 
				                </select>
								
							</td>
							 <td align="right">
							 <?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireCalculateTaxOrShippingButton()): ?>
						 <?php
						 	$button_text=SimpleEcommCartSession::get('SimpleEcommCartCart')->getCalculateTaxOrShippingButtonText();
						 ?>
						<input type="submit" name="updateCart" value='<?php echo $button_text ?>' id="billing_shipping_submit" class="SimpleEcommCartButtonSecondary" />
					<?php endif; ?>
							 </td>
						</tr>
					<?php endif; ?>
					<?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireBillTo()): ?>
						<tr  style="border-bottom:none;">
							<td>
								Bill to:
								<select title="country" id="billing_country" name="billing_country">
				                  <?php  
				                    foreach(SimpleEcommCartCommon::getCountries(false) as $code => $name) { 
									 if($name=='All Countries')continue;
									 $selected='';
									  if(SimpleEcommCartSession::get('simpleecommcart_billing_country')=="$code~$name")
									  {
									  	$selected='selected="selected"';
									  }
				                      echo "<option value=\"$code~$name\" $selected>$name</option>";
				                    }
				                  ?>
				                </select>
								<input  type="hidden" id="hdnBillingState" value="<?php echo SimpleEcommCartSession::get('simpleecommcart_billing_state') ?>"/>
								<select name='billing_state_usa' id="billing_state_usa">
				                  <option value="">&nbsp;</option> 
				                
				                    <option value="AL">Alabama</option>
				                    <option value="AK">Alaska</option>
				                    <option value="AZ">Arizona</option>
				                    <option value="AR">Arkansas</option>
				                    <option value="CA">California</option>
				                    <option value="CO">Colorado</option>
				                    <option value="CT">Connecticut</option>
				                    <option value="DC">D. C.</option>
				                    <option value="DE">Delaware</option>
				                    <option value="FL">Florida</option>
				                    <option value="GA">Georgia</option>
				                    <option value="HI">Hawaii</option>
				                    <option value="ID">Idaho</option>
				                    <option value="IL">Illinois</option>
				                    <option value="IN">Indiana</option>
				                    <option value="IA">Iowa</option>
				                    <option value="KS">Kansas</option>
				                    <option value="KY">Kentucky</option>
				                    <option value="LA">Louisiana</option>
				                    <option value="ME">Maine</option>
				                    <option value="MD">Maryland</option>
				                    <option value="MA">Massachusetts</option>
				                    <option value="MI">Michigan</option>
				                    <option value="MN">Minnesota</option>
				                    <option value="MS">Mississippi</option>
				                    <option value="MO">Missouri</option>
				                    <option value="MT">Montana</option>
				                    <option value="NE">Nebraska</option>
				                    <option value="NV">Nevada</option>
				                    <option value="NH">New Hampshire</option>
				                    <option value="NJ">New Jersey</option>
				                    <option value="NM">New Mexico</option>
				                    <option value="NY">New York</option>
				                    <option value="NC">North Carolina</option>
				                    <option value="ND">North Dakota</option>
				                    <option value="OH">Ohio</option>
				                    <option value="OK">Oklahoma</option>
				                    <option value="OR">Oregon</option>
				                    <option value="PA">Pennsylvania</option>
				                    <option value="RI">Rhode Island</option>
				                    <option value="SC">South Carolina</option>
				                    <option value="SD">South Dakota</option>
				                    <option value="TN">Tennessee</option>
				                    <option value="TX">Texas</option>
				                    <option value="UT">Utah</option>
				                    <option value="VT">Vermont</option>
				                    <option value="VA">Virginia</option>
				                    <option value="WA">Washington</option>
				                    <option value="WV">West Virginia</option>
				                    <option value="WI">Wisconsin</option>
				                    <option value="WY">Wyoming</option>
				                  
				                </select>
								<select name='billing_state_canada' id="billing_state_canada">
				                  <option value="">&nbsp;</option>  
				                    <option value="AB">Alberta</option>
				                    <option value="BC">British Columbia</option>
				                    <option value="MB">Manitoba</option>
				                    <option value="NB">New Brunswick</option>
				                    <option value="NF">Newfoundland</option>
				                    <option value="NT">Northwest Territories</option>
				                    <option value="NS">Nova Scotia</option>
				                    <option value="NU">Nunavut</option>
				                    <option value="ON">Ontario</option>
				                    <option value="PE">Prince Edward Island</option>
				                    <option value="PQ">Quebec</option>
				                    <option value="SK">Saskatchewan</option>
				                    <option value="YT">Yukon Territory</option> 
				                </select>
							</td>
							 
						</tr> 
					<?php endif; ?> 
					</table> 
					<?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipping() && 
					SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShippingVariation()): ?>
		 				
							<table>
								<tr  style="border-bottom:none;">
									<td>
										Shipping Variation:
										<select title="shippingvariations" id="shipping_variations" name="shipping_variations">
										<?php
											$shipping_variation = new SimpleEcommCartShippingVariation();
											$shipping_variations = $shipping_variation->getModels();
											foreach($shipping_variations as $s_variation)
											{
												$text = $s_variation->variation.'(+'.SIMPLEECOMMCART_CURRENCY_SYMBOL. $s_variation->additional_price.')';
												$val = $s_variation->id;
												
												$selected='';
									  			if(SimpleEcommCartSession::get('simpleecommcart_shipping_variations')=="$val")
									  			{
									  				$selected='selected="selected"';
									  			}
				                     			echo "<option value=\"$val\" $selected>$text</option>";
											}
										?>
										</select>
									</td>
									 
								</tr>
							</table>
					<?php endif; ?>
					
					
				</th>
			</tr>
	  	
		<?php  else: ?>
			 &nbsp; 
		<?php endif; ?>
	<?php endif; ?>
	
    <tr class="subtotal">
      <?php if($fullMode): ?>
      
      <td colspan='3'>
        <input type='submit' name='updateCart' value='<?php _e( 'UPDATE CART' , 'simpleecommcart' ); ?>' class="SimpleEcommCartUpdateTotalButton SimpleEcommCartButtonPrimary" />
		 <input type='submit' name='updateCart' value='<?php _e( 'CLEAR CART' , 'simpleecommcart' ); ?>' class="SimpleEcommCartUpdateTotalButton SimpleEcommCartButtonSecondary" />
      </td>
      <?php else: ?>
        <td colspan='3'>&nbsp;</td>
      <?php endif; ?>
      <td class="alignRight strong" colspan="1"><?php _e( 'Subtotal' , 'simpleecommcart' ); ?>:</td>
      <td class='strong' colspan="1"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($subtotal, 2); ?></td>
    </tr>
    
    <?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipping()): ?>
    <tr class="shipping">
      <td colspan='1'>&nbsp;</td>
      <td colspan="2">&nbsp;</td>
      <td colspan="1" class="alignRight strong"><?php _e( 'Shipping' , 'simpleecommcart' ); ?>:</td>
      <td colspan="1" class="strong"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo $shipping ?></td>
    </tr>
    <?php endif; ?>
    
    <?php if($promotion): ?>
      <tr class="coupon">
        <td colspan='2'>&nbsp;</td>
        <td colspan="2" class="alignRight strong"><?php _e( 'Coupon' , 'simpleecommcart' ); ?>:</td>
        <td colspan="1" class="strong"><?php echo $promotion->getAmountDescription(); ?></td>
      </tr>
    <?php endif; ?>
    
    
    <?php if($tax > 0): ?>
      <tr class="tax">
        <td colspan='3'>&nbsp;</td>
        <td colspan="1" class="alignRight strong"><?php _e( 'Tax' , 'simpleecommcart' ); ?>:</td>
        <td colspan="1" class="strong"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($tax, 2); ?></td>
      </tr>
    <?php endif; ?>
    
      <tr class="total">
        <?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->getNonSubscriptionAmount() > 0): ?>
        <td class="alignRight" colspan='3'>
          <?php if($fullMode && SimpleEcommCartCommon::activePromotions()): ?>
            <p class="haveCoupon"><?php _e( 'Do you have a coupon?' , 'simpleecommcart' ); ?>
            <div id="couponCode"> <?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->getPromoStatus() < 0): ?>
              <span class="promoMessage"><?php echo SimpleEcommCartSession::get('SimpleEcommCartCart')->getPromoMessage(); ?></span>
            <?php endif; ?><input type='text' name='couponCode' value='' /></div>
            <div id="updateCart"><input type='submit' name='updateCart' value='<?php _e( 'Apply Coupon' , 'simpleecommcart' ); ?>' class="SimpleEcommCartApplyCouponButton SimpleEcommCartButtonSecondary" /></div></p>
           
          <?php endif; ?>&nbsp;
        </td>
        <?php else: ?>
          <td colspan='3'>&nbsp;</td>
        <?php endif; ?>
        <td colspan="1" class="alignRight strong SimpleEcommCartCartTotalLabel"><?php _e( 'Total' , 'simpleecommcart' ); ?>:</td>
        <td colspan="1" class="strong">
          <?php 
            echo SIMPLEECOMMCART_CURRENCY_SYMBOL;
            echo number_format(SimpleEcommCartSession::get('SimpleEcommCartCart')->getGrandTotal() + $tax, 2);
          ?>
        </td>
      </tr>
  </table>
</form>

  <?php if($fullMode): ?>
    
  <div id="viewCartNav">
	

	
	  <?php	  
  	  // dont show checkout until terms are accepted (if necessary)
  	 if((SimpleEcommCartSetting::getValue('require_terms') != 1) ||  
  	    (SimpleEcommCartSetting::getValue('require_terms') == 1 && (isset($_POST['terms_acceptance']) || SimpleEcommCartSession::get("terms_acceptance")=="accepted")) ) :  
  	    
  	    if(SimpleEcommCartSetting::getValue('require_terms') == 1){
  	      SimpleEcommCartSession::set("terms_acceptance","accepted",true);        
  	    }
  	    
  	?>
        <?php
          $checkoutImg = false;
          if($cartImgPath) {
            $checkoutImg = $cartImgPath . 'checkout.png';
          }
        ?>
	  <?php
		$terms_page_link='#';
		if(SimpleEcommCartSetting::getValue('terms_and_condition')=='yes' && SimpleEcommCartSetting::getValue('terms_and_condition_page')!=NULL)
		{
			$terms_page_id = SimpleEcommCartSetting::getValue('terms_and_condition_page');
			//echo '<br>$terms_page_id:'.$terms_page_id;
			$terms_page_link = get_permalink($terms_page_id);
			//echo '<br>$terms_page_link:'.$terms_page_link;
		?>
		<div style="clear:both; width:100%;text-align:right;">
		<input type="checkbox" id="agreeTermsAndCondition" name="agreeTermsAndCondition"/><span>I agree to the</span>	<a style="margin-right:7px;" href="<?php echo $terms_page_link ?>" target="_blank">Terms & Conditions</a>
</div>
	<?php
		} 
	 ?>
<div id="continueShopping" style="padding-left:5px;">
        <?php if($cartImgPath): ?>
          <a href='<?php echo SimpleEcommCartSession::get('SimpleEcommCartLastPage'); ?>' class="SimpleEcommCartCartContinueShopping" ><img src='<?php echo $continueShoppingImg ?>' /></a>
        <?php else: ?>
          <a href='<?php echo SimpleEcommCartSession::get('SimpleEcommCartLastPage'); ?>' class="SimpleEcommCartButtonSecondary SimpleEcommCartCartContinueShopping" title="Continue Shopping"><?php _e( '<< CONTINUE SHOPPING' , 'simpleecommcart' ); ?></a>
        <?php endif; ?>
	</div>
      <div id="checkoutShopping" style="padding-right:10px;"> 
	  	<select name="checkout_select" id="checkout_select"> 
			 <?php 
			 echo 'use_authorize_checkout:'.SimpleEcommCartSetting::getValue('use_authorize_checkout');
			 	 
				if(SimpleEcommCartSetting::getValue('use_paypal_standard_checkout') == 'on')
				{
					?>
						<option value="paypalCheckout">Paypal Checkout</option>
					<?php
				}
				/*if(SimpleEcommCartSetting::getValue('use_authorize_checkout') == 'on')
				{
					?>
						<option value="authCheckout">Authorize.NET Checkout</option>
					<?php
				}*/
				 
			 ?>
			
			
			
			
		</select>
        <?php if($checkoutImg): ?>
          <a id="SimpleEcommCartCheckoutButton" href='<?php echo get_permalink($checkoutPage->ID) ?>'><img src='<?php echo $checkoutImg ?>' /></a>
        <?php else: ?>
          <a id="SimpleEcommCartCheckoutButton" href='<?php echo get_permalink($checkoutPage->ID) ?>' class="SimpleEcommCartButtonPrimary" title="Continue to Checkout"><?php _e( 'PROCEED TO SECURE CHECKOUT' , 'simpleecommcart' ); ?></a>
        <?php endif; ?>
    	</div>
    <?php else: ?>
    <div id="SimpleEcommCartCheckoutReplacementText">
        <?php echo SimpleEcommCartSetting::getValue('cart_terms_replacement_text');  ?>
    </div>
    <?php endif; ?>
	
	
	   <?php  

    	if(SIMPLEECOMMCART_PRO && SimpleEcommCartSetting::getValue('require_terms') == 1 && (!isset($_POST['terms_acceptance']) && SimpleEcommCartSession::get("terms_acceptance")!="accepted") ){
    	    echo SimpleEcommCartCommon::getView("pro/views/terms.php",array("location"=>"SimpleEcommCartCartTOS"));
    	} 

    	 ?>
	
	</div>
	
	
  <?php endif; ?>
<?php else: ?>
  <div id="emptyCartMsg">
  <h3>Your Cart Is Empty</h3>
  <?php if($cartImgPath): ?>
    <p><a href='<?php echo SimpleEcommCartSession::get('SimpleEcommCartLastPage'); ?>' title="Continue Shopping" class="SimpleEcommCartCartContinueShopping"><img alt="Continue Shopping" class="continueShoppingImg" src='<?php echo $continueShoppingImg ?>' /></a>
  <?php else: ?>
    <p><a href='<?php echo SimpleEcommCartSession::get('SimpleEcommCartLastPage'); ?>' class="SimpleEcommCartButtonSecondary" title="Continue Shopping"><?php _e( 'Continue Shopping' , 'simpleecommcart' ); ?></a>
  <?php endif; ?>
  </div>
  <?php
    SimpleEcommCartSession::get('SimpleEcommCartCart')->clearPromotion();
    SimpleEcommCartSession::drop("terms_acceptance");
  ?>
<?php endif; ?>

<script type="text/javascript" charset="utf-8">
/* <![CDATA[ */
  $jq = jQuery.noConflict();

  $jq('document').ready(function() {
    $jq('#shipping_method_id').change(function() {
      $jq('#SimpleEcommCartCartForm').submit();
    });
    
    $jq('#live_rates').change(function() {
      $jq('#SimpleEcommCartCartForm').submit();
    });
    
    $jq('.showEntriesLink').click(function() {
      var panel = $jq(this).attr('rel');
      $jq('#' + panel).toggle();
      return false;
    });
    
    $jq('#change_shipping_zip_link').click(function() {
      $jq('#set_shipping_zip_row').toggle();
      return false;
    });
	
	selectState();
	showHideState();
	$jq('#shipping_country').change(function(){
		showHideState();
	});
	$jq('#billing_country').change(function(){
		showHideState();
	});
	
	$jq('#SimpleEcommCartCheckoutButton').click(function(){
		if($jq('#agreeTermsAndCondition').val()=='on') ;// do nothing
		else return true;
		
		if($jq('#agreeTermsAndCondition:checked').val()=='on')
		{
			return true;
		}
		else
		{
			alert('You have to agree the Terms & Conditions');
			return false;
		}
	});
	populateCheckoutButtonURL();
	 
	$jq('#checkout_select').change(function(){ 
		populateCheckoutButtonURL();
	});
  });
  function selectState()
  {
  	var shipping_country = $jq('#shipping_country').val();
	var billing_country = $jq('#billing_country').val();
	
  	var shipping_state = $jq('#hdnShippingState').val();
	var billing_state = $jq('#hdnBillingState').val();
	
	if(shipping_country == 'US~United States' )
	{
		$jq('#shipping_state_usa').val(shipping_state);
	}
	else
	{
		$jq('#shipping_state_canada').val(shipping_state);
	}
	if(billing_country == 'US~United States' )
	{
		$jq('#billing_state_usa').val(billing_state);
	}
	else
	{
		$jq('#billing_state_canada').val(billing_state);
	}
  }
  function showHideState()
  {
  	$jq('#shipping_state_usa').hide();
  	$jq('#shipping_state_canada').hide();
	$jq('#billing_state_usa').hide();
  	$jq('#billing_state_canada').hide();
	
	
  	var shipping_country = $jq('#shipping_country').val();
	var billing_country = $jq('#billing_country').val();
	
  	if(shipping_country == 'US~United States' )
	{
		$jq('#shipping_state_usa').show();
	}
	else
	{
		$jq('#shipping_state_usa').hide();
	}
	
	if(shipping_country == 'CA~Canada' )
	{
		$jq('#shipping_state_canada').show();
	}
	else
	{
		$jq('#shipping_state_canada').hide();
	}
	
	if(billing_country == 'US~United States' )
	{
		$jq('#billing_state_usa').show();
	}
	else
	{
		$jq('#billing_state_usa').hide();
	}
	
	if(billing_country == 'CA~Canada' )
	{
		$jq('#billing_state_canada').show();
	}
	else
	{
		$jq('#billing_state_canada').hide();
	}
  }
  function populateCheckoutButtonURL()
  {
  	//
  	var selected = $jq('#checkout_select').val();
	var url=$jq('#SimpleEcommCartCheckoutButton').attr('href');
	
	var index = url.indexOf('checkout_select');
	if(index>-1)
	{
		//update
		url=url.substring(0,index - 1);
	}
	
	var index_of_question_mark=url.indexOf('?');
	if(index_of_question_mark > -1){
		url+='&checkout_select='+selected;
	}
	else
	{
		url+='?checkout_select='+selected;
	}
	
	$jq('#SimpleEcommCartCheckoutButton').attr('href',url);
  }
/* ]]> */
</script>
