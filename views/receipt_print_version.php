<?php 
  $product = new SimpleEcommCartProduct();
  $order = $data['order']; 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<title><?php _e( 'Your Receipt' , 'simpleecommcart' ); ?></title>
	<style type="text/css" media="print">
/*<![CDATA[*/
	 #print_button {
	   display: none;
	 }
/*]]>*/
	</style>
	<style type="text/css" media="screen,print">
  /*<![CDATA[*/
     body {
       font-family: arial;     
       font-size: 12px;      
       color: black;   
     }       
     table {
       margin: 10px;   
     }
     h1 {
       font-size: 14px;
     }
     p {
       padding: 3px 0px;
     }

     #viewCartTable th,
     #viewCartTable td {
       padding: 5px;
     }  
     
     table .entry-details {
       width: 100%;
     }

     table .entry-details tbody {
       padding: 0px;
       margin: 0px;
       background-color: #fff;
     }

     #viewCartTable td .entry-view-field-name {
       font-weight: bold;
       margin: 0px;
     }

     #viewCartTable td .entry-view-field-value {
       padding-left: 25px !important;
       border: none !important;
     }
  /*]]>*/
  </style>
</head>

<body>
  <h1><?php echo get_bloginfo('name'); ?></h1>
  
  <table border="0" cellpadding="0" cellspacing="0">
    <tr>
      <td colspan="3"><p><strong><?php _e( 'Order Number' , 'simpleecommcart' ); ?>: <?php echo $order->trans_id ?></strong></p></td>
      <!-- <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
      <td align="left"><p><strong>Date: <?php echo date('m/d/Y', strtotime($order->ordered_on)); ?></strong></p></td> -->
    </tr>
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
          Phone: <?php echo SimpleEcommCartCommon::formatPhone($order->phone) ?><br/>
        <?php endif; ?>
        Email: <?php echo $order->email ?><br/>
        Date: <?php echo date('m/d/Y g:i a', strtotime($order->ordered_on)) ?>
        </p>
      </td>
    </tr>
    <tr>
      <td>
        <?php if($order->shipping_method != 'None'): ?>
        <p>
          <strong><?php _e( 'Shipping Information' , 'simpleecommcart' ); ?></strong><br/>
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

        <br/><em><?php _e( 'Delivery via' , 'simpleecommcart' ); ?>: <?php echo $order->shipping_method ?></em><br/>
        </p>
        <?php endif; ?>
      </td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
  </table>

  <table id="viewCartTable" cellspacing="0" cellpadding="0">
    <tr>
      <th style="text-align: left;"><?php _e( 'Product' , 'simpleecommcart' ); ?></th>
      <th style="text-align: center;"><?php _e( 'Quantity' , 'simpleecommcart' ); ?></th>
      <th style="text-align: left;"><?php _e( 'Item Price' , 'simpleecommcart' ); ?></th>
      <th style="text-align: left;"><?php _e( 'Item Total' , 'simpleecommcart' ); ?></th>
    </tr>

    <?php foreach($order->getItems() as $item): ?>
      <?php 
        $product->load($item->product_id);
        $price = $item->product_price * $item->quantity;
      ?>
      <tr>
        <td>
          <?php echo str_replace("'", "&#039;", $item->description); ?>
          <?php
            $product->load($item->product_id);
            if($product->isDigital()) {
              $receiptPage = get_page_by_path('store/receipt');
              $receiptPageLink = get_permalink($receiptPage);
              $receiptPageLink .= (strstr($receiptPageLink, '?')) ? '&duid=' . $item->duid : '?duid=' . $item->duid;
              //echo "<br/><a href='$receiptPageLink'>Download</a>";
            }
          ?>

        </td>
        <td style="text-align: center;"><?php echo $item->quantity ?></td>
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

    <tr>
      <td class='noBorder' colspan='1'>&nbsp;</td>
      <td class='noBorder' colspan="1" style='text-align: center;'>&nbsp;</td>
      <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Subtotal' , 'simpleecommcart' ); ?>:</td>
      <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo $order->subtotal; ?></td>
    </tr>

    <?php if($order->shipping_method != 'None' && $order->shipping_method != 'Download'): ?>
    <tr>
      <td class='noBorder' colspan='1'>&nbsp;</td>
      <td class='noBorder' colspan="1" style='text-align: center;'>&nbsp;</td>
      <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Shipping' , 'simpleecommcart' ); ?>:</td>
      <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo $order->shipping; ?></td>
    </tr>
    <?php endif; ?>

    <?php if($order->discount_amount > 0): ?>
      <tr>
        <td class='noBorder' colspan='2'>&nbsp;</td>
        <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Discount' , 'simpleecommcart' ); ?>:</td>
        <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;">-<?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($order->discount_amount, 2); ?></td>
      </tr>
    <?php endif; ?>

    <?php if($order->tax > 0): ?>
      <tr>
        <td class='noBorder' colspan='2'>&nbsp;</td>
        <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Tax' , 'simpleecommcart' ); ?>:</td>
        <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($order->tax, 2); ?></td>
      </tr>
    <?php endif; ?>

    <tr>
      <td class='noBorder' colspan='2' style='text-align: center;'>&nbsp;</td>
      <td class='noBorder' colspan="1" style='text-align: right; font-weight: bold;'><?php _e( 'Total' , 'simpleecommcart' ); ?>:</td>
      <td class='noBorder' colspan="1" style="text-align: left; font-weight: bold;"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?><?php echo number_format($order->total, 2); ?></td>
    </tr>
  </table>
  
  <form>
    <input type="button" onClick="window.print();" name="print_button" id="print_button" value="Print Receipt" />
  </form>
</body>
</html>
