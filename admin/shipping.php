<?php 
if($_SERVER['REQUEST_METHOD'] == "POST") {
  if($_POST['simpleecommcart-action'] == 'save flat rate options') {
  	$flat_rate_rate=$_POST["shipping_options_flat_rate"]; 
	SimpleEcommCartSetting::setValue('shipping_options_radio','1');
	SimpleEcommCartSetting::setValue('shipping_options_flat_rate_option', $flat_rate_rate["option"]);
	SimpleEcommCartSetting::setValue('shipping_options_flat_rate_option2_local', $flat_rate_rate["option2_local"]);
	SimpleEcommCartSetting::setValue('shipping_options_flat_rate_option2_international', $flat_rate_rate["option2_international"]);
	SimpleEcommCartSetting::setValue('shipping_options_flat_rate_option3_local', $flat_rate_rate["option3_local"]);
	SimpleEcommCartSetting::setValue('shipping_options_flat_rate_option3_international', $flat_rate_rate["option3_international"]); 
  } 
} 
?> 
<h2>Shipping</h2>


<div class='wrap'><div class='wrap' style="width:80%;max-width:80%;float:left;"> 
 
 <div id="widgets-left" style="margin-right: 5px;">
    <div id="available-widgets"> 
		<div class="widgets-holder-wrap">
			<div class="sidebar-name"> 
         		<h3><?php _e( 'Shipping Method' , 'simpleecommcart' ); ?></h3>
        	</div>
       		<div class="widget-holder"> 
				<div id="dvFlatRate" >
		<form action="" method="post">
	    <input type="hidden" name="simpleecommcart-action" value="save flat rate options" />
		<input type="hidden" value="1" name="shipping_options[radio]"/>
		<h3><?php _e( 'Flat Rate(Product Specific Shipping Rate)' , 'simpleecommcart' ); ?></h3>
		<table>
				<tr>
					<td>
						<input type="radio" value="1" name="shipping_options_flat_rate[option]" <?php echo (SimpleEcommCartSetting::getValue("shipping_options_flat_rate_option")  == '1' || SimpleEcommCartSetting::getValue("shipping_options_flat_rate_option")==NULL)? 'checked="true"' : '' ?>>Apply Product Specific Shipping Rates for Individual Products</input>
						 <img title="Calculate shipping individually for the products that have specific shipping configured (single and bundle rate under individual product)." src=" <?php echo INFO_ICON ?>"/>
					</td>
				</tr>
				<tr>
					<td>
						<input type="radio" value="2" name="shipping_options_flat_rate[option]" <?php echo (SimpleEcommCartSetting::getValue("shipping_options_flat_rate_option")  == '2')? 'checked="true"' : '' ?>>Apply a Flat Rate regardless the number of Items in the Cart</input>
						  <img title="Customers will be charged a fixed shipping price regardless the amount of items in the cart. " src=" <?php echo INFO_ICON ?>"/>
						<br/>
						<table>
							<tr>
								<td>Flat Rate amount local</td>
								<td><input  type="text" style="width: 75px;"  name="shipping_options_flat_rate[option2_local]" value='<?php echo SimpleEcommCartSetting::getValue("shipping_options_flat_rate_option2_local") ?>'/></td>
								<td>International</td>
								<td><input  type="text" style="width: 75px;" name="shipping_options_flat_rate[option2_international]"  value='<?php echo SimpleEcommCartSetting::getValue("shipping_options_flat_rate_option2_international") ?>'/></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td>
						<input type="radio" value="3" name="shipping_options_flat_rate[option]" <?php echo (SimpleEcommCartSetting::getValue("shipping_options_flat_rate_option")  == '3')? 'checked="true"' : '' ?>>Apply a Flat Rate for each Individual Product in the Cart</input>
						 <img title="Customer will be charged a fixed shipping price for each individual product in the cart (only the products that have shipping required selected “Yes”). Example: 1 pencil and a 1 laptop in the cart and the Flat rate amount local is $2. The total shipping price will be $2+$2=$4." src=" <?php echo INFO_ICON ?>"/>
						<br/>
						<table>
							<tr>
								<td>Flat Rate amount local</td>
								<td><input  type="text" style="width: 75px;"  name="shipping_options_flat_rate[option3_local]"  value='<?php echo SimpleEcommCartSetting::getValue("shipping_options_flat_rate_option3_local") ?>'/></td>
								<td>International</td>
								<td><input  type="text" style="width: 75px;" name="shipping_options_flat_rate[option3_international]"  value='<?php echo SimpleEcommCartSetting::getValue("shipping_options_flat_rate_option3_international") ?>'/></td>
							</tr>
							</table>
					</td>
				</tr>
			</table>
		<input type='submit' name='submit' class="button-primary" style='width: 80px;' value='Save' />
		</form>
		<br>
	</div> 
			</div>
		</div>
	</div>
</div>

  
	
</div>

<div style="float:right;width:18%;max-width:18%">
	<?php
	 	echo SimpleEcommCartCommon::getView('admin/more.php',NULL);
	?>
</div>
<div style="clear:both;"/>
<!--<h2  style="font-weight:normal;">Shipping discounts (Free shipping) and variations, Other shipping methods such as Weight base shipping rates, table rate shipping are available in Simple eCommerce Premium Version.</h2>-->

<script type='text/javascript'>
  $jq = jQuery.noConflict();
  
  $jq(document).ready(function() { 
  });
   
   
</script>