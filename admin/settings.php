<?php
 
 
$rate = new SimpleEcommCartTaxRate();
$setting = new SimpleEcommCartSetting();
$product_category = new SimpleEcommCartProductCategory();
$successMessage = '';
$versionInfo = false;

if($_SERVER['REQUEST_METHOD'] == "POST") {
  if($_POST['simpleecommcart-action'] == 'save rate') {
    $data = $_POST['tax'];
    if(isset($data['zip']) && !empty($data['zip'])) {
      list($low, $high) = explode('-', $data['zip']);
      
      if(isset($low)) {
        $low = trim($low);
      }
      
      if(isset($high)) {
        $high = trim($high);
      }
      else { $high = $low; }
      
      if(is_numeric($low) && is_numeric($high)) {
        if($low > $high) {
          $x = $high;
          $high = $low;
          $low = $x;
        }
        $data['zip_low'] = $low;
        $data['zip_high'] = $high;
      }
      
    }
    $rate->setData($data);
    $rate->save();
    $rate->clear();
    $successMessage = "Tax rate saved";
  }
  elseif($_POST['simpleecommcart-action'] == 'saveOrderNumber' && SIMPLEECOMMCART_PRO) {
    $orderNumber = trim(SimpleEcommCartCommon::postVal('order_number'));
    SimpleEcommCartSetting::setValue('order_number', $orderNumber);
    $versionInfo = SimpleEcommCartProCommon::getVersionInfo();
    if($versionInfo) {
      $successMessage = "Thank you! SimpleEcommCart has been activated.";
    }
    else {
      SimpleEcommCartSetting::setValue('order_number', '');
      $orderNumberFailed = true;
    }
  }
  elseif($_POST['simpleecommcart-action'] == 'save product category') {
	$data = $_POST['product_category'];
	$product_category->setData($data);
	$product_category->save();
	$product_category->clear(); 
  }
} 
elseif(isset($_GET['task']) && $_GET['task'] == 'deleteTax' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = SimpleEcommCartCommon::getVal('id');
  $rate->load($id);
  $rate->deleteMe();
  $rate->clear();
}
elseif(isset($_GET['task']) && $_GET['task'] == 'editProductCategory' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = SimpleEcommCartCommon::getVal('id');
  $product_category->load($id);
}
elseif(isset($_GET['task']) && $_GET['task'] == 'deleteProductCategory' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = SimpleEcommCartCommon::getVal('id');
  $product_category->load($id);
  $product_category->deleteMe();
  $product_category->clear();
}

$cardTypes = SimpleEcommCartSetting::getValue('auth_card_types');
if($cardTypes) {
  $cardTypes = explode('~', $cardTypes);
}
else {
  $cardTypes = array();
}

?>

<?php if(!empty($successMessage)): ?>
  
<script type='text/javascript'>
  var $j = jQuery.noConflict();

  $j(document).ready(function() {
    setTimeout("$j('#SimpleEcommCartSuccessBox').hide('slow')", 2000);
  });
  
  <?php if($versionInfo): ?>
    setTimeout("$j('.unregistered').hide('slow')", 1000);
  <?php  endif; ?>
</script>
  
<div class='SimpleEcommCartSuccessModal' id="SimpleEcommCartSuccessBox" style=''>
  <p><strong><?php _e( 'Success' , 'simpleecommcart' ); ?></strong><br/>
  <?php echo $successMessage ?></p>
</div>


<?php endif; ?> 

<h2><?php _e( 'Settings' , 'simpleecommcart' ); ?></h2>

