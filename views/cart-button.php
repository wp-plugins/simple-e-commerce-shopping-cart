 <object class="simpleecommcartAddToCartButton">
 

 <?php 
  // Only render the ajax code for tracking inventory if inventory tracking is enabled
  $setting = new SimpleEcommCartSetting();
  $trackInventory = SimpleEcommCartSetting::getValue('track_inventory');
  $id = SimpleEcommCartCommon::getButtonId($data['product']->id); 
?>

<?php if($data['showPrice'] == 'only'): ?>
  <p class="SimpleEcommCartPrice" <?php echo $style; ?>>
  	<span style="color:#666;font-size:1.1em;"><em><?php echo $data['price_description'] ?></em></span> 
  	<?php _e( 'Price' , 'simpleecommcart' ); ?><?php echo (SimpleEcommCartSetting::getValue('SIMPLEECOMMCART_CURRENCY_SYMBOL_text')!=NULL)?'('. SimpleEcommCartSetting::getValue('SIMPLEECOMMCART_CURRENCY_SYMBOL_text').')':'' ?>: <?php echo $data['price'] ?>
  
  </p>
<?php else: ?>

  <form id='cartButtonForm_<?php echo $id ?>' class="SimpleEcommCartCartButton" method="post" action="<?php echo SimpleEcommCartCommon::getPageLink('store/cart'); ?>" <?php echo $data['style']; ?>>
    <input type='hidden' name='task' id="task_<?php echo $id ?>" value='addToCart' />
    <input type='hidden' name='simpleecommcartItemId' value='<?php echo $data['product']->id; ?>' />
    
	<input type='hidden' name='hascartwidget' class="hascartwidget" value='no' />
	
    <?php if($data['showName'] == 'true'): ?> 
      <span class="SimpleEcommCartProductName"><?php echo $data['product']->name; ?></span>
    <?php endif; ?>    
    
    <?php if($data['showPrice'] == 'yes' && $data['is_user_price'] != 1): ?> 
	  <span style="color:#666;font-size:1.1em;"><em><?php echo $data['price_description'] ?></em></span>
	  <?php
	  	if(!empty($data['price_description']))
		{
			echo '<br>';
	    } 
	  ?>
	   
      <span style="font-weight:bold;font-size:1.01em;"><?php _e( 'Price' , 'simpleecommcart' ); ?><?php echo (SimpleEcommCartSetting::getValue('SIMPLEECOMMCART_CURRENCY_SYMBOL_text')!=NULL)?'('. SimpleEcommCartSetting::getValue('SIMPLEECOMMCART_CURRENCY_SYMBOL_text').')':'' ?>: <?php echo $data['price']; ?></span>
    <?php endif; ?>
    
    <?php if($data['is_user_price'] == 1) : ?>
      <div class="SimpleEcommCartUserPrice">
        <label for="SimpleEcommCartUserPriceInput_<?php echo $id ?>"><?php echo (SimpleEcommCartSetting::getValue('userPriceLabel')) ? SimpleEcommCartSetting::getValue('userPriceLabel') : __( 'Enter an amount: ' ) ?> </label><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><input id="SimpleEcommCartUserPriceInput_<?php echo $id ?>" name="item_user_price" value="<?php echo str_replace(SIMPLEECOMMCART_CURRENCY_SYMBOL,"",$data['price']);?>" size="5">
      </div>
    <?php endif; ?>
    
    <?php 
      if(strpos($data['quantity'],'user') !== FALSE && $data['is_user_price'] != 1): 
        $quantityString = explode(":",$data['quantity']);
        if(isset($quantityString[1])){
          $defaultQuantity = (is_numeric($quantityString[1])) ? $quantityString[1] : 1;
        }
        else{
          $defaultQuantity = "";
        }
        
    ?>
      <div class="SimpleEcommCartUserQuantity">
       <label for="SimpleEcommCartUserQuantityInput_<?php echo $id; ?>"><?php echo (SimpleEcommCartSetting::getValue('userQuantityLabel')) ? SimpleEcommCartSetting::getValue('userQuantityLabel') : __( 'Quantity: ' ) ?> </label>
       <input id="SimpleEcommCartUserQuantityInput_<?php echo $id; ?>" name="item_quantity" value="<?php echo $defaultQuantity; ?>" size="4">
      </div> 
    <?php elseif(is_numeric($data['quantity']) && $data['is_user_price'] != 1): ?>
       <input type="hidden" name="item_quantity" class="SimpleEcommCartItemQuantityInput" value="<?php echo $data['quantity']; ?>">       
    <?php endif; ?>
      
      
    <?php if($data['product']->isAvailable()): ?>
      <?php echo $data['productOptions'] ?>
    
      <?php if($data['product']->recurring_interval > 0 && !SIMPLEECOMMCART_PRO): ?>
          <div class='SimpleEcommCartProRequired'><a href='http://simpleecommcartbasic.wordpress.com/'><?php _e( 'SimpleEcommCart Professional' , 'simpleecommcart' ); ?></a> <?php _e( 'is required to sell subscriptions' , 'simpleecommcart' ); ?></div>
      <?php else: ?>
	  
	  <?php
	  	$p = $data['product'];
		
		if(!empty($p->button_image_path 	)) 
		{ 
			 $upload_dir = wp_upload_dir(); 
			 $path = $upload_dir['baseurl'].'/simpleecommcart/digitalproduct/'.$p->button_image_path;
						
			?>
			  <br><input type='image' value='Add To Cart' src='<?php echo $path ?>'  name='addToCart_<?php echo $id ?>' id='addToCart_<?php echo $id ?>'/>
			<?php
		} 
		else
		{
			?>
			 <br><input type='submit' value='Add To Cart' name='addToCart_<?php echo $id ?>' id='addToCart_<?php echo $id ?>' />
			<?php
		}
	  ?>
	  
	  
        <?php if($data['addToCartPath']): ?> 
        
        <?php else: ?>
         
        <?php endif; ?>
      <?php endif; ?>
    
    <?php else: ?>
      <span class='SimpleEcommCartOutOfStock'><?php _e( 'Out of stock' , 'simpleecommcart' ); ?></span>
    <?php endif; ?>
    
    <?php if($trackInventory): ?>
      <input type="hidden" name="action" value="check_inventory_on_add_to_cart" />
      <div id="stock_message_box_<?php echo $id ?>" class="SimpleEcommCartUnavailable" style="display: none;">
        <h2>We're Sorry</h2>
        <p id="stock_message_<?php echo $id ?>"></p>
        <input type="button" name="close" value="Ok" id="close" class="modalClose" />
      </div>
    <?php endif; ?>

  </form>
 
