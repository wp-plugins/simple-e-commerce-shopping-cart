<?php
if ( !defined( 'ABSPATH' ) )
  die( 'Do not include this file directly' );

$product= new SimpleEcommCartProduct();

$tinyURI = get_bloginfo('wpurl')."/wp-includes/js/tinymce";

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>SimpleEcommCart</title>
	<link type="text/css" rel="stylesheet" href="<?php echo SIMPLEECOMMCART_URL; ?>/js/simpleecommcart.css" />
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo $tinyURI; ?>/utils/form_utils.js"></script>
	
  <script type="text/javascript" src="<?php echo get_bloginfo('wpurl')?>/wp-includes/js/jquery/jquery.js"></script>
	<script language="javascript" type="text/javascript">
	<!--
  var $jq = jQuery.noConflict();
	tinyMCEPopup.onInit.add( function(){window.setTimeout(function(){$jq('#productName').focus();},500);} );

	<?php
	$prices = array();
	$types = array(); 
	$options='';
	$products = $product->getModels("where id>0", "order by name");
	if(count($products)):
	  $i=0;
	  foreach($products as $p) {
	    // Only show non-gravity products in this list
	    if(!$p->isGravityProduct()) {
	      /*if($p->itemNumber=="")
		  {
          	$id=$p->id;
          	$type='id';
          	$description = "";
          }
        	else{
          	$id=$p->itemNumber;
          	$type='item';
          	$description = '(# '.$p->itemNumber.')';
        	}*/
        
		$id=$p->id;
        $type='id';
        $description = '(# '.$p->id.')';
		  
  	    $types[] = htmlspecialchars($type);
  	    
  	    if(SIMPLEECOMMCART_PRO && $p->isPayPalSubscription()) {
  	      $sub = new SimpleEcommCartPayPalSubscription($p->id);
  	      $subPrice = strip_tags($sub->getPriceDescription($sub->offerTrial > 0, '(trial)'));
  	      $prices[] = htmlspecialchars($subPrice);
  	      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] subscription price in dialog: $subPrice");
  	    }
  	    else {
  	      $prices[] = htmlspecialchars(strip_tags($p->getPriceDescription()));
  	    }
  	    
  	    
  	    $options .= '<option value="'.$id.'">'.$p->name.' '.$description.'</option>';
  	    $i++;
	    }
	  }
	
	else:
	  $options .= '<option value="">No products</option>';
	endif;
	 
	 $prodTypes = implode("\",\"",$types);
	 $prodPrices = implode("\",\"", $prices);
  ?>
  
  var prodtype = new Array("<?php echo $prodTypes; ?>");
  var prodprices = new Array("<?php echo $prodPrices; ?>");
  
 

	function init() {
		mcTabs.displayTab('tab', 'panel');
	}
	
	function preview(){
	   	
	  var productIndex = jQuery("#productNameSelector option:selected").index();
	  
	  var priceDescription = jQuery("<div/>").html(prodprices[productIndex]).text();
    var price = "<p style='margin-top:2px;'><label id='priceLabel'>" + priceDescription + "</label></p>";
	  if(jQuery("input[name='showPrice']:checked").val()=="no"){
	    price = "";
	  }
	  
	  var style = "";
	  if(jQuery("#productStyle").val()!="") {
	    style = jQuery("#productStyle").val();
	  }
	  
    <?php 
      $setting = new SimpleEcommCartSetting();
      $cartImgPath = SimpleEcommCartSetting::getValue('cart_images_url');
      if($cartImgPath) {
        if(strpos(strrev($cartImgPath), '/') !== 0) {
          $cartImgPath .= '/';
        }
        $buttonPath = $cartImgPath . 'add-to-cart.png';
      }
    ?>

    var button = '';

    <?php if($cartImgPath): ?>
      var buttonPath = '<?php echo $buttonPath ?>';
      button = "<img src='"+buttonPath+"' title='Add to Cart' alt='SimpleEcommCart Add To Cart Button'>";
    <?php else: ?>
      button = "<input type='button' class='SimpleEcommCartButtonPrimary' value='Add To Cart' />";
    <?php endif; ?>

	  if($jq("#buttonImage").val()!=""){
	    button = "<img src='"+jQuery("#buttonImage").val()+"' title='Add to Cart' alt='SimpleEcommCart Add To Cart Button'>";
	  } 
    
    if($jq("input[name='showPrice']:checked").val()=="only"){
      button= "";
    }
    
    var prevBox = "<div style='"+style+"'>"+price+button+"</div>";
	  
	  jQuery("#buttonPreview").html(prevBox).text();
	}

	function insertProductCode() {
		prod  = jQuery("#productNameSelector option:selected").val();

    showPrice = $jq("input[name='showPrice']:checked").val();
    if(showPrice == 'no') {
      showPrice = 'showprice="no"';
    }
    else if(showPrice == 'only'){
      showPrice = 'showprice="only"';
    }
    else {
      showPrice = '';
    }

    buttonImage = '';
		if($jq("#buttonImage").val() != "") {
      //buttonImage = 'img="' + $jq("#buttonImage").val() + '"';
    }

		type =  prodtype[jQuery("#productNameSelector option:selected").index()];
		if($jq("#productStyle").val()!=""){
		  style  = 'style="'+$jq("#productStyle").val()+'"';
		}
		else {
		  style = '';
		}
		
		style = '';
		html = '&nbsp;[simpleecommcart_add_to_cart '+type+'="'+prod+'" '+style+' ' +showPrice+' '+buttonImage+' ]&nbsp;';

		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		tinyMCEPopup.execCommand('mceInsertContent', false, html);
	 	tinyMCEPopup.execCommand("mceEndUndoLevel");
	  tinyMCEPopup.close();
	}
	
	function toggleInsert(){
	  if($jq("#panel2").is(":visible")){
	    $jq("#insertProductButton").hide();
	    
	  }
	  else{
	    $jq("#insertProductButton").show();
	  }
	}
	
	function shortcode(code){
	  html = '&nbsp;['+code+']&nbsp;';

		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		tinyMCEPopup.execCommand('mceInsertContent', false, html);
	 	tinyMCEPopup.execCommand("mceEndUndoLevel");
	  tinyMCEPopup.close();
	}

	function shortcode_wrap(open, close){
	  html = '&nbsp;['+open+"]&nbsp;<br/>[/"+close+']';

		tinyMCEPopup.execCommand("mceBeginUndoLevel");
		tinyMCEPopup.execCommand('mceInsertContent', false, html);
	 	tinyMCEPopup.execCommand("mceEndUndoLevel");
	  tinyMCEPopup.close();
	}
	
	jQuery(document).ready(function(){
	  preview();
	  jQuery("input").change(function(){preview();});
	  jQuery("input").click(function(){preview();});
	  
	  
	  jQuery(".smallText").click(function(){
	    jQuery(".gfProductMessage").show();
	  })
	  jQuery(".closeMessage").click(function(){
	    jQuery(".gfProductMessage").hide();
	  })
	  
	  jQuery("#productNameSelector").change(function(){
	    preview();
	  })
	})
	
	//-->
	</script>
	<style type="text/css" media="screen">
	 #buttonPreview{
	   padding:5px;
	 }
	</style>
	<base target="_self" />
	
	<style type="text/css">
	#shortCodeList,
	#systemShortCodeList {
	  border-collapse: collapse;
	}
	#systemShortCodeList tr td,
	#shortCodeList tr td {
    padding: 5px;
    border-spacing: 0px;
  }
  .smallText{
    font-size:11px;
    text-decoration:underline;
    cursor:pointer;
  }
  .gfProductMessage{
    display:none;
    position:absolute;
    background-color:#fff;
    border:1px solid;
    font-size:12px;
    padding:0px 15px;
    width:430px;
    margin:-10px 0px 0px 0px;
  }
  .closeMessage{
    font-size:14px;
  }
	</style>
