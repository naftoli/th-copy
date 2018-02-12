jQuery(window).load(function() {
	jQuery(document).find('body').addClass('loaded');
	// Username-less login
	if(jQuery('#tzuserless').length > 0) {
		jQuery('#username').parent().parent().css('display', 'none');
		jQuery('#username').val(jQuery('#tzuserless').attr('data-username'));
		
		jQuery('#tzwronguserless').click(function(e) {
			e.preventDefault();
			jQuery(e.target).parent().remove();	
			jQuery('#username').parent().parent().css('display', 'block');
			jQuery('#username').val('');
			jQuery('#password').val('');
		});
	}
    jQuery(document).find('#tz-loading').fadeOut('fast',function(){
       jQuery(document).find('#tz-loading').remove();
    });
});

jQuery(document).ready(function(){

   var w_w = jQuery(window).width();
   var w_h = jQuery(window).height();
   var ld_w = jQuery('.tz-loading-content').width();
   var ld_h = jQuery('.tz-loading-content').height();

   var ps_top = (w_h/2) - (ld_h/2);
   var ps_left = (w_w/2) - (ld_w/2);
    jQuery('#tz-loading').css({
        'z-index':'9999',
        'background':'#282929'
        });
    jQuery('.tz-loading-content').css({
        top:ps_top+'px',
        left:0,
        width:'100%',
        'text-align':'center',
        opacity:1
    });

});

// Start set background when window loading
//jQuery(document).ready(function(){
//    var $html   = '<div id="tz-meloul-page-loading">' +
//        '<div class="tz-meloul-page-loading" style="top:'+jQuery(window).height()/2+'">' +
//        '<span class="loading"></span>' +
//        '<span class="logo"></span> '+
//        '</div>'+
//        '</div>';
//    jQuery(document).find('body').append($html);
//    var $a  = jQuery('#tz-pinme-page-loading .tz-pinme-page-loading');
//    $a.css({'margin-top': - ($a.height())/2,
//        'margin-left': - ($a.width())/2});
//});
// End set background when window loading

var JCaption = function() {};
var Tips = function() {};