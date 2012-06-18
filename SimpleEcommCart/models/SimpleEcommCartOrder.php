<?php
class SimpleEcommCartOrder extends SimpleEcommCartModelAbstract {
  
  protected $_orderInfo = array();
  protected $_items = array();
  
  public function __construct($id=null) {
    $this->_tableName = SimpleEcommCartCommon::getTableName('orders');
    parent::__construct($id);
  }
  
  public function loadByOuid($ouid) {
    $sql = $this->_db->prepare("SELECT id from $this->_tableName where ouid=%s", $ouid);
    $id = $this->_db->get_var($sql);
    $this->load($id);
  }
  
  public function setInfo(array $info) {
    $this->_orderInfo = $info;
  }
  
  public function setItems(array $items) {
    $this->_items = $items;
  }
  
  public function save() {
    $this->_orderInfo['ouid'] = md5($this->_orderInfo['trans_id'] . $this->_orderInfo['bill_address']);
    SimpleEcommCartCommon::log('order.php:' . __LINE__ . ' - Saving Order Information (Items: ' . count($this->_items). '): ' . print_r($this->_orderInfo, true));
    $this->_db->insert($this->_tableName, $this->_orderInfo);
    $this->id = $this->_db->insert_id;
    $key = $this->_orderInfo['trans_id'] . '-' . $this->id . '-';
    foreach($this->_items as $item) {
      
      // Deduct from inventory
      SimpleEcommCartProduct::decrementInventory($item->getProductId(), $item->getOptionInfo(), $item->getQuantity());
       
      $data = array(
        'order_id' => $this->id,
        'product_id' => $item->getProductId(),
        'product_price' => $item->getProductPrice(),
        'item_number' => $item->getItemNumber(),
		'product_name' => $item->getProductName(),
        'description' => $item->getFullDisplayName(),
        'quantity' => $item->getQuantity(),
        'duid' => md5($key . $item->getProductId())
      );
      
      $formEntryIds = '';
      $fIds = $item->getFormEntryIds();
      if(is_array($fIds) && count($fIds)) {
        $formEntryIds = implode(',', $fIds);
      }
      $data['form_entry_ids'] = $formEntryIds;
      
      if($item->getCustomFieldInfo()) {
        $data['description'] .= "\n" . $item->getCustomFieldDesc() . ":\n" . $item->getCustomFieldInfo();
      }
      
      $orderItems = SimpleEcommCartCommon::getTableName('order_items');
      $this->_db->insert($orderItems, $data);
      $orderItemId = $this->_db->insert_id;
      SimpleEcommCartCommon::log("Saved order item ($orderItemId): " . $data['description'] . "\nSQL: " . $this->_db->last_query);
    }

	//redeem coupon
	   SimpleEcommCartCommon::log("will reedem coupon now");
	   $promo = SimpleEcommCartSession::get('SimpleEcommCartCart')->getPromotion();
       if($promo)
	   {  
			SimpleEcommCartPromotion::redeemCoupon($promo->code);
	   }
    return $this->id;
  }
  
  public function getOrderRows($where=null) {
    if(!empty($where)) {
      $sql = "SELECT * from $this->_tableName $where order by ordered_on desc";
    }
    else {
      $sql = "SELECT * from $this->_tableName order by ordered_on desc";
    }
    $orders = $this->_db->get_results($sql);
    return $orders;
  }
  
  public function getItems() {
    $orderItems = SimpleEcommCartCommon::getTableName('order_items');
    $sql = "SELECT * from $orderItems where order_id = $this->id order by product_price desc";
    $items = $this->_db->get_results($sql);
    return $items;
  }
  
