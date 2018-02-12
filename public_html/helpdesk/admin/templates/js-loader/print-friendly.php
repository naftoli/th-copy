<?php
// Print Friendly Service
// More info and options @ www.printfriendly.com
?>
<script>
//<![CDATA[
var pfHeaderImgUrl      = '';
var pfHeaderTagline     = '<?php echo str_replace("'", "\'", mswCleanData($SETTINGS->website)); ?>';
var pfdisableClickToDel = 0;
var pfHideImages        = 1;
var pfImageDisplayStyle = 'right';
var pfDisablePDF        = 0;
var pfDisableEmail      = 0;
var pfDisablePrint      = 0;
var pfCustomCSS         = '';
var pfBtVersion         = '1';
(function(){
var js, pf;
pf      = document.createElement('script');
pf.type = 'text/javascript';
if('https:' == document.location.protocol){
  js = 'https://pf-cdn.printfriendly.com/ssl/main.js'
} else {
  js = 'http://cdn.printfriendly.com/printfriendly.js'
}
pf.src = js;
document.getElementsByTagName('head')[0].appendChild(pf)
})();
//]]>
</script>