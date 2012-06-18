<?php
class SimpleEcommCartProCommon {
  
  public static function checkUpdate(){
    if(IS_ADMIN) {
      $pluginName = "simpleecommcart/simpleecommcart.php";
      $option = function_exists('get_transient') ? get_transient("update_plugins") : get_option("update_plugins");
      $option = self::getUpdatePluginsOption($option);
      
      if(function_exists('set_transient')) {
        SimpleEcommCartCommon::log('Setting Transient Value: ' . print_r($option->response[$pluginName], true));
        set_transient("update_plugins", $option);
      }
    }
  }
  
  public static function getUpdatePluginsOption($option) {
    $pluginName = "simpleecommcart/simpleecommcart.php";
    $versionInfo = SimpleEcommCartProCommon::getVersionInfo();
    if(is_array($versionInfo)) {

      $simpleecommcartOption = isset($option->response[$pluginName]) ? $option->response[$pluginName] : '';
      if(empty($simpleecommcartOption)) {
        $option->response[$pluginName] = new stdClass();
      }

      $setting = new SimpleEcommCartSetting();
      $orderNumber = SimpleEcommCartSetting::getValue('order_number');
      $currentVersion = SimpleEcommCartSetting::getValue('version');
      if(version_compare($currentVersion, $versionInfo['version'], '<')) {
        $newVersion = $versionInfo['version'];
        SimpleEcommCartCommon::log("New Version Available: $currentVersion < $newVersion");
        $option->response[$pluginName]->url = "http://simpleecommcartbasic.wordpress.com/";
        $option->response[$pluginName]->slug = "simpleecommcart";
        $option->response[$pluginName]->package = str_replace("{KEY}", $orderNumber, $versionInfo["url"]);
        $option->response[$pluginName]->new_version = $versionInfo["version"];
        $option->response[$pluginName]->id = "0";
      }
      else {
        unset($option->response[$pluginName]);
      }
    }
    return $option;
  }
  
  public static function getVersionInfo() {
    $callback = "http://wordpress.org";
    $versionInfo = false;
    $setting = new SimpleEcommCartSetting();
    $orderNumber = SimpleEcommCartSetting::getValue('order_number');
    if($orderNumber) {
      $body = 'key=$orderNumber';
      $options = array('method' => 'POST', 'timeout' => 3, 'body' => $body);
      $options['headers'] = array(
          'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
          'Content-Length' => strlen($body),
          'User-Agent' => 'WordPress/' . get_bloginfo("version"),
          'Referer' => get_bloginfo("url")
      );
      $callBackLink = $callback . "/simpleecommcart-version.php?" . self::getRemoteRequestParams();
      SimpleEcommCartCommon::log("Callback link: $callBackLink");
      $raw = wp_remote_request($callBackLink, $options);
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Version info from remote request: " . print_r($raw, 1));
      if (!is_wp_error($raw) && 200 == $raw['response']['code']) {
        $info = explode("~", $raw['body']);
        $versionInfo = array("isValidKey" => $info[0], "version" => $info[1], "url" => $info[2]);
      }
    }
    return $versionInfo;      
  }
  
  public static function getRemoteRequestParams() {
    $params = false;
    $setting = new SimpleEcommCartSetting();
    $orderNumber = SimpleEcommCartSetting::getValue('order_number');
    if(!$orderNumber) {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Order number not available");
    }
    $version = SimpleEcommCartSetting::getValue('version');
    if(!$version) {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Version number not available");
    }
    if($orderNumber && $version) {
      global $wpdb;
      $versionName = 'pro';
      $params = sprintf("task=getLatestVersion&pn=SimpleEcommCart&key=%s&v=%s&vnm=%s&wp=%s&php=%s&mysql=%s&ws=%s", 
        urlencode($orderNumber), 
        urlencode($version), 
        urlencode($versionName),
        urlencode(get_bloginfo("version")), 
        urlencode(phpversion()), 
        urlencode($wpdb->db_version()),
        urlencode(get_bloginfo("url"))
      );
    }
    return $params;
  }
  
  public static function showChangelog() {
    if($_REQUEST["plugin"] == "simpleecommcart") {
      $setting = new SimpleEcommCartSetting();
      $orderNumber = SimpleEcommCartSetting::getValue('order_number');
      
      if($orderNumber) {
        $raw = file_get_contents('http://simpleecommcart.com/latest-simpleecommcart');
        $raw = str_replace("\n", '', $raw);
        $matches = array();
        preg_match('/<div class="entry">(.+?)<\/div>/m', $raw, $matches);
        $raw = "<h1>SimpleEcommCart</h1>$matches[1]";
        echo $raw;
      }
      
      exit;
    }
  }
  
  public static function getUpsServices() {
    
    $usaServices = array(
      'UPS Next Day Air' => '01',
      'UPS Second Day Air' => '02',
      'UPS Ground' => '03',
      'UPS Worldwide Express' => '07',
      'UPS Worldwide Expedited' => '08',
      'UPS Standard' => '11',
      'UPS Three-Day Select' => '12',
      'UPS Next Day Air Early A.M.' => '14',
      'UPS Worldwide Express Plus' => '54',
      'UPS Second Day Air A.M.' => '59',
      'UPS Saver' => '65'
    );
    
    $internationalServices = array(
      'UPS Express' =>	'01',
      'UPS Expedited' =>	'02',
      'UPS Worldwide Express' =>	'07',
      'UPS Worldwide Expedited' =>	'08',
      'UPS Standard' =>	'11',
      'UPS Three-Day Select' =>	'12',
      'UPS Saver' =>	'13',
      'UPS Express Early A.M.' =>	'14',
      'UPS Worldwide Express Plus' =>	'54',
      'UPS Saver' =>	'65'
    );
    
    $homeCountryCode = 'US';
    $setting = new SimpleEcommCartSetting();
    $home = SimpleEcommCartSetting::getValue('home_country');
    if($home) {
      list($homeCountryCode, $name) = explode('~', $home);
    }
    
    $services = $homeCountryCode == 'US' ? $usaServices : $internationalServices;
    
    return $services;
  }
  
  public static function getUspsServices() {
    $usaServices = array(
      'USPS First-Class Mail' => 'First-Class Mail Package',
      'USPS Express Mail' => 'Express Mail',
      'USPS Priority Mail' => 'Priority Mail',
      'USPS Parcel Post' => 'Parcel Post',
      'USPS Media Mail' => 'Media Mail',
      'USPS Express Mail International' => 'Express Mail International',
      'USPS Priority Mail International' => 'Priority Mail International',
      'USPS First-Class Mail International' => 'First-Class Mail International Package'
    );
    
    return $usaServices;
  }
  
  public function getLogoutUrl() {
    $url = SimpleEcommCartCommon::getCurrentPageUrl();
    $pgs = get_posts('numberposts=1&post_type=any&meta_key=simpleecommcart_member&meta_value=logout');
    if(count($pgs)) {
      $url = get_permalink($pgs[0]->ID);
    }
    return $url;
  }
  
}