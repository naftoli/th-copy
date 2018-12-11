jQuery(window).bind('load resize',function(){
    var slide_h = jQuery(window).height() - jQuery('#tz-header').height();
    var play_w = jQuery('#tz-slide .tz-slide').width();
    var max_w = jQuery('#tz-header .container-fluid').width();
    var player_height = jQuery('#tz-slide .jp-audio').height();
    var img_h =    slide_h - player_height;

    jQuery('.jp-type-playlist').css('max-width',max_w +'px');
    jQuery('.tz-music-content').css('height',img_h + 'px');
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
    jQuery('#tz-slide .music-center').css({
    'margin-right':30+ 'px'
    });
}

});

jQuery('.music-info').click(function(){
    jQuery(this).toggleClass('music-info-active');
    jQuery('.jp-playlist').slideToggle();
    });
jQuery('.item-music-content img').animate({
    opacity:1
    },1000);

jQuery(function() {
    jQuery('.item-music-image').css({backgroundSize: "cover"});
});

jQuery(document).ready(function(){
    var w_w = jQuery(window).width();
    if(w_w < 500){
    heights = jQuery("div.looptz-news-sf_latest_new").map(function ()
    {
    return jQuery(this).innerHeight();
    }).get(),
maxHeight = Math.max.apply(null, heights);
jQuery('.tz-news-sf_latest_new').css({
    height: 330 + 'px'
    });

swiperLoop = jQuery('.tz-news-sf_latest_new').swiper({
    slidesPerSlide :1,
    loop:true
    });
} else {
    heights = jQuery("div.looptz-news-sf_latest_new").map(function ()
    {
        return jQuery(this).height();
    }).get(),
maxHeight = Math.max.apply(null, heights);

jQuery('.tz-news-sf_latest_new').css({
    height: maxHeight + 'px',
    'min-height':'258' +'px'
    });


swiperLoop = jQuery('.tz-news-sf_latest_new').swiper({
    slidesPerSlide : 5,
    loop:true
    });
}
        var w_w = jQuery(window).width();
        if(w_w < 500){
        heights = jQuery("div.looptz-news-sf_bg_gradien").map(function ()
        {
        return jQuery(this).innerHeight();
        }).get(),
    maxHeight = Math.max.apply(null, heights);
    jQuery('.tz-news-sf_bg_gradien').css({
        height: 330 + 'px'
        });

    swiperLoop = jQuery('.tz-news-sf_bg_gradien').swiper({
        slidesPerSlide :1,
        loop:true
        });
    } else {
        heights = jQuery("div.looptz-news-sf_bg_gradien").map(function ()
        {
            return jQuery(this).height();
        }).get(),
    maxHeight = Math.max.apply(null, heights);

    jQuery('.tz-news-sf_bg_gradien').css({
        height: maxHeight + 'px',
        'min-height':'193' +'px'
        });


    swiperLoop = jQuery('.tz-news-sf_bg_gradien').swiper({
        slidesPerSlide : 5,
        loop:true
        });
    }

});

