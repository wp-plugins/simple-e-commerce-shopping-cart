<?php
class SimpleEcommCartShippingVariation extends SimpleEcommCartModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = SimpleEcommCartCommon::getTableName('shipping_variation');
    parent::__construct($id);
  }
  
}