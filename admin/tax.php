<?php  
$rate = new SimpleEcommCartTaxRate();
if($_SERVER['REQUEST_METHOD'] == "POST") {
	if($_POST['simpleecommcart-action'] == 'saveTaxSettings') 
	{  
	  $tax_settings_forSave = serialize($_POST['tax_settings']);
      SimpleEcommCartSetting::setValue('tax_settings', $tax_settings_forSave);  
    }
}  
$tax_settings = unserialize(SimpleEcommCartSetting::getValue('tax_settings')); 
 
?>

<h2>Calculate Tax</h2> 
 
<div class='wrap' style="width:80%;max-width:80%;float:left;"> 
	<div id="widgets-left" style="margin-right: 5px;">
	    <div id="available-widgets">
			<div class="widgets-holder-wrap">
				<div class="sidebar-name"> 
	         		<h3><?php _e( 'Tax Settings' , 'simpleecommcart' ); ?></h3>
	        	</div>
	       		<div class="widget-holder">
					<form class='phorm' action="" method="post">
	<input type="hidden" name="simpleecommcart-action" value="saveTaxSettings" />		
	<h3></h3>
	<table>
	 
		<tr>
			<td>
				<input   value="1" type="hidden" name="tax_settings[option]"   />
			</td>
			<td>
				Flat Rate
			</td>
			<td>
			 <input id="txtFlatRate" type="text" style="width: 75px;" id="tax_settings_flat_rate" name="tax_settings[flat_rate]" value='<?php echo $tax_settings["flat_rate"] ?>' />%
			 <img title="Enter a value or leave blank for charge nothing." src=" <?php echo INFO_ICON ?>"/>
			</td>
		</tr>
		 
		
	</table>
	<br>
	<div id="dvTaxLogic" >
	<h3>Tax Logic</h3>
	<table>
		<tr>
			<td>
				<input id="rdApplyTaxOnTotShopAmount" value="1" type="radio" name="tax_settings[logic]" <?php echo ($tax_settings["logic"]  == '1')? 'checked="true"' : '' ?> />
			</td>
			 <td>
			 	Apply flat rate to all the products regardless their Product Specific Tax selected Yes or No
				 <img title="Flat tax rate will be applied on all products regardless the “apply tax” is selected “No” under individual products." src=" <?php echo INFO_ICON ?>"/>
			 </td>
		</tr>
		<tr>
			<td>
				<input id="rdApplyTaxOnProd" value="2" type="radio" name="tax_settings[logic]" <?php echo ($tax_settings["logic"]  == '2')? 'checked="true"' : '' ?> />
			</td>
			 <td>
			 	Apply flat rate only to the products that have Product Specific Tax selected Yes.
				 <img title="Flat tax rate will be applied only on the products that have “apply tax” selected “Yes”." src=" <?php echo INFO_ICON ?>"/>
			 </td>
		</tr>
		 
	</table>
	</div>
	<br>
	<input type='submit' name='submit' class="button-primary" style='width: 60px;' value='Save' />
	<br/><br/>
</form>
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
<!--<h2  style="font-weight:normal;">Individual Product Specific tax rates, Tax rates by Country and States, and other tax conditions such as Charge tax on shipping, Apply tax on Shipping or Billing address are available in Simple eCommerce Premium Version</h2>-->