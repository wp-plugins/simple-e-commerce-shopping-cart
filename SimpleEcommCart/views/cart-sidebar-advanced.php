<?php echo $data['beforeWidget']; ?>
  
  <?php echo $data['beforeTitle'] . '<span id="SimpleEcommCartWidgetCartTitle">' . $data['title'] . '</span>' . $data['afterTitle']; ?>  

	<input  type="hidden" id="simpleecommcart_cart_sidebar" value="yes" />

  <?php if($data['numItems']): ?> 
      <p id="SimpleEcommCartWidgetCartEmpty">
        <?php _e( 'You have' , 'simpleecommcart' ); ?> <?php echo $data['numItems']; ?> 
        <?php echo $data['numItems'] > 1 ? ' items' : ' item' ?> 
        (<?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL . number_format($data['cartWidget']->getSubTotal() - $data['cartWidget']->getDiscountAmount(), 2); ?>) <?php _e( 'in your shopping cart' , 'simpleecommcart' ); ?>.
      </p>
      <?php 
        $items = $data['items'];
        $product = new SimpleEcommCartProduct();
        $subtotal = SimpleEcommCartSession::get('SimpleEcommCartCart')->getSubTotal();
        $shippingMethods = SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingMethods();
        $shipping = SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingCost();
 
        $tax = 0;
          if(isset($data['tax']) && $data['tax'] > 0) {
            $tax = $data['tax'];
          }
          else {
            // Check to see if all sales are taxed
            $tax = SimpleEcommCartSession::get('SimpleEcommCartCart')->getTax('All Sales');
        }
      ?>
          <form id='SimpleEcommCartCartForm' action="" method="post">
            <input type='hidden' name='task' value='updateCart' />
        <table id='SimpleEcommCartAdvancedWidgetCartTable' class="SimpleEcommCartAdvancedWidgetCartTable">
          <?php foreach($items as $itemIndex => $item): ?>
            <?php 
            $product->load($item->getProductId());
            $productPrice = $item->getProductPrice();
            $productSubtotal = $item->getProductPrice() * $item->getQuantity();
            ?>
            <tr>
              <td>
                <span class="SimpleEcommCartProductTitle"><?php echo $item->getFullDisplayName(); ?></span>
                <span class="SimpleEcommCartQuanPrice">
                    <span class="SimpleEcommCartProductQuantity"><?php echo $item->getQuantity() ?></span> 
                    <span class="SimpleEcommCartMetaSep">x</span> 
                    <span class="SimpleEcommCartCurSymbol"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?></span>
                    <span class="SimpleEcommCartProductPrice"><?php echo number_format($productPrice, 2) ?></span>
                </span>
              </td>
              <td class="SimpleEcommCartProductSubtotalColumn">
                <span class="SimpleEcommCartProductSubtotal"><?php echo number_format($productSubtotal, 2) ?></span>
              </td>
            </tr>
          <?php endforeach; ?>
        <tr class="SimpleEcommCartSubtotalRow">
            <td colspan="2"><span class="SimpleEcommCartCartSubTotalLabel"><?php _e( 'Subtotal' , 'simpleecommcart' ); ?></span><span class="SimpleEcommCartMetaSep">: </span>
                <span class="SimpleEcommCartCurSymbol"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?></span><span class="SimpleEcommCartSubtotal"><?php echo number_format($subtotal, 2); ?></span>
            </td>
        </tr>
        
        <?php if(isset($data['shipping'] ) && $data['shipping'] == true): ?>
              <?php if(SimpleEcommCartSession::get('SimpleEcommCartCart')->requireShipping()): ?>

                <?php if(SIMPLEECOMMCART_PRO && SimpleEcommCartSetting::getValue('use_live_rates')): ?>
                  <?php $zipStyle = "style=''"; ?>

                    <?php if(SimpleEcommCartSession::get('simpleecommcart_shipping_zip')): ?>
                      <?php $zipStyle = "style='display: none;'"; ?>
                      <tr class="SimpleEcommCartShippingToRow">
                        <th colspan="2">
                          <?php _e( 'Shipping to' , 'simpleecommcart' ); ?> <?php echo SimpleEcommCartSession::get('simpleecommcart_shipping_zip'); ?> 
                          <?php
                            if(SimpleEcommCartSetting::getValue('international_sales')) {
                              echo SimpleEcommCartSession::get('simpleecommcart_shipping_country_code');
                            }
                          ?>
                          (<a href="#" id="change_shipping_zip_link"><?php _e( 'change' , 'simpleecommcart' ); ?></a>)
                          &nbsp;
                          <?php
                            $liveRates = SimpleEcommCartSession::get('SimpleEcommCartCart')->getLiveRates();
                            $rates = $liveRates->getRates();
                            SimpleEcommCartCommon::log('[' . basename(__FILE__) . ' - line ' . __LINE__ . "] LIVE RATES: " . print_r($rates, true));
                            $selectedRate = $liveRates->getSelected();
                            $shipping = SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingCost();
                          ?>
                          <select name="live_rates" id="live_rates">
                            <?php foreach($rates as $rate): ?>
                              <option value='<?php echo $rate->service ?>' <?php if($selectedRate->service == $rate->service) { echo 'selected="selected"'; } ?>>
                                <?php 
                                  if($rate->rate !== false) {
                                    echo "$rate->service: \$$rate->rate";
                                  }
                                  else {
                                    echo "$rate->service";
                                  }
                                ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </th>
                      </tr>
                    <?php endif; ?>

                    <tr id="set_shipping_zip_row" <?php echo $zipStyle; ?>>
                      <th colspan="2"><?php _e( 'Enter Your Zip Code' , 'simpleecommcart' ); ?>:
                        <input type="text" name="shipping_zip" value="" id="shipping_zip" size="5" />

                        <?php if(SimpleEcommCartSetting::getValue('international_sales')): ?>
                          <select name="shipping_country_code" class="SimpleEcommCartCountrySelect">
                            <?php
                              $customCountries = SimpleEcommCartCommon::getCustomCountries();
                              foreach($customCountries as $code => $name) {
                                echo "<option value='$code'>$name</option>\n";
                              }
                            ?>
                          </select>
                        <?php else: ?>
                          <input type="hidden" name="shipping_country_code" value="<?php echo SimpleEcommCartCommon::getHomeCountryCode(); ?>" id="shipping_country_code">
                        <?php endif; ?>

                        <input type="submit" name="updateCart" value="Calculate Shipping" id="shipping_submit" class="SimpleEcommCartButtonSecondaryWidget" />
                      </th>
                    </tr>

                <?php  else: ?>
                  <?php if(count($shippingMethods) > 1): ?>
                    <tr>
                      <th colspan="2"><?php _e( 'Shipping Method' , 'simpleecommcart' ); ?><span class="SimpleEcommCartMetaSep">: </span> 
                        <select name='shipping_method_id' id='shipping_method_id' class="SimpleEcommCartShippingMethodSelect">
                          <?php foreach($shippingMethods as $name => $id): ?>
                            <option value='<?php echo $id ?>' 
                            <?php echo ($id == SimpleEcommCartSession::get('SimpleEcommCartCart')->getShippingMethodId())? 'selected' : ''; ?>><?php echo $name ?></option>
                          <?php endforeach; ?>
                        </select>
                      </th>
                    </tr>
                  <?php endif; ?>
                <?php endif; ?>
              <?php endif; ?>
            <?php endif; ?>
              
          <?php if($tax > 0): ?>
            <tr class="tax">
              <td colspan="2"><?php _e( 'Tax' , 'simpleecommcart' ); ?><span class="SimpleEcommCartMetaSep">:</span>
              <span class="SimpleEcommCartCurSymbol"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL ?></span><span class="SimpleEcommCartTaxCost"><?php echo number_format($tax, 2); ?></span></td>
            </tr>
          <?php endif; ?>
        
        
      </table>
      </form>
      <div class="SimpleEcommCartWidgetViewCartCheckout">
	   <a class="SimpleEcommCartWidgetViewCart" href='<?php echo get_permalink($data['clearCartPage']->ID) ?>'>Clear Cart</a>&nbsp;|&nbsp;<a class="SimpleEcommCartWidgetViewCart" href='<?php echo get_permalink($data['cartPage']->ID) ?>'>Checkout</a>
      </div>
  <?php else: ?>
        <div class="SimpleEcommCartWidgetViewCartCheckout">
            <p class="SimpleEcommCartWidgetCartEmpty">You have <?php echo $data['numItems']; ?> <?php _e( 'items in your shopping cart' , 'simpleecommcart' ); ?>.
         <!--   <a class="SimpleEcommCartWidgetViewCart" href='<?php echo get_permalink($data['cartPage']->ID) ?>'><?php _e( 'Checkout' , 'simpleecommcart' ); ?></a>-->
            </p>
        </div>
  <?php endif; ?>

  <script type="text/javascript">
  /* <![CDATA[ */
    (function($){
      $(document).ready(function(){
        $('#shipping_method_id').change(function() {
          $('#SimpleEcommCartCartForm').submit();
        });

        $('#live_rates').change(function() {
          $('#SimpleEcommCartCartForm').submit();
        });

        $('#change_shipping_zip_link').click(function() {
          $('#set_shipping_zip_row').toggle();
          return false;
        });
      })
    })(jQuery);
  /* ]]> */
  </script> 
  <script type="text/javascript" charset="utf-8">
    $jq = jQuery.noConflict();
    $jq('document').ready(function() {
    });
  </script>


<?php echo $data['afterWidget']; ?>