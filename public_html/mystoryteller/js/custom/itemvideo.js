jQuery(window).bind('load resize', function(){
    var w_w1 = jQuery('.jp-controls-holder').width();

    var ps_left1 = (w_w1/2) - (48);
    jQuery('.jp-controls-holder .jp-controls').css({
        left:ps_left1 +'px'
    });

    var isiPad = navigator.userAgent.match(/iPad/i) != null;
    if(isiPad == true){
        var video_w = jQuery('.TzItemPageInner').width();

        var video_h = (video_w * 360) / 640;
        jQuery('#jquery_jplayer_detail video').css({
            'width':video_w+'px',
            'float':'left',
            'position':'relative',
            'z-index':'1000',
            'min-height':video_h+'px'
        });
        jQuery('.jp-gui').css({
            'z-index': '2000'
        });

    }
});
jQuery(window).bind("load resize",function(){
    var video_w = jQuery('.TzItemPageInner').width();

    var video_h = (video_w * 360) / 640;


    jQuery("#jquery_jplayer_detail").jPlayer({
        ready: function () {
            jQuery(this).jPlayer("setMedia", {
                m4v:"../../Video/videoplayback.m4v",
                ogv:"../../Video/videoplayback.ogv"
            }).jPlayer("play");
        },
        swfPath: "js/jplayer",
        solution: 'html, flash',
        wmode:'window',
        supplied: "m4v, ogv",
        cssSelectorAncestor: "#jp_container_detail",
        size: {
            width: video_w +'px',
            height: video_h + 'px',
            cssClass: "jp-video-360p"
        }
    });

    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
        jQuery('div.time-inner').css({
            right:30+'px'
        });
    }
});
