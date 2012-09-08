<?php
class SimpleEcommCart {
  
  public function install() {
    global $wpdb;
    $prefix = SimpleEcommCartCommon::getTablePrefix();
    $sqlFile = SIMPLEECOMMCART_PATH . '/sql/database.sql';
    $sql = str_replace('[prefix]', $prefix, file_get_contents($sqlFile));
    $queries = explode(";\n", $sql);
    $wpdb->hide_errors();
    foreach($queries as $sql) {
      if(strlen($sql) > 5) {
        $wpdb->query($sql);
        SimpleEcommCartCommon::log("Running: $sql");
      }
    }
    require_once(SIMPLEECOMMCART_PATH . "/geninitpages.php");

    
	$this->installDefaultSettings();
  }
  public function installDefaultSettings()
  {
  	// Set the version number for this version of SimpleEcommCart
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartSetting.php");
    SimpleEcommCartSetting::setValue('version', SIMPLEECOMMCART_VERSION_NUMBER);
    
    // Look for hard coded order number
    if(SIMPLEECOMMCART_PRO && SIMPLEECOMMCART_ORDER_NUMBER !== false) {
      SimpleEcommCartSetting::setValue('order_number', SIMPLEECOMMCART_ORDER_NUMBER);
      $versionInfo = SimpleEcommCartProCommon::getVersionInfo();
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Trying to register order number: " . 
        SIMPLEECOMMCART_ORDER_NUMBER . print_r($versionInfo, true));
      if(!$versionInfo) {
        SimpleEcommCartSetting::setValue('order_number', '');
      }
    }
	
	//track inventory
	 SimpleEcommCartSetting::setValue('track_inventory', '1');
	 
	 //api key 
	 if(SimpleEcommCartSetting::getValue('webservice_iphone_api_key') == NULL) 
	 	SimpleEcommCartSetting::setValue('webservice_iphone_api_key', 'wsiak123456');
	 
	 	
	 
	 
	 $upload_dir = wp_upload_dir();
 	 $upload_base_path = $upload_dir['basedir'];
 
     //create wpsimpleecommcart directory inside wpsimpleecommcart
     $wpsimpleecommcart_path = $upload_base_path.DIRECTORY_SEPARATOR.'simpleecommcart'.DIRECTORY_SEPARATOR;
     if (!is_dir($wpsimpleecommcart_path)) {
      mkdir($wpsimpleecommcart_path);
     } 
	 
	 //digital product forlder path 
	  $digital_product_folder_path = $upload_dir['basedir'].DIRECTORY_SEPARATOR.'simpleecommcart'.DIRECTORY_SEPARATOR.'digitalproduct'.DIRECTORY_SEPARATOR;
	  if (!is_dir($digital_product_folder_path)) {
    	mkdir($digital_product_folder_path);
	  }
 
	 SimpleEcommCartCommon::log($digital_product_folder_path);
	 SimpleEcommCartSetting::setValue('product_folder', $digital_product_folder_path);
	 
	 //tmp forlder path
	 $tmp_product_folder_path = $upload_dir['basedir'].DIRECTORY_SEPARATOR.'simpleecommcart'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR;
	 if (!is_dir($tmp_product_folder_path)) {
    	mkdir($tmp_product_folder_path);
	 } 
	 SimpleEcommCartCommon::log($tmp_product_folder_path);
	 SimpleEcommCartSetting::setValue('tmp_folder', $tmp_product_folder_path);
	 
	 
	 //inventory stock settings
	 SimpleEcommCartSetting::setValue('out_of_stock_notification', '1');
	 SimpleEcommCartSetting::setValue('out_of_stock_threshhold', '0');
	 SimpleEcommCartSetting::setValue('low_stock_notification', '0'); 
	 
	 //default liscence n
	 SimpleEcommCartSetting::setValue('order_number', '0000000'); 
	 
	 //home country
	 if(SimpleEcommCartSetting::getValue('home_country') == NULL) 
	 	SimpleEcommCartSetting::setValue('home_country', 'US~United States'); 
	 
	 //store type
	 if(SimpleEcommCartSetting::getValue('store_type') == NULL) 
	 	SimpleEcommCartSetting::setValue('store_type', 'mixed'); 
	  
	 //store page settings
	 if(SimpleEcommCartSetting::getValue('display_products') == NULL) 
	 	SimpleEcommCartSetting::setValue('display_products', 'list'); 
	 
	 //status option
	 if(SimpleEcommCartSetting::getValue('delivery_status_options') == NULL) 
	 	SimpleEcommCartSetting::setValue('delivery_status_options', 'Pending,Shipped,Complete'); 
	 if(SimpleEcommCartSetting::getValue('status_options') == NULL) 
	 	SimpleEcommCartSetting::setValue('status_options', 'Pending,Complete'); 
	 if(SimpleEcommCartSetting::getValue('payment_status_options') == NULL) 
	 	SimpleEcommCartSetting::setValue('payment_status_options', 'Pending,Complete,Refund');  
	 
