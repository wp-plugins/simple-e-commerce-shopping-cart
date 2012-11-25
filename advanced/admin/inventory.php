<?php if(SIMPLEECOMMCART_PRO && $track != 1): ?>
  <div class="SimpleEcommCartError" style="width: 500px;">
    <h1><?php _e( 'Inventory Tracking Is Not Active' , 'simpleecommcart' ); ?></h1>
    <p><?php _e( 'You must enable inventory tracking in in the <a href="'.$wpurl.'/wp-admin/admin.php?page=simpleecommcart-settings">settings panel</a>.' , 'simpleecommcart' ); ?></p>
  </div>
<?php endif; ?>

<p style="width: 400px;"><?php _e( 'Track your inventory by selecting the checkbox next to the products you want to track and enter 
  the quantity you have in stock in the text field. If you are tracking inventory for a product, SimpleEcommCart will check the 
  inventory levels every time a product is added to the shopping cart, every time the quantity of a product in the shopping 
  cart is changed, and on the checkout page. Inventory is reduced after a successful sale, not when a product is added to 
  the shopping cart.' , 'simpleecommcart' ); ?></p>

<?php if(count($products)): ?>
	<script type="text/javascript">
	  var $jq = jQuery.noConflict();
	  $jq(document).ready(function() {
	    $jq(".SimpleEcommCartBoxSelectAll").click(function(){
				 $jq(".SimpleEcommCartInventoryCheckbox").attr('checked',true);
			})
		
			$jq(".SimpleEcommCartBoxSelectNone").click(function(){
				 $jq(".SimpleEcommCartInventoryCheckbox").attr('checked',false);
			})
		
			$jq(".SimpleEcommCartBoxSelectQty").click(function(){
				 $jq(".SimpleEcommCartInventoryCheckbox").each(function(){
						if($jq(this).parent().parent().find(".SimpleEcommCartInventoryQty").val()>0){
							$jq(this).attr("checked",true);
						}
				 })
			})
				
	  });
  </script>
	
<div class="SimpleEcommCartSelectAll">
	 <span class="SimpleEcommCartBoxSelectAll"><?php _e( 'Select All' , 'simpleecommcart' ); ?></span> | 
	 <span class="SimpleEcommCartBoxSelectNone"><?php _e( 'Select None' , 'simpleecommcart' ); ?></span> |
   <span class="SimpleEcommCartBoxSelectQty"><?php _e( 'Select With Quantity' , 'simpleecommcart' ); ?></span>
</div>
<form action="" method="post">
  <input type="hidden" name="simpleecommcart-task" value="save-inventory-form" id="simpleecommcart-task" />
  <table class="widefat" style="margin: 0px; width: auto;">
  <thead>
  	<tr>
  	  <th><?php _e( 'Track' , 'simpleecommcart' ); ?></th>
  	  <th><?php _e( 'Product Name' , 'simpleecommcart' ); ?></th>
  		<th><?php _e( 'Product Variation' , 'simpleecommcart' ); ?></th>
  		<th><?php _e( 'Quantity' , 'simpleecommcart' ); ?></th>
  	</tr>
  </thead>
  <tfoot>
      <tr>
        <th><?php _e( 'Track' , 'simpleecommcart' ); ?></th>
    		<th><?php _e( 'Product Name' , 'simpleecommcart' ); ?></th>
    		<th><?php _e( 'Product Variation' , 'simpleecommcart' ); ?></th>
    		<th><?php _e( 'Quantity' , 'simpleecommcart' ); ?></th>
    	</tr>
  </tfoot>
  <tbody>
    <?php
      $ikeyList = array();
      foreach($products as $p) {
        $p->insertInventoryData();
        $combos = $p->getAllOptionCombinations();
        if(count($combos)) {
          foreach($combos as $c) {
            $k = $p->getInventoryKey($c);
            $ikeyList[] = $k;
            if($save) { $p->updateInventoryFromPost($k); }
            ?>
            <tr>
              <td><input type="checkbox" name="track_<?php echo $k ?>" value="1" id="track_<?php echo $k ?>" <?php echo ($p->isInventoryTracked($k)) ? 'checked="checked"' : ''; ?> class="SimpleEcommCartInventoryCheckbox" /></td>
              <td><?php echo $p->name ?></td>
              <td><?php echo $c ?></td>
              <td><input type="text" name="qty_<?php echo $k ?>" value="<?php echo $p->getInventoryCount($k); ?>" id="qty_<?php echo $k ?>" style="width:50px;" class="SimpleEcommCartInventoryQty" />
            </tr>
            <?php
          }
        }
        else {
          $k = $p->getInventoryKey();
          $ikeyList[] = $k;
          if($save) { $p->updateInventoryFromPost($k); }
          ?>
            <tr>
              <td><input type="checkbox" name="track_<?php echo $k ?>" value="1" id="track_<?php echo $k ?>" <?php echo ($p->isInventoryTracked($k)) ? 'checked="checked"' : ''; ?> class="SimpleEcommCartInventoryCheckbox" /></td>
              <td><?php echo $p->name ?></td>
              <td>&nbsp;</td>
              <td><input type="text" name="qty_<?php echo $k ?>" value="<?php echo $p->getInventoryCount($k); ?>" id="qty_<?php echo $k ?>" style="width:50px;" class="SimpleEcommCartInventoryQty" />
            </tr>
          <?php          
        }
      }
    
      if($save) { $p->pruneInventory($ikeyList); }
    ?>
  </tbody>
  </table>

  <input type="submit" name="submit" value="Save" id="submit" style="width: 80px; margin-top: 20px;" class="button-primary" />
</form>
<?php else: ?>
  <p><?php _e( 'You do not have any products' , 'simpleecommcart' ); ?></p>
<?php endif; ?>