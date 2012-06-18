<?php
class SimpleEcommCartShippingRate extends SimpleEcommCartModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = SimpleEcommCartCommon::getTableName('shipping_rates');
    parent::__construct($id);
  }
  
}