<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'reports';
require_once('admin_ui.php');
require_once('calendar.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Report - List'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=T_('Reports')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_report_list.php" method="get" accept-charset="UTF-8">
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

<? if($school_id == -1): ?>
<P><?=T_('Please select an Institution.')?></P>
<? else: ?>
<DIV class="ui_body">
<DIV class="ui_menu">
<?ui_menu();?>
</DIV>
<DIV class="content">

<H2><?=T_('Reports')?></H2>
<?
$view_all = gri('view_all', 0);
?>
<P>
<A HREF="admin_report_list.php?school_id=<?=$school_id?>&amp;view_all=<?=!$view_all?>"><?= $view_all ? T_('List only unfinished reports') : T_('List all reports') ?>&raquo;</A>
</P>
<?
if($action = gr('action')) switch($action) {
  case 'report_print':
    if(!isset($do)) $do = 'print_date = NOW()';
  case 'report_unprint':
    if(!isset($do)) $do = 'print_date = NULL';
  case 'report_processed':
    if(!isset($do)) $do = 'process_date = NOW()';
  case 'report_unprocess':
    if(!isset($do)) $do = 'process_date = NULL';

    $report_id = gri('report_id', -1);
    if(mysql_result(mq("SELECT COUNT(*) FROM reports WHERE report_id = $report_id"), 0))
      mq("INSERT INTO report_marks SET report_id = $report_id, auth = 'school', id = $school_id, $do ON DUPLICATE KEY UPDATE $do");
    unset($do);
    break;
}
?>
<TABLE class="pretty_grid list_<?=$align_start?>">
<THEAD>
<TR>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('View/Print')?></TH>
  <TH><?=T_('Complete')?></TH>
</TR>
</THEAD>

<? $result = mq("SELECT reports.report_id, report_name, report_type, start_date, end_date, print_date, process_date, visibility FROM reports LEFT JOIN report_marks ON (reports.report_id = report_marks.report_id AND id = $school_id AND auth = 'school') WHERE visibility != 'none'" . ($view_all ? '' : " AND ((print_date IS NULL AND visibility != 'process') OR process_date IS NULL)") . ' ORDER BY creation_date, report_name, reports.report_id'); ?>

<?while($row=mysql_fetch_assoc($result)):?>

<TR>
<TD><?=es($row['report_name'])?></TD>
<TD>
  <? if(is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel'): ?>
  <? if($row['visibility'] != 'process'): ?>
  <A HREF="<?=reportTypeURL_view($row['report_type'], 'school', $school_id, $row['start_date'], $row['end_date'])?>"><?=T_('View and print Report')?>&raquo;</A>
  <? else: ?>
  <?=T_('Not available for printing')?>
  <? endif; ?>
  <BR>
  <? endif; ?>
  <? if(!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel'): ?>
  <A HREF="<?=reportTypeURL_mark($row['report_type'], 'school', $school_id, $row['start_date'], $row['end_date'])?>"><?=T_('Process Report')?>&raquo;</A>
  <? endif; ?>
</TD>
<TD>
  <? if($row['visibility'] != 'process' && (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel')): ?>
  <?=is_null($row['print_date']) ? "<A HREF='admin_report_list.php?action=report_print&amp;report_id={$row['report_id']}&amp;school_id=$school_id'>" . T_('Mark as printed') . '&raquo;</A>' : T_('Printed on') . " {$row['print_date']}<BR><A HREF='admin_report_list.php?action=report_unprint&amp;report_id={$row['report_id']}&amp;school_id=$school_id'>" . T_('Unmark as printed') . '&raquo;</A>' ?>
  <BR>
  <? endif; ?>
  <? if(!is_null($row['print_date']) || $view_all || $row['visibility'] == 'process' || $row['report_type'] == 'Hakhel'): ?>
  <?=is_null($row['process_date']) ? "<A HREF='admin_report_list.php?action=report_processed&amp;report_id={$row['report_id']}&amp;school_id=$school_id'>" . T_('Mark as done') . '&raquo;</A>' : T_('Processed on') . " {$row['process_date']}<BR><A HREF='admin_report_list.php?action=report_unprocess&amp;report_id={$row['report_id']}&amp;school_id=$school_id'>" . T_('Unmark as done') . '&raquo;</A>' ?>
  <? endif; ?>
</TD>
</TR>

<?endwhile;?>

</TABLE>

<BR style="clear: both;">
</DIV>
</DIV>
<? endif; ?>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
