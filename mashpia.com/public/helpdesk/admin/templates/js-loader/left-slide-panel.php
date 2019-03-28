    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      jQuery('#leftpanelmenu').mmenu({
        'extensions' : [
          'pageshadow'
         ]
      });
      var mmapi = jQuery('#leftpanelmenu').data('mmenu');
      jQuery('#leftpanelbutton').click(function() {
        mmapi.open();
      });
      jQuery('#leftpanelbuttonxs').click(function() {
        mmapi.open();
      });
      mmapi.bind('opened', function () {
        mswMenuButton('open');
      });
      mmapi.bind('closed', function () {
        mswMenuButton('close');
      });
		});
    //]]>
    </script>