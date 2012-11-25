<?php
$promo = new SimpleEcommCartPromotion();
$errorMessage = false;
if($_SERVER['REQUEST_METHOD'] == "POST")
{ 
	if(  $_POST['simpleecommcart-action'] == 'save promotion')
 	{
	  	try {
		  if($_POST['promo']['redemption_limit'] =='' ) $_POST['promo']['redemption_limit'] = 999999;
		  $promo->setData($_POST['promo']);
		  $promo->save();
		  $promo->clear();
  		}
  		catch(SimpleEcommCartException $e) {
    		$errorCode = $e->getCode();
    	if($errorCode == 66103) 
		{
	      // Coupon save failed
	      $errors = $promo->getErrors();
	      $errorMessage = SimpleEcommCartCommon::showErrors($errors, "<p><b>" . __("The coupon could not be saved for the following reasons","simpleecommcart") . ":</b></p>");
	    } 
    	SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Coupon save failed ($errorCode): " . strip_tags($errorMessage));
  		}
	}
 	elseif($_POST['simpleecommcart-action'] == 'check uncheck use coupon') {
	  	if($_POST["use_coupons_on_checkout"]=='on')
		 	SimpleEcommCartSetting::setValue('use_coupons_on_checkout', 1);
		else 
			SimpleEcommCartSetting::setValue('use_coupons_on_checkout', 0);
  	}
}
elseif(isset($_GET['task']) && $_GET['task'] == 'edit' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = SimpleEcommCartCommon::getVal('id');
  $promo->load($id);
  if($promo->redemption_limit == '999999')$promo->redemption_limit='';
}
elseif(isset($_GET['task']) && $_GET['task'] == 'delete' && isset($_GET['id']) && $_GET['id'] > 0) {
  $id = SimpleEcommCartCommon::getVal('id');
  $promo->load($id);
  $promo->deleteMe();
  $promo->clear();
}
?>

<?php if($errorMessage): ?>
<div style="margin: 30px 50px 10px 5px;"><?php echo $errorMessage ?></div>
<?php endif; ?>

<h2>Coupons</h2>
<div class='wrap' style="width:80%;max-width:80%;float:left;"> 


