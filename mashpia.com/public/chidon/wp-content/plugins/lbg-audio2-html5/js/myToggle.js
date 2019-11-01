function mytoggle(id,id_btn)
{
	//alert (jQuery('#'+id).css("display"));
	if (jQuery('#'+id).css("display")=="none") {
		jQuery('#'+id).slideDown(300, 'linear');
		jQuery('#'+id_btn).toggleClass('toogle-btn-opened');
		jQuery('#'+id+' .ajax-message').html(''); //clear ajax message
		//jQuery('#'+id+".toogle-btn").css("display")="none";
	} else {
		jQuery('#'+id).slideUp(250, 'linear');
		jQuery('#'+id_btn).toggleClass('toogle-btn-opened');
		//jQuery(this).css("display")="none";
	}
}
