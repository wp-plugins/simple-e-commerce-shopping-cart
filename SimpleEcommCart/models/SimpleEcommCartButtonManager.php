<?php
class SimpleEcommCartButtonManager {

  /**
   * Return the HTML for rendering the add to cart buton for the given product id
   */
  public static function getCartButton(SimpleEcommCartProduct $product, $attrs) {
    $view = "<p>" . __("Could not load product information","simpleecommcart") . "</p>";
    if($product->id > 0) {

      // Set CSS style if available
      $style = isset($attrs['style']) ? 'style="' . $attrs['style'] . '"' : '';

      $price = '';
      $quantity = (isset($attrs['quantity'])) ? $attrs['quantity'] : 1;
      
      $showName = isset($attrs['show_name']) ? strtolower($attrs['show_name']) : '';
      
      $showPrice = isset($attrs['showprice']) ? strtolower($attrs['showprice']) : 'yes';
      if($showPrice == 'yes' || $showPrice == 'only') {
        $price = SIMPLEECOMMCART_CURRENCY_SYMBOL . number_format($product->price, 2);
        
        // Check for subscription pricing
        if($product->isSubscription()) {
          if($product->isPayPalSubscription()) {
            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] Rendering button for PayPal subscription");
            $sub = new SimpleEcommCartPayPalSubscription($product->id);
            $price = $sub->getPriceDescription($sub->offerTrial > 0, '(trial)');
          }
          else {
            if($product->price > 0) {
              $price .= ' + ' . $product->getRecurringPriceSummary();;
            }
            else {
              $price =  $product->getRecurringPriceSummary();
            }
          }
        }
        else {
          //$price = $product->getPriceDescription();
        }
        
      }
      
      $data = array(
        'price' => $price,
		'price_description' => $product->getPriceDescription(),
        'is_user_price' => $product->is_user_price,
        'min_price' => $product->min_price,
        'max_price' => $product->max_price,
        'quantity' => $quantity,
        'showPrice' => $showPrice,
        'showName' => $showName,
        'style' => $style,
        'addToCartPath' => self::getAddToCartImagePath($attrs),
        'product' => $product,
        'productOptions' => $product->getOptions()
      );
      $view = SimpleEcommCartCommon::getView('views/cart-button.php', $data);
    }
    return $view;
  }

  /**
   * Return the image path for the add to cart button or false if no path is available
   */
  public function getAddToCartImagePath($attrs) {
    $path = false;

    if(isset($attrs['img'])) {
      // Look for custom image for this instance of the button
      $path = $attrs['img'];
    }
    else {
      // Look for common images
      $cartImgPath = SimpleEcommCartSetting::getValue('cart_images_url');
      if($cartImgPath) {
        $cartImgPath = SimpleEcommCartCommon::endSlashPath($cartImgPath);
        $path = $cartImgPath . 'add-to-cart.png';
      }
    }

    return $path;
  }

}