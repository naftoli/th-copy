window.addEvent('load', function() {
    new JCaption('img.caption');
});
window.addEvent('domready', function() {
    $$('.hasTip').each(function(el) {
        var title = el.get('title');
        if (title) {
            var parts = title.split('::', 2);
            el.store('tip:title', parts[0]);
            el.store('tip:text', parts[1]);
        }
    });
    var JTooltips = new Tips($$('.hasTip'), { maxTitleChars: 50, fixed: false});
});
jQuery(window).load(function(){
    var box_image = jQuery('.TzBlogMedia');

    var box_img_h = box_image.height();
    var box_img_w = box_image.width();
    var btn_img_w = jQuery('.music-play-blog').width();
    var btn_img_h = jQuery('.music-play-blog').height();
    var btn_p_top = (box_img_h/2) - (btn_img_h/2);
    var btn_p_left = (box_img_w/2) - (btn_img_w/2);
    var intro_margin = box_img_w + 33;

    jQuery('.music-play-blog, .music-pause-blog').css({
    top: btn_p_top,
    left: btn_p_left
    });
jQuery('.tz-info-item').css({
    'padding-left': intro_margin +'px'
    });

jQuery('#jquery_jplayer_blog').css({
    'position':'absolute',
    bottom:61 + 'px'
    });
if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
    jQuery('.controls-inner').css({
        'margin-right':0
    });
}
});
jQuery(document).ready(function(){

    var myPlaylist =   new jPlayerBlogPlaylist({
        jPlayer: "#jquery_jplayer_blog",
        cssSelectorAncestor: "#jp_container_blog"
    }, [
        {
            title:"Aenean tristique",
            artist:"Fusce Andena ",
            m4v:"../Video/videoplayback.m4v",
            ogv:"../Video/videoplayback.ogv"

        },
        {
            title:"Quisque sit amet nis",
            artist:"Donec mattis ",
            m4v:"../Video/Miss_A.m4v",
            ogv:"../Video/Miss_A.ogv"

        },
        {
            title:"Pellentesque et nibh",
            artist:"Morbi sodales ",
            m4v:"../Video/videoplayback.m4v",
            ogv:"../Video/videoplayback.ogv"

        },
        {
            title:"Proin convallis tinci",
            artist:"Cras sapien ",
            m4v:"../Video/Miss_A.m4v",
            ogv:"../Video/Miss_A.ogv"

        }
    ], {
        swfPath: "js/js_jplayer",
        supplied: "m4v, ogv",
        wmode: "window"
    });
    jQuery(".music-play-blog").click( function() {
        var blog_item_index = jQuery(this).attr('data-option-value');
        jQuery('.TzBlogMedia').find('.music-pause-blog').fadeOut();
        jQuery('.TzBlogMedia').find('.music-play-blog').fadeIn();
        jQuery(this).fadeOut();
        jQuery(this).parent().find('.music-pause-blog').fadeIn();
        myPlaylist.play(blog_item_index);
        jQuery('.blog-music').animate({
            bottom:0
        },1000);
        jQuery('#jquery_jplayer_blog').fadeIn();
    });
    jQuery(".music-pause-blog").click( function() {
        jQuery(this).fadeOut();
        jQuery('.TzBlogMedia').find('.music-play-blog').fadeIn();
        myPlaylist.pause();
        jQuery('.blog-music').animate({
            bottom:-70
        },1000);
        jQuery('#jquery_jplayer_blog').fadeOut();
    });

    var max_w = jQuery('#tz-header .container-fluid').width();
    jQuery('#jp_container_blog').css('max-width',max_w +'px');

});
