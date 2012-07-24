<?php
class SimpleEcommCartShippingWeightRate extends SimpleEcommCartModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = SimpleEcommCartCommon::getTableName('shipping_weight_rate');
    parent::__construct($id);
  }
  public function getWeightRates() { 
    $sql = "SELECT * from  ".$this->_tableName." order by total_weight";
	 
    $items = $this->_db->get_results($sql);
    return $items;
  }
}