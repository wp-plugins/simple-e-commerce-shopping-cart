<?php
$page = array(
  'post_title' => 'Store',
  'post_name' => 'store',
  'post_content' => '[simpleecommcart_store_home]',
  'post_parent' => 0,
  'post_status' => 'publish',
  'post_type' => 'page',
  'comment_status' => 'closed',
  'ping_status' => 'closed'
);

// Create the top level store page
$p = get_page_by_path('store');
if(!$p) {
  $pageId = wp_insert_post($page);
  $parentId = $pageId;
}
else {
  $parentId = $p->ID;
}


// Insert the page to view the cart
$p = get_page_by_path('store/cart');
if(!$p) {
  $page['post_title'] = 'Cart';
  $page['post_name'] = 'cart';
  $page['post_content'] = "<img src='".SHOPPING_CART_IMAGE."'/>\n<h1>Your Shopping Cart</h1> \n[simpleecommcart_show_cart]";
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
}

// Insert the checkout page
$p = get_page_by_path('store/checkout');
if(!$p) {
  $page['post_title'] = 'Checkout';
  $page['post_name'] = 'checkout';
  $page['post_content'] = "<h1>Checkout</h1>\n[simpleecommcart_show_cart mode=\"read\"]\n[simpleecommcart_checkout_select]";
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
}

 
// Insert the page to process Instant Payment Notifications
$p = get_page_by_path('store/ipn');
if(!$p) {
  $page['post_title'] = 'IPN';
  $page['post_name'] = 'IPN';
  $page['post_content'] = '[simpleecommcart_ipn]';
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
} 
// Insert the page to process Instant Payment Notifications
$p = get_page_by_path('store/receipt');
if(!$p) {
  $page['post_title'] = 'Receipt';
  $page['post_name'] = 'receipt';
  $page['post_content'] = "<h1>Your Receipt</h1>\n[simpleecommcart_receipt]";
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
}

// Insert the page to clearcart
$p = get_page_by_path('store/clear');
if(!$p) {
  $page['post_title'] = 'Clear';
  $page['post_name'] = 'clear';
  $page['post_content'] = "[simpleecommcart_clear_cart][simpleecommcart_redirect_to_previous_page]";
  $page['post_parent'] = $parentId;
  wp_insert_post($page);
}
 