</head>
<body id="simpleecommcart" onLoad="tinyMCEPopup.executeOnLoad('init();');" style="display: none">
	<form onSubmit="insertSomething();" action="#">
	<div class="tabs">
		<ul>
			<li id="tab"><span><a href="javascript:mcTabs.displayTab('tab','panel');toggleInsert();"><?php  _e('Add a Product'); ?></a></span></li>
			<!--<li id="tab2"><span><a href="javascript:mcTabs.displayTab('tab2','panel2');toggleInsert();"><?php  _e('Shortcode Reference'); ?></a></span></li>-->
		</ul>
	</div>
	<div class="panel_wrapper">
		<div id="panel" class="panel current">
		  <div class="gfProductMessage">
			  <p>When using a SimpleEcommCart product attached to a Gravity Form it is important to use the Gravity Form shortcode and not the SimpleEcommCart one. If you do use the SimpleEcommCart [simpleecommcart_add_to_cart ] shortcode, the product is not added to the cart. To prevent confusion ONLY the non-Gravity Forms products are displayed in this dropdown.</p>
			  
			  <p>To add the Gravity Form product, simply use the Gravity Forms button to insert the form and SimpleEcommCart will do the rest.</p>
			  
			  <p align="center" class="mceActionPanel">
			    <input type="button" id="closeMessage" value="<?php  _e('OK'); ?>" class="closeMessage button" />
			  </p>
			</div>
			<table border="0" cellspacing="0" cellpadding="2">
				<tr>
					<td class="phplabel"><label for="productNameSelector"><?php  _e('Product List'); ?>:</label></td>
					<td class="phpinput"><select id="productNameSelector" name="productName"><?php echo $options; ?></select></td>
					 
				</tr>
				<!--<tr>
				  <td class="phplabel"><label for="productStyle"><?php  _e('CSS style'); ?>:</label></td>
				  <td class="phpinput"><input id="productStyle" name="productStyle" size="34"></td>
				</tr>-->
				<tr>
				  <td class="phplabel"><label for="showPrice"><?php  _e('Show price'); ?>:</label></td>
          <td class="phpinput">
            <input type='radio' style="border: none;" id="showPrice" name="showPrice" value='yes' checked> Yes
            <input type='radio' style="border: none;" id="showPrice" name="showPrice" value='no'> No
            <!--<input type='radio' style="border: none;" id="showPrice" name="showPrice" value='only'> Price Only-->
          </td>
				</tr>
				<!--<tr>
				  <td class="phplabel"><label for="buttonImage"><?php  _e('Button path'); ?>:</label></td>
				  <td class="phpinput"><input id="buttonImage" name="buttonImage" size="34"></td>
				</tr>-->
				<!--<tr>
				  <td class="phplabel" valign="top"><label for="buttonImage"><?php  _e('Preview'); ?>:</label></td>
				  <td class="" valign="top" id="buttonPreview"> 
				  </td>
				</tr>-->
			</table>
		</div>
    
    <div id="panel2" class="panel">
      <p>Click on a short code to insert it into your content.</p>
      
      <table id="shortCodeList" class="66altColor" cellpadding="0" width="95%">
        <tr>
          <td colspan="2"><br/><strong>Shortcode Quick Reference:</strong></td>
        </tr>
        
        <?php if(SIMPLEECOMMCART_PRO): ?>
        
        <?php endif; ?>
        
        
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_add_to_cart item=&quot;&quot;');"><a title="Insert [simpleecommcart_add_to_cart]">[simpleecommcart_add_to_cart item=""]</a></div></td>
          <td>Create add to cart button</td>
        </tr>
        
        
        <?php if(SIMPLEECOMMCART_PRO): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('cancel_paypal_subscription');"><a title="Insert [cancel_paypal_subscription]">[cancel_paypal_subscription]</a></div></td>
          <td>Link to cancel PayPal subscription</td>
        </tr>
        <?php endif; ?>
        
        
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_show_cart');"><a title="Insert [simpleecommcart_show_cart]">[simpleecommcart_show_cart]</a></div></td>
          <td>Show the shopping cart</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_show_cart mode=&quot;read&quot;');"><a title="Insert [simpleecommcart_show_cart mode=&quot;read&quot;]">[simpleecommcart_show_cart mode="read"]</a></div></td>
          <td>Show the shopping cart in read-only mode</td>
        </tr>
        
        
        <?php if(SIMPLEECOMMCART_PRO): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_checkout_authorizenet');"><a title="Insert [simpleecommcart_checkout_authorizenet]">[simpleecommcart_checkout_authorizenet]</a></div></td>
          <td>Authorize.net (or AIM compatible gateway) checkout form</td>
        </tr>
        <?php endif; ?>
        
        
        <tr>
          <td><div class="shortcode" onclick="shortcode('checkout_manual');"><a title="Insert [checkout_manual]">[checkout_manual]</a></div></td>
          <td>Checkout form that does not process credit cards</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_checkout_paypal');"><a title="Insert [simpleecommcart_checkout_paypal]">[simpleecommcart_checkout_paypal]</a></div></td>
          <td>PayPal Website Payments Standard checkout button</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_checkout_paypal_express');"><a title="Insert [simpleecommcart_checkout_paypal_express]">[simpleecommcart_checkout_paypal_express]</a></div></td>
          <td>PayPal Express checkout button</td>
        </tr>
        
        
        <?php if(SIMPLEECOMMCART_PRO): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_checkout_paypal_pro');"><a title="Insert [simpleecommcart_checkout_paypal_pro]">[simpleecommcart_checkout_paypal_pro]</a></div></td>
          <td>PayPal Pro checkout form</td>
        </tr>
        <?php endif; ?>
        
        
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_clear_cart');"><a title="Insert [simpleecommcart_clear_cart]">[simpleecommcart_clear_cart]</a></div></td>
          <td>Clear the contents of the shopping cart</td>
        </tr>
        
        <?php if(SIMPLEECOMMCART_PRO): ?>
         
        <?php endif; ?>
        
        
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_shopping_cart');"><a title="Insert [simpleecommcart_shopping_cart]">[simpleecommcart_shopping_cart]</a></div></td>
          <td>Show the SimpleEcommCart sidebar widget</td>
        </tr>


        <?php if(SIMPLEECOMMCART_PRO): ?>
        
        <?php endif; ?>
        
      </table>
      
      <br/>
      
      <table id="systemShortCodeList" class="66altColor" cellpadding="0" width="95%">
        <tr>
          <td colspan="2"><strong>System Shortcodes</strong></td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('express');"><a title="Insert [express]">[express]</a></div></td>
          <td>Listens for PayPal Express callbacks <br/>Belongs on system page store/express</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_ipn');"><a title="Insert [simpleecommcart_ipn]">[simpleecommcart_ipn]</a></div></td>
          <td>PayPal Instant Payment Notification <br/>Belongs on system page store/ipn</td>
        </tr>
        <tr>
          <td><div class="shortcode" onclick="shortcode('simpleecommcart_receipt');"><a title="Insert [simpleecommcart_receipt]">[simpleecommcart_receipt]</a></div></td>
          <td>Shows the customer's receipt after a successful sale <br/>Belongs on system page store/receipt</td>
        </tr>
        
        <?php if(SIMPLEECOMMCART_PRO && false): ?>
        <tr>
          <td><div class="shortcode" onclick="shortcode('spreedly_listener');"><a title="Insert [spreedly_listener]">[spreedly_listener]</a></div></td>
          <td>Listens for spreedly account changes <br/>Belongs on system page store/spreedly</td>
        </tr>
        <?php endif; ?>
        
      </table>
      
    </div>
	</div>
	<div class="mceActionPanel">
		<div id="insertProductButton" style="float: right">
				<input type="button" id="insert" name="insert" value="<?php  _e('Insert'); ?>" onClick="insertProductCode();" />
		</div>
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php  _e('Cancel'); ?>" onClick="tinyMCEPopup.close();" />
		</div>
	</div>
</form>

<script language="javascript">
jQuery.noConflict();
jQuery(document).ready(function($){
  $(".66altColor tr:even").css("background-color", "#fff");
  $(".66altColor tr:odd").css("background-color", "#eee");
});
</script>
</body>
</html>