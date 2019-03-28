<?php
// Date Pickers
?>
<script>
//<![CDATA[
jQuery(document).ready(function() {
 jQuery('#from').datepicker({
  changeMonth: true,
  changeYear: true,
  monthNamesShort: <?php echo trim($msg_cal); ?>,
  dayNamesMin: <?php echo trim($msg_cal2); ?>,
  firstDay: <?php echo ($SETTINGS->weekStart=='sun' ? '0' : '1'); ?>,
  dateFormat: '<?php echo $MSDT->mswDatePickerFormat(); ?>',
  isRTL: <?php echo $msg_cal3; ?>
 });
 jQuery('#to').datepicker({
  changeMonth: true,
  changeYear: true,
  monthNamesShort: <?php echo trim($msg_cal); ?>,
  dayNamesMin: <?php echo trim($msg_cal2); ?>,
  firstDay: <?php echo ($SETTINGS->weekStart=='sun' ? '0' : '1'); ?>,
  dateFormat: '<?php echo $MSDT->mswDatePickerFormat(); ?>',
  isRTL: <?php echo $msg_cal3; ?>
 });
});
//]]>
</script>