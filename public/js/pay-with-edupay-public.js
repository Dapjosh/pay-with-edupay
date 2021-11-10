/* global location flw_payment_args jQuery*/
'use strict';

// var form   = jQuery( '#flw-pay-now-button' );

// if ( form ) {

//   form.on( 'click', function( evt ) {
//     evt.preventDefault();
//     location.href = flw_payment_args.cb_url;
//   } );

// }

// 'use strict';

// var raveLogo = 'https://res.cloudinary.com/dkbfehjxf/image/upload/v1511542310/Pasted_image_at_2017_11_09_04_50_PM_vc75kz.png'
var p_key = edp_payment_args.public_key;

console.log(p_key);
var successCallback= function(data){
  var checkout_form = $('form.woocommerce-checkout');


  checkout_form.off('checkout_place_order', tokenRequest);

  checkout_form.submit();
}
var errorCallback = function(data) {
  console.log(data);
};

var tokenRequest = function() {

successCallback();
return false;

};

jQuery(function($){

var checkout_form = $( 'form.woocommerce-checkout' );
checkout_form.on( 'checkout_place_order', tokenRequest );

});