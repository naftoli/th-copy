<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 report_id, '' report_name, '' report_type, " . (unixtojd()+1) . ' start_date, ' . (unixtojd()+8) . " end_date, 'all' visibility");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    mq('INSERT INTO reports (report_name, report_type, start_date, end_date, visibility) VALUES (' . ms(gr('report_name')) . ', ' . ms(gr('report_type')) . ', ' . gri('start_date', 0) . ', ' . gri('end_date', 0) . ', ' . ms(gr('visibility')) . ')');
    $message = T_('Report added');
    break;

  case 'delete':
    mq('DELETE FROM report_marks WHERE report_id = ' . gri('report_id', -1));
    mq('DELETE FROM reports WHERE report_id = ' . gri('report_id', -1));
    $message = T_('Report deleted');
    break;

  case 'edit':
    $result = mq("SELECT report_id, report_name, report_type, start_date, end_date, visibility FROM reports WHERE report_id = " . gri('report_id', -1));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    mq('UPDATE reports SET report_name = ' . ms(gr('report_name')) . ', report_type = ' . ms(gr('report_type')) . ', start_date = ' . gri('start_date', 0) . ', end_date = ' . gri('end_date', 0) . ', visibility = ' . ms(gr('visibility')) . ' WHERE report_id = ' . gri('report_id', -1));
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Reports'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Reports')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_reports.php" method="post" accept-charset="UTF-8" name="report">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="report_id" value="<?=$edit_row['report_id']?>">
<LABEL><?=T_('Name')?>: <INPUT type="text" name="report_name" maxlength=255 value="<?=es($edit_row['report_name'])?>"></LABEL><BR>
<LABEL><?=T_('Type')?>: <SELECT name="report_type">
      <? foreach(mysql_enum_values('reports','report_type') as $type): ?>
        <OPTION VALUE="<?=$type?>" <?=$edit_row['report_type'] == $type ? 'SELECTED' : '' ?>><?=$type?></OPTION>
      <? endforeach; ?>
</SELECT></LABEL><BR>
<LABEL><?=T_('From')?>: <INPUT type="text" name="start_date_disp" READONLY value="<?=es(dateToHebrew($edit_row['start_date']))?>" onClick="getDate(this.form, 'start_date', true);"></LABEL><INPUT type="hidden" name="start_date" value="<?=$edit_row['start_date']?>"><BR>
<LABEL><?=T_('To')?>: <INPUT type="text" name="end_date_disp" READONLY value="<?=es(dateToHebrew($edit_row['end_date']))?>" onClick="getDate(this.form, 'end_date', true);"></LABEL><INPUT type="hidden" name="end_date" value="<?=$edit_row['end_date']?>"><BR>
<LABEL><?=T_('Visibility')?>: <SELECT name="visibility">
<OPTION value="all" <?='all' == $edit_row['visibility'] ? 'SELECTED' : ''?>>Normal
<OPTION value="process" <?='process' == $edit_row['visibility'] ? 'SELECTED' : ''?>>Process only
<OPTION value="none" <?='none' == $edit_row['visibility'] ? 'SELECTED' : ''?>>Hidden
</SELECT></LABEL><BR>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_reports.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT report_id, report_name, report_type, start_date, end_date, visibility, COUNT(print_date) printed, COUNT(process_date) processed FROM reports LEFT JOIN report_marks USING (report_id) GROUP BY report_id, report_name, start_date, end_date ORDER BY visibility, creation_date, report_name, report_id');?>

<A HREF="admin_reports.php?action=add"><?=T_('Add new Report')?></A>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('Visibility')?></TH>
  <TH><?=T_('Name')?></TH>
  <TH><?=T_('Type')?></TH>
  <TH><?=T_('From')?></TH>
  <TH><?=T_('To')?></TH>
  <TH><?=T_('# Printed')?></TH>
  <TH><?=T_('# Processed')?></TH>
  <TH></TH>
  <TH></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['visibility'] == 'all' ? '' : ($row['visibility'] == 'process' ? 'Process Only' : ($row['visibility'] == 'none' ? 'Hidden' : $row['visibility'])))?></TD>
    <TD><?=es($row['report_name'])?></TD>
    <TD><?=es($row['report_type'])?></TD>
    <TD><?=es(dateToHebrew($row['start_date']))?></TD>
    <TD><?=es(dateToHebrew($row['end_date']))?></TD>
    <TD><?=$row['printed']?></TD>
    <TD><?=$row['processed']?></TD>
    <TD><A HREF="<?=reportTypeURL_view($row['report_type'], '', NULL, $row['start_date'], $row['end_date'])?>"><?=T_('View Report')?></A></TD>
    <TD><A HREF="<?=reportTypeURL_mark($row['report_type'], '', NULL, $row['start_date'], $row['end_date'])?>"><?=T_('Process Report')?></A></TD>
    <TD><A HREF="admin_reports.php?action=edit&amp;report_id=<?=$row['report_id']?>"><?=T_('Edit Report')?></A></TD>
    <TD><A HREF="admin_reports.php?action=delete&amp;report_id=<?=$row['report_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Report')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
