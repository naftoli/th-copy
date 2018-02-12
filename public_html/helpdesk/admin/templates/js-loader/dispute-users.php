<?php
// Dispute users
?>
<script>
//<![CDATA[
jQuery(document).ready(function() {
  jQuery('input[name="search"]').autocomplete({
	  source: 'index.php?ajax=auto-users&dispute=<?php echo $dispID; ?>',
		minLength: 3,
		select: function(event, ui) {
      if (ui.item.value > 0) {
        mswLoadDisputeUser(ui.item.value,ui.item.name,ui.item.email,ui.item.access);
      } else {
        setTimeout(function() {
          jQuery('input[name="search"]').val('');
        }, 400);
      }
		}
  });
});
function mswLoadDisputeUser(id,name,email,accs) {
  jQuery('.nav-tabs a:first').tab('show');
  setTimeout(function() {
    mswShowSpinner();
  }, 300);
  setTimeout(function() {
    jQuery('div[class="table-responsive"] tbody tr').last().after(jQuery('div[class="table-responsive"] tbody tr').last().clone());
    jQuery('div[class="table-responsive"] tbody tr').last().hide();
    jQuery('div[class="table-responsive"] tbody tr:last').attr('id', 'duser_' + id);
    var hidbox = '<input type="hidden" name="userID[]" value="' + id +'">';
    if (accs == 'no') {
      hidbox += '<input type="hidden" name="nm_' + id + '" value="' + name +'"><input type="hidden" name="em_' + id + '" value="' + email +'">';
    }
    switch(accs) {
      case 'yes':
        jQuery('div[class="table-responsive"] tbody tr:last td:first').html(hidbox + '<a href="?p=accounts&amp;edit=' + id + '">' + name + '</a><span class="tdCellInfo">' + email + '</span>');
        break;
      case 'no':
        jQuery('div[class="table-responsive"] tbody tr:last td:first').html(hidbox + name + '<span class="tdCellInfo">' + email + '</span>');
        break;
    }
    jQuery('div[class="table-responsive"] tbody tr:last td:nth-child(2)').html('0');
    jQuery('div[class="table-responsive"] tbody tr:last td:nth-child(3) input[type="checkbox"]').attr('value', id);
    jQuery('div[class="table-responsive"] tbody tr:last td:nth-child(4) input[type="checkbox"]').attr('value', id);
    if (jQuery('div[class="table-responsive"] tbody tr:last td:nth-child(5) a').html()) {
      jQuery('div[class="table-responsive"] tbody tr:last td:nth-child(5) a').attr('onclick', 'mswRowForDel(\'duser_' + id + '\',\'duser\');return false');
    } else {
      jQuery('div[class="table-responsive"] tbody tr:last td:nth-child(5)').html('<a href="#" onclick="mswRowForDel(\'duser_' + id + '\',\'duser\');return false"><i class="fa fa-times fa-fw ms_red"></i></a>');
    }
    jQuery('div[class="table-responsive"] tbody tr').last().fadeIn(500);
    jQuery('input[name="search"]').val('');
    mswProcessOK();
  }, 600);
}
function mswLoadDisputeNewUser() {
  var nwent = jQuery('input[name="newacc"]').val();
  if (nwent == '') {
    jQuery('input[name="newacc"]').focus();
  }
  var user = nwent.split(',');
  mswLoadDisputeUser(mswRandString(20),jQuery.trim(user[0]),jQuery.trim(user[1]),'no');
}
//]]>
</script>