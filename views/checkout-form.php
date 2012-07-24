<?php
$account = false;
if(SIMPLEECOMMCART_PRO) {
 
}

if(empty($b['country'])){
   $b['country'] = SimpleEcommCartCommon::getHomeCountryCode();
}

// Show errors
if(count($errors)) {
  echo SimpleEcommCartCommon::showErrors($errors);
}
?>

<form action="" method='post' id="<?php echo $gatewayName ?>_form" class="phorm2<?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipping() && $gatewayName != 'SimpleEcommCartManualGateway'): echo ' shipping'; endif; ?> <?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->hasSubscriptionProducts() || SimpleEcommCartSession::get('SimpleEcommCartCart')->hasMembershipProducts()): echo ' subscription'; endif; ?>">
  <input type="hidden" name="simpleecommcart-gateway-name" value="<?php echo $gatewayName ?>"/>
<div id="ccInfo">
	<div id="billingInfo">
        <ul id="billingAddress" class="shortLabels" >
          <?php if($gatewayName == 'SimpleEcommCartManualGateway' && !SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipping()): ?>
            <li><h2><?php _e( 'Order Information' , 'simpleecommcart' ); ?></h2></li>
          <?php else: ?>
            <li><h2><?php _e( 'Billing Details' , 'simpleecommcart' ); ?></h2></li>
          <?php endif; ?>

          <li>
            <label for="billing-firstName"><?php _e( 'First name' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="billing-firstName" name="billing[firstName]" value="<?php SimpleEcommCartCommon::showValue($b['firstName']); ?>">
          </li>

          <li>
            <label for="billing-lastName"><?php _e( 'Last name' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="billing-lastName" name="billing[lastName]" value="<?php SimpleEcommCartCommon::showValue($b['lastName']); ?>">
          </li>

          <li>
            <label for="billing-address"><?php _e( 'Address' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="billing-address" name="billing[address]" value="<?php SimpleEcommCartCommon::showValue($b['address']); ?>">
          </li>

          <li>
            <label for="billing-address2" id="billing-address2-label" class="SimpleEcommCartHidden"><?php _e( 'Address 2' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="billing-address2" name="billing[address2]" value="<?php SimpleEcommCartCommon::showValue($b['address2']); ?>">
          </li>

          <li>
            <label for="billing-city"><?php _e( 'City' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="billing-city" name="billing[city]" value="<?php SimpleEcommCartCommon::showValue($b['city']); ?>">
          </li>

          <li><label for="billing-state_text" class="short billing-state_label"><?php _e( 'State' , 'simpleecommcart' ); ?>:</label>
            <input type="text" name="billing[state_text]" value="<?php SimpleEcommCartCommon::showValue($b['state']); ?>" id="billing-state_text" class="state_text_field" />
            <select id="billing-state" class="required" title="State billing address" name="billing[state]">
              
			    <option value="0">&nbsp;</option> 
                  <optgroup label="United States">
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
                  </optgroup>
                  <optgroup label="Canada">
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
                  </optgroup>
              <?php
               /* SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Country code on checkout form: $billingCountryCode");
                $zone = SimpleEcommCartCommon::getZones($billingCountryCode);
                foreach($zone as $code => $name) {
                  $selected = ($b['state'] == $code) ? 'selected="selected"' : '';
                  echo '<option value="' . $code . '" ' . $selected . '>' . $name . '</option>';
                }*/
              ?>
            </select>
          </li>

          <li>
            <label for="billing-zip" class="billing-zip_label"><?php _e( 'Zip code' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="billing-zip" name="billing[zip]" value="<?php SimpleEcommCartCommon::showValue($b['zip']); ?>">
          </li>

          <li>
		   <?php 
			   $all_country=false; 
			   if(strpos(SimpleEcommCartSetting::getValue('countries'), "00~All Countries") === false)
			   {
			  		//do nothing
			   }
			   else
			   {
			   		$all_country=true; 
			   }
		   ?>
            <label for="billing-country" class="short"><?php _e( 'Country' , 'simpleecommcart' ); ?>:</label>
            <select title="country" id="billing-country" name="billing[country]" class="billing_countries">
              <?php foreach(SimpleEcommCartCommon::getCountries($all_country) as $code => $name): ?>
			  	<?php if($code=='00')continue; ?>
                <option value="<?php echo $code ?>" <?php if($code == $billingCountryCode) { echo 'selected="selected"'; } ?>><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        </ul>
	</div><!-- #billingInfo -->
   
  <?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipping()): ?>
	<div id="shippingInfo">
        <ul id="shippingAddressCheckbox">
          <li><h2><?php _e( 'Shipping Details' , 'simpleecommcart' ); ?></h2></li>
    
          <li>
            <label for="sameAsBilling"><?php _e( 'Same as billing address' , 'simpleecommcart' ); ?>:</label>
            <input type='checkbox' class='sameAsBilling' id='sameAsBilling' name='sameAsBilling' value='1'>
          </li>
        </ul>

        <ul id="shippingAddress" class="shippingAddress shortLabels">

          <li>
            <label for="shipping-firstName"><?php _e( 'First name' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="shipping-firstName" name="shipping[firstName]" value="<?php SimpleEcommCartCommon::showValue($s['firstName']); ?>">
          </li>

          <li>
            <label for="shipping-lastName"><?php _e( 'Last name' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="shipping-lastName" name="shipping[lastName]" value="<?php SimpleEcommCartCommon::showValue($s['lastName']); ?>">
          </li>

          <li>
            <label for="shipping-address"><?php _e( 'Address' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="shipping-address" name="shipping[address]" value="<?php SimpleEcommCartCommon::showValue($s['address']); ?>">
          </li>

          <li>
            <label for="shipping-address2">&nbsp;</label>
            <input type="text" id="shipping-address2" name="shipping[address2]" value="<?php SimpleEcommCartCommon::showValue($s['address2']); ?>">
          </li>

          <li>
            <label for="shipping-city"><?php _e( 'City' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="shipping-city" name="shipping[city]" value="<?php SimpleEcommCartCommon::showValue($s['city']); ?>">
          </li>

          <li>
            <label for="shipping-state_text" class="short shipping-state_label"><?php _e( 'State' , 'simpleecommcart' ); ?>:</label>
            <input type="text" name="shipping[state_text]" value="<?php SimpleEcommCartCommon::showValue($s['state']); ?>" id="shipping-state_text" class="state_text_field" />
            <select id="shipping-state" class="shipping_countries required" title="State shipping address" name="shipping[state]">
              <option value="0">&nbsp;</option>    
                  <optgroup label="United States">
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
                  </optgroup>
                  <optgroup label="Canada">
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
                  </optgroup>            
              <?php
			  /*
                $zone = SimpleEcommCartCommon::getZones($shippingCountryCode);
                foreach($zone as $code => $name) {
                  $selected = ($s['state'] == $code) ? 'selected="selected"' : '';
                  echo '<option value="' . $code . '" ' . $selected . '>' . $name . '</option>';
                }
				*/
              ?>
            </select>
          </li>

          <li>
            <label for="shipping-zip" class="shipping-zip_label"><?php _e( 'Zip code' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="shipping-zip" name="shipping[zip]" value="<?php SimpleEcommCartCommon::showValue($s['zip']); ?>">
          </li>

          <li>
            <label for="shipping-country" class="short"><?php _e( 'Country' , 'simpleecommcart' ); ?>:</label>
            <select title="country" id="shipping-country" name="shipping[country]">
			 <?php 
			   $all_country=false; 
			   if(strpos(SimpleEcommCartSetting::getValue('countries'), "00~All Countries") === false)
			   {
			  		//do nothing
			   }
			   else
			   {
			   		$all_country=true; 
			   }
		   ?>
              <?php foreach(SimpleEcommCartCommon::getCountries($all_country) as $code => $name): ?>
                <option value="<?php echo $code ?>" <?php if($code == $shippingCountryCode) { echo 'selected="selected"'; } ?>><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        </ul>
     </div> <!--shippingInfo-->
	
        <?php else: ?>
          <input type='hidden' id='sameAsBilling' name='sameAsBilling' value='1' />
        <?php endif; ?>
<div id="paymentInfo">
        <ul id="contactPaymentInfo" class="shortLabels">
          <?php if($gatewayName == 'SimpleEcommCartManualGateway'): ?>
            <li><h2><?php _e( 'Contact Info' , 'simpleecommcart' ); ?></h2></li>
          <?php else: ?>
            <li><h2><?php _e( 'Payment Information' , 'simpleecommcart' ); ?></h2></li>
          <?php endif; ?>
        
          <?php if($gatewayName != 'SimpleEcommCartManualGateway'): ?>
          <li>
            <label for="payment-cardType">Card Type:</label>
            <select id="payment-cardType" name="payment[cardType]">
              <?php foreach($data['gateway']->getCreditCardTypes() as $name => $value): ?>
                <option value="<?php echo $value ?>"><?php echo $name ?></option>
              <?php endforeach; ?>
            </select>
          </li>
        
          <li>
            <label for="payment-cardNumber"><?php _e( 'Card Number' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="payment-cardNumber" name="payment[cardNumber]" value="<?php SimpleEcommCartCommon::showValue($p['cardNumber']); ?>">
          </li>
        
          <li>
            <label for="payment-cardExpirationMonth"><?php _e( 'Expiration' , 'simpleecommcart' ); ?>:</label>
            <select id="payment-cardExpirationMonth" name="payment[cardExpirationMonth]">
              <option value=''></option>
              <?php 
                for($i=1; $i<=12; $i++){
                  $val = $i;
                  if(strlen($val) == 1) {
                    $val = '0' . $i;
                  }
                  $selected = '';
                  if(isset($p['cardExpirationMonth']) && $val == $p['cardExpirationMonth']) {
                    $selected = 'selected="selected"';
                  }
                  echo "<option value='$val' $selected>$val</option>\n";
                } 
              ?>
            </select> / <select id="payment-cardExpirationYear" name="payment[cardExpirationYear]">
              <option value=''></option>
              <?php
                $year = date('Y', SimpleEcommCartCommon::localTs());
                for($i=$year; $i<=$year+12; $i++) {
                  $selected = '';
                  if(isset($p['cardExpirationYear']) && $i == $p['cardExpirationYear']) {
                    $selected = 'selected="selected"';
                  }
                  echo "<option value='$i' $selected>$i</option>\n";
                } 
              ?>
            </select>
          
          </li>
          
          <li>
            <label for="payment-securityId"><?php _e( 'Security ID' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="payment-securityId" name="payment[securityId]" value="<?php SimpleEcommCartCommon::showValue($p['securityId']); ?>">
            <p class="description"><?php _e( 'Security code on back of card' , 'simpleecommcart' ); ?></p>
          </li>

          <?php endif; ?>
          <li>
            <label for="payment-phone"><?php _e( 'Phone' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="payment-phone" name="payment[phone]" value="<?php SimpleEcommCartCommon::showValue($p['phone']); ?>">
          </li>
          
          <li>
            <label for="payment-email"><?php _e( 'Email' , 'simpleecommcart' ); ?>:</label>
            <input type="text" id="payment-email" name="payment[email]" value="<?php SimpleEcommCartCommon::showValue($p['email']); ?>">
          </li>
          </ul>

	</div> 
</div> 

			
			<?php if(!SimpleEcommCartCommon::isLoggedIn()): ?>
	          <?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->hasSubscriptionProducts() || SimpleEcommCartSession::get('SimpleEcommCartCart')->hasMembershipProducts()): ?>
	            <?php echo SimpleEcommCartCommon::getView('pro/views/account-form.php', array('account' => $account, 'embed' => false)); ?>
	          <?php endif; ?>
	        <?php endif; ?>
			
          <div id="SimpleEcommCartCheckoutButtonDiv">
            <label for="SimpleEcommCartCheckoutButton" class="SimpleEcommCartHidden"><?php _e( 'Checkout' , 'simpleecommcart' ); ?></label>
            <?php
              $cartImgPath = SimpleEcommCartSetting::getValue('cart_images_url');
              if($cartImgPath) {
                if(strpos(strrev($cartImgPath), '/') !== 0) {
                  $cartImgPath .= '/';
                }
                $completeImgPath = $cartImgPath . 'complete-order.png';
              }
            ?>
            <?php if($cartImgPath): ?>
              <input id="SimpleEcommCartCheckoutButton" type="image" src='<?php echo $completeImgPath ?>' value="Complete Order" name="Complete Order"/>
            <?php else: ?>
              <input id="SimpleEcommCartCheckoutButton" class="SimpleEcommCartButtonPrimary SimpleEcommCartCompleteOrderButton" type="submit"  value="Complete Order" name="Complete Order"/>
            <?php endif; ?>

           
          </div>
</form>