<? $dual_auth = true; ?>
<? $admin_auth = array('school', 'class', 'team', 'user'); ?>
<? require('header.php'); ?>
<? if(!($page = gri('page'))): ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Tanya Lines Print'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Tanya Lines Print')?></H1>

<FORM action="admin_tanya_lines_print.php" method="get" accept-charset="UTF-8">
<? $max_page = mysql_result(mq('SELECT MAX(page) FROM tanya_lines'), 0); ?>
<P>
<SELECT name="page">
<? for($i = 1; $i <= $max_page; $i++): ?>
<OPTION value="<?=$i?>"><?=T_('Page'), ' ', $i?>
<? endfor; ?>
</SELECT> <INPUT type="submit" value="Display">
</P>
</FORM>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
<?else:?>
<?
$old_perek = '';
$old_page = 0;
$result = mq("SELECT page, perek, text, line FROM tanya_lines WHERE page = $page ORDER BY line");

$input = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>

<report name="tanya_bar_codes">
EOT;

while($row = mysql_fetch_assoc($result)) {
  if($old_perek != $row['perek'] || $old_page != $row['page']) {
    if($old_page) $input .= "</lines>\n</page>\n";
    $old_perek = $row['perek'];
    $old_page = $row['page'];
    $input .= "
<page>
  <pagenumber>{$row['page']}</pagenumber>
  <perek>" . es($row['perek']) . "</perek>
  <lines>
";
  }
  $input .= "    <line linenumber='{$row['line']}' barcode='71" . str_pad($row['line'], 4, '0', STR_PAD_LEFT) . "' />\n";
}
$input .= "  </lines>\n</page>\n</report>\n";
sendReport($input, "tanya_lines_page_" . $page . '.pdf');
?>
<?endif;?>