	 if(SimpleEcommCartSetting::getValue('terms_and_condition') == NULL) 
	 	SimpleEcommCartSetting::setValue('terms_and_condition', 'no');  
	 
	 if(SimpleEcommCartSetting::getValue('disable_caching') == NULL) 
	 	SimpleEcommCartSetting::setValue('disable_caching', '0');  
	 
	 //default shipping settings
	 if(SimpleEcommCartSetting::getValue('shipping_options_radio') == NULL) 
	 	SimpleEcommCartSetting::setValue('shipping_options_radio', '1');
	
	 if(SimpleEcommCartSetting::getValue('shipping_options_flat_rate_option') == NULL) 
	 	SimpleEcommCartSetting::setValue('shipping_options_flat_rate_option', '1');
	 
	 //default tax settings
	 $t_data['option']='1';
	 $t_data['flat_rate']='';
	 $t_data['logic']='2';
	 $tax_settings_forSave = serialize($t_data);
	 if(SimpleEcommCartSetting::getValue('tax_settings') == NULL) 
	 	SimpleEcommCartSetting::setValue('tax_settings', $tax_settings_forSave);
	 
	 //default email settings
	 if(SimpleEcommCartSetting::getValue('email_from_name') == NULL) 
	 	SimpleEcommCartSetting::setValue('email_from_name ', get_option('blogname'));
	 if(SimpleEcommCartSetting::getValue('email_from_address') == NULL) 
	 	SimpleEcommCartSetting::setValue('email_from_address ', get_option('admin_email'));
	 
	 if(SimpleEcommCartSetting::getValue('email_sent_on_purchase') == NULL) 
	 	SimpleEcommCartSetting::setValue('email_sent_on_purchase ', 'on');
	 if(SimpleEcommCartSetting::getValue('email_sent_on_purchase_subject') == NULL) 
		 SimpleEcommCartSetting::setValue('email_sent_on_purchase_subject ', 'Thank you for your purchase');
	 
