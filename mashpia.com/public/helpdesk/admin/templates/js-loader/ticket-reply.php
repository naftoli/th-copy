<?php
// Ticket reply ops
?>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  jQuery('input[name="sresp"]').autocomplete({
	  source: 'index.php?ajax=auto-response&dept=<?php echo $SUPTICK->department; ?>',
		minLength: 3,
		select: function(event, ui) {
      if (ui.item.value > 0) {
        mswLoadResponse(ui.item.value);
      } else {
        setTimeout(function() {
          jQuery('input[name="sresp"]').val('');
        }, 400);
      }
		}
  });
  jQuery('input[name="mergeid"]').autocomplete({
	  source: 'index.php?ajax=auto-merge&visitor=<?php echo $SUPTICK->visitorID; ?>&id=<?php echo $SUPTICK->id; ?>',
		minLength: 3,
		select: function(event, ui) {
      if (ui.item.value > 0) {
        setTimeout(function() {
          jQuery('input[name="mergeid"]').val(ui.item.ticket);
          jQuery('<input type="hidden" name="prevtxt" value="' + jQuery('button[type="submit"] span').html() + '">').appendTo('form');
          jQuery('button[type="submit"] span').html(ui.item.txt);
        }, 200);
      } else {
        setTimeout(function() {
          jQuery('input[name="mergeid"]').val('');
        }, 400);
      }
		}
  });
});
function mswMergeClear() {
  if (jQuery('input[name="mergeid"]').val() == '') {
    jQuery('button[type="submit"] span').html(jQuery('input[name="prevtxt"]').val())
    jQuery('input[name="prevtxt"]').remove();
  }
}
function mswLoadResponse(id) {
  jQuery('.nav-tabs a:first').tab('show');
  setTimeout(function() {
    jQuery('textarea[name="comments"]').css('background', 'url(templates/images/loading.gif) no-repeat 50% 50%');
  }, 300);
  setTimeout(function() {
    jQuery('input[name="sresp"]').val('');
    jQuery(document).ready(function() {
      jQuery.ajax({
        url: 'index.php',
        data: 'ajax=tickresponse&id=' + id,
        dataType: 'json',
        success: function(data) {
          jQuery('textarea[name="comments"]').css('background-image', 'none');
          jQuery('textarea[name="comments"]').val(data['response']);
        }
      });
    });
    return false;
  }, 600);
}
//]]>
</script>