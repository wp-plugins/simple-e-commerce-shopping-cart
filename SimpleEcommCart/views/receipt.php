<?php
global $wpdb;

$product = new SimpleEcommCartProduct();

$order = false;
if(isset($_GET['ouid'])) {
  $order = new SimpleEcommCartOrder();
  $order->loadByOuid($_GET['ouid']);
  if(empty($order->id)) {
    echo "<h2>This order is no longer in the system</h2>";
    exit();
  }
}

// Process Affiliate Payments
// Begin processing affiliate information
if(SimpleEcommCartSession::get('ap_id')) {
  $referrer = SimpleEcommCartSession::get('ap_id');
}
elseif(isset($_COOKIE['ap_id'])) {
  $referrer = $_COOKIE['ap_id'];
}

if (!empty($referrer)) {
  SimpleEcommCartCommon::awardCommission($order->id, $referrer);
}
// End processing affiliate information

// Begin iDevAffiliate Tracking
if(SIMPLEECOMMCART_PRO && $url = SimpleEcommCartSetting::getValue('idevaff_url')) {
  require_once(SIMPLEECOMMCART_PATH . "/advanced/idevaffiliate-award.php");
}
// End iDevAffiliate Tracking
if(isset($_COOKIE['ap_id']) && $_COOKIE['ap_id']) {
  setcookie('ap_id',$referrer, time() - 3600, "/");
  unset($_COOKIE['ap_id']);
}
SimpleEcommCartSession::drop('app_id');


if(isset($_GET['duid'])) {
  $duid = $_GET['duid'];
  
  //check payment status
  $order = new SimpleEcommCartOrder(); 
  $order->loadByDuid($duid);
  if( $order->payment_status != 'Complete')
  {
  	 echo "Your payment is not completed yet";
	 exit();
  }
  
  $product = new SimpleEcommCartProduct();
  if($product->loadByDuid($duid)) {
    $okToDownload = true;
    if($product->download_limit > 0) {
      // Check if download limit has been exceeded
      if($product->countDownloadsForDuid($duid) >= $product->download_limit) {
        $okToDownload = false;
      }
    }
    
    if($okToDownload) {
      $data = array(
        'duid' => $duid,
        'downloaded_on' => date('Y-m-d H:i:s'),
        'ip' => $_SERVER['REMOTE_ADDR']
      );
      $downloadsTable = SimpleEcommCartCommon::getTableName('downloads');
      $wpdb->insert($downloadsTable, $data, array('%s', '%s', '%s'));
      
      $setting = new SimpleEcommCartSetting();
       
	  if(!empty($product->digital_prdoduct_url)) { 
	   $dir = SimpleEcommCartSetting::getValue('tmp_folder'); 
	   $fileName = basename($product->digital_prdoduct_url);
	   $data = file_get_contents($product->digital_prdoduct_url);
	   $path=$dir . DIRECTORY_SEPARATOR .$fileName;
	   $fh = fopen($path,"w");
 		
	   fwrite($fh,$data);
 	   fclose($fh);
	   SimpleEcommCartCommon::downloadTmpFile($path);
      }
      else {
        $dir = SimpleEcommCartSetting::getValue('product_folder');
        $path = $dir . DIRECTORY_SEPARATOR . $product->download_path;
        SimpleEcommCartCommon::downloadFile($path);
      } 
    }
    else {
      echo "You have exceeded the maximum number of downloads for this product";
    }
    exit();
  }
}
?>

<?php  if($order !== false): ?>
<h2>Order Number: <?php echo $order->trans_id ?></h2>

<?php 
if(SIMPLEECOMMCART_PRO) {
  $logInLink = '';//SimpleEcommCartAccessManager::getLogInLink();
  if(SimpleEcommCartSession::get('SimpleEcommCartLoginInfo') && $logInLink !== false) {
    echo '<h2>Your Account</h2>';
    echo "<p><a href=\"$logInLink\">Log into your account</a>.</p>";
  }
}
?>

<table border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td valign="top">
      <p>
        <strong><?php _e( 'Billing Information' , 'simpleecommcart' ); ?></strong><br/>
      <?php echo $order->bill_first_name ?> <?php echo $order->bill_last_name ?><br/>
      <?php echo $order->bill_address ?><br/>
      <?php if(!empty($order->bill_address2)): ?>
        <?php echo $order->bill_address2 ?><br/>
      <?php endif; ?>

      <?php if(!empty($order->bill_city)): ?>
        <?php echo $order->bill_city ?> <?php echo $order->bill_state ?>, <?php echo $order->bill_zip ?><br/>
      <?php endif; ?>
      
      <?php if(!empty($order->bill_country)): ?>
        <?php echo $order->bill_country ?><br/>
      <?php endif; ?>
      </p>
    </td>
    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td valign="top">
      <p><strong><?php _e( 'Contact Information' , 'simpleecommcart' ); ?></strong><br/>
      <?php if(!empty($order->phone)): ?>
        <?php _e( 'Phone' , 'simpleecommcart' ); ?>: <?php echo SimpleEcommCartCommon::formatPhone($order->phone) ?><br/>
      <?php endif; ?>
      <?php _e( 'Email' , 'simpleecommcart' ); ?>: <?php echo $order->email ?><br/>
      <?php _e( 'Date' , 'simpleecommcart' ); ?>: <?php echo date('m/d/Y g:i a', strtotime($order->ordered_on)) ?>
      </p>
    </td>
  </tr>
  <tr>
    <td>
      <?php if($order->shipping_method != 'None'): ?>
        <?php if($order->hasShippingInfo()): ?>
          
          <p><strong><?php _e( 'Shipping Information' , 'simpleecommcart' ); ?></strong><br/>
          <?php echo $order->ship_first_name ?> <?php echo $order->ship_last_name ?><br/>
          <?php echo $order->ship_address ?><br/>
      
          <?php if(!empty($order->ship_address2)): ?>
            <?php echo $order->ship_address2 ?><br/>
          <?php endif; ?>
      
          <?php if($order->ship_city != ''): ?>
            <?php echo $order->ship_city ?> <?php echo $order->ship_state ?>, <?php echo $order->ship_zip ?><br/>
          <?php endif; ?>
      
          <?php if(!empty($order->ship_country)): ?>
            <?php echo $order->ship_country ?><br/>
          <?php endif; ?>
      
        <?php endif; ?>
      
      <br/><em><?php _e( 'Delivery via' , 'simpleecommcart' ); ?>: <?php echo $order->shipping_method ?></em><br/>
      </p>
      <?php endif; ?>
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>