<form action="" method="post">
	     	<input type="hidden" name="simpleecommcart-action" value="check uncheck use coupon" />
			<input id="use_coupons_on_checkout" name="use_coupons_on_checkout" type="checkbox"  onclick="submit();" <?php echo (SimpleEcommCartSetting::getValue('use_coupons_on_checkout') == '1')? 'checked="checked"' : '' ?>  /><span>Use Coupons on Checkout</span>
			<img title="Check the box if you are offering coupons or discounts so customers can apply them at Checkout." src=" <?php echo INFO_ICON ?>"/>
	 </form>


   <form action="" method='post'>
    <input type='hidden' name='simpleecommcart-action' value='save promotion' />
    <input type='hidden' name='promo[id]' value='<?php echo $promo->id ?>' />
    
    <div id="widgets-left" style="margin-right: 5px;">
    	<div id="available-widgets">
     		<div class="widgets-holder-wrap">
        		<div class="sidebar-name">
         		 <div class="sidebar-name-arrow"><br/></div>
         		 <h3><?php _e( 'Create a Coupon' , 'simpleecommcart' ); ?></h3>
       			</div>
        		<div class="widget-holder">
				 <ul>
				    <li>
				        <label class="long" for="promo-code"><?php _e( 'Coupon Code' , 'simpleecommcart' ); ?>:</label>
				        <input type='text' name='promo[code]' id='promo-code' style='width: 225px;' value='<?php echo $promo->code ?>' /><img title="Enter a unique coupon code" src=" <?php echo INFO_ICON ?>"/>
				      </li>
					<li>
				        <label class="long" for="promo-description"><?php _e( 'Description' , 'simpleecommcart' ); ?>:</label>
				        <input type='text' name='promo[description]' id='promo-code' style='width: 225px;' value='<?php echo $promo->description ?>' />
						<img title="Brief  description about the coupon or discount" src=" <?php echo INFO_ICON ?>"/>
				      </li>
				    <li>
					 <label class="long" for="promo-apply_for_all_products"><?php _e( 'Apply on any or all products' , 'simpleecommcart' ); ?>:</label>
               <input type="radio" value="1" id="apply_for_all_products_yes"  name="promo[apply_for_all_products]"  <?php echo ($promo->apply_for_all_products == NULL || $promo->apply_for_all_products == '1')? 'checked="true"' : '' ?>>Yes</input>
			     <input type="radio" value="0" id="apply_for_all_products_no"  name="promo[apply_for_all_products]"   <?php echo ($promo->apply_for_all_products == '0')? 'checked="true"' : '' ?>>No</input> 
						</li>
					<li id="liProductSelection">
					 
						<label class="long"  ><?php _e( 'Select Products' , 'simpleecommcart' ); ?>:</label> 
						<a id="productSelectButton" href="#" class="button-secondary">...</a>
						 <input id="promo_products" type='hidden' name='promo[products]' value='<?php echo $promo->products ?>' />
						 <img title="The coupon will be only applied on the product/products that are selected. " src=" <?php echo INFO_ICON ?>"/>
						 
					</li>
			      	<li>
        <label class="long" for="promo-type"><?php _e( 'Discount Type' , 'simpleecommcart' ); ?>:</label>
        <select name="promo[type]" id="promo-type">
          <option value="dollar" <?php if($promo->type == 'dollar') { echo 'selected'; } ?>><?php _e( 'Money Amount' , 'simpleecommcart' ); ?></option>
          <option value="percentage" <?php if($promo->type == 'percentage') { echo 'selected'; } ?>><?php _e( 'Percentage' , 'simpleecommcart' ); ?></option>
        </select>
		
		 <img title="Select a discount type that you want to use for the coupon" src=" <?php echo INFO_ICON ?>"/>
      </li>		 
      				<li>
        <label class="long" for="promo-amount"><?php _e( 'Discount Value' , 'simpleecommcart' ); ?>:<span id="dollarSign"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?></span></label>
        
        <input type="text" style="width: 75px;" name="promo[amount]" id="promo-amount" value="<?php echo $promo->amount ?>"> 
        <span id="percentSign">%</span>
		 <img title="Only put the value without the $ or % sign" src=" <?php echo INFO_ICON ?>"/>
      </li>
      				<!--<li>
        			<label class="long" for="promo-min_order"><?php _e( 'Minimum Amount' , 'simpleecommcart' ); ?>:</label>
       <input type="text" style="width: 75px;" id="promo-min_order" name="promo[min_order]" value="<?php echo $promo->minOrder ?>">
       
      </li>-->
					<li>
        <label class="long" for="promo-redemption_limit"><?php _e( 'Redemption Limit' , 'simpleecommcart' ); ?>:</label>
        <input type="text" style="width: 75px;" id="promo-redemption_limit" name="promo[redemption_limit]" value="<?php echo $promo->redemption_limit ?>">
	 <img title="Maximum number of time the coupon can be used. Leave it blank for unlimited use." src=" <?php echo INFO_ICON ?>"/>
      </li>
					<li>
		<?php
			$start_date_string='';
			if(SimpleEcommCartCommon::isDateValid($promo->start_date))
			{
				$date = date_create(''.$promo->start_date);
				$start_date_string = date_format($date, 'Y-m-d'); 
			}
		?>
        <label class="long" for="promo-start_date"><?php _e( 'Start Date(yyyy-mm-dd)' , 'simpleecommcart' ); ?>:</label>
		 
        <input type="text" style="width: 75px;" id="promo-start_date" name="promo[start_date]" value="<?php echo $start_date_string  ?>">
		<img title="Enter a date when the promotion should start. Leave it blank for immediate use. Date format yyyy-mm-dd." src=" <?php echo INFO_ICON ?>"/> 
       
      </li>
					<li>
					<?php
			$expire_date_string='';
			if(SimpleEcommCartCommon::isDateValid($promo->expiry_date))
			{
				$date = date_create(''.$promo->expiry_date);
				$expire_date_string = date_format($date, 'Y-m-d'); 
			}
		?>
        <label class="long" for="promo-expiry_date"><?php _e( 'Expiry Date(yyyy-mm-dd)' , 'simpleecommcart' ); ?>:</label>
        <input type="text" style="width: 75px;" id="promo-expiry_date" name="promo[expiry_date]" value="<?php echo $expire_date_string ?>">
		 <img title="Enter a date when the promotion should finish. Leave it blank for no expiry. Date format yyyy-mm-dd." src=" <?php echo INFO_ICON ?>"/>
       
      </li>
 					<li>
					 <label class="long" for="promo-active"><?php _e( 'Active' , 'simpleecommcart' ); ?>:</label>
               <input type="radio" value="1"   name="promo[active]"  <?php echo ($promo->active==NULL || $promo->active == '1')? 'checked="true"' : '' ?>>Yes</input>
			     <input type="radio" value="0" name="promo[active]"   <?php echo ($promo->active == '0')? 'checked="true"' : '' ?>>No</input>  
					</li>
					<li>
