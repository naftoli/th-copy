  <script>
  //<![CDATA[
  function autoPath(type,box) {
    jQuery(document).ready(function() {
     jQuery('input[name="' + box + '"]').css('background','url(templates/images/spinner.gif) no-repeat 99% 50%');
	   jQuery.ajax({
      url: 'index.php',
      data: 'ajax=autopath&type='+type,
      dataType: 'json',
      success: function (data) {
	      jQuery('input[name="' + box + '"]').css('background-image','none');
		    jQuery('input[name="' + box + '"]').val(data['path']);
      }
     });
    });
    return false;
  }
  //]]>
  </script>