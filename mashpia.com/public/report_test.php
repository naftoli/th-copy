<?
if(!isset($remote)) {
?>
<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
}

if(!isset($remote) && $input = gr('input')):
  sendReport($input, 'report_' . date('YmdGis') . '.pdf', gr('fmt', 'xml'), gr('disposition', 'inline'));
else:
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
<TITLE>Report test</TITLE>
</HEAD>
<BODY>
<FORM method="post" action="<?=isset($remote) ? $remote : ''?>report_test.php" accept-charset="UTF-8">
<P>
<LABEL>
Enter XML for report:<BR>
<TEXTAREA rows="20" cols="80" name="input"><?=es($input)?></TEXTAREA></LABEL><BR>
<INPUT type="submit" value="Generate Report"><BR>
<LABEL><INPUT type="radio" name="disposition" value="inline" checked>View</LABEL>
<LABEL><INPUT type="radio" name="disposition" value="attachment">Download</LABEL><BR>
<LABEL><INPUT type="radio" name="fmt" value="xml" checked>XML</LABEL>
<LABEL><INPUT type="radio" name="fmt" value="csv">CSV</LABEL><BR>
</P>
</FORM>
</BODY>
</HTML>
<?endif;?>