<h4>Conditions (Optional)</h4>
<table>
	<tr>
		<td>
			If
		</td>
		<td>
			<input id="promo_optional_option1"  type="hidden" value="<?php echo $promo->optional_option1 ?>"/>
			  <select name="promo[optional_option1]" id="optional_option1">
			  	 <option value="1" <?php if($promo->optional_option1 == '1') { echo 'selected'; } ?>><?php _e( 'Individual Product Quantity' , 'simpleecommcart' ); ?></option>
				  <option value="2" <?php if($promo->optional_option1 == '2') { echo 'selected'; } ?>><?php _e( 'Total Product Quantity' , 'simpleecommcart' ); ?></option>
				   <option value="3" <?php if($promo->optional_option1 == '3') { echo 'selected'; } ?>><?php _e( 'Sub Total Cart Amount' , 'simpleecommcart' ); ?></option>
			  </select>
		</td>
		<td>
			is
		</td>
		<td>
			 <select name="promo[optional_option1_condition]" id="optional_option1_condition">
			  	 <option value="1" <?php if($promo->optional_option1_condition == '1') { echo 'selected'; } ?>><?php _e( 'equal to' , 'simpleecommcart' ); ?></option>
				  <option value="2" <?php if($promo->optional_option1_condition == '2') { echo 'selected'; } ?>><?php _e( 'greater than' , 'simpleecommcart' ); ?></option>
				   <option value="3" <?php if($promo->optional_option1_condition == '3') { echo 'selected'; } ?>><?php _e( 'less than' , 'simpleecommcart' ); ?></option>
			  </select>
		</td>
		<td>
			 <input type='text' name='promo[optional_option1_value]' id='promo-optional_option1_value' style='width: 50px;' value='<?php echo ($promo->optional_option1_value =='0')?'':$promo->optional_option1_value ?>' />
		</td>
		<td>
		<img title="Additional coupon conditions (optional)." src=" <?php echo INFO_ICON ?>"/>
		</td>
	</tr>
