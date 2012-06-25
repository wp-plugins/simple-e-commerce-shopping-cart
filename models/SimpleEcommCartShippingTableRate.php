<?php
class SimpleEcommCartShippingTableRate extends SimpleEcommCartModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = SimpleEcommCartCommon::getTableName('shipping_table_rate');
    parent::__construct($id);
  }
  public function getTableRates() { 
    $sql = "SELECT * from  ".$this->_tableName." order by total_cart_price";
	 
    $items = $this->_db->get_results($sql);
    return $items;
  }
}