	 $mail_body="Dear {first_name} {last_name}
		 
Thank you for your purchase!
 {product_details}
Tax:{total_tax}
Shipping:{total_shipping}
Total:{total_minus_total_tax} 
Any items to be shipped will be processed as soon as possible, any items that can be downloaded can be  downloaded using the encrypted links below.
{product_link_digital_items_only}

Thanks";
		if(SimpleEcommCartSetting::getValue('email_sent_on_purchase_body') == NULL) 
			SimpleEcommCartSetting::setValue('email_sent_on_purchase_body ', $mail_body);
	 
  }
  public function init() {
    $this->loadCoreModels();
	$this->loadExternalModels();
    $this->initCurrencySymbols();
    
    // Verify that upgrade has been run
    if(IS_ADMIN) {
      $dbVersion = SimpleEcommCartSetting::getValue('version');
      if(version_compare(SIMPLEECOMMCART_VERSION_NUMBER, $dbVersion)) {
        $this->install();
      }
    }
    
    // Set default admin page roles if there isn't any
    $pageRoles = SimpleEcommCartSetting::getValue('admin_page_roles');
    if(empty($pageRoles)){
     $defaultPageRoles = array(
        'orders' => 'edit_pages',
        'products' => 'manage_options',
        'paypal-subscriptions' => 'manage_options',
        'inventory' => 'manage_options',
        'promotions' => 'manage_options',
        'shipping' => 'manage_options',
        'settings' => 'manage_options',
        'reports' => 'manage_options',
        'accounts' => 'manage_options',
		'tax' => 'manage_options'
     ); SimpleEcommCartSetting::setValue('admin_page_roles',serialize($defaultPageRoles)); 
    }
    
    // Define debugging and testing info
    $simpleecommcartLogging = SimpleEcommCartSetting::getValue('enable_logging') ? true : false;
    $sandbox = SimpleEcommCartSetting::getValue('paypal_sandbox') ? true : false;
    define("SIMPLEECOMMCART_DEBUG", $simpleecommcartLogging);
    define("SANDBOX", $sandbox);
    
    // Ajax actions
    if(SIMPLEECOMMCART_PRO) {
      add_action('wp_ajax_check_inventory_on_add_to_cart', array('SimpleEcommCartAjax', 'checkInventoryOnAddToCart'));
      add_action('wp_ajax_nopriv_check_inventory_on_add_to_cart', array('SimpleEcommCartAjax', 'checkInventoryOnAddToCart'));
    }
    
    // Handle dynamic JS requests
    // See: http://ottopress.com/2010/dont-include-wp-load-please/ for why
    add_filter('query_vars', array($this, 'addJsTrigger'));
    add_action('template_redirect', array($this, 'jsTriggerCheck'));
    
    if(IS_ADMIN) {
      //add_action( 'admin_notices', 'simpleecommcart_data_collection' );
      add_action('admin_head', array( $this, 'registerBasicScripts'));

      if(strpos($_SERVER['QUERY_STRING'], 'page=simpleecommcart') !== false) {
        add_action('admin_head', array($this, 'registerAdminStyles'));
        add_action('admin_init', array($this, 'registerCustomScripts'));
      }
      
      add_action('admin_menu', array($this, 'buildAdminMenu'));
      add_action('admin_init', array($this, 'addEditorButtons'));
      add_action('admin_init', array($this, 'forceDownload'));
      add_action('wp_ajax_save_settings', array('SimpleEcommCartAjax', 'saveSettings'));
      
      if(SIMPLEECOMMCART_PRO) {
        add_action('wp_ajax_update_gravity_product_quantity_field', array('SimpleEcommCartAjax', 'updateGravityProductQuantityField'));
      }
      
       
      //Plugin update actions
      if(SIMPLEECOMMCART_PRO) {
        add_action('update_option__transient_update_plugins', array('SimpleEcommCartProCommon', 'checkUpdate'));             //used by WP 2.8
        add_filter('pre_set_site_transient_update_plugins', array('SimpleEcommCartProCommon', 'getUpdatePluginsOption'));    //used by WP 3.0
        add_action('install_plugins_pre_plugin-information', array('SimpleEcommCartProCommon', 'showChangelog'));
      }
    }
    else {
      $this->initShortcodes();
      $this->initCart();
      add_action('wp_enqueue_scripts', array('SimpleEcommCart', 'enqueueScripts'));

      if(SIMPLEECOMMCART_PRO) {
        add_action('wp_head', array($this, 'checkInventoryOnCheckout'));
        add_action('wp_head', array($this, 'checkShippingMethodOnCheckout'));
        add_action('wp_head', array($this, 'checkZipOnCheckout'));
        add_action('wp_head', array($this, 'checkTermsOnCheckout'));
        add_filter('wp_list_pages_excludes', array($this, 'hideStorePages'));
        
//        add_filter('wp_nav_menu_items', array($this, 'filterPrivateMenuItems'), 10, 2);
      }
      
      add_action('wp_head', array($this, 'displayVersionInfo'));
      add_action('template_redirect', array($this, 'dontCacheMeBro'));
      add_action('shutdown', array('SimpleEcommCartSession', 'touch'));
    }
    
    
    
    function simpleecommcart_data_collection(){
         global $current_screen;
         
         echo '<div class="updated">';
         echo '<script type="text/javascript">
          (function($){
            $(document).ready(function(){
              $("#simpleecommcartSendSurvey").click(function(){
                $.get("http://simpleecommcart.com/survey/",function(data){
                  alert(data)
                })
              })
            })
          })(jQuery);
         </script>  ';
         echo '<H3>SimpleEcommCart Usage Survey</h3>';
         echo '<p>To improve our customer experience, SimpleEcommCart would love for you to participate in an anonymous usage survey. This data will be sent one time, and does not contain any personal or identification information.</p>';
         echo '<p>Here\'s what is being sent:<br><br>';
         echo SimpleEcommCartCommon::showReportData();
         echo '<p><a id="simpleecommcartSendSurvey" class="button" href="#">Send</a> &nbsp;&nbsp;&nbsp; <a class="button" href="#">No thanks</a></p>';
         echo '</div>';
    }
    
    
    
    // ================================================================
    // = Intercept query string simpleecommcart tasks                          =
    // ================================================================
     
    // Logout the logged in user
    $isLoggedIn = SimpleEcommCartCommon::isLoggedIn();
    if(isset($_REQUEST['simpleecommcart-task']) && $_REQUEST['simpleecommcart-task'] == 'logout' && $isLoggedIn) {
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Intercepting SimpleEcommCart Logout task");
      $url = SimpleEcommCartProCommon::getLogoutUrl();
      SimpleEcommCartAccount::logout($url);
    }
    
    if($_SERVER['REQUEST_METHOD'] == 'GET' &&  SimpleEcommCartCommon::getVal('task') == 'member_download') {
      if(SimpleEcommCartCommon::isLoggedIn()) {
        $path = $_GET['path'];
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Attempting a member download file request: $path");
        SimpleEcommCartCommon::downloadFile($path);
      }
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'GET' && SimpleEcommCartCommon::getVal('task') == 'add-to-cart-anchor') {
      $options = null;
      if(isset($_GET['options'])) {
        $options = SimpleEcommCartCommon::getVal('options');
      }
      SimpleEcommCartSession::get('SimpleEcommCartCart')->addItem(SimpleEcommCartCommon::getVal('simpleecommcartItemId'), 1, $options);
    }
    
  }
  
  public function displayVersionInfo() {
    if(SIMPLEECOMMCART_PRO) {
      echo '<meta name="SimpleEcommCartVersion" content="Professional ' . SimpleEcommCartSetting::getValue('version') . '" />' . "\n";
    }
    else {
      echo '<meta name="SimpleEcommCartVersion" content="Lite ' . SimpleEcommCartSetting::getValue('version') . '" />' . "\n";
    }
  }
  
  /*public function filterPrivateMenuItems($menuItems, $args=null) {
    $links = explode("</li>", $menuItems);
    $filteredMenuItems = '';
    
    if(SimpleEcommCartCommon::isLoggedIn()) {
      // User is logged in so hide the guest only pages
      $pageIds = SimpleEcommCartAccessManager::getGuestOnlyPageIds();
    }
    else {
      // User is not logged in so hide the private pages
      $pageIds = SimpleEcommCartAccessManager::getPrivatePageIds();
    }
    
    foreach($links as $link) {
      $addLink = true;
      $link = trim($link);
      
      if(empty($link)) {
        $addLink = false;
      }
      else {
        foreach($pageIds as $pageId) {
          $permalink = get_permalink($pageId);
          if(strpos($link, $permalink) !== false) {
            $addLink = false;
            break;
          }
        }
      }
         
      if($addLink) {
        $filteredMenuItems .= "$link</li>";
      }
    }
    
    return $filteredMenuItems;
  }*/
  
  public static function enqueueScripts() {
    $url = SIMPLEECOMMCART_URL . '/simpleecommcart.css';
    wp_enqueue_style('simpleecommcart-css', $url, null, SIMPLEECOMMCART_VERSION_NUMBER, 'all');

    if($css = SimpleEcommCartSetting::getValue('styles_url')) {
      wp_enqueue_style('simpleecommcart-custom-css', $css, null, SIMPLEECOMMCART_VERSION_NUMBER, 'all');
    }
    
    // Include the simpleecommcart javascript library
    $path = SIMPLEECOMMCART_URL . '/js/simpleecommcart-library.js';
    wp_enqueue_script('simpleecommcart-library', $path, array('jquery'), SIMPLEECOMMCART_VERSION_NUMBER);
  }
  
  public function loadCoreModels() {
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartBaseModelAbstract.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartModelAbstract.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartSession.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartSetting.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartAdmin.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartAjax.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartLog.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartProduct.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartCartItem.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartCart.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartCartWidget.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartCheckoutThrottle.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartException.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartTaxRate.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartOrder.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartPromotion.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartShippingMethod.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartShippingRate.php");
	require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartShippingWeightRate.php");
	require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartShippingTableRate.php"); 
	require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartShippingVariation.php"); 
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartShippingRule.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartShortcodeManager.php");
    require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartButtonManager.php");
	require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartProductCategory.php");
	require_once(SIMPLEECOMMCART_PATH . "/payment_gateways/SimpleEcommCartGatewayAbstract.php");
    
	
    if(SIMPLEECOMMCART_PRO) {
	  require_once(SIMPLEECOMMCART_PATH . "/advanced/models/SimpleEcommCartJsonRPCClient.php"); 
    }

    require_once(SIMPLEECOMMCART_PATH . "/payment_gateways/SimpleEcommCartGatewayAbstract.php");
     
  }   
  public function loadExternalModels() {
    	require_once(SIMPLEECOMMCART_PATH . "/libchart/classes/libchart.php"); 
  }
  public function initCurrencySymbols() {
    $cs = SimpleEcommCartSetting::getValue('SIMPLEECOMMCART_CURRENCY_SYMBOL');
    $cs = $cs ? $cs : '$';
    $cst = SimpleEcommCartSetting::getValue('SIMPLEECOMMCART_CURRENCY_SYMBOL_text');
    $cst = $cst ? $cst : '$';
    //$ccd = SimpleEcommCartSetting::getValue('currency_code');
	$ccd = SimpleEcommCartSetting::getValue('SIMPLEECOMMCART_CURRENCY_SYMBOL_text');
    $ccd = $ccd ? $ccd : 'USD';
    define("SIMPLEECOMMCART_CURRENCY_SYMBOL", $cs);
    define("SIMPLEECOMMCART_CURRENCY_SYMBOL_TEXT", $cst);
    define("CURRENCY_CODE", $ccd);
  }
  
  public function registerBasicScripts() {
    ?><script type="text/javascript">var wpurl = '<?php echo esc_js( home_url('/') ); ?>';</script><?php
  }
  
  public function registerCustomScripts() {
    if(strpos($_SERVER['QUERY_STRING'], 'page=simpleecommcart') !== false) {
      $path = SIMPLEECOMMCART_URL . '/js/ajax-setting-form.js';
      wp_enqueue_script('ajax-setting-form', $path);

      // Include jquery-multiselect and jquery-ui
      wp_enqueue_script('jquery');
      wp_enqueue_script('jquery-ui-core');
      wp_enqueue_script('jquery-ui-sortable');
	  wp_enqueue_script('jquery-ui-dialog');
      $path = SIMPLEECOMMCART_URL . '/js/ui.multiselect.js';
      wp_enqueue_script('jquery-multiselect', $path, null, null, true);
 
      // Include the jquery table quicksearch library
      $path = SIMPLEECOMMCART_URL . '/js/jquery.quicksearch.js';
      wp_enqueue_script('quicksearch', $path, array('jquery'));
    }
  }
  
  public function registerAdminStyles() {
    if(strpos($_SERVER['QUERY_STRING'], 'page=simpleecommcart') !== false) {
      $widgetCss = WPURL . '/wp-admin/css/widgets.css';
      echo "<link rel='stylesheet' type='text/css' href='$widgetCss' />\n";

    	$adminCss = SIMPLEECOMMCART_URL . '/admin/admin-styles.css';
      echo "<link rel='stylesheet' type='text/css' href='$adminCss' />\n";

      $uiCss = SIMPLEECOMMCART_URL . '/admin/jquery-ui-1.7.1.custom.css';
      echo "<link rel='stylesheet' type='text/css' href='$uiCss' />\n";
    }
  }
  
  public function dontCacheMeBro() {
    if(!IS_ADMIN) {
      global $post;
      $sendHeaders = false;
      if($disableCaching = SimpleEcommCartSetting::getValue('disable_caching')) {
        if($disableCaching === '1') {
          $cartPage = get_page_by_path('store/cart');
          $checkoutPage = get_page_by_path('store/checkout');
          $cartPages = array($checkoutPage->ID, $cartPage->ID);
          if( isset( $post->ID ) && in_array($post->ID, $cartPages) ) {
            $sendHeaders = true;
            //SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] set to send no cache headers for cart pages");
          }
          else {
            if(!isset($post->ID)) {
              SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] The POST ID is not set");
            }
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Not a cart page! Therefore need to set the headers to disable cache");
          }
        }
        elseif($disableCaching === '2') {
          $sendHeaders = true;
        }
      }
      
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Disable caching is: $disableCaching");
      
      if($sendHeaders) {
        // SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Sending no cache headers");
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', FALSE);
        header('Pragma: no-cache');
      }
      
    }
  }

  /**
   * Put SimpleEcommCart in the admin menu
   */
  public function buildAdminMenu() {
    $icon = SIMPLEECOMMCART_URL . '/images/simpleecommcart_logo_16.gif';
    $pageRoles = SimpleEcommCartSetting::getValue('admin_page_roles');
    $pageRoles = unserialize($pageRoles);
    
    add_menu_page('Simple eCommerce', 'Simple eCommerce', $pageRoles['reports'], 'simpleecommcart_admin', null, $icon);
    add_submenu_page('simpleecommcart_admin', __('Reports', 'simpleecommcart'), __('Reports', 'simpleecommcart'), $pageRoles['reports'], 'simpleecommcart_admin', array('SimpleEcommCartAdmin', 'reportsPage'));
	
	
	
	
	
    add_submenu_page('simpleecommcart_admin', __('Add/Edit Products', 'simpleecommcart'), __('Add/Edit Products', 'simpleecommcart'), $pageRoles['products'], 'simpleecommcart-products', array('SimpleEcommCartAdmin', 'productsPage'));
   /* add_submenu_page('simpleecommcart_admin', __('PayPal Subscriptions', 'simpleecommcart'), __('PayPal Subscriptions', 'simpleecommcart'), $pageRoles['paypal-subscriptions'], 'simpleecommcart-paypal-subscriptions', array('SimpleEcommCartAdmin', 'paypalSubscriptions'));*/
     add_submenu_page('simpleecommcart_admin', __('Inventory', 'simpleecommcart'), __('Inventory', 'simpleecommcart'), $pageRoles['inventory'], 'simpleecommcart-inventory', array('SimpleEcommCartAdmin', 'inventoryPage')); 
    add_submenu_page('simpleecommcart_admin', __('Coupons', 'simpleecommcart'), __('Coupons', 'simpleecommcart'), $pageRoles['promotions'], 'simpleecommcart-promotions', array('SimpleEcommCartAdmin', 'promotionsPage'));
	add_submenu_page('simpleecommcart_admin', __('Tax', 'simpleecommcart'), __('Tax', 'simpleecommcart'), $pageRoles['tax'], 'simpleecommcart-tax', array('SimpleEcommCartAdmin', 'taxPage'));
    add_submenu_page('simpleecommcart_admin', __('Shipping', 'simpleecommcart'), __('Shipping', 'simpleecommcart'), $pageRoles['shipping'], 'simpleecommcart-shipping', array('SimpleEcommCartAdmin', 'shippingPage'));
    
   add_submenu_page('simpleecommcart_admin', __('Orders', 'simpleecommcart'), __('Orders', 'simpleecommcart'), $pageRoles['orders'], 'simpleecommcart-orders', array('SimpleEcommCartAdmin', 'ordersPage'));
  /*  add_submenu_page('simpleecommcart_admin', __('Accounts', 'simpleecommcart'), __('Accounts', 'simpleecommcart'), $pageRoles['accounts'], 'simpleecommcart-accounts', array('SimpleEcommCartAdmin', 'accountsPage'));*/
	
	add_submenu_page('simpleecommcart_admin', __('Settings', 'simpleecommcart'), __('Settings', 'simpleecommcart'), $pageRoles['settings'], 'simpleecommcart-settings', array('SimpleEcommCartAdmin', 'settingsPage'));
  }
  

  /**
   * Check inventory levels when accessing the checkout page.
   * If inventory is insufficient place a warning message in SimpleEcommCartSession::get('SimpleEcommCartInventoryWarning')
   */
  public function checkInventoryOnCheckout() {
    if($_SERVER['REQUEST_METHOD'] == 'GET') {
      global $post;
      $checkoutPage = get_page_by_path('store/checkout');
      if( isset( $post->ID ) && $post->ID == $checkoutPage->ID) {
        $inventoryMessage = SimpleEcommCartSession::get('SimpleEcommCartCart')->checkCartInventory();
        if(!empty($inventoryMessage)) { SimpleEcommCartSession::set('SimpleEcommCartInventoryWarning', $inventoryMessage); }
      }
    }
  }
  
  public function checkShippingMethodOnCheckout() {
    if($_SERVER['REQUEST_METHOD'] == 'GET') {
      global $post;
      $checkoutPage = get_page_by_path('store/checkout');
      
      if(!SimpleEcommCartSetting::getValue('use_live_rates')) {
        SimpleEcommCartSession::drop('SimpleEcommCartLiveRates');
      }
      
      if( isset( $post->ID ) && $post->ID == $checkoutPage->ID) {
        if(SimpleEcommCartSession::get('SimpleEcommCartLiveRates') && get_class(SimpleEcommCartSession::get('SimpleEcommCartLiveRates')) == 'SimpleEcommCartLiveRates') {
          if(!SimpleEcommCartSession::get('SimpleEcommCartLiveRates')->hasValidShippingService()) {
            SimpleEcommCartSession::set('SimpleEcommCartShippingWarning', true);
            $viewCartPage = get_page_by_path('store/cart');
            $viewCartLink = get_permalink($viewCartPage->ID);
            wp_redirect($viewCartLink);
            exit();
          }
        }
      }
    }
  }
  
  public function checkTermsOnCheckout() {
      if(SimpleEcommCartSetting::getValue('require_terms') == 1) {
        global $post;
        $checkoutPage = get_page_by_path('store/checkout');
        $cartPage = get_page_by_path('store/cart');
        if( isset( $post->ID ) && $post->ID == $checkoutPage->ID || $post->ID == $cartPage->ID) {
          
          $sendBack = false;
         
          if($post->ID == $cartPage->ID && isset($_POST['terms_acceptance']) && $_POST['terms_acceptance'] == "I_Accept"){
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Terms are accepted, forwarding to checkout");
            SimpleEcommCartSession::set("terms_acceptance","accepted",true);
            $link = get_permalink($checkoutPage->ID);
            $sendBack = true;
          }
          elseif($post->ID == $checkoutPage->ID && (!SimpleEcommCartSession::get('terms_acceptance') || SimpleEcommCartSession::get('terms_acceptance') != "accepted")) {
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Terms not accepted, send back to cart");
            $link = get_permalink($cartPage->ID);
            $sendBack = true;
          }
        
          if($sendBack) {
            wp_redirect($link);
            exit();
          }
          
        } // End if checkout or cart page
      } // End if require terms
  }
  
  public function checkZipOnCheckout() {
    if(SIMPLEECOMMCART_PRO && $_SERVER['REQUEST_METHOD'] == 'GET') {
      if(SimpleEcommCartSetting::getValue('use_live_rates') && SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipping()) {
        global $post;
        $checkoutPage = get_page_by_path('store/checkout');
        if( isset( $post->ID ) && $post->ID == $checkoutPage->ID) {
          $cartPage = get_page_by_path('store/cart');
          $link = get_permalink($cartPage->ID);
          $sendBack = false;
          
          if(!SimpleEcommCartSession::get('simpleecommcart_shipping_zip')) {
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live rate warning: No shipping zip in session");
            SimpleEcommCartSession::set('SimpleEcommCartZipWarning', true);
            $sendBack = true;
          }
          elseif(!SimpleEcommCartSession::get('simpleecommcart_shipping_country_code')) {
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Live rate warning: No shipping country code in session");
            SimpleEcommCartSession::set('SimpleEcommCartShippingWarning', true);
            $sendBack = true;
          }
          
          if($sendBack) {
            wp_redirect($link);
            exit();
          }
          
        } // End if checkout page
      } // End if using live rates
    } // End if GET
  }
  
  /**
   *  Add SimpleEcommCart to the TinyMCE editor
   */
  public function addEditorButtons() {
    // Don't bother doing this stuff if the current user lacks permissions
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
    return;

    // Add only in Rich Editor mode
    if ( get_user_option('rich_editing') == 'true') {
      add_filter('mce_external_plugins', array('SimpleEcommCart', 'addTinymcePlugin'));
      add_filter('mce_buttons', array('SimpleEcommCart','registerEditorButton'));
    }
  }

  public function registerEditorButton($buttons) {
    array_push($buttons, "|", "simpleecommcart");
    return $buttons;
  }

  public function addTinymcePlugin($plugin_array) {
    $plugin_array['simpleecommcart'] = SIMPLEECOMMCART_URL . '/js/editor_plugin_src.js';
    return $plugin_array;
  }
  
  /**
   * Load the cart from the session or put a new cart in the session
   */
  public function initCart() {

    if(!SimpleEcommCartSession::get('SimpleEcommCartCart')) {
      SimpleEcommCartSession::set('SimpleEcommCartCart', new SimpleEcommCartCart());
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Creating a new SimpleEcommCartCart OBJECT for the database session.");
    }

    if(isset($_POST['task'])) {
      if($_POST['task'] == 'addToCart') {
        SimpleEcommCartSession::get('SimpleEcommCartCart')->addToCart();
      }
      elseif($_POST['task'] == 'updateCart') {
        SimpleEcommCartSession::get('SimpleEcommCartCart')->updateCart();
      }
    }
    elseif(isset($_GET['task'])) {
      if($_GET['task']=='removeItem') {
        $itemIndex = SimpleEcommCartCommon::getVal('itemIndex');
        SimpleEcommCartSession::get('SimpleEcommCartCart')->removeItem($itemIndex);
      }
    }
    elseif(isset($_POST['simpleecommcart-action'])) {
      $task = SimpleEcommCartCommon::postVal('simpleecommcart-action');
      if($task == 'authcheckout') {
        $inventoryMessage = SimpleEcommCartSession::get('SimpleEcommCartCart')->checkCartInventory();
        if(!empty($inventoryMessage)) { SimpleEcommCartSession::set('SimpleEcommCartInventoryWarning', $inventoryMessage); }
      }
    }
    
  }
  
  public function initShortcodes() {
    $sc = new SimpleEcommCartShortcodeManager();
     
    add_shortcode('simpleecommcart_add_to_cart',          array($sc, 'showCartButton'));
    add_shortcode('simpleecommcart_add_to_cart_anchor',           array($sc, 'showCartAnchor'));
    add_shortcode('simpleecommcart_show_cart',                         array($sc, 'showCart'));
    add_shortcode('simpleecommcart_download',              array($sc, 'downloadFile'));
    add_shortcode('simpleecommcart_checkout_authorizenet',        array($sc, 'authCheckout'));
    add_shortcode('simpleecommcart_checkout_paypal',              array($sc, 'paypalCheckout'));
    add_shortcode('simpleecommcart_clear_cart',                   array($sc, 'clearCart'));
    add_shortcode('simpleecommcart_shopping_cart',                array($sc, 'shoppingCart'));
   	add_shortcode('simpleecommcart_redirect_to_previous_page',                      array($sc, 'redirectToPreviousPage'));
   
	//added by dipankar 
	add_shortcode('simpleecommcart_checkout_select',             array($sc, 'checkoutSelect'));
	add_shortcode('simpleecommcart_store_home',             array($sc, 'storeHome'));
	add_shortcode('simpleecommcart_store_category',             array($sc, 'storeCategory'));
	  
    // System shortcodes
    add_shortcode('simpleecommcart_tests',                 array($sc, 'simpleecommcartTests'));
    add_shortcode('simpleecommcart_ipn',                          array($sc, 'processIPN'));
    add_shortcode('simpleecommcart_receipt',                      array($sc, 'showReceipt'));
    
    // Enable Gravity Forms hooks if Gravity Forms is available
    if(SIMPLEECOMMCART_PRO && class_exists('RGForms')) {
      add_action("gform_post_submission", array($sc, 'gravityFormToCart'));
    }
    
  }
  
  /**
   * Adds a query var trigger for the dynamic JS dialog
   */
  public function addJsTrigger($vars) {
    $vars[] = 'simpleecommcartdialog';
    return $vars;
  }

  /**
   * Handles the query var trigger for the dyamic JS dialog
   */
  public function jsTriggerCheck() {
    if ( intval( get_query_var( 'simpleecommcartdialog' ) ) == 1 ) {
      include( SIMPLEECOMMCART_PATH . '/js/simpleecommcartDialog.php' );
      exit;
    }
  }

  /**
   * Register SimpleEcommCart cart sidebar widget
   */
  public function registerCartWidget() {
    register_widget('SimpleEcommCartCartWidget');
  }
  
  public function addFeatureLevelMetaBox() {
    if(SIMPLEECOMMCART_PRO) {
      add_meta_box('simpleecommcart_feature_level_meta', __('Feature Levels', 'simpleecommcart'), array($this, 'drawFeatureLevelMetaBox'), 'page', 'side', 'low');
      add_meta_box('simpleecommcart_feature_level_meta', __('Feature Levels', 'simpleecommcart'), array($this, 'drawFeatureLevelMetaBox'), 'post', 'side', 'low');
    }
  }  
  
  public function drawFeatureLevelMetaBox($post) {
    if(SIMPLEECOMMCART_PRO) {
      $plans = array();
      $featureLevels = array();
      $data = array();
      
      // Load feature levels defined in Spreedly if available
      if(class_exists('SpreedlySubscription')) {
        $sub = new SpreedlySubscription();
        $subs = $sub->getSubscriptions();
        foreach($subs as $s) {
          // $plans[] = array('feature_level' => (string)$s->featureLevel, 'name' => (string)$s->name);
          $plans[(string)$s->name] = (string)$s->featureLevel;
          $featureLevels[] = (string)$s->featureLevel;
        }
      }

      // Load feature levels defined in PayPal subscriptions
      $sub = new SimpleEcommCartPayPalSubscription();
      $subs = $sub->getSubscriptionPlans();
      foreach($subs as $s) {
        $plans[$s->name] = $s->featureLevel;
        $featureLevels[] = $s->featureLevel;
      }
      
      // Load feature levels defined in Membership products
      foreach(SimpleEcommCartProduct::getMembershipProducts() as $membership) {
        $plans[$membership->name] = $membership->featureLevel;
        $featureLevels[] = $membership->featureLevel;
      }

      // Put unique feature levels in alphabetical order
      if(count($featureLevels)) {
        $featureLevels = array_unique($featureLevels);
        sort($featureLevels);  

        $savedPlanCsv = get_post_meta($post->ID, '_simpleecommcart_subscription', true);
        $savedFeatureLevels = empty($savedPlanCsv) ? array() : explode(',', $savedPlanCsv);
        SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] SimpleEcommCart Saved Plans: $savedPlanCsv -- " . print_r($savedFeatureLevels, true));
        $data = array('featureLevels' => $featureLevels, 'plans' => $plans, 'saved_feature_levels' => $savedFeatureLevels);
      }
      $box = SimpleEcommCartCommon::getView('pro/views/feature-level-meta-box.php', $data);
      echo $box;
    }
  }
  
  /**
   * Convert selected plan ids into a CSV string.
   * If no plans are selected, the meta key is deleted for the post.
   */
  public function saveFeatureLevelMetaBoxData($postId) {
    $nonce = isset($_REQUEST['simpleecommcart_spreedly_meta_box_nonce']) ? $_REQUEST['simpleecommcart_spreedly_meta_box_nonce'] : '';
    if(wp_verify_nonce($nonce, 'spreedly_meta_box')) {
      $featureLevels = null;
      if(isset($_REQUEST['feature_levels']) && is_array($_REQUEST['feature_levels'])) {
        $featureLevels = implode(',', $_REQUEST['feature_levels']);
      }
      
      if(!empty($featureLevels)) {
        add_post_meta($postId, '_simpleecommcart_subscription', $featureLevels, true) or update_post_meta($postId, '_simpleecommcart_subscription', $featureLevels);
      }
      else {
        delete_post_meta($postId, '_simpleecommcart_subscription');
      }
    }
  }
  
  public function hideStorePages($excludes) {
    
    if(SimpleEcommCartSetting::getValue('hide_system_pages') == 1) {
      $store = get_page_by_path('store');
      $excludes[] = $store->ID;

      $cart = get_page_by_path('store/cart');
      $excludes[] = $cart->ID;

      $checkout = get_page_by_path('store/checkout');
      $excludes[] = $checkout->ID;
    }

    $express = get_page_by_path('store/express');
    $excludes[] = $express->ID;

    $ipn = get_page_by_path('store/ipn');
    $excludes[] = $ipn->ID;

    $receipt = get_page_by_path('store/receipt');
    $excludes[] = $receipt->ID;
	
	$clear = get_page_by_path('store/clear');
    $excludes[] = $clear->ID;
    
    $spreedly = get_page_by_path('store/spreedly');
    if ( isset( $spreedly->ID ) )
			$excludes[] = $spreedly->ID;
    
    if(is_array(get_option('exclude_pages'))){
  		$excludes = array_merge(get_option('exclude_pages'), $excludes );
  	}
  	sort($excludes);
    
  	return $excludes;
  }
  
  
  
  public function appendLogoutLink($output) {
    $output .= "<li><a href='" . SimpleEcommCartCommon::appendQueryString('simpleecommcart-task=logout') . "'>Log out</a></li>";
    return $output;
  }
  
  /**
   * Force downloads for
   *   -- SimpleEcommCart reports (admin)
   *   -- Downloading the debuggin log file (admin)
   *   -- Downloading digital product files
   */
  public function forceDownload() {

    ob_end_clean();

    if($_SERVER['REQUEST_METHOD'] == 'POST' && SimpleEcommCartCommon::postVal('simpleecommcart-action') == 'export_csv') {
      require_once(SIMPLEECOMMCART_PATH . "/models/SimpleEcommCartExporter.php");
      $start = str_replace(';', '', $_POST['start_date']);
      $end = str_replace(';', '', $_POST['end_date']);
      SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Date parameters for report: START $start and END $end");
      $report = SimpleEcommCartExporter::exportOrders($start, $end);

      header('Content-Type: application/csv'); 
      header('Content-Disposition: inline; filename="SimpleEcommCartReport.csv"');
      echo $report;
      die();
    }
    elseif($_SERVER['REQUEST_METHOD'] == 'POST' && SimpleEcommCartCommon::postVal('simpleecommcart-action') == 'download log file') {

      $logFilePath = SimpleEcommCartLog::getLogFilePath();
      if(file_exists($logFilePath)) {
        $logData = file_get_contents($logFilePath);
        $cartSettings = SimpleEcommCartLog::getCartSettings();

        header('Content-Description: File Transfer');
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=SimpleEcommCartLogFile.txt');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        echo $cartSettings . "\n\n";
        echo $logData;
        die();
      }
    }
    
  }
  
}