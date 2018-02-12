jQuery(document).ready(function(){

    jQuery("#jquery_jplayer_detail").jPlayer({
        ready: function (event) {
            jQuery(this).jPlayer("setMedia", {
                m4a:"../audio/TSP-01-Cro_magnon_man.m4a",
                oga:"../audio/TSP-01-Cro_magnon_man.ogg",
                mp3:"../audio/TSP-01-Cro_magnon_man.mp3"
            });
        },
        swfPath: "../js/jplayer",
        solution: 'html, flash',
        supplied: "mp3,m4a,ogg",
        wmode: "window",
        cssSelectorAncestor: "#jp_container_detail"
    });

    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) ) {
        jQuery('div.time-inner').css({
            right:30+'px'
        });
    }

});
