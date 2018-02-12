function keepAlive() {	var myAjax = new Request({method: "get", url: "index.php"}).send();} window.addEvent("domready", function(){ keepAlive.periodical(840000); });
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
window.addEvent('load', function() {
    new JCaption('img.caption');
});
$TZ_MENU = [];
$TZ_MENU['animation'] = "height";
$TZ_MENU['animation_speed'] = "fast";
jQuery(document).ready(function(){

    jQuery('.hasTooltip').tooltip({});
    var newsletter = jQuery('input.letter');
    if(newsletter){
        newsletter.focus(function(){
            jQuery(this).animate({
                width: 200
            },1000);
        });
        newsletter.blur(function(){
            jQuery(this).animate({
                width: 150
            },1000);
        });
    }
    var c_max_w = jQuery('#tz-header .container-fluid').width();
    var banner = jQuery('.banner');
    if(banner){
        jQuery(banner).css('max-width',c_max_w+'px');
    }
    jQuery(function(){
        if (window.PIE) {
            jQuery('.btn-base').each(function() {
                PIE.attach(this);
            });
            jQuery('.btn').each(function() {
                PIE.attach(this);
            });
            jQuery('.music-play-blog').each(function() {
                PIE.attach(this);
            });
            jQuery('.music-pause-blog').each(function() {
                PIE.attach(this);
            });
            jQuery('div.TzPagination a').each(function() {
                PIE.attach(this);
            });
            jQuery('.search-query').each(function() {
                PIE.attach(this);
            });
        }
    });

    jQuery('*[rel=tooltip]').tooltip();
    jQuery('*[rel=popover]').popover();
    jQuery('.tip-bottom').tooltip({placement: "bottom"});

    <!-- facebook-->
    (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "../../../../connect.facebook.net/en_US/all.js#xfbml=1";
    fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));

    <!-- +1 button -->
    window.___gcfg = {lang: 'en-GB'};
    (function() {
        var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
        po.src = '../../../../../apis.google.com/js/plusone.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
    })();

});