</table>
</li>
      				<li>
        <label class="med">&nbsp;</label>
        <?php if($promo->id > 0): ?>
          <a href='?page=simpleecommcart-promotions' class='button-secondary linkButton' style=""><?php _e( 'Cancel' , 'simpleecommcart' ); ?></a>
        <?php endif; ?>
        <input type='submit' name='submit' class="button-primary" style='width: 80px;' value='Save Coupon' />
      </li>
   				 </ul>
          		</div>
          </div>
        </div> 
   </div>
    
  </form>
  
  <?php
  $promos = $promo->getModels();
  if(count($promos)):
  ?> 
  <h3 style="margin-left: 5px;"><?php _e( 'Manage Coupons' , 'simpleecommcart' ); ?></h3>
  <table class="widefat" style="margin-top: 5px;"> 
  <thead>
  	<tr>
  		<th><?php _e( 'Code' , 'simpleecommcart' ); ?></th>
		<th><?php _e( 'Description' , 'simpleecommcart' ); ?></th>
  		<!--<th><?php _e( 'Products' , 'simpleecommcart' ); ?></th>-->
		<th><?php _e( 'Discount Value' , 'simpleecommcart' ); ?></th>
  		<th><?php _e( 'Redemption Limit' , 'simpleecommcart' ); ?></th>
		<th><?php _e( 'Used' , 'simpleecommcart' ); ?></th>
		<th><?php _e( 'Start' , 'simpleecommcart' ); ?></th>
		<th><?php _e( 'Expire' , 'simpleecommcart' ); ?></th> 
		<th><?php _e( 'Active' , 'simpleecommcart' ); ?></th>
  		<th><?php _e( 'Actions' , 'simpleecommcart' ); ?></th>
  	</tr>
  </thead>
  <tfoot>
     
  </tfoot>
  <tbody>
    <?php foreach($promos as $p): ?>
     <tr>
       <td><?php echo $p->code ?></td>
	   <td><?php echo $p->description ?></td>
	   <!--<td><?php echo ($p->apply_for_all_products=='1')?'All': $p->products ?></td>-->
       <td><?php 
	   if($p->type=='dollar')
	   {
	   		echo SIMPLEECOMMCART_CURRENCY_SYMBOL.$p->amount;
	   }
	   else
	   {
	   	echo  $p->amount.'%';
	   }
	   
	   ?></td>
       <td>
	   <?php
	   	if($p->redemption_limit == '999999')
			$p->redemption_limit='-';
	   ?>
	   <?php echo  $p->redemption_limit ?></td>
	   <td><?php echo $p->redemption_count ?></td>
	   <td>
	    <?php
			$start_date_string='';
			if(SimpleEcommCartCommon::isDateValid($p->start_date))
			{
				$date = date_create(''.$p->start_date);
				$start_date_string = date_format($date, 'Y-m-d'); 
			}
		?>
	   <?php echo $start_date_string ?>
	   
	   </td>
	   <td>
	   <?php
			$expire_date_string='';
			if(SimpleEcommCartCommon::isDateValid($p->expiry_date))
			{
				$date = date_create(''.$p->expiry_date);
				$expire_date_string = date_format($date, 'Y-m-d'); 
			}
		?>
	   <?php echo $expire_date_string ?></td>
	  
	   <td><?php echo ($p->active==1)?'Yes':'No' ?></td>
       <td>
         <a href='?page=simpleecommcart-promotions&task=edit&id=<?php echo $p->id ?>'><?php _e( 'Edit' , 'simpleecommcart' ); ?></a> | 
         <a class='delete' href='?page=simpleecommcart-promotions&task=delete&id=<?php echo $p->id ?>'><?php _e( 'Delete' , 'simpleecommcart' ); ?></a>
       </td>
     </tr>
    <?php endforeach; ?>
  </tbody>
  </table>
  <?php endif; ?>
</div>
<div style="float:right;width:18%;max-width:18%">
	<?php
	 	echo SimpleEcommCartCommon::getView('admin/more.php',NULL);
	?>
</div>
<div style="clear:both;"/>

