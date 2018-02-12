<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Invoice Summary'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<DIV class="left_menu"><?include('admin_inc.php');?></DIV>
<H1><?=T_('Invoice Summary')?></H1>
<? $result = mq('SELECT SUM(item_price) total, item_ref_type FROM invoice_items GROUP BY item_ref_type'); ?>
<TABLE CLASS="list">
<TR>
  <TH><?=T_('Type')?></TH>
  <TH><?=T_('Amount')?></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
  <TR>
    <TD><?=$row['item_ref_type'] == 'school_packages' ? T_('Annual Base Memberships') : ($row['item_ref_type'] == 'school_package_fees' ? T_('Annual Chayol memberships') : ($row['item_ref_type'] == 'payment' ? T_('Payments') : ($row['item_ref_type'] == 'charge' ? T_('Other Charges') : ($row['item_ref_type'] == 'credit' ? T_('Credits') : ($row['item_ref_type'] == 'note' ? T_('Notes') : $row['item_ref_type'])))))?></TD>
    <TD style="text-align: <?=$align_end?>;"><?=money_format('%n', $row['total'])?></TD>
</TR>
<? endwhile; ?>
</TABLE>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
