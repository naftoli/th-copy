<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'school';
require_once('admin_ui.php');

check_id_access();
$school_id = gri('school_id', -1);
$type = gr('type', 'b');
$sort = gr('sort', 'class');
$search_user_serial = gr('search_user_serial');
$search_first = gr('search_first');
$search_last = gr('search_last');
$search_class_id = gri('search_class_id', -1);

if($scan_code = gr('scan')) $message = include('admin_scan_processor.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('View Vouchers'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=T_('Base Management')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_withdraw.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<DIV class="ui_body">
<DIV class="ui_menu">
<?ui_menu();?>
</DIV>
<DIV class="content">
<DIV class="infobox">
<P>
<?=T_("When a Chayol gives you a voucher, scan the bar code to confirm that they have received a pack of Rebbe pictures. (Please note: you do not need to scan the Chayol's rank card before you scan the voucher.)")?>
</P>
<P>
<?=T_('If a chayol claims to have lost their voucher, then choose on "printed but not cashed", and make sure that the chayol has a printed voucher that has not yet been cashed. Then click on the bar code to mark that voucher as cashed.')?>
</P>
</DIV>
<DIV class="infobox2">
<FORM action="admin_withdraw.php" method="get" accept-charset="UTF-8">
<P>
<B><?=T_('Search by')?>:</B><BR>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<LABEL style="white-space: nowrap;"><?=T_('Serial #')?>: <INPUT type="text" name="search_user_serial" value="<?=es($search_user_serial)?>"></LABEL>
<LABEL style="white-space: nowrap;"><?=T_('First name')?>: <INPUT type="text" name="search_first" value="<?=es($search_first)?>"></LABEL>
<LABEL style="white-space: nowrap;"><?=T_('Last name')?>: <INPUT type="text" name="search_last" value="<?=es($search_last)?>"></LABEL>
<?$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");?>
<LABEL style="white-space: nowrap;"><?=T_('Platoon')?>: <SELECT name="search_class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?while($class_row = mysql_fetch_assoc($class_result)):?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $search_class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
</P>

<P>
<B><?=T_('Show vouchers that were')?>:</B>
<LABEL><INPUT type="radio" name="type" value="p" <?=$type=='p' ? 'CHECKED' : ''?>><?=T_('Printed but not Cashed')?></LABEL>
<LABEL><INPUT type="radio" name="type" value="ps" <?=$type=='ps' ? 'CHECKED' : ''?>><?=T_('Printed &amp; Cashed')?></LABEL>
<LABEL><INPUT type="radio" name="type" value="b" <?=$type=='b' ? 'CHECKED' : ''?>><?=T_('Both')?></LABEL>
</P>

<P>
<B><?=T_('Sort by')?>:</B>
<SELECT name="sort">
<OPTION value="print_date" <?=$sort=='print_date' ? 'SELECTED' : ''?>><?=T_('Print Date')?>
<OPTION value="scan_date" <?=$sort=='scan_date' ? 'SELECTED' : ''?>><?=T_('Scan Date')?>
<OPTION value="name" <?=$sort=='name' ? 'SELECTED' : ''?>><?=T_('Name')?>
<OPTION value="class" <?=$sort=='class' ? 'SELECTED' : ''?>><?=T_('Class then Name')?>
</SELECT>
</P>
<P>
<INPUT type="hidden" name="scan" id="scan">
<INPUT class="submit" type="submit" name="search" value="<?=T_('Go')?>">
</P>
</FORM>
</DIV>
<BR><BR>
<?if(!gr('search') && !gr('scan')):?>
<P><?=T_('Please use the search form above to view vouchers.')?></P>
<?else:?>
<? $result = mq("SELECT class_grade, class_sub, user_id, username, first, last, user_serial, code_id, print_date, scan_date, points FROM user_withdraw JOIN users USING (user_id) LEFT JOIN classes USING (class_id, school_id) WHERE school_id = $school_id" . ($type == 'p' ? ' AND scan_date IS NULL' : ($type == 'ps' ? ' AND scan_date IS NOT NULL' : '')) . ($search_first !== '' ? ' AND first LIKE ' . ms("$search_first%") : '') . ($search_user_serial !== '' ? ' AND user_serial = ' . intval($search_user_serial) : '') . ($search_last !== '' ? ' AND last LIKE ' . ms("$search_last%") : '') . ($search_class_id != -1 ? " AND class_id = $search_class_id" : '') . ' ORDER BY ' . ($sort == 'print_date' ? 'print_date' : ($sort == 'scan_date' ? 'scan_date' : ($sort == 'name' ? 'last, first, print_date' : ($sort == 'class' ? 'class_grade, class_sub, last, first, print_date' : 'print_date'))))); ?>
<TABLE CLASS="list list_<?=$align_start?>" style="font-size:12px;">
<THEAD>
<TR>
  <TH><?=T_('Platoon')?></TH>
  <TH><?=T_('Name')?></TH>
  <TH><?=T_('Serial #')?></TH>
  <TH><?=T_('Scan Code')?><BR><SPAN style="font-size: 85%; color: blue;">(<?=T_('Click to mark as Cashed')?>)</SPAN></TH>
  <TH><?=T_('Print Date')?></TH>
  <TH><?=T_('Scan Date')?></TH>
  <TH><?=T_('Points')?></TH>
</TR>
</THEAD>
<? while($row = mysql_fetch_assoc($result)): ?>
<? $row['code_id'] = str_pad($row['code_id'], strlen($row['code_id']) <= 9 ? 9 : 17, '0', STR_PAD_LEFT); ?>
<TR>
  <TD><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></TD>
  <TD><?=es("{$row['first']} {$row['last']}")?></TD>
  <TD><?=$row['user_serial']?></TD>
  <TD><A HREF="admin_scan.php?school_id=<?=$school_id?>&amp;scan=1<?=$row['code_id']?>" onClick="document.getElementById('scan').value=this.textContent; document.getElementById('scan').form.method='post'; document.getElementById('scan').form.submit(); return false;">1<?=$row['code_id']?></A></TD>
  <TD><?=$row['print_date']?></TD>
  <TD><?=$row['scan_date']?></TD>
  <TD><?=$row['points']?></TD>
</TR>
<? endwhile; ?>
</TABLE>
<?endif;?>
<BR style="clear: both;">
</DIV>
</DIV>
<? endif; ?>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