  public function getItemsByProductId($product_id) {
    $orderItems = SimpleEcommCartCommon::getTableName('order_items');
	if($product_id == '0') $sql = "SELECT * from $orderItems";
    else $sql = "SELECT * from $orderItems where product_id = $product_id";
    $items = $this->_db->get_results($sql);
    return $items;
  }
   public function getOrdersByProductId($product_id) {
    $orderItems = SimpleEcommCartCommon::getTableName('order_items');
	$orders = SimpleEcommCartCommon::getTableName('orders');
	$sql="SELECT  o.* FROM $orders o INNER JOIN $orderItems oi ON o.id = oi.order_id where oi.product_id=$product_id";
    $items = $this->_db->get_results($sql);
    return $items;
  }
  public function getOrdersByMonthAll($month,$year) { 
	$orders = SimpleEcommCartCommon::getTableName('orders');
	$sql="SELECT * FROM $orders WHERE MONTH(ordered_on) = $month and YEAR(ordered_on) = $year";
    $items = $this->_db->get_results($sql);
    return $items;
  }
  public function getOrdersByMonth($month,$year) { 
	$orders = SimpleEcommCartCommon::getTableName('orders');
	$sql="SELECT * FROM $orders WHERE (payment_status = 'Complete' or  payment_status = 'Refund') and MONTH(ordered_on) = $month and YEAR(ordered_on) = $year";
    $items = $this->_db->get_results($sql);
    return $items;
  }
 public function getCompletedOrdersByMonth($month,$year) { 
	$orders = SimpleEcommCartCommon::getTableName('orders');
	$sql="SELECT * FROM $orders WHERE payment_status='Complete' and MONTH(ordered_on) = $month and YEAR(ordered_on) = $year";
    $items = $this->_db->get_results($sql);
    return $items;
  } 
  public function getOrdersByDateRange($start_date,$end_date) { 
	$orders = SimpleEcommCartCommon::getTableName('orders');
	$sql="SELECT * FROM $orders WHERE  ordered_on >= '$start_date' and  ordered_on <= '$end_date'";
	 
    $items = $this->_db->get_results($sql);
    return $items;
  } 
 public function getOrdersByPaymentStatusAndMethod($payment_status,$payment_method,$short_by,$order_by)
 {
 	$orders = SimpleEcommCartCommon::getTableName('orders');
	$sql="SELECT * FROM $orders ";
	if($payment_status == "allorders" && $payment_method == "all")
	{
		//do nothing
	}
	else
	{
		$whereCond="";
		if($payment_status!="allorders")
		{
			$whereCond.="payment_status='$payment_status'"; 
		}
		if($payment_method!="all")
		{
			if(strlen($whereCond)>0)$whereCond.=" AND ";
			$whereCond.=" payment_method='$payment_method'";
		}
			
		
		$sql.=" WHERE ".$whereCond;
	}
	 
	$sql.= " ORDER BY $short_by $order_by ";
	
	$items = $this->_db->get_results($sql);
    return $items;
 }
 public function getTopTenOrders()
 {
 	$orders = SimpleEcommCartCommon::getTableName('orders');
	$sql="SELECT * FROM $orders ORDER BY ordered_on desc LIMIT 10";
	//echo $sql;
	$items = $this->_db->get_results($sql);
    return $items;
 }
  public function updateStatus($status) {
    if($this->id > 0) {
      $data['status'] = $status;
      $this->_db->update($this->_tableName, $data, array('id' => $this->id), array('%s'));
      return $status;
    }
    return false;
  }
  public function updateDeliveryStatus($status) {
    if($this->id > 0) {
      $data['delivery_status'] = $status;
      $this->_db->update($this->_tableName, $data, array('id' => $this->id), array('%s'));
      return $status;
    }
    return false;
  }
  public function updatePaymentStatus($status) {
    if($this->id > 0) {
      $data['payment_status'] = $status;
      $this->_db->update($this->_tableName, $data, array('id' => $this->id), array('%s'));
      return $status;
    }
    return false;
  }
  public function deleteMe() {
    if($this->id > 0) {
      
      // Delete attached Gravity Forms if they exist
      $items = $this->getItems();
      foreach($items as $item) {
        if(!empty($item->form_entry_ids)) {
          $entryIds = explode(',', $item->form_entry_ids);
          if(is_array($entryIds)) {
            foreach($entryIds as $entryId) {
              RGFormsModel::delete_lead($entryId);
            }
          } 
        }
      }
      
      // Delete order items
      $orderItems = SimpleEcommCartCommon::getTableName('order_items');
      $sql = "DELETE from $orderItems where order_id = $this->id";
      $this->_db->query($sql);
      
      // Delete the order
      $sql = "DELETE from $this->_tableName where id = $this->id";
      $this->_db->query($sql);
    }
  }
  
  public function hasShippingInfo() {
    return strlen(trim($this->ship_first_name) . trim($this->ship_last_name) . trim($this->ship_address)) > 0;
  }
  public function loadByDuid($duid) {
    $itemsTable = SimpleEcommCartCommon::getTableName('order_items');
    $sql = "SELECT order_id from $itemsTable where duid = '$duid'";
    $id = $this->_db->get_var($sql);
    $this->load($id);
    return $this->id;
  }
}
