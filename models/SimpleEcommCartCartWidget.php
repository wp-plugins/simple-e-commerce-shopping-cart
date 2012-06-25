<?php
class SimpleEcommCartCartWidget extends WP_Widget {

  private $_items = array();
  
	public function SimpleEcommCartCartWidget() {
    $widget_ops = array('classname' => 'SimpleEcommCartCartWidget', 'description' => 'Sidebar shopping cart for SimpleEcommCart' );
    $this->WP_Widget('SimpleEcommCartCartWidget', 'Simple eCommerce shopping cart', $widget_ops);
  }

	public function widget($args, $instance) {
    extract($args);			
    $data['title'] = $instance['title'];
    $data['shipping'] = isset($instance['shipping']) ? $instance['shipping'] : false;
    if(SimpleEcommCartSession::get('SimpleEcommCartCart') && get_class(SimpleEcommCartSession::get('SimpleEcommCartCart')) == 'SimpleEcommCartCart') {
      $this->_items = SimpleEcommCartSession::get('SimpleEcommCartCart')->getItems();
      $data['items'] = $this->_items;
    }
    $data['cartPage'] = get_page_by_path('store/cart');
    $data['checkoutPage'] = get_page_by_path('store/checkout');
	$data['clearCartPage'] = get_page_by_path('store/clear');
    $data['numItems'] = $this->countItems();
    $data['cartWidget'] = $this;
    $data['beforeWidget'] = $before_widget;
    $data['afterWidget'] = $after_widget;
    $data['beforeTitle'] = $before_title;
    $data['afterTitle'] = $after_title;
	
    
    if (isset($instance['standard_advanced']) && $instance['standard_advanced'] == 'advanced') { 
      echo SimpleEcommCartCommon::getView('views/cart-sidebar-advanced.php', $data); 
    } else {
      echo SimpleEcommCartCommon::getView('views/cart-sidebar.php', $data);
    }
  }

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['standard_advanced'] = $new_instance['standard_advanced'];
		$instance['shipping'] = !empty($new_instance['shipping']) ? 1 : 0;
		return $instance;
	}
  
  public function countItems() {
    if(SimpleEcommCartSession::get('SimpleEcommCartCart')) {
      return SimpleEcommCartSession::get('SimpleEcommCartCart')->countItems();
    }
  }
  
  public function getItems() {
    if(SimpleEcommCartSession::get('SimpleEcommCartCart')) {
      return SimpleEcommCartSession::get('SimpleEcommCartCart')->getItems();
    }
  }
  
  public function getSubTotal() {
    if(SimpleEcommCartSession::get('SimpleEcommCartCart')) {
      return SimpleEcommCartSession::get('SimpleEcommCartCart')->getSubTotal();
    }
  }
  
  public function getDiscountAmount() {
    if(SimpleEcommCartSession::get('SimpleEcommCartCart')) {
      return SimpleEcommCartSession::get('SimpleEcommCartCart')->getDiscountAmount();
    }
  }
  
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'standard_advanced' => 'standard', 'title' => '') );
    $shipping = isset( $instance['shipping'] ) ? (bool) $instance['shipping'] : false;
    $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
	?>
		<p>
		  <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'simpleecommcart-cart'); ?>:
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('standard_advanced'); ?>"><?php _e('Choose Cart Widget Type:', 'simpleecommcart-cart'); ?></label>
			<select name="<?php echo $this->get_field_name('standard_advanced'); ?>" id="<?php echo $this->get_field_id('standard_advanced'); ?>" class="widefat widgetModeSelector">
				<option value="standard"<?php selected( $instance['standard_advanced'], 'standard' ); ?>><?php _e('Standard', 'simpleecommcart-cart'); ?></option>
				<option value="advanced"<?php selected( $instance['standard_advanced'], 'advanced' ); ?>><?php _e('Advanced', 'simpleecommcart-cart'); ?></option>
			</select>
		</p>
		  
  	<script type="text/javascript">
          (function($){
            $(document).ready(function(){
              $(".widgetModeSelector").change(function(){
                if($(this).val()=="advanced"){
                  $(".CartAdvancedOptions").show();
                }
                else{
                  $(".CartAdvancedOptions").hide();
                }
              })
            })
          })(jQuery);
        </script>
<?php
	}

}