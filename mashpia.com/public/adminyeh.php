<? $admin_auth = array('school','class','team','user'); ?>
<? require('header.php'); ?>
<? require_once('file_save.php'); ?>
<? require_once('calendar.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Admin Menu'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<STYLE type="text/css">
.points {
  margin: 10px 0px;
}

.points tbody th {
  text-align: <?=$align_start?>;
}

.points tbody td {
  text-align: right;
}
</STYLE>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">

<DIV class="admin">

<? $first = true; ?>
<? foreach($admin_user['auths']['school'] as $school_id): ?>
<?
unset($report_id);
if(($action = gr('action')) && gri('todo_school_id') == $school_id) switch($action) {
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

  case 'todo_mark':
    $todo_id = gri('todo_id', -1);
    if(mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
      mq("INSERT IGNORE INTO todo_list_marks SET todo_id = $todo_id, auth = 'school', id = $school_id");
    break;

  case 'todo_unmark':
    $todo_id = gri('todo_id', -1);
    if(mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
      mq("DELETE FROM todo_list_marks WHERE todo_id = $todo_id AND auth = 'school' AND id = $school_id");
    break;
}
?>

<? $row = mysql_fetch_assoc(mq("SELECT school_name, inst_name, school_address1, school_address2, school_city, school_state, school_country, school_postal, school_phone, school_logo_id, school_types.school_type_name, tracks.track_name FROM schools LEFT JOIN school_types ON (schools.default_school_type_id = school_types.school_type_id) LEFT JOIN tracks ON (schools.default_track_id = tracks.track_id) LEFT JOIN institutions USING (inst_id) WHERE school_id = $school_id")); ?>
<?=$first ? '<H1>' . T_('Home page') . '</H1>' . (!empty($message) ? '<H2>' . $message . '</H2>' : '') : '<HR><HR>'?>

<H2><?=T_('My Dashboard for')?>:</H2>
<DIV><?=!is_null($row['school_logo_id']) ? linkImgFile($row['school_logo_id'], NULL, '100') : ''?></DIV>
<DIV><?=T_('Welcome, Commanding Officer')?>: <?=$admin_user['display']?></DIV>
<H3><?=T_('of')?> <?=es($school_name = $row['school_name'])?></H3>
<ADDRESS>
<?=es($row['school_address1'])?><BR>
<?=es($row['school_address2'])?><?=$row['school_address2'] ? '<BR>' : ''?>
<?=es($row['school_city'])?> <?=es($row['school_state'])?>, <?=es($row['school_postal'])?><BR>
<?=es($row['school_country'])?><?=$row['school_country'] ? '<BR>' : ''?>
<?=es($row['school_phone'])?><?=$row['school_phone'] ? '<BR>' : ''?>
</ADDRESS>
<P>
<?=T_('Default Tzivos Hashem Type')?>: <?=es($row['school_type_name'])?><BR>
<?=T_('Default Ladder')?>: <?=es($row['track_name'])?><BR>
</P>

<HR>

<? if($admin_user['auth'] == 'inactive'): // fixme: loop for each school, or class, or user, etc ?>
<H2>Base Management</H2>

<P>
View your <A HREF="admin_school.php" style="background-color: lightblue;">Base Profile</A> to upload your school logo, school database, or edit the information about your school.
</P>
<P>
You will receive an e-mail when your account activation is complete.
</P>
<P>
Please proceed to the To-Do list below:
</P>
<P>
Thank you and much Hatzlocho!
</P>
<HR>
<? endif; ?>

<H2 id="todo_list<?=$school_id?>"><?=T_('My To-Do list')?></H2>

<? $view_all = gri('view_all', 0); ?>

<P>
<A HREF="admin.php?view_all=<?=!$view_all?>#todo_list<?=$school_id?>"><?= $view_all ? T_('List only unfinished to-do items') : T_('List all to-do items') ?>&raquo;</A>
</P>

<TABLE class="list list_<?=$align_start?>">
<THEAD>
<TR>
  <TH><?=T_('Priority')?></TH>
  <TH><?=T_('Due Date')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('View/Print')?></TH>
  <TH><?=T_('Complete')?></TH>
</TR>
</THEAD>

<? if($admin_user['auth'] != 'inactive'): ?>

<? $result = mq("SELECT reports.report_id, report_name, report_type, start_date, end_date, print_date, process_date, visibility FROM reports LEFT JOIN report_marks ON (reports.report_id = report_marks.report_id AND id = $school_id AND auth = 'school') WHERE visibility != 'none'" . ($view_all ? '' : ' AND (print_date IS NULL OR process_date IS NULL)') . ' ORDER BY creation_date, report_name, reports.report_id'); ?>

<TBODY>
  <TR>
    <TH colspan="4">
	<A HREF="#cat_<?=$school_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>" 
	onClick="var el = document.getElementById('cat_<?=$school_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>');
	if(el.style.display == '') { el.style.display = 'none'; this.getElementsByTagName('span')[0].innerHTML = '+'; } else { el.style.display = ''; this.getElementsByTagName('span')[0].innerHTML = '&minus;'; }; return false;"><SPAN>+plus</SPAN> &lt;<?=T_('Reports')?>&gt;</A></TH>
    <TH><?=sprintf(T_('%d items'), mysql_num_rows($result))?></TH>
  </TR>
</TBODY>

<TBODY id="cat_<?=$school_id, '_', str_replace('%', ':', rawurlencode('report')), ':'?>" style="<?=!isset($report_id) ? 'display: none;' : ''?> border-top: none;">

<?while($row=mysql_fetch_assoc($result)):?>

<TR>
<TD></TD>
<TD></TD>
<TD><?=es($row['report_name'])?></TD>
<TD>
  <? if(is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel'): ?>
  <? if($row['visibility'] != 'process'): ?>
  <A HREF=
<?=$row['report_type'] != '' ? "'admin_report_" . strtolower($row['report_type']) . ".php?date={$row['start_date']}'" :
"'admin_report.php?start_date={$row['start_date']}&amp;end_date={$row['end_date']}'" ?>
><?=T_('View and print Report')?>&raquo;</A>
  <? else: ?>
  <?=T_('Not available for printing')?>
  <? endif; ?>
  <BR>
  <? endif; ?>
  <? if(!is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel'): ?>
  <A HREF=
<?=$row['report_type'] != '' ? ($row['report_type'] == 'Auction' ? "'admin_user_auction" : "'admin_marks_" . strtolower($row['report_type'])) . ".php?date={$row['start_date']}'" : "'admin_marks.php?start_date={$row['start_date']}&amp;end_date={$row['end_date']}'"?>
><?=T_('Process Report')?>&raquo;</A>
  <? endif; ?>
</TD>
<TD>
  <? if($row['visibility'] != 'process' && (is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel')): ?>
  <?=is_null($row['print_date']) ? "<A HREF='admin.php?action=report_print&amp;report_id={$row['report_id']}&amp;todo_school_id=$school_id#todo_list$school_id'>" . T_('Mark as printed') . '&raquo;</A>' : T_('Printed on') . " {$row['print_date']}<BR><A HREF='admin.php?action=report_unprint&amp;report_id={$row['report_id']}&amp;todo_school_id=$school_id#todo_list$school_id'>" . T_('Unmark as printed') . '&raquo;</A>' ?>
  <BR>
  <? endif; ?>
  <? if(!is_null($row['print_date']) || $view_all || $row['report_type'] == 'Hakhel'): ?>
  <?=is_null($row['process_date']) ? "<A HREF='admin.php?action=report_processed&amp;report_id={$row['report_id']}&amp;todo_school_id=$school_id#todo_list$school_id'>" . T_('Mark as done') . '&raquo;</A>' : T_('Processed on') . " {$row['process_date']}<BR><A HREF='admin.php?action=report_unprocess&amp;report_id={$row['report_id']}&amp;todo_school_id=$school_id#todo_list$school_id'>" . T_('Unmark as done') . '&raquo;</A>' ?>
  <? endif; ?>
</TD>
</TR>

<?endwhile;?>
</TBODY>

<? endif; ?>

<?
$result = mq("SELECT todo_list.todo_id, todo_text, todo_priority, category_name, sub_name, sub_id, todo_due_date, todo_file_id, todo_url, mark_date, todo_list.recip_id FROM todo_list LEFT JOIN todo_category_sub USING (sub_id) LEFT JOIN todo_categories USING (category_id) LEFT JOIN todo_list_marks ON (todo_list.todo_id = todo_list_marks.todo_id AND todo_list_marks.auth = 'school' AND todo_list_marks.id = $school_id) WHERE visibility != 'none' AND todo_list.school_id IS NULL AND todo_list.recip = 'school' AND (todo_list.recip_id = $school_id OR todo_list.recip_id IS NULL)" . ($view_all ? '' : ' AND mark_date IS NULL') . ' ORDER BY category_name, sub_name, todo_priority, todo_due_date, todo_text, creation_date, todo_list.todo_id');
echo "sql is here";
echo $result;
echo "after";
$row = $old_row = mysql_fetch_assoc($result);

if($row) do {
  $count = 0;
  ob_start();

  do {
    $count++;
    if(isset($todo_id) && $row['todo_id'] == $todo_id) $this_todo = true;
    $old_row = $row;
    $cat = $row['sub_id'];
?>
<TR>
<TD><?=$row['todo_priority']?></TD>
<TD><?=es(dateToHebrew($row['todo_due_date']))?></TD>
<TD><?=is_null($row['recip_id']) ? '' : $s = '* ', es($row['todo_text'])?></TD>
<TD><?if(!is_null($row['todo_file_id'])):?><A HREF="file_view.php?id=<?=$row['todo_file_id']?>&amp;m=d"><?=T_('View/Print File')?>&raquo;</A><?endif;?> <?if($row['todo_url']):?><A HREF="<?=es($row['todo_url'])?>"><?=T_('Goto Link')?>&raquo;</A><?endif;?></TD>
<TD><?=is_null($row['mark_date']) ? "<A HREF='admin.php?action=todo_mark&amp;todo_id={$row['todo_id']}&amp;todo_school_id=$school_id&amp;cat=$cat#todo_list$school_id'>" . T_('Mark as done') . '&raquo;</A>' : T_('Marked on') . " {$row['mark_date']}<BR><A HREF='admin.php?action=todo_unmark&amp;todo_id={$row['todo_id']}&amp;todo_school_id=$school_id&amp;cat=$cat#todo_list$school_id'>" . T_('Unmark as done') . '&raquo;</A>' ?></TD>
</TR>
<?
    $row = mysql_fetch_assoc($result);
  } while($row && $row['sub_id'] == $old_row['sub_id']);
?>
<? $out = ob_get_clean(); ?>
<TBODY>
  <TR>
    <TH colspan="4"><A HREF="#cat_<?=$school_id, '_', $old_row['sub_id']?>" onClick="var el = document.getElementById('cat_<?=$school_id, '_', $old_row['sub_id']?>'); if(el.style.display == '') { el.style.display = 'none'; this.getElementsByTagName('span')[0].innerHTML = '+'; } else { el.style.display = ''; this.getElementsByTagName('span')[0].innerHTML = '&minus;'; }; return false;"><SPAN>+yehudah</SPAN> <?=es($old_row['category_name']), ' / ', es($old_row['sub_name'])?></A></TH>
    <TH><?=sprintf(T_('%d items'), $count)?></TH>
  </TR>
</TBODY>
<TBODY id="cat_<?=$school_id, '_', $old_row['sub_id']?>" style="<?=$old_row['sub_id'] == gr('cat') ? '' : 'display: none;'?> border-top: none;">
<?=$out?>
</TBODY>
<?
} while($row);
unset($out);
?>

</TABLE>
<?if(isset($s)):?><P>* <?=T_('This Todo is for your school only.')?></P><?unset($s);?><?endif;?>
<HR>

<? if($admin_user['auth'] != 'inactive'): ?>

<H2><?=T_('My Management system')?></H2>

<? $menu_type = 'school'; ?>
<? include('admin_inc.php'); ?>

<HR>

<H2><?=T_('Tzivos Hashem Stats')?></H2>

<TABLE class="pretty_grid points">
<THEAD>
<TR>
  <TH></TH>
  <TH><?=T_('Soldiers')?></TH>
  <TH><?=T_('Total Points')?></TH>
  <TH><?=T_('Average Points')?></TH>
</TR>
</THEAD>
<? $result = mq("
SELECT 0 ord, '" . T_('All schools') . "' name, (SELECT COUNT(*) FROM users WHERE user_start_date IS NOT NULL) num, (" . totalMarks('JOIN users USING (user_id)') . ") points
UNION ALL
SELECT 1 ord, " . ms($school_name) . " name, (SELECT COUNT(*) FROM users WHERE school_id = $school_id AND user_start_date IS NOT NULL) num, (" . totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL") . ") points
UNION ALL
SELECT 2 ord, IFNULL(CONCAT('" . T_('Platoon') . ": ', class_grade, '-', class_sub), '". T_('Not in a platoon') . "') name, COUNT(DISTINCT users.user_id), IFNULL(marks.mark_points, 0) FROM users LEFT JOIN classes USING (school_id, class_id) LEFT JOIN (" . totalMarks("JOIN users USING (user_id) LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL", 'school_id, class_id') . ") marks USING (school_id, class_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL GROUP BY classes.school_id, classes.class_id, mark_points
/*
UNION ALL
SELECT 3 ord, IFNULL(CONCAT('" . T_('Squad') . ": ', team_name), '". T_('Not in a squad') . "') name, COUNT(DISTINCT users.user_id), IFNULL(marks.mark_points, 0) FROM users LEFT JOIN teams USING (school_id, team_id) LEFT JOIN (" . totalMarks("JOIN users USING (user_id) LEFT JOIN teams USING (school_id, team_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL", 'school_id, team_id') . ") marks USING (school_id, team_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL GROUP BY teams.school_id, teams.team_id, mark_points
*/
ORDER BY ord, name
"); ?>
<TBODY>
<?while($row=mysql_fetch_assoc($result)):?>
<TR>
<TH><?=es($row['name'])?></TH>
<TD><?=$row['num']?></TD>
<TD><?=number_format($row['points'], 2)?></TD>
<TD><?=$row['num'] ? number_format($row['points']/$row['num'], 2) : '-'?></TD>
</TR>
<?endwhile;?>
</TBODY>
</TABLE>
<? endif; ?>

<? $first = false; ?>
<? endforeach; ?>
<? unset($school_id); ?>

<? foreach($admin_user['auths']['class'] as $class_id): ?>
<HR>
<H2><?=T_('My Platoon')?></H2>
<? $menu_type = 'class'; ?>
<? include('admin_inc.php'); ?>
<? endforeach; ?>
<? unset($class_id); ?>

<? $range = gri('range', 0); ?>
<? foreach($admin_user['auths']['user'] as $user_id): ?>
<?
if(($action = gr('action')) && gri('todo_user_id') == $user_id) switch($action) {
  case 'todo_mark':
    $todo_id = gri('todo_id', -1);
    if(mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
      mq("INSERT IGNORE INTO todo_list_marks SET todo_id = $todo_id, auth = 'user', id = $user_id");
    break;

  case 'todo_unmark':
    $todo_id = gri('todo_id', -1);
    if(mysql_result(mq("SELECT COUNT(*) FROM todo_list WHERE todo_id = $todo_id"), 0))
      mq("DELETE FROM todo_list_marks WHERE todo_id = $todo_id AND auth = 'user' AND id = $user_id");
    break;
}

if(($codes = gra('code')) && gri('user_id') == $user_id) foreach($codes as $code) {
  mq("INSERT IGNORE INTO user_codes (user_id, code_id, code_id_prefix, admin_id) VALUES ($user_id, " . preg_replace('/\D/', '', substr($code, 1)) . ', ' . intval(substr($code, 0, 1)) . ", {$admin_user['admin_id']})");
  $stored_code = true;
}
?>

<?=$first ? '<H1>' . T_('Home page') . '</H1>' . (!empty($message) ? '<H2>' . $message . '</H2>' : '') : '<HR><HR>'?>

<? $user_row = mysql_fetch_assoc(mq("SELECT first, last, user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, school_name, school_id, class_grade, class_sub, role_name FROM users LEFT JOIN schools USING (school_id) LEFT JOIN classes USING (school_id, class_id) LEFT JOIN admin_auths ON (user_id = id) LEFT JOIN roles USING (role_id) WHERE admin_id = {$admin_user['admin_id']} AND user_id = $user_id")); ?>

<H2><?=T_('Welcome')?>, <?=$user_row['role_name'], $user_row['role_name'] ? ' ' . T_('of') : ''?> <?=$admin_user['display']?></H2>
<H3><?=T_('My Soldier')?></H3>

<DIV style="font-size: 150%;"><?=es($user_row['first'] . ' ' . $user_row['last'])?><BR>
<?=es($user_row['school_name']), ': ', $user_row['class_grade'], '-', $user_row['class_sub']?>
</DIV>
<ADDRESS>
<?=es($user_row['user_address1'])?><BR>
<?=es($user_row['user_address2'])?><?=$user_row['user_address2'] ? '<BR>' : ''?>
<?=es($user_row['user_city'])?> <?=es($user_row['user_state'])?>, <?=es($user_row['user_postal'])?><BR>
<?=es($user_row['user_country'])?><?=$user_row['user_country'] ? '<BR>' : ''?>
<?=es($user_row['user_phone'])?><?=$user_row['user_phone'] ? '<BR>' : ''?>
</ADDRESS>

<H2 id="todo_list<?=$user_id?>"><?=T_('My To-Do list')?></H2>
<? $view_all = gri('view_all', 0); ?>

<P>
<A HREF="admin.php?view_all=<?=!$view_all?>#todo_list<?=$user_id?>"><?= $view_all ? T_('List only unfinished to-do items') : T_('List all to-do items') ?>&raquo;</A>
</P>
<TABLE class="list list_<?=$align_start?>">
<THEAD>
<TR>
  <TH><?=T_('Priority')?></TH>
  <TH><?=T_('Due Date')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('View/Print')?></TH>
  <TH><?=T_('Complete')?></TH>
</TR>
</THEAD>
<?
$result = mq("SELECT todo_list.todo_id, todo_text, todo_priority, category_name, sub_name, sub_id, todo_due_date, todo_file_id, todo_url, mark_date, todo_list.recip_id FROM users JOIN todo_list LEFT JOIN todo_category_sub USING (sub_id) LEFT JOIN todo_categories USING (category_id) LEFT JOIN todo_list_marks ON (todo_list.todo_id = todo_list_marks.todo_id AND todo_list_marks.auth = 'user' AND todo_list_marks.id = $user_id) WHERE user_id = $user_id AND visibility != 'none' AND todo_list.school_id = users.school_id AND todo_list.recip = 'user' AND (todo_list.recip_id = $user_id OR todo_list.recip_id IS NULL)" . ($view_all ? '' : ' AND mark_date IS NULL') . ' ORDER BY category_name, sub_name, todo_priority, todo_due_date, todo_text, creation_date, todo_list.todo_id');

$row = $old_row = mysql_fetch_assoc($result);

if($row) do {
  $count = 0;
  ob_start();

  do {
    $count++;
    if(isset($todo_id) && $row['todo_id'] == $todo_id) $this_todo = true;
    $old_row = $row;
    $cat = $row['sub_id'];
?>
<TR>
<TD><?=$row['todo_priority']?></TD>
<TD><?=es(dateToHebrew($row['todo_due_date']))?></TD>
<TD><?=is_null($row['recip_id']) ? '' : $s = '* ', es($row['todo_text'])?></TD>
<TD><?if(!is_null($row['todo_file_id'])):?><A HREF="file_view.php?id=<?=$row['todo_file_id']?>&amp;m=d"><?=T_('View/Print File')?>&raquo;</A><?endif;?> <?if($row['todo_url']):?><A HREF="<?=es($row['todo_url'])?>"><?=T_('Goto Link')?>&raquo;</A><?endif;?></TD>
<TD><?=is_null($row['mark_date']) ? "<A HREF='admin.php?action=todo_mark&amp;todo_id={$row['todo_id']}&amp;todo_user_id=$user_id&amp;cat=$cat#todo_list$user_id'>" . T_('Mark as done') . '&raquo;</A>' : T_('Marked on') . " {$row['mark_date']}<BR><A HREF='admin.php?action=todo_unmark&amp;todo_id={$row['todo_id']}&amp;todo_user_id=$user_id&amp;cat=$cat#todo_list$user_id'>" . T_('Unmark as done') . '&raquo;</A>' ?></TD>
</TR>
<?
    $row = mysql_fetch_assoc($result);
  } while($row && $row['sub_id'] == $old_row['sub_id']);
?>
<? $out = ob_get_clean(); ?>
<TBODY>
  <TR>
    <TH colspan="4"><A HREF="#cat_<?=$user_id, '_', $old_row['sub_id']?>" onClick="var el = document.getElementById('cat_<?=$user_id, '_', $old_row['sub_id']?>'); if(el.style.display == '') { el.style.display = 'none'; this.getElementsByTagName('span')[0].innerHTML = '+'; } else { el.style.display = ''; this.getElementsByTagName('span')[0].innerHTML = '&minus;'; }; return false;"><SPAN>+</SPAN> <?=es($old_row['category_name']), ' / ', es($old_row['sub_name'])?></A></TH>
    <TH><?=sprintf(T_('%d items'), $count)?></TH>
  </TR>
</TBODY>
<TBODY id="cat_<?=$user_id, '_', $old_row['sub_id']?>" style="<?=$old_row['sub_id'] == gr('cat') ? '' : 'display: none;'?> border-top: none;">
<?=$out?>
</TBODY>
<?
} while($row);
unset($out);
?>

</TABLE>
<?if(isset($s)):?><P>* <?=T_('This Todo is for you only.')?></P><?unset($s);?><?endif;?>
<HR>

<? $menu_type = 'user'; ?>
<? include('admin_inc.php'); ?>

<HR>

<? if(isset($stored_code)): ?>
<P style="font-size: 200%; color: brown; text-decoration: underline;"><?=sprintf(T_('Card: %s granted to Soldier.'), implode(', ', $codes))?></P>
<? endif; ?>

<H2><?=T_('Award Achievement Cards')?></H2>

<H3><?=T_('Local (school) achievement cards')?></H3>

<FORM action="admin_points_print.php" method="get" accept-charset="UTF-8">
<?$template_result = mq("SELECT points_codes_template_id, points, subject_name, left_circle, right_circle, description FROM points_codes_templates LEFT JOIN subjects USING (subject_id) WHERE (school_id IS NULL OR school_id = {$user_row['school_id']}) ORDER BY subject_name, left_circle, right_circle, description, points_codes_template_id");?>
<P>
<LABEL><?=T_('Select Template')?>: <SELECT name="points_codes_template_id">
<?while($row = mysql_fetch_assoc($template_result)):?>
<OPTION value="<?=$row['points_codes_template_id']?>"><?=floatval($row['points'])?> <?=T_('Miles')?> : (<?=es($row['left_circle'])?>) <?=es($row['description'])?> : (<?=es($row['right_circle'])?>) <?=es($row['subject_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<H3><?=T_('Tanya in 5 minutes a day')?></H3>

<P><?=T_("Once a week print an achievement card for the amount of days your child learnt tanya ba'al peh for 5 minutes.")?></P>

<FORM action="admin_points_print.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Tanya was learned for')?>: <SELECT name="tanya">
<OPTION value="1">1 <?=T_('day')?>, 0.5 <?=T_('miles')?>
<OPTION value="2">2 <?=T_('days')?>, 1.0 <?=T_('miles')?>
<OPTION value="3">3 <?=T_('days')?>, 1.5 <?=T_('miles')?>
<OPTION value="4">4 <?=T_('days')?>, 2.0 <?=T_('miles')?>
<OPTION value="5">5 <?=T_('days')?>, 2.5 <?=T_('miles')?>
<OPTION value="6">6 <?=T_('days')?>, 3.0 <?=T_('miles')?>
<OPTION value="7">7 <?=T_('days')?>, 7.0 <?=T_('miles')?> (<?=T_('Includes bonus')?>)
</SELECT></LABEL><BR>
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<H3><?=T_('Tanya release cards')?></H3>

<P><?=T_('Along the way to earning each medal there are 4 checkpoints when your child will need to be tested on ALL THE TANYA HE KNOWS. Once he hits a check point he will no longer be able to scan more lines. Once you have tested him on EVERYTHING he knows and he can say it perfectly without any mistakes you can award him the tanya release card.')?></P>

<FORM action="admin_points_print.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Medal stage/progress')?>: <SELECT name="medal_stage">
<OPTION value="1">25%
<OPTION value="2">50%
<OPTION value="3">75%
<OPTION value="4">100%
</SELECT></LABEL><BR>
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<H3><?=T_('Hakhel Cards')?></H3>

<P><?=T_('Once you child has completed his hakhel mission for server days you award him a hakhel mission card. If he did 4 out of the six hakhel tasks at least once during the seven days then he gets the bonus card.')?></P>

<FORM action="admin_points_print.php" method="get" accept-charset="UTF-8">
<P>
<? $mission_result = mq('SELECT DISTINCT subject_id, subject_name, inst_name, mission_name, mission_number FROM subjects JOIN institutions USING (inst_id) JOIN school_type_subjects USING (subject_id) JOIN date_tasks_missions USING (school_type_id, subject_id) WHERE'  . ($admin_user['auth'] != 'super' ? ' inst_id IN (' . implode(',', $admin_user['inst_ids']) . ') AND' : '') . ' subject_type != \'school_points\' AND mission_number IS NOT NULL ORDER BY inst_name, subject_name, mission_number, mission_name'); ?>
<LABEL><?=T_('Select Mission')?>:
<SELECT name="subject_mission">
  <? while($row = mysql_fetch_assoc($mission_result)): ?>
    <OPTION VALUE="<?=$row['subject_id']?>/<?=$row['mission_number']?>"><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name']), ', ', es($row['mission_name']), ' #', $row['mission_number']?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL><BR>
<LABEL><?=T_('Cards with Bonus')?>: <SELECT name="is_bonus" style="width: auto">
<OPTION value="0">
<OPTION value="1"><?=T_('Yes')?>
</SELECT></LABEL><BR>
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<HR>

<?if(!is_null($range)):?>
<?
$user_miles = mysql_result(mq(totalMarks("WHERE user_id = $user_id")), 0);
$result = userStatement($user_id, $range);
$running_balance = $user_miles;
?>
<H2><?=T_("Soldier's Miles Statement")?></H2>
<TABLE class="pretty_grid">
<? if(mysql_num_rows($result)): ?>
<TR>
  <TH><?=T_('Posting Date')?></TH>
  <TH><?=T_('Subject')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('Points Earned')?></TH>
  <TH><?=T_('Balance')?></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
  <TD><?=dateToHebrew($row['mark_date'])?></TD>
  <TD><?=es($row['subject_name'])?><BR><?=es($row['name'])?></TD>
  <TD><?=es($row['description'])?></TD>
  <TD style="text-align: right;"><?=floatval($row['points']) ? number_format($row['points'], 2) : '-'?></TD>
  <TD style="text-align: right;"><?=number_format($running_balance, 2)?></TD>
</TR>
<? $running_balance -= $row['points']; ?>
<? endwhile; ?>
<? else: ?>
<TR><TD><?=T_('No transactions for the time period selected.')?></TD></TR>
<? endif; ?>
</TABLE>

<P class="noprint">
<?=T_('Show')?>:
<A HREF="admin.php?range=0"><?=T_('Today')?></A> &bull;
<A HREF="admin.php?range=1"><?=T_('This week')?></A> &bull;
<A HREF="admin.php?range=2"><?=T_('Two weeks')?></A> &bull;
<A HREF="admin.php?range=4"><?=T_('Four weeks')?></A> &bull;
<A HREF="admin.php?range=52"><?=T_('One Year')?></A>
</P>

<?endif;?>

<? $first = false; ?>
<? endforeach; ?>
<? unset($user_id); ?>

<? if($admin_user['auth'] != 'inactive'): ?>
<HR>

<? if($admin_user['auth'] == 'super'): ?>
<H1><?=T_('Overall Admin page')?></H1>
<? endif; ?>

<? $menu_type = ''; ?>
<? include('admin_inc.php'); ?>

<HR>

<? endif; ?>

<? if(count($admin_user['auths']['school']) || count($admin_user['auths']['class']) || count($admin_user['auths']['team'])): ?>

<H2><?=T_('Tzivos Hashem army recruitment video')?>:</H2>

<P>
<A HREF="http://anash.com/th.html"><?=T_('Watch')?></A> &bull; <A HREF="http://www.anash.com/video/Tzivos_Hashem_intro_to_CTH.avi"><?=T_('Download super high quality')?> (1GB)</A> &bull; <A HREF="http://anash.com/THpresentation_one_4000k.wmv"><?=T_('Download high quality')?> (143MB)</A> &bull; <A HREF="http://anash.com/THpresentation_one_340k.wmv"><?=T_('Download low quality')?> (12MB)</A>
</P>

<HR>

<H2><?=T_('Hakhel Slideshow')?></H2>

<A HREF="Hakhel_Slideshow_for_Teachers.pdf"><?=T_('Download Hakhel Slideshow for Teachers')?></A>

<HR>

<? endif; ?>

<!--
<H2><?=T_('Login to my Tzivos Hashem shop')?></H2>

<FORM action="http://www.tzivoshashem.org/shop/index.php?l=login" method="post">
<P>
<INPUT type="hidden" name="issecure" value="">

<LABEL>Username:<BR>
<INPUT type="text" name="username" size="15">
</LABEL>
<BR>
<LABEL>Password:<BR>
<INPUT type="password" name="password" size="15">
</LABEL>
<BR>
<INPUT type="submit" value="<?=T_('Log In')?>">
</P>

<P>
<A HREF="http://www.tzivoshashem.org/shop/index.php?l=account"><?=T_('Register')?></A> &bull;
<A HREF="http://www.tzivoshashem.org/shop/index.php?l=page_view&amp;p=forgot_password"><?=T_('Forgot Password?')?></A>
</P>

</FORM>

<HR>
-->

</DIV>
</DIV>

<? include('admin_footer.php'); ?>
</BODY>
</HTML>
