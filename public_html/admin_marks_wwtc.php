<? $admin_auth = array('school', 'user'); ?>
<? require('header.php'); ?>
<?
// TODO Fix point max to take into account previous entries
// TODO Show multiple checkboxes if mandatory_qty or optional_qty make it possible
require_once('calendar.php');
require_once('file_save.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$user_id = gri('user_id', -1);

if($auth_mode == 'user') check_school_setting($user_id, 'home_school');

$date = gri('date', unixtojd());

if($tasks = gra('tasks')) {
  foreach($tasks as $task_user_id => $data) {
    $mission_date_tasks = array();
    foreach($data as $date_task_id => $value) {
      foreach($value as $type => $entry) {
        $task_user_id = intval($task_user_id);
        $date_task_id = intval($date_task_id);
        $entry = intval($entry);
        $mission_date_tasks[] = $date_task_id;
        if($entry == 0) {
          mq("
DELETE FROM date_tasks_marks USING users
     JOIN subjects
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
     JOIN date_tasks USING (date_tasks_mission_id)
     JOIN date_tasks_marks USING (date_task_id, user_id)
WHERE subject_type = 'WWTC' AND
      date_task_id = $date_task_id AND
      mark_date = $date AND
      user_id = $task_user_id AND
      school_id = $school_id AND
" . ($class_id != -1 ? " class_id = $class_id AND" : '') . "
" . ($user_id != -1 ? " user_id = $user_id AND" : '') . "
      start_date <= $date AND end_date >= $date
");
        } else {
          if($type == 'done') {
            mq("
INSERT INTO date_tasks_marks (date_task_id, user_id, mark_date, done_qty, mark_description, mark_points, mark_quantity)
SELECT date_task_id, user_id, $date mark_date, $entry done_qty, description, points * LEAST($entry, mandatory_qty+optional_qty) points, 0 mark_quantity
FROM users
     JOIN subjects
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
     JOIN date_tasks USING (date_tasks_mission_id)
WHERE subject_type = 'WWTC' AND
      date_task_id = $date_task_id AND
      user_id = $task_user_id AND
      school_id = $school_id AND
" . ($class_id != -1 ? " class_id = $class_id AND" : '') . "
" . ($user_id != -1 ? " user_id = $user_id AND" : '') . "
      start_date <= $date AND end_date >= $date
ON DUPLICATE KEY UPDATE
      done_qty = VALUES(done_qty),
      mark_description = VALUES(mark_description),
      mark_points = VALUES(mark_points),
      mark_quantity = VALUES(mark_quantity)
");
          } elseif($type == 'quantity') {
            mq("
INSERT INTO date_tasks_marks (date_task_id, user_id, mark_date, done_qty, mark_description, mark_points, mark_quantity)
SELECT date_task_id, user_id, $date mark_date, 1 done_qty, description, LEAST($entry, quantity*(mandatory_qty+optional_qty))*(points/quantity) points, $entry mark_quantity
FROM users
     JOIN subjects
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
     JOIN date_tasks USING (date_tasks_mission_id)
WHERE subject_type = 'WWTC' AND
      date_task_id = $date_task_id AND
      user_id = $task_user_id AND
      school_id = $school_id AND
" . ($class_id != -1 ? " class_id = $class_id AND" : '') . "
" . ($user_id != -1 ? " user_id = $user_id AND" : '') . "
      start_date <= $date AND end_date >= $date
ON DUPLICATE KEY UPDATE
      done_qty = VALUES(done_qty),
      mark_description = VALUES(mark_description),
      mark_points = VALUES(mark_points),
      mark_quantity = VALUES(mark_quantity)
");
          } else {
            user_error('unknown type', E_USER_ERROR);
          }
        }
      }
    }

    $result = mq("
SELECT DISTINCT date_tasks_mission_id
FROM users
     JOIN subjects
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
     JOIN date_tasks USING (date_tasks_mission_id)
WHERE subject_type = 'WWTC' AND
      date_task_id IN (" . implode(',', $mission_date_tasks) . ") AND
      user_id = $task_user_id AND
" . ($class_id != -1 ? " class_id = $class_id AND" : '') . "
" . ($user_id != -1 ? " user_id = $user_id AND" : '') . "
      school_id = $school_id
");

    while($row = mysql_fetch_assoc($result)) $missions[$task_user_id][] = $row['date_tasks_mission_id'];
  }
}

if($delete = gra('delete')) foreach($delete as $data) {
  list($date_task_id, $task_user_id, $mark_date) = explode(',', $data);
  $date_task_id = intval($date_task_id);
  $task_user_id = intval($task_user_id);
  $mark_date = intval($mark_date);

  $result = mq("
SELECT date_tasks_mission_id
FROM users
     JOIN subjects
     JOIN date_tasks_missions USING (subject_id)
     JOIN date_tasks USING (date_tasks_mission_id)
     JOIN date_tasks_marks USING (date_task_id, user_id)
WHERE subject_type = 'WWTC' AND
      date_task_id = $date_task_id AND
      mark_date = $mark_date AND
      user_id = $task_user_id AND
" . ($class_id != -1 ? " class_id = $class_id AND" : '') . "
" . ($user_id != -1 ? " user_id = $user_id AND" : '') . "
      school_id = $school_id
");
if($row = mysql_fetch_assoc($result)) $missions[$task_user_id][] = $row['date_tasks_mission_id'];

  mq("
DELETE FROM date_tasks_marks USING users
     JOIN subjects
     JOIN date_tasks_missions USING (subject_id)
     JOIN date_tasks USING (date_tasks_mission_id)
     JOIN date_tasks_marks USING (date_task_id, user_id)
WHERE subject_type = 'WWTC' AND
      date_task_id = $date_task_id AND
      mark_date = $mark_date AND
      user_id = $task_user_id AND
" . ($class_id != -1 ? " class_id = $class_id AND" : '') . "
" . ($user_id != -1 ? " user_id = $user_id AND" : '') . "
      school_id = $school_id
");
}

if(isset($missions)) foreach($missions as $task_user_id => $mission_ids) {
  $result = mq("
SELECT $task_user_id user_id, date_tasks_mission_id, subject_id, mission_value, mission_name,
       NOT EXISTS (
        SELECT *
        FROM date_tasks
             LEFT JOIN date_tasks_marks ON (date_tasks.date_task_id = date_tasks_marks.date_task_id AND date_tasks_marks.user_id = $task_user_id)
        WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND (IFNULL(done_qty, 0) < mandatory_qty OR (quantity IS NOT NULL AND IFNULL(mark_quantity, 0) < quantity*mandatory_qty))
       ) done
FROM   date_tasks_missions
WHERE date_tasks_mission_id IN (" . implode(',', $mission_ids) . ")
");
  while($row = mysql_fetch_assoc($result)) {
    if($row['done']) {
      mq("INSERT INTO date_tasks_mission_marks SET user_id = $task_user_id, date_tasks_mission_id = {$row['date_tasks_mission_id']}, subject_id = {$row['subject_id']}, mission_value = {$row['mission_value']}, mark_date = $date, mission_name = " . ms($row['mission_name']) . " ON DUPLICATE KEY UPDATE subject_id = {$row['subject_id']}, mission_value = {$row['mission_value']}, mark_date = $date, mission_name = " . ms($row['mission_name']));
    } else {
      mq("DELETE FROM date_tasks_mission_marks WHERE user_id = $task_user_id AND date_tasks_mission_id = {$row['date_tasks_mission_id']}");
    }
  }

  mq("DELETE FROM medal_marks USING medal_marks JOIN subjects USING (subject_id) JOIN medals_subjects_totals USING (medal_ord, subject_id) LEFT JOIN (SELECT SUM(mission_value) missions, subject_id, user_id, MAX(mark_date) date_awarded FROM date_tasks_mission_marks JOIN users USING (user_id) JOIN subjects USING (subject_id) WHERE user_id = $task_user_id GROUP BY subject_id, user_id) missions_done USING (subject_id, user_id) WHERE user_id = $task_user_id AND (missions_required_total > missions OR missions IS NULL) AND subject_type = 'WWTC'");
  mq("INSERT IGNORE INTO medal_marks (medal_ord, subject_id, user_id, date_awarded) SELECT medal_ord, missions_done.subject_id, user_id, date_awarded FROM (SELECT SUM(mission_value) missions, subject_id, user_id, MAX(mark_date) date_awarded FROM date_tasks_mission_marks JOIN subjects USING (subject_id) WHERE user_id = $task_user_id AND subject_type = 'WWTC' GROUP BY subject_id, user_id) missions_done JOIN medals_subjects_totals ON (missions_done.subject_id = medals_subjects_totals.subject_id AND missions >= missions_required_total)");
  mq("INSERT IGNORE INTO rank_marks (rank_ord, user_id, date_promoted) SELECT rank_ord, user_id, date_awarded date_promoted FROM (SELECT COUNT(*) medals, user_id, MAX(date_awarded) date_awarded FROM medal_marks WHERE user_id = $task_user_id GROUP BY user_id) medals_done JOIN ranks ON (medals >= medals_required)");
}

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, last, first, username");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Date Tasks Marks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Date Tasks Marks')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($admin_user['auth'] == 'super' || $auth_mode == 'school' && count($admin_user['auths']['school']) > 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_marks_wwtc.php" method="get" accept-charset="UTF-8">
<P>
<?if($admin_user['auth'] == 'super'):?>
<LABEL><?=T_('Effective Date')?>:<INPUT type="text" name="date_disp" READONLY value="<?=es(dateToHebrew($date))?>" onClick="getDate(this.form, 'date');"></LABEL><INPUT type="hidden" name="date" value="<?=$date?>"><BR>
<?else:?>
<INPUT type="hidden" name="date" value="<?=$date?>">
<?endif;?>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>"><BR>
</P>
</FORM>

<HR>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<?if($admin_user['auth'] == 'super' || $auth_mode == 'school'):?>
<FORM action="admin_marks_wwtc.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="date" value="<?=$date?>">

<?=T_('Show Platoon')?> <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($class_row = mysql_fetch_assoc($class_result)): ?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT><BR>
<?=T_('Show Soldier')?> <SELECT name="user_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($user_row = mysql_fetch_assoc($user_result)): ?>
<OPTION value="<?=$user_row['user_id']?>" <?=$user_row['user_id'] == $user_id ? 'SELECTED' : ''?>><?=$class_id == -1 && $user_row['class_grade'] != '' ? es($user_row['class_grade'] . '-' . $user_row['class_sub']) . ': ' : ''?><?=es($user_row['last'])?>, <?=es($user_row['first'])?> (<?=es($user_row['username'])?>)</OPTION>
<?endwhile;?>
</SELECT><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<? endif; ?>

<? $total_marks = mysql_result(mq("SELECT COUNT(DISTINCT user_id) FROM date_tasks_marks JOIN users USING (user_id) JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN subjects USING (subject_id) WHERE subject_type = 'WWTC' AND mark_date = $date AND school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . ($user_id != -1 ? " AND user_id = $user_id" : '') ), 0); ?>

<?
$users = mq("
SELECT user_id, first, last, first_he, last_he, username, user_serial, class_grade, class_sub, school_type_id
FROM users
     LEFT JOIN classes USING (school_id, class_id)
WHERE school_id = $school_id" .
      ($class_id != -1 ? " AND class_id = $class_id" : '') .
      ($user_id != -1 ? " AND user_id = $user_id" : '') .
" ORDER BY class_grade, class_sub, last, first, user_id");
?>

<H2><?=T_('Total Soldiers')?>: <?=mysql_num_rows($users)?> &mdash; <?=T_('Total Entries')?>: <?=$total_marks?> <?= mysql_num_rows($users) ? '&mdash; (' . round(100*$total_marks/mysql_num_rows($users), 2) . '%)' : ''?></H2>

<FORM action="admin_marks_wwtc.php" method="post" accept-charset="UTF-8">
<TABLE class="pretty_grid">
<THEAD>
<TR>
<TH colspan="4"><?=T_('Soldier Name')?></TH>
</TR>
<TR>
<TH><?=T_('Mission Name')?></TH>
<TH><?=T_('Ladder/Year')?></TH>
<TD><?=T_('Task Name')?></TD>
<!-- <TD><?=T_('Previously Entered')?></TD> -->
<TD><?=T_('Current Entry')?></TD>
</TR>
<TR><TD colspan="4" style="border: none;">&nbsp;</TD></TR>
</THEAD>
<? while($user_row = mysql_fetch_assoc($users)): ?>
<TBODY>
<TR>
<TH><?if($auth_mode != 'user'):?><A HREF="admin_user_track.php?action=edit&amp;user_id=<?=$user_row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=T_("Edit Soldier's ladders/years")?></A><?endif;?></TH>
<TH colspan="3">
<? if($user_row['class_grade'] != ''): ?><?=T_('Platoon')?>: <?=es($user_row['class_grade'])?>-<?=es($user_row['class_sub'])?><BR><?endif;?>
<?=T_('Soldier Name')?>: <!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first'])?> <?=es($user_row['last'])?> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?> #<?=es($user_row['user_serial'])?>
</TH>
</TR>

<?
$result = mq("
SELECT date_tasks_mission_id, mission_name, track_name, level,
       date_tasks.date_task_id, name, mandatory_qty, optional_qty, quantity, points, label_id, label_image_id,
       done_qty, mark_description, mark_quantity,
       (SELECT IFNULL(SUM(done_qty), 0) done_total FROM date_tasks_marks WHERE date_tasks_marks.user_id = {$user_row['user_id']} AND date_tasks_marks.date_task_id = date_tasks.date_task_id AND mark_date != $date) done_total,
       (SELECT IFNULL(SUM(mark_quantity), 0) quantity_total FROM date_tasks_marks WHERE date_tasks_marks.user_id = {$user_row['user_id']} AND date_tasks_marks.date_task_id = date_tasks.date_task_id AND mark_date != $date) quantity_total,
       (SELECT IFNULL(SUM(mark_points), 0) points_total FROM date_tasks_marks WHERE date_tasks_marks.user_id = {$user_row['user_id']} AND date_tasks_marks.date_task_id = date_tasks.date_task_id AND mark_date != $date) points_total
FROM subjects
     JOIN user_tracks USING (subject_id)
     JOIN date_tasks_missions USING (subject_id, level, track_id)
     JOIN date_tasks USING (date_tasks_mission_id)
     LEFT JOIN labels USING (label_id)
     LEFT JOIN date_tasks_marks ON (date_tasks.date_task_id = date_tasks_marks.date_task_id AND date_tasks_marks.user_id = {$user_row['user_id']} AND date_tasks_marks.mark_date = $date)
     LEFT JOIN tracks USING (track_id)
WHERE user_tracks.user_id = {$user_row['user_id']} AND school_type_id = {$user_row['school_type_id']} AND subject_type = 'WWTC' AND
      start_date <= $date AND end_date >= $date
ORDER BY mission_name, date_tasks_mission_id, ord, name, date_tasks.date_task_id
");

$old_row = false;
$row = mysql_fetch_assoc($result);
if($row) do {
?>
<TR>
<TH><?=es($row['mission_name'])?></TH>
<TH><?=T_('Ladder')?>: <?=$row['track_name']?> <?=T_('Year')?>: <?=$row['level']?></TH>
<? do { ?>
<? if($old_row['date_tasks_mission_id'] == $row['date_tasks_mission_id']) echo '<TR><TD></TD><TD></TD>' ?>
<? $old_row = $row; ?>
<TD><?=!is_null($row['label_image_id']) ? linkImgFile($row['label_image_id'], NULL, 20, 'style="vertical-align: middle;"') : ''?> <?=es($row['name'])?></TD>
<!--<TD>
  <?=T_('Miles')?>: <?=floatval($row['points_total'])?>
  <?=is_null($row['quantity']) ? T_('Done') . ': ' . $row['done_total'] : T_('Quantity') . ': ' . $row['quantity_total'] ?>
</TD>-->
<TD>
  <? if(is_null($row['quantity'])): ?>
    <INPUT type="hidden" name="tasks[<?=$user_row['user_id']?>][<?=$row['date_task_id']?>][done]" value="0"><LABEL><?=T_('Completed')?>? <INPUT type="checkbox" <?=$row['done_qty'] ? 'checked' : ''?> name="tasks[<?=$user_row['user_id']?>][<?=$row['date_task_id']?>][done]" value="1"></LABEL>
  <? else: ?>
    <INPUT type="text" name="tasks[<?=$user_row['user_id']?>][<?=$row['date_task_id']?>][quantity]" value="<?=$row['mark_quantity']?>" maxlength="10" size="10" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 4294967295));">
  <? endif; ?>
</TD>
</TR>
<? $row = mysql_fetch_assoc($result); ?>
<? } while($row && $row['date_tasks_mission_id'] == $old_row['date_tasks_mission_id']); ?>
<? } while($row); ?>

<? $result = mq("SELECT mission_name, name, track_name, level, done_qty, mark_quantity, mark_points, date_task_id, quantity, label_image_id FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN subjects USING (subject_id) LEFT JOIN labels USING (label_id) LEFT JOIN tracks USING (track_id) LEFT JOIN user_tracks USING (user_id, subject_id, track_id, level) WHERE subject_type = 'WWTC' AND user_id = {$user_row['user_id']} AND mark_date = $date AND user_tracks.user_id IS NULL"); ?>

<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
  <TH>
    <?=es($row['mission_name'])?>
  </TH>
  <TH>
    <?=T_('Mark awarded in different Ladder or Year')?><BR>
    <?=T_('Ladder')?>: <?=$row['track_name']?> <?=T_('Year')?>: <?=$row['level']?>
  </TH>
  <TD><?=!is_null($row['label_image_id']) ? linkImgFile($row['label_image_id'], NULL, 20, 'style="vertical-align: middle;"') : ''?> <?=es($row['name'])?></TD>
  <TD>
    <?=T_('Miles')?>: <?=floatval($row['mark_points'])?>
    <?=is_null($row['quantity']) ? T_('Done') . ': ' . $row['done_qty'] : T_('Quantity') . ': ' . $row['mark_quantity'] ?><BR>
    <?=T_('Delete')?>? <INPUT type="checkbox" name="delete[]" value="<?=$row['date_task_id']?>,<?=$user_row['user_id']?>,<?=$date?>">
  </TD>
</TR>
<? endwhile; ?>

<TR><TD colspan="4" style="border: none;">&nbsp;</TD></TR>
</TBODY>
<? endwhile; ?>
</TABLE>
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<INPUT type="hidden" name="date" value="<?=$date?>">
<INPUT type="submit" value="<?=T_('Save')?>"> <?=T_('Note: Once a rank has been awarded for completing missions it will not be revoked if the mission entry is subsequently removed.')?>
</P>
</FORM>
<?endif;?>
</DIV>
</BODY>
</HTML>