<script type="text/javascript" charset="utf-8">
/* <![CDATA[ */

  $jq = jQuery.noConflict();

  $jq('document').ready(function() {
    setPromoSign();
	initializePromotionsUI();
	promotionInitCustom();
  });
  
  $jq('.delete').click(function() {
    return confirm('Are you sure you want to delete this item?');
  });

  $jq('#promo-type').change(function () {
    setPromoSign();
  }); 

  function setPromoSign() {
    var v = $jq('#promo-type').val();
    if(v == 'percentage') {
      $jq('#dollarSign').hide();
      $jq('#percentSign').show();
    }
    else {
      $jq('#dollarSign').show();
      $jq('#percentSign').hide();
    }
  }
  function initializePromotionsUI()
  { 
  	 jQuery( "#dialog:ui-dialog" ).dialog( "destroy" );
	
		jQuery( "#product_selection_dialog" ).dialog({
			autoOpen: false,
			height: 200,
			height: 400,
			modal: true, 
			buttons: {
				"Save": function() { 
					jQuery("#promo_products").val("");
					var idsString="";
					var ids=[];
					var i=0;
					jQuery(".prduct_check").each(function(){
						if(jQuery(this).attr("checked")=="checked")
						{
							ids[i]=jQuery(this).val();
							i++;
						} 
					}); 
					for(var j=0;j<i;j++)
					{
						 idsString+=ids[j];
						 if(j<i-1)idsString+=",";
					}
					
					jQuery("#promo_products").val(idsString);
					jQuery( this ).dialog( "close" );
				},
				Cancel: function() {
					jQuery( this ).dialog( "close" );
				}
			}
		});
    jQuery("#productSelectButton").click(function(event) {
		jQuery(".prduct_check").each(function(){
			if(jQuery(this).attr("checked")=="checked")
			{
				jQuery(this).removeAttr("checked");
			} 
		}); 
		
		var _ids=jQuery("#promo_products").val().split(",");
		for(var i=0;i<_ids.length;i++)
		{
			if(_ids[i]!="")
			{
				jQuery(".prduct_check").each(function(){
					if(jQuery(this).val()==_ids[i])
					{
						jQuery(this).attr("checked","checked");
					} 
				}); 
			}
		}
		
        jQuery("#product_selection_dialog").dialog("open");
    });

  }
  function promotionInitCustom()
  {
  	showHideProductSelection();
	showHideProductQuantity();
  	jQuery("#apply_for_all_products_yes").click(function(){ 
		showHideProductSelection();
		showHideProductQuantity();
	});
	jQuery("#apply_for_all_products_no").click(function(){
		showHideProductSelection();
		showHideProductQuantity();
	}); 
  	
	jQuery("#promo-amount").change(function(){
		checkAmount();
	});
	jQuery("#promo-type").change(function(){
		checkAmount();
	});
  }
  function checkAmount()
  {
  	if(jQuery("#promo-type").val()=="percentage")
	{
			var amount=parseFloat(jQuery("#promo-amount").val());
			if(amount>100)
			{
				alert('More Than 100% is not allowed');
				jQuery("#promo-amount").val("100");
			}
	}
  }
  function showHideProductSelection()
  {
  	if(jQuery("#apply_for_all_products_yes").attr("checked")=="checked")
	{
		jQuery("#liProductSelection").hide();
	}
  	else if(jQuery("#apply_for_all_products_no").attr("checked")=="checked")
	{
		jQuery("#liProductSelection").show();
	}
	else
	{
		jQuery("#liProductSelection").hide();
	}
  }
  function showHideProductQuantity()
  {
  	if(jQuery("#apply_for_all_products_yes").attr("checked")=="checked")
	{ 
		jQuery("#optional_option1 option[value='1']").remove(); 
	} 
	else
	{
		jQuery("#optional_option1 option[value='1']").remove(); 
		jQuery("#optional_option1 option[value='2']").remove(); 
		jQuery("#optional_option1 option[value='3']").remove(); 
		
		jQuery("#optional_option1").append('<option value="1">Individual Product Quantity</option>');
		jQuery("#optional_option1").append('<option value="2">Total Product Quantity</option>');
		jQuery("#optional_option1").append('<option value="3">Sub Total Cart Amount</option>');
		
	}
	
	jQuery("#optional_option1").val(jQuery("#promo_optional_option1").val());
  }
/* ]]> */
</script>

<div id="product_selection_dialog" title="Select Products">
	<table class="widefat" style="margin: 0px; width: auto;">
  		<thead> 
  			<tr>
  	 		 	<th><?php _e( 'ID' , 'simpleecommcart' ); ?></th>
  	  			<th><?php _e( 'Product Name' , 'simpleecommcart' ); ?></th>
				<th></th>  
  			</tr>
  		</thead>
  		<tfoot> 
  		</tfoot>
  		<tbody>
		  	<?php
				$product = new SimpleEcommCartProduct();
		
		  		$products = $product->getNonSubscriptionProducts();
		 
				foreach($products as $p)
				{
					echo('<tr>');
					echo('<td>'.$p->id.'</td><td>'.$p->name.'</td>');
					echo('<td><input class="prduct_check" type="checkbox" value="'.$p->id.'"/></td>');
					echo('</tr>'); 
				}
			?>
  		</tbody>
  	</table>
</div>
