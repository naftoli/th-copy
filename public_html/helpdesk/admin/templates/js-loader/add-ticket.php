  <script>
  //<![CDATA[
  function mswUncheckAssigned(area) {
    switch (area) {
	    case 'box':
	    if (jQuery('input[name="waiting"]:checkbox').val()) {
	      jQuery('input[name="waiting"]:checkbox').prop('checked',false);
	    }
	    break;
	    case 'wait':
        jQuery('input[name="assigned[]"]:checkbox').prop('checked',false);
	      jQuery('input[name="assignMail"]:checkbox').prop('checked',false);
	      break;
	}
  }
  function mswAddTicketCusFields(dept) {
    jQuery(document).ready(function() {
      jQuery.ajax({
        url: 'index.php',
        data: 'ajax=add-cus-field&dept='+dept,
        dataType: 'json',
        success: function (data) {
	        if (data['fields']) {
		        if (jQuery('#cusFieldsTab').css('display') == 'none') {
		          jQuery('#cusFieldsTab').show();
		        }
		        jQuery('#customFieldsArea').html(data['fields']);
	        } else {
		        if (jQuery('#cusFieldsTab').css('display') != 'none') {
		          jQuery('#cusFieldsTab').hide();
		        }
	          jQuery('#customFieldsArea').html(data['fields']);
	        }
        }
      });
    });
    return false;
  }
  //]]>
  </script>