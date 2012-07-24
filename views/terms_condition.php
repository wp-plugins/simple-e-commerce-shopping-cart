<?php
		$terms_page_link='#';
		if(SimpleEcommCartSetting::getValue('terms_and_condition')=='yes' && SimpleEcommCartSetting::getValue('terms_and_condition_page')!=NULL)
		{
			$terms_page_id = SimpleEcommCartSetting::getValue('terms_and_condition_page');
			$terms_page_link = get_permalink($terms_page_id);
			}
		?>
<div style="clear:both; width:100%;text-align:right;">
	<input type="checkbox" id="agreeTermsAndCondition" name="agreeTermsAndCondition"/><span>I agree to the</span>	 <a style="margin-right:7px;" href="<?php $terms_page_link ?>" target="_blank">Terms & Conditions</a>
</div>