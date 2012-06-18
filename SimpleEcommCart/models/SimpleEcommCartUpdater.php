<?php
class SimpleEcommCartUpdater {
  
  protected $_version;
  protected $_orderNumber;
  protected $_motherShipUrl = 'http://simpleecommcartbasic.wordpress.com//simpleecommcart-latest.php';
  
  public function __construct() {
    $setting = new SimpleEcommCartSetting();
    $this->_version = SimpleEcommCartSetting::getValue('version');
    $this->_orderNumber = SimpleEcommCartSetting::getValue('order_number');
  }
  
  /**
   * Check the currently running versoin against the version of the latest release.
   * 
   * @return mixed The new version number if there is a new version, otherwise false.
   */
  public function newVersion() {
    $setting = new SimpleEcommCartSetting();
    $orderNumber = SimpleEcommCartSetting::getValue('orderNumber');

    $versionCheck = $this->_motherShipUrl . "?task=getLatestVersion&id=$this->_orderNumber";
    $newVersion = false;
    
    $latest = @file_get_contents($versionCheck);
    if(!empty($latest)) {
      if($latest != $this->_version) {
        $newVersion = $latest;
      }
    }
    
    return $newVersion;
  }
  
  public function getCallHomeUrl() {
    return $this->_motherShipUrl;
  }
  
  public function getOrderNumber() {
    return $this->_orderNumber;
  }

}