<script>
//<![CDATA[
function mswResetPurgeFields(type, pval) {
  jQuery('div[class="panel-footer"]').hide();
  jQuery('#' + type).show();
  if (pval) {
    jQuery('#f1 input[type="hidden"]').val(pval);
  }
}
function mswCheckResetAcc() {
  var cnt = 0;
  if (jQuery('input[name="visitors"]:checked').val()) {
    ++cnt;
  }
  if (jQuery('input[name="team"]:checked').val()) {
    ++cnt;
  }
  jQuery('#f3 button').prop('disabled', (cnt > 0 ? false : true));
}
function mswSelectMailTag(val) {
  if (val) {
	  mswInsertAtCursor('message',val);
	  return true;
	}
	return false;
}
//]]>
</script>