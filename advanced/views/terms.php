<div class="SimpleEcommCartTermsOfServiceWrapper <?php echo $data['location']; ?>">
  <div class="SimpleEcommCartTermsTitle"><?php echo SimpleEcommCartSetting::getValue('cart_terms_title'); ?></div>
  <div class="SimpleEcommCartTermsText"><?php echo SimpleEcommCartSetting::getValue('cart_terms_text'); ?></div>

  <form action="<?php echo SimpleEcommCartCommon::getPageLink('store/cart'); ?>" method="POST" class="SimpleEcommCartTermsAcceptance">
    <input type="hidden" name="terms_acceptance" value="I_Accept" />
    <input type="submit" name="accept_terms" value="<?php echo SimpleEcommCartSetting::getValue('cart_terms_acceptance_label'); ?>" class="SimpleEcommCartAcceptTermsButton" />
  </form>
</div>