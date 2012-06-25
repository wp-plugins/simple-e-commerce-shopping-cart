<?php
class SimpleEcommCartShippingRule extends SimpleEcommCartModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = SimpleEcommCartCommon::getTableName('shipping_rules');
    parent::__construct($id);
  }
  
}