<?php endif; ?>
 
<?php if($trackInventory): ?>

  <?php if(is_user_logged_in()): ?>
    <div class="SimpleEcommCartAjaxWarning">Inventory tracking will not work because your site has javascript errors. 
      <a href="http://simpleecommcartbasic.wordpress.com//jquery-errors/">Possible solutions</a></div>
  <?php endif; ?>

<script type="text/javascript">
/* <![CDATA[ */

(function($){
  $(document).ready(function(){
    $('.SimpleEcommCartAjaxWarning').hide();
    
    $('#addToCart_<?php echo $id ?>').click(function() {
      $('#task_<?php echo $id ?>').val('ajax');
      var mydata = getCartButtonFormData('cartButtonForm_<?php echo $id ?>');
      <?php
        $url = admin_url('admin-ajax.php');
        if(SimpleEcommCartCommon::isHttps()) {
          $url = preg_replace('/http[s]*:/', 'https:', $url);
        }
        else {
          $url = preg_replace('/http[s]*:/', 'http:', $url);
        } 
      ?>
      $.ajax({
          type: "POST",
          url: '<?php echo $url; ?>',
          data: mydata,
          dataType: 'json',
          success: function(result) {
            if(result[0]) {
              $('#task_<?php echo $id ?>').val('addToCart');
              $('#cartButtonForm_<?php echo $id ?>').submit();
            }
            else {
              $('#stock_message_box_<?php echo $id ?>').fadeIn(300);
              $('#stock_message_<?php echo $id ?>').html(result[1]);
            }
          },
          error: function(xhr,err){
              alert("readyState: "+xhr.readyState+"\nstatus: "+xhr.status);
              <?php 
                //alert("responseText: "+xhr.responseText);
                //alert('echo $url ?' + mydata);
              ?>
          }
      });
      return false;
    });
 
	$(".hascartwidget").val($("#simpleecommcart_cart_sidebar").val());
  })
})(jQuery);

/* ]]> */
</script>

<?php endif; ?>
</object>
