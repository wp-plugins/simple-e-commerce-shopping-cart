<?php echo $data['beforeWidget']; ?>
  
  <?php echo $data['beforeTitle'] . '<span id="SimpleEcommCartWidgetCartTitle">' . $data['title'] . '</span>' . $data['afterTitle']; ?>
  
  <input  type="hidden" id="simpleecommcart_cart_sidebar" value="yes" />
  
  <?php if($data['numItems']): ?>
    <div id="SimpleEcommCartWidgetCartContents">
      <a id="SimpleEcommCartWidgetCartLink" href='<?php echo get_permalink($data['cartPage']->ID) ?>'>
      <span id="SimpleEcommCartWidgetCartCount"><?php echo $data['numItems']; ?></span>
      <span id="SimpleEcommCartWidgetCartCountText"><?php echo $data['numItems'] > 1 ? ' items' : ' item' ?></span> 
      <span id="SimpleEcommCartWidgetCartCountDash">&ndash;</span>
      <span id="SimpleEcommCartWidgetCartPrice"><?php echo SIMPLEECOMMCART_CURRENCY_SYMBOL . 
        number_format($data['cartWidget']->getSubTotal() - $data['cartWidget']->getDiscountAmount(), 2); ?>
      </span></a>
   <a class="SimpleEcommCartWidgetViewCart" href='<?php echo get_permalink($data['clearCartPage']->ID) ?>'>Clear Cart</a>&nbsp;|&nbsp;<a id="SimpleEcommCartWidgetViewCart" href='<?php echo get_permalink($data['cartPage']->ID) ?>'><?php _e( 'Check out' , 'simpleecommcart' ); ?></a>
    </div>
  <?php else: ?>
    <p id="SimpleEcommCartWidgetCartEmpty"><?php _e( 'Your cart is empty.' , 'simpleecommcart' ); ?></p>
  <?php endif; ?>

<?php echo $data['afterWidget']; ?>