<table id='viewCartTable' cellspacing="0" cellpadding="0">
  <tr>
    <th style='text-align: left;'><?php _e( 'Product' , 'simpleecommcart' ); ?></th>
    <th style='text-align: center;'><?php _e( 'Quantity' , 'simpleecommcart' ); ?></th>
    <th style='text-align: left;'><?php _e( 'Item Price' , 'simpleecommcart' ); ?></th>
    <th style='text-align: left;'><?php _e( 'Item Total' , 'simpleecommcart' ); ?></th>
  </tr>

  <?php foreach($order->getItems() as $item): ?>
    <?php 
      $product->load($item->product_id);
      $price = $item->product_price * $item->quantity;
    ?>
    <tr>
      <td>
        <?php echo nl2br($item->description) ?>
        <?php
          $product->load($item->product_id);
          if($product->isDigital()) {
            $receiptPage = get_page_by_path('store/receipt');
            $receiptPageLink = get_permalink($receiptPage);
            $receiptPageLink .= (strstr($receiptPageLink, '?')) ? '&duid=' . $item->duid : '?duid=' . $item->duid;
            echo "<br/><a href='$receiptPageLink'>Download</a>";
          }
        ?>
        
      </td>
      <td style='text-align: center;'><?php echo $item->quantity ?></td>
      <td><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($item->product_price, 2) ?></td>
      <td><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($item->product_price * $item->quantity, 2) ?></td>
    </tr>
    <?php
      if(!empty($item->form_entry_ids)) {
        $entries = explode(',', $item->form_entry_ids);
        foreach($entries as $entryId) {
          if(class_exists('RGFormsModel')) {
            if(RGFormsModel::get_lead($entryId)) {
              echo "<tr><td colspan='4'><div class='SimpleEcommCartGravityFormDisplay'>" . SimpleEcommCartGravityReader::displayGravityForm($entryId) . "</div></td></tr>";
            }
          }
          else {
            echo "<tr><td colspan='5' style='color: #955;'>This order requires Gravity Forms in order to view all of the order information</td></tr>";
          }
        }
      }
    ?>
  <?php endforeach; ?>

  <tr class="noBorder">
    <td colspan='1'>&nbsp;</td>
    <td colspan="1" style='text-align: center;'>&nbsp;</td>
    <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Subtotal' , 'simpleecommcart' ); ?>:</td>
    <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo $order->subtotal; ?></td>
  </tr>
  
  <?php if($order->shipping_method != 'None' && $order->shipping_method != 'Download'): ?>
  <tr class="noBorder">
    <td colspan='1'>&nbsp;</td>
    <td colspan="1" style='text-align: center;'>&nbsp;</td>
    <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Shipping' , 'simpleecommcart' ); ?>:</td>
    <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo $order->shipping; ?></td>
  </tr>
  <?php endif; ?>
  
  <?php if($order->discount_amount > 0): ?>
    <tr class="noBorder">
      <td colspan='2'>&nbsp;</td>
      <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Discount' , 'simpleecommcart' ); ?>:</td>
      <td colspan="1" style="text-align: left; font-weight: bold;">-<?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($order->discount_amount, 2); ?></td>
    </tr>
  <?php endif; ?>
  
  <?php if($order->tax > 0): ?>
    <tr class="noBorder">
      <td colspan='2'>&nbsp;</td>
      <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Tax' , 'simpleecommcart' ); ?>:</td>
      <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($order->tax, 2); ?></td>
    </tr>
  <?php endif; ?>
  
  <tr class="noBorder">
    <td colspan='2' style='text-align: center;'>&nbsp;</td>
    <td colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Total' , 'simpleecommcart' ); ?>:</td>
    <td colspan="1" style="text-align: left; font-weight: bold;"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($order->total, 2); ?></td>
  </tr>
</table>

<p><a href='#' id="print_version"><?php _e( 'Printer Friendly Receipt' , 'simpleecommcart' ); ?></a></p>

<?php
  // Erase the shopping cart from the session at the end of viewing the receipt
  SimpleEcommCartSession::drop('SimpleEcommCartCart');
?>
<?php else: ?>
  <p><?php _e( 'Receipt not available' , 'simpleecommcart' ); ?></p>
<?php endif; ?>


<?php
  if($order !== false) {
    $printView = SimpleEcommCartCommon::getView('views/receipt_print_version.php', array('order' => $order));
    $printView = str_replace("\n", '', $printView);
    $printView = str_replace("'", '"', $printView);
  }
?>

<script type="text/javascript">
/* <![CDATA[ */
jQuery(document).ready(function($) {
  $('#print_version').click(function() {
    myWindow = window.open('','Your_Receipt','resizable=yes,scrollbars=yes,width=550,height=700');
    myWindow.document.open("text/html","replace");
    myWindow.document.write(decodeURIComponent('<?php echo rawurlencode($printView); ?>' + ''));
    return false;
  });
});
/* ]]> */
</script>