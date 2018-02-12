<?php
foreach (explode(',',AUTO_COMPLETE_FIELDS) AS $autoComFields) {
?>
jQuery('input[name="<?php echo $autoComFields; ?>"]').autocomplete({
 source: '<?php echo AUTO_COMPLETE_URL; ?>&field=<?php echo $autoComFields; ?>',
 minLength: 2,
 select: function(event,ui) {
 }
});
<?php
}
?>