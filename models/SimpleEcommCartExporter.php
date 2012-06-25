<?php
class SimpleEcommCartExporter {
  
  public static function exportOrders($startDate, $endDate) {
    global $wpdb;
    $start = date('Y-m-d 00:00:00', strtotime($startDate));
    $end = date('Y-m-d 00:00:00', strtotime($endDate . ' + 1 day'));
    
    $orders = SimpleEcommCartCommon::getTableName('orders');
    $items = SimpleEcommCartCommon::getTableName('order_items');
    
    $orderHeaders = array(
      'id' => 'Order ID',
      'trans_id' => 'Order Number',
      'ordered_on' => 'Date',
      'bill_first_name' => 'Billing First Name',
      'bill_last_name' => 'Billing Last Name',
      'bill_address' => 'Billing Address',
      'bill_address2' => 'Billing Address 2',
      'bill_city' => 'Billing City',
      'bill_state' => 'Billing State',
      'bill_country' => 'Billing Country',
      'bill_zip' => 'Billing Zip Code',
      'ship_first_name' => 'Shipping First Name',
      'ship_last_name' => 'Shipping Last Name',
      'ship_address' => 'Shipping Address',
      'ship_address2' => 'Shipping Address 2',
      'ship_city' => 'Shipping City',
      'ship_state' => 'Shipping State',
      'ship_country' => 'Shipping Country',
      'ship_zip' => 'Shipping Zip Code',
      'phone' => 'Phone',
      'email' => 'Email',
      'coupon' => 'Coupon',
      'discount_amount' => 'Discount Amount',
      'shipping' => 'Shipping Cost',
      'subtotal' => 'Subtotal',
      'tax' => 'Tax',
      'total' => 'Total',
      'ip' => 'IP Address',
      'shipping_method' => 'Delivery Method'
    );
    
    $orderColHeaders = implode(',', $orderHeaders);
    $orderColSql = implode(',', array_keys($orderHeaders));
    $out  = $orderColHeaders . ",Item Number,Description,Quantity,Product Price\n";
    
    $sql = "SELECT $orderColSql from $orders where ordered_on >= %s AND ordered_on < %s order by ordered_on";
    $sql = $wpdb->prepare($sql, $start, $end);
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] SQL: $sql");
    $selectedOrders = $wpdb->get_results($sql, ARRAY_A);
    
    foreach($selectedOrders as $o) {
      $itemRowPrefix = '"' . $o['id'] . '","' . $o['trans_id'] . '",' . str_repeat(',', count($o)-3);
      $orderId = $o['id'];
      $sql = "SELECT item_number, description, quantity, product_price FROM $items where order_id = $orderId";
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Item query: $sql");
      $selectedItems = $wpdb->get_results($sql, ARRAY_A);
      $out .= '"' . implode('","', $o) . '"';
      $printItemRowPrefix = false;
      foreach($selectedItems as $i) {
        if($printItemRowPrefix) {
          $out .= $itemRowPrefix;
        }
        $out .= ',"' . implode('","', $i) . '"';
        $out .= "\n";
        $printItemRowPrefix = true;
      }
	  
	  if(count($selectedItems)  <=0 ) $out .= "\n";
    }
    
    SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Report\n$out");
    return $out;
  }
  
}