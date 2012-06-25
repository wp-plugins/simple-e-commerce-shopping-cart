<?php
class SimpleEcommCartProductCategory extends SimpleEcommCartModelAbstract {
  
  public function __construct($id=null) {
    $this->_tableName = SimpleEcommCartCommon::getTableName('product_categories');
    parent::__construct($id);
  } 
  public function getCategory($id) { 
    $sql = "SELECT * from ".$this->_tableName ." where id = $id";
    $items = $this->_db->get_results($sql);
    return $items;
  }
  public function getProductCount( $id) { 
  	$product_table_name = SimpleEcommCartCommon::getTableName('products');
    $sql = "SELECT COUNT(*) from ".$product_table_name ." where category =  $id"; 
	 
    $itemCount = $this->_db->get_var($sql);
    return $itemCount;
  }
}