<div id="saveResult"></div>
<div class='wrap' style="width:80%;max-width:80%;float:left;"> 
<div id="widgets-left" style="margin-right: 5px;">
  <div id="available-widgets">

	<!-- General Settings -->
	<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'General Settings' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
			<div> 
			 	<form id="generalSettingsForm" class="ajaxSettingForm" action="" method='post'>
             <input type='hidden' name='action' value="save_settings" />
             <input type='hidden' name='_success' value="Your general settings have been saved.">
            <ul>
             
              <li>
                <label style="display: inline-block; width: 120px; text-align: right;"  for='home_country'><?php _e( 'Base Country' , 'simpleecommcart' ); ?>:</label>
               <select title="country" id="home_country" name="home_country">
                  <?php 
                    $homeCountryCode = 'US';
                    $homeCountry = SimpleEcommCartSetting::getValue('home_country');
                    if($homeCountry) {
                      list($homeCountryCode, $homeCountryName) = explode('~', $homeCountry);
                    }
                    
                    foreach(SimpleEcommCartCommon::getCountries(true) as $code => $name) {
                      $selected = ($code == $homeCountryCode) ? 'selected="selected"' : '';
                      echo "<option value=\"$code~$name\" $selected>$name</option>";
                    }
                  ?>
                </select><img title="The country you are operating from. Required for calculating local and international shipping cost." src=" <?php echo INFO_ICON ?>"/>
              </li>
             <li>
                <label style="display: inline-block; width: 120px; text-align: right;"  for='home_country_zip_code'><?php _e( 'Zip/Post Code' , 'simpleecommcart' ); ?>:</label>
                 <input  type="text" id="home_country_zip_code" name="home_country_zip_code" value="<?php echo SimpleEcommCartSetting::getValue('home_country_zip_code')?>" />
				      <img title="Optional" src=" <?php echo INFO_ICON ?>"/>
              </li>
			  
			  <li>
                <label style="display: inline-block; width: 120px; text-align: right;" for='international_sales'><?php _e( 'International sales' , 'simpleecommcart' ); ?>:</label>
                <input type='radio' name='international_sales' id='international_sales_yes' value="1" 
                  <?php echo SimpleEcommCartSetting::getValue('international_sales') == '1' ? 'checked="checked"' : '' ?>/> <?php _e( 'Yes' , 'simpleecommcart' ); ?>
                <input type='radio' name='international_sales' id='international_sales_no' value="" 
                  <?php echo SimpleEcommCartSetting::getValue('international_sales') != '1'? 'checked="checked"' : '' ?>/> <?php _e( 'No' , 'simpleecommcart' ); ?>
				  <img title="Select “No” if you only want to sell locally else “Yes” and you can choose specific countries or all countries" src=" <?php echo INFO_ICON ?>"/>
	   

              </li>
              
              <li id="eligible_countries_block">
                <label style="display: inline-block; width: 120px; text-align: right;" for="countries"><?php _e( 'Target Markets International' , 'simpleecommcart' ); ?>:</label> 
                <div style="float: none; margin: -10px 0px 20px 125px;">
				<input id="hdnCountries" type="hidden" name="countries" value=""/>
				<table>
				<tr>
				<td style="padding:5px;">Select</td>
				<td style="padding:5px;"><a id="selectAll" href="#">All</a></td>
				<td style="padding:5px;"><a id="selectNone" href="#">None</a></td>
				</tr>
				</table>
					<div style="height:200px;overflow:auto;">
					  <?php
                    $countryList = SimpleEcommCartSetting::getValue('countries');
                    $countryList = $countryList ? explode(',', $countryList) : array();
                  ?>
				 <?php foreach(SimpleEcommCartCommon::getCountries(true) as $code => $country): ?>
				 <?php 
                      $selected = (in_array($code . '~' .$country, $countryList)) ? 'checked="checked"' : '';
                      if(!empty($code)):
                    ?>
                     <input class="targetmarketselect" type="checkbox" id="chk_<?php echo $code?>" value="<?php echo $code . '~' . $country; ?>" 
					 <?php echo $selected ?>/><?php echo $country ?><br>
                      
                    <?php endif; ?>
                  <?php endforeach; ?> 
				  </div>
                </div>
              </li>

  			  <li>
                <label style="display: inline-block; width: 120px; text-align: right;" >&nbsp;</label>
                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" />
                
              </li>
            </ul>
          </form> 
        	</div>
	  	</div>
	</div>
	
	<!-- Currency & Payments Settings -->
	<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'Currency & Payments Settings' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
			 <div>
		          <form id="orderNumberForm" class="ajaxSettingForm" action="" method='post'>
		            <input type='hidden' name='action' value="save_settings" />
		            <input type='hidden' name='_success' value="Your currency & payments settings have been saved.">
		            <ul> 
		              <li>
		                <label style="display: inline-block; width: 120px; text-align: right;">Currency:</label>
						<select id="SIMPLEECOMMCART_CURRENCY_SYMBOL_text" name="SIMPLEECOMMCART_CURRENCY_SYMBOL_text">
		                  <?php 
		                    
		                    foreach(SimpleEcommCartCommon::getPayPalCurrencyCodes(true) as $name => $code) {
		                     $selected = ($code == SimpleEcommCartSetting::getValue('SIMPLEECOMMCART_CURRENCY_SYMBOL_text')) ? 'selected="selected"' : '';
		                      echo "<option value=\"$code\" $selected>$name($code)</option>";
		                    }
		                  ?>
						  </select> 
						   <img title="Select the currency that you would like to receive your payments in." src=" <?php echo INFO_ICON ?>"/>
		              </li>
		              <li>
		                <label style="display: inline-block; width: 120px; text-align: right;"><?php _e( 'Currency Symbol' , 'simpleecommcart' ); ?>:</label>
		                <input type="text" name="SIMPLEECOMMCART_CURRENCY_SYMBOL" value="<?php echo SimpleEcommCartSetting::getValue('SIMPLEECOMMCART_CURRENCY_SYMBOL'); ?>" id="SIMPLEECOMMCART_CURRENCY_SYMBOL"/>
		                <img title="By default if you keep this field empty “$” sign will be used. You can add other symbols eg £, €" src=" <?php echo INFO_ICON ?>"/>

		              </li> 
		              <li>
		                <label style="display: inline-block; width: 120px; text-align: right;" >&nbsp;</label>
		                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" />
		              </li>
		
		            </ul>
		          </form>
        </div>
	  	</div>
	</div>
	
	<!-- Payment Gatway Settings -->
	<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'Payment Gateway Settings' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
		
      	<div class="widget-holder">
			<form id="paymentGatwaySettingsForm" class="ajaxSettingForm" action="" method='post'>
	             <input type='hidden' name='action' value="save_settings" />
	             <input type='hidden' name='_success' value="Your Payment Gateway settings have been saved.">
				 
				 <input type="hidden" name="use_paypal_standard_checkout" value="" id="use_paypal_standard_checkout" />
				  
				 <input type="hidden" name="use_authorize_checkout" value="" id="use_authorize_checkout" />
	 
				 
				 
				 
			<div style="padding-left:10px;"> 
				<h4>Paypal Settings</h4>
				<div id="paypalstandard">
				<input type='checkbox' name='use_paypal_standard_checkout' id='use_paypal_standard_checkout'
                  <?php echo SimpleEcommCartSetting::getValue('use_paypal_standard_checkout') ? 'checked="checked"' : '' ?>
                /> Use Paypal Standard Checkout
					<div style="padding:5px;">
						<ul>
              			<li><label style="display: inline-block; width: 120px; text-align: right;" for='paypal_email'><?php _e( 'PayPal Email' , 'simpleecommcart' ); ?>:</label>
              			<input type='text' name='paypal_email' id='paypal_email' style='width: 375px;' value="<?php echo SimpleEcommCartSetting::getValue('paypal_email'); ?>" />
              </li>
			  </ul>
					</div>
				</div>
			 
			</div>
		<!--	<div style="padding-left:10px;"> 
				<h4>Authorize.Net Settings</h4>
				<input type='checkbox' name='use_authorize_checkout' id='use_authorize_checkout'
                  <?php echo SimpleEcommCartSetting::getValue('use_authorize_checkout') ? 'checked="checked"' : '' ?>  /> 
				 
					Use Authorize.Net Checkout
					<div style="padding:5px;">
						 <ul>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_url'><?php _e( 'Gateway' , 'simpleecommcart' ); ?>:</label>
                <select name="auth_url" id="auth_url">
                  <option value="https://secure.authorize.net/gateway/transact.dll">Authorize.net</option>
                  <option value="https://test.authorize.net/gateway/transact.dll">Authorize.net Test</option> 
                </select>
                
              </li>
              
              <li id="emulation_url_item">
                <label style="display: inline-block; width: 120px; text-align: right;" for='emulation_url'><?php _e( 'Emulation URL' , 'simpleecommcart' ); ?>:</label>
                <input type='text' name='auth_url_other' id='auth_url_other' style='width: 375px;' value="<?php echo SimpleEcommCartSetting::getValue('auth_url_other'); ?>" />
               
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_username'><?php _e( 'API Login ID' , 'simpleecommcart' ); ?>:</label>
              <input type='text' name='auth_username' id='auth_username' style='width: 375px;' value="<?php echo SimpleEcommCartSetting::getValue('auth_username'); ?>" />
             
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='auth_trans_key'><?php _e( 'Transaction key' , 'simpleecommcart' ); ?>:</label>
              <input type='text' name='auth_trans_key' id='auth_trans_key' style='width: 375px;' 
                value="<?php echo SimpleEcommCartSetting::getValue('auth_trans_key'); ?>" />
              </li>
              
              <li><label style="display: inline-block; width: 120px; text-align: right;" for=""><?php _e( 'Accept Cards' , 'simpleecommcart' ); ?>:</label>
              <input type="checkbox" name="auth_card_types[]" value="mastercard" style='width: auto;' 
                <?php echo in_array('mastercard', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>Mastercard</label>
              <input type="checkbox" name="auth_card_types[]" value="visa" style='width: auto;'
                <?php echo in_array('visa', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>Visa</label>
              <input type="checkbox" name="auth_card_types[]" value="amex" style='width: auto;'
                <?php echo in_array('amex', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>American Express</label>
              <input type="checkbox" name="auth_card_types[]" value="discover" style='width: auto;'
                <?php echo in_array('discover', $cardTypes) ? 'checked="checked"' : '' ?>><label style='width: auto; padding-left: 5px;'>Discover</label>
              </li>

              <li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label> 
              </li>
            </ul>
					</div> 
			</div>-->
			  
			<div style="padding:10px;">
				<input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" />
			</div>
			</form>
		</div>
	</div>
	 
	<!-- Product Categories -->
	<div class="widgets-holder-wrap <?php echo ($_GET['task'] == 'editProductCategory')?'':'closed' ?>">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'Product Categories' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
			<div style="padding-left:10px;">
			<h4>Add/Edit Category </h4>
		 	<form action="" method='post'>
            	<input type='hidden' name='simpleecommcart-action' value="save product category" />
			    <input type="hidden" name="product_category[id]" value="<?php echo $product_category->id ?>" />
				<table>
					<tr>
						<td>
							<?php _e( 'Category Name' , 'simpleecommcart' ); ?>
						</td>
						<td>
							<input type='text' name="product_category[name]" id="product_category_name" value='<?php echo $product_category->name ?>' />
						</td>
					</tr>
					<tr>
						<td>
							<?php _e( 'Description' , 'simpleecommcart' ); ?>
						</td>
						<td>  
							<textarea name="product_category[description]" id="product_category_description"  ><?php echo $product_category->description ?></textarea>
						</td>
					</tr>
					<tr>
						<td colspan="2">
						  <input type='submit' name='submit' class="button-primary"  value="Save Category" />
						</td>
					</tr>
				</table>
          	</form>
		</div>
		<div style="padding-left:10px;">
			<?php $product_categories = $product_category->getModels(); ?>
			<?php if(count($product_categories)): ?>
			 <h4>Category List </h4>
			 <table class="widefat" style='width: 500px; margin-bottom: 30px;'>
          		<thead>
          	<tr>
          		<th><?php _e( 'Category ID' , 'simpleecommcart' ); ?></th>
          		<th><?php _e( 'Category' , 'simpleecommcart' ); ?></th>
          		<th><?php _e( 'Description' , 'simpleecommcart' ); ?></th>
          		<th><?php _e( 'Actions' , 'simpleecommcart' ); ?></th>
          	</tr>
          </thead>
         		<tbody>
            <?php foreach($product_categories as $category): ?> 
             <tr>
               <td><?php echo  $category->id ?> </td>
			   <td><?php echo  $category->name ?> </td>
			   <td><?php echo  $category->description ?> </td>
               <td> 
				   <a href='?page=simpleecommcart-settings&task=editProductCategory&id=<?php echo $category->id ?>'>Edit</a> | 
       <a class='delete' href='?page=simpleecommcart-settings&task=deleteProductCategory&id=<?php echo $category->id ?>'>Delete</a>
               </td>
             </tr>
            <?php endforeach; ?>
          </tbody>
          	 </table>
          <?php endif; ?>
		
      </div>
	  	</div>
	</div>
	
	<!-- Store and Page Settings -->
	<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'Store and Page Settings' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
		<div style="padding-left:5px;">
			<form id="storeSettingsorm" class="ajaxSettingForm" action="" method='post'>
		    	<input type='hidden' name='action' value="save_settings" />
		        <input type='hidden' name='_success' value="Your store and page settings have been saved.">
					<h4>Store Page<img title="Store page where all your products are displayed under categories." src=" <?php echo INFO_ICON ?>"/></h4>
		            <ul> 
		              <li>
		                <label style="display: inline-block; width: 120px; text-align: right;">Main store page:</label> 
						<select id="main_store_page" name="main_store_page">
		                  <?php 
						    global $wpdb;
							$post_table = $wpdb->base_prefix . "posts"; 
							$pages = $wpdb->get_results("SELECT * from $post_table where post_type='page'");
							foreach($pages as $page)
							{
							    if(SimpleEcommCartSetting::getValue('main_store_page') == NULL && $page->post_title=='Store')
								{
									echo '<option value="'.$page->ID.'" selected="selected">'.$page->post_title.'</option>';                      
								}
								else if(SimpleEcommCartSetting::getValue('main_store_page') == $page->ID)
								{
									echo '<option value="'.$page->ID.'" selected="selected">'.$page->post_title.'</option>';   
								}
								else
								{
									echo '<option value="'.$page->ID.'">'.$page->post_title.'</option>';
								} 
							}
						?>
						</select> 
						<img title="The page will be used as store page. By default plugin creates a store page. If  a different page is selected then add this shortcode to that page: [simpleecommcart_store_home]" src=" <?php echo INFO_ICON ?>"/>
		              </li>
					   <li>
		                <label style="display: inline-block; width: 120px; text-align: right;">Display Products:</label> 
						<select id="display_products" name="display_products" style="width:100px;">
		                   <option  value="grid" <?php echo SimpleEcommCartSetting::getValue('display_products')=='grid'?'selected="selected"':''  ?> >Grid</option>
						    <option  value="list" <?php echo SimpleEcommCartSetting::getValue('display_products')=='list'?'selected="selected"':''  ?> >List</option>
						</select> 
						<img title="Products in the store page can be displayed either list view or grid view." src=" <?php echo INFO_ICON ?>"/>
		              </li>
					</ul>

					<!--<h4>Checkout Page</h4>
		            <ul> 
		              <li>
		                <label style="display: inline-block; width: 120px; text-align: right;">Checkout page:</label> 
						<select id="checkout_page" name="checkout_page">
		                  <?php 
						    global $wpdb;
							$post_table = $wpdb->base_prefix . "posts"; 
							$pages = $wpdb->get_results("SELECT * from $post_table where post_type='page'");
							foreach($pages as $page)
							{
							    if(SimpleEcommCartSetting::getValue('checkout_page') == NULL && $page->post_title=='Checkout')
								{
									echo '<option value="'.$page->ID.'" selected="selected">'.$page->post_title.'</option>';                      
								}
								else if(SimpleEcommCartSetting::getValue('checkout_page') == $page->ID)
								{
									echo '<option value="'.$page->ID.'" selected="selected">'.$page->post_title.'</option>';   
								}
								else
								{
									echo '<option value="'.$page->ID.'">'.$page->post_title.'</option>';
								} 
							}
						?>
						</select> 
		              </li> 
					</ul>-->
					
					<h4>Landing Page/'Thank you' Page <img title="Landing page where customers get redirected after a successful purchase." src=" <?php echo INFO_ICON ?>"/></h4>
		            <ul> 
		              <li>
		                <label style="display: inline-block; width: 120px; text-align: right;">Landing page:</label> 
						<select id="landing_page" name="landing_page">
		                  <?php 
						    global $wpdb;
							$post_table = $wpdb->base_prefix . "posts"; 
							$pages = $wpdb->get_results("SELECT * from $post_table where post_type='page'");
							foreach($pages as $page)
							{
							    if(SimpleEcommCartSetting::getValue('landing_page') == $page->ID)
								{
									echo '<option value="'.$page->ID.'" selected="selected">'.$page->post_title.'</option>';   
								}
								else
								{
									echo '<option value="'.$page->ID.'">'.$page->post_title.'</option>';
								} 
							}
						?>
						</select> 
						<img title="The page will be used as Landing page." src=" <?php echo INFO_ICON ?>"/>
		              </li> 
					</ul>
					<h4>Terms and Condition</h4>
		            <ul> 
					 <li>
		                <label style="display: inline-block; width: 120px; text-align: right;">Terms & Conditions:</label> 
						<select id="terms_and_condition" name="terms_and_condition" style="width:100px;">
		                   <option  value="yes" <?php echo SimpleEcommCartSetting::getValue('terms_and_condition')=='yes'?'selected="selected"':''  ?> >Yes</option>
						    <option  value="no" <?php echo SimpleEcommCartSetting::getValue('terms_and_condition')=='no'?'selected="selected"':''  ?> >No</option>
						</select> 
						<img title="Select “NO” if you don’t wish to provide Terms and Conditions. If Selected “Yes” customer will be able to view Terms and conditions link on the cart page before payment." src=" <?php echo INFO_ICON ?>"/>
		              </li>
		              <li>
		                <label style="display: inline-block; width: 120px; text-align: right;">Terms & Conditions Page:</label> 
						<select id="terms_and_condition_page" name="terms_and_condition_page">
		                  <?php 
						    global $wpdb;
							$post_table = $wpdb->base_prefix . "posts"; 
							$pages = $wpdb->get_results("SELECT * from $post_table where post_type='page'");
							foreach($pages as $page)
							{
							   if(SimpleEcommCartSetting::getValue('terms_and_condition_page') == $page->ID)
								{
									echo '<option value="'.$page->ID.'" selected="selected">'.$page->post_title.'</option>';   
								}
								else
								{
									echo '<option value="'.$page->ID.'">'.$page->post_title.'</option>';
								} 
							}
						?>
						</select> 
						<img title="This page will be used as Terms and Condition page. " src=" <?php echo INFO_ICON ?>"/>
		              </li>
					  
					</ul>

					<ul>
					 <li>
		                <label style="display: inline-block; width: 120px; text-align: right;" >&nbsp;</label>
		                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" />
		              </li>
					</ul>
				</form>
			</div>
	  	</div>
	</div>
	
	<!-- Email & Text Notification Settings -->
	<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'Email Settings' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
			<form id="emailReceiptForm" class="ajaxSettingForm" action="" method='post'>
            <input type='hidden' name='action' value="save_settings" />
            <input type='hidden' name='_success' value="The email & text notification settings have been saved.">
            <ul>
 				<li><label style="display: inline-block; width: 120px; text-align: right;" for='email_from_address'><?php _e( 'Email Address' , 'simpleecommcart' ); ?>:</label>
              <input type='text' name='email_from_address' id='email_from_address' style='width: 375px;' 
              value="<?php echo SimpleEcommCartSetting::getValue('email_from_address'); ?>" />
              
              </li>
              <li><label style="display: inline-block; width: 120px; text-align: right;" for='email_from_name'><?php _e( 'Email From' , 'simpleecommcart' ); ?>:</label>
              <input type='text' name='email_from_name' id='email_from_name' style='width: 375px;' 
              value="<?php echo SimpleEcommCartSetting::getValue('email_from_name', true); ?>" />
              </li> 
            </ul>
			
			<table>
				<tr>
				<td colspan="2">
						<h4><input type="checkbox" name="email_sent_on_purchase" <?php echo (SimpleEcommCartSetting::getValue('email_sent_on_purchase')=='on')? 'checked="checked"':''?>/>Email Sent on Purchase</h4>
				</td>
			</tr>				
				<tr>
				<td>
				 <label style="display: inline-block; width: 120px; text-align: right;" for='email_sent_on_purchase_subject'><?php _e( 'Email Subject' , 'simpleecommcart' ); ?>:</label>
				</td>
				<td>
			<input type='text' name='email_sent_on_purchase_subject' id='email_sent_on_purchase_subject' value="<?php echo SimpleEcommCartSetting::getValue('email_sent_on_purchase_subject'); ?>" /> 			</td>
			</tr>						
				<tr>
			<td><label style="display: inline-block; width: 120px; text-align: right;" for='email_sent_on_purchase_body'><?php _e( 'Email Body' , 'simpleecommcart' ); ?>:</label>
			</td>
			
			<td>
			   <textarea  style="text-align:left;" rows="5" cols="100"    name='email_sent_on_purchase_body' id='email_sent_on_purchase_body'><?php echo SimpleEcommCartSetting::getValue('email_sent_on_purchase_body', true); ?></textarea>
			</td>
			</tr>									
			</table> 
			 
			<!--<table>
				<tr>
				<td colspan="2">
					<h4> Email Sent when Order Pending</h4>
					<h3>This is only available in Simple eCommerce Premium version.</h3>
				</td>
			</tr>				
				 								
			</table> 
			
			<table>
				<tr>
					<td colspan="2">
							<h4> Email Sent when Shipped</h4>
							<h3>This is only available in Simple eCommerce Premium version.</h3>
					</td>
				</tr> 							
			</table> 
			
			<table>
				<tr>
				<td colspan="2">
						<h4> Email Sent on refund</h4>
						<h3>This is only available in Simple eCommerce Premium version.</h3>
				</td>
			</tr>				
			 								
			</table>
					 -->
			<table>
				<tr>
				<td>
						<h4><input type="checkbox" name="email_signature" <?php echo (SimpleEcommCartSetting::getValue('email_signature')=='on')? 'checked="checked"':''?>/>Email Signature</h4>
				</td>
				<td>
					<input type='text' name='email_signature_text' id='email_signature_text' value="<?php echo SimpleEcommCartSetting::getValue('email_signature_text'); ?>" /> 
				</td>
			</tr>	 		
			</table> 
					 
			<ul>    
				<li><label style="display: inline-block; width: 120px; text-align: right;" for='submit'>&nbsp;</label>	
				<input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" /></li>
			 </ul>
          </form>
	  	</div>
	</div>

<!-- Amazon S3 Settings -->
<!--<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'Amazon S3 Settings' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
			 <h2 style="font-weight:normal;">This feature is available in Simple eCommerce Premium Version</h2>
        
	  	</div>
	</div>-->

<!-- Webservice for iPhone Settings  -->
<!--<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'Webservice for iPhone Settings' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
			 <h2 style="font-weight:normal;">This feature is available in Simple eCommerce Premium Version</h2>
        
	  	</div>
	</div>

	-->
	
	<!-- Testing and Debugging -->
	<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'Testing and Debugging' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
			<div>
		          <form id="testtingAndDebuggingForm" class="ajaxSettingForm" action="" method='post'>
		            <input type='hidden' name='action' value="save_settings" />
		            <input type='hidden' name='_success' value="Your testing and debugging have been saved.">
		            <ul> 
		              <li>
		                <label style="display: inline-block; width: 120px; text-align: right;">PayPal Test Mode On:</label>
						<select id="paypal_sandbox" name="paypal_sandbox">
							<option value="1" <?php echo ((SimpleEcommCartSetting::getValue('paypal_sandbox')=='1')||(SimpleEcommCartSetting::getValue('paypal_sandbox')==NULL))?'selected="selected"':''  ?>>Yes</option>
							<option value="0" <?php echo (SimpleEcommCartSetting::getValue('paypal_sandbox')=='0')?'selected="selected"':''  ?>>No</option> 
						</select> 
						 <img title="Select “Yes” when testing with Paypal sandbox." src=" <?php echo INFO_ICON ?>"/>
		              </li> 
					  <li>
		                <label style="display: inline-block; width: 120px; text-align: right;">Enable logging:</label>
						<select id="enable_logging" name="enable_logging">
							<option value="1" <?php echo ((SimpleEcommCartSetting::getValue('enable_logging')=='1')||(SimpleEcommCartSetting::getValue('enable_logging')==NULL))?'selected="selected"':''  ?>>Yes</option>
							<option value="0" <?php echo (SimpleEcommCartSetting::getValue('enable_logging')=='0')?'selected="selected"':''  ?>>No</option> 
						</select> 
						<img title="If selected yes a log file called log.txt will be created." src=" <?php echo INFO_ICON ?>"/>
		              </li> 
					<li>
		                <label style="display: inline-block; width: 120px; text-align: right;">Delete database when uninstalling:</label>
						<select id="uninstall_db" name="uninstall_db">
							<option value="1" <?php echo ((SimpleEcommCartSetting::getValue('uninstall_db')=='1')||(SimpleEcommCartSetting::getValue('uninstall_db')==NULL))?'selected="selected"':''  ?>>Yes</option>
							<option value="0" <?php echo (SimpleEcommCartSetting::getValue('uninstall_db')=='0')?'selected="selected"':''  ?>>No</option> 
						</select> 
						   <img title="Select “Yes” when removing the plugin with entire database." src=" <?php echo INFO_ICON ?>"/>
		              </li> 
		              <li>
		                <label style="display: inline-block; width: 120px; text-align: right;" >&nbsp;</label>
		                <input type='submit' name='submit' class="button-primary" style='width: 60px;' value="Save" />
		              </li>
		
		            </ul>
		          </form>
        </div>
	  	</div>
	</div>
<!--<h3>3rd Party Integration</h3>-->
<!-- AWeber Settings   -->
<!--<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'AWeber Settings' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
			 <h2 style="font-weight:normal;">This feature is available in Simple eCommerce Premium Version</h2>
        
	  	</div>
	</div>-->
	
	<!-- MailChimp Settings    -->
<!--<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'MailChimp Settings' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
			 <h2 style="font-weight:normal;">This feature is available in Simple eCommerce Premium Version</h2>
        
	  	</div>
	</div>-->
	
	<!-- GetResponse Settings    -->
<!--<div class="widgets-holder-wrap closed">
    	<div class="sidebar-name">
        	<div class="sidebar-name-arrow"><br/></div>
        		<h3><?php _e( 'GetResponse Settings' , 'simpleecommcart' ); ?> <span><img class="ajax-feedback" alt="" title="" src="images/wpspin_light.gif"/></span></h3>
      	</div>
      	<div class="widget-holder">
			 <h2 style="font-weight:normal;">This feature is available in Simple eCommerce Premium Version</h2>
        
	  	</div>
	</div>-->
	
  </div>
</div>
</div>
<div style="float:right;width:18%;max-width:18%">
	<?php
	 	echo SimpleEcommCartCommon::getView('admin/more.php',NULL);
	?>
</div>
<div style="clear:both;"/>
 

<script type='text/javascript'>
  $jq = jQuery.noConflict();
  
  $jq(document).ready(function() {
    $jq(".multiselect").multiselect({sortable: true});
    
    $jq('.sidebar-name').click(function() {
     $jq(this.parentNode).toggleClass("closed");
    });
    
    $jq("#continue_shopping").val("<?php echo SimpleEcommCartSetting::getValue('continue_shopping'); ?>");

    $jq('#international_sales_yes').click(function() {
     $jq('#eligible_countries_block').show();
    });

    $jq('#international_sales_no').click(function() {
     $jq('#eligible_countries_block').hide();
    });

    if($jq('#international_sales_no').attr('checked')) {
     $jq('#eligible_countries_block').hide();
    }
    
    $jq("#html_yes").change(function(){
      if($jq(this).attr('checked')){
        $jq("#html_editor").show();
      }
    })
    $jq("#html_no").change(function(){
      if($jq(this).attr('checked')){
        $jq("#html_editor").hide();
      }
    })
    
    $jq('#auth_url').change(function() {
      setGatewayDisplay();
    });
    
    <?php if($authUrl = SimpleEcommCartSetting::getValue('auth_url')): ?>
        $jq('#auth_url').val('<?php echo $authUrl; ?>').attr('selected', true);
    <?php endif; ?>
    
    setGatewayDisplay();
    function setGatewayDisplay() {
      if($jq('#auth_url').val() == 'other') {
        $jq('#emulation_url_item').css('display', 'inline');
      }
      else {
        $jq('#emulation_url_item').css('display', 'none');
      }
      
      if($jq('#auth_url :selected').text() == 'Authorize.net Test'){
        $jq("#authorizenetTestMessage").show();
      }
      else{
        $jq("#authorizenetTestMessage").hide();
      }
      
      if($jq('#auth_url :selected').text() == 'Authorize.net' || $jq('#auth_url :selected').text() == 'Authorize.net Test') {
        $jq('#authnet-image').css('display', 'block');
      }
      else {
        $jq('#authnet-image').css('display', 'none');
      }
    }
	generateCommaSeparatedCountryList();
    $jq('.targetmarketselect').change(function(){
		generateCommaSeparatedCountryList();
	});
	$jq('#selectAll').click(function(){
		$jq('.targetmarketselect').each(function(){
		  $jq(this).attr('checked','checked');
		});
		generateCommaSeparatedCountryList();
	});
	$jq('#selectNone').click(function(){
		$jq('.targetmarketselect').each(function(){
		  $jq(this).removeAttr('checked','checked');
		});
		generateCommaSeparatedCountryList();
	});
  });
  
  function generateCommaSeparatedCountryList()
  {
  	var str="";
  	$jq('.targetmarketselect').each(function(){
		if( $jq(this).attr('checked') =='checked')
		{
			var val = $jq(this).val();
			str+=","+val;
		} 
	});
	str = str.substring(1);
	//alert(str);
	$jq('#hdnCountries').val(str);
  }
  
</script>
