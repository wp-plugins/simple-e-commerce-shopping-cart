jQuery(document).ready(function($) {
  
  $('.modalClose').click(function() {
    $('.SimpleEcommCartUnavailable').fadeOut(800);
  });
  
  $('#SimpleEcommCartCancelPayPalSubscription').click(function() {
    return confirm('Are you sure you want to cancel your subscription?\n');
  });
});


var $pj = jQuery.noConflict();

function getCartButtonFormData(formId) {
  var theForm = $pj('#' + formId);
  var str = '';
  $pj('input:not([type=checkbox], :radio), input[type=checkbox]:checked, input:radio:checked, select, textarea', theForm).each(
      function() {
        var name = $pj(this).attr('name');
        var val = $pj(this).val();
        str += name + '=' + encodeURIComponent(val) + '&';
      }
  );

  return str.substring(0, str.length-1);
}