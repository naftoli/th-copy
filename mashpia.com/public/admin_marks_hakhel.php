<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');

assure_id_school('school_id');
$school_id = gri('school_id', -1);
$date = gri('date', unixtojd());

$class_id = gri('class_id', -1);

if($missions = gra('missions')) {
  $bonus = gra('bonus');
  foreach($missions as $user_id => $data) {
    $user_id = intval($user_id);
    foreach($data as $date_tasks_mission_id) {
      $date_tasks_mission_id = intval($date_tasks_mission_id);

      mq('INSERT IGNORE INTO date_tasks_marks (date_task_id, user_id, mark_date, done_qty, mark_description, mark_points, mark_quantity) SELECT date_task_id, user_id, ' . unixtojd() . ", mandatory_qty, description, mandatory_qty*points, quantity FROM date_tasks JOIN users JOIN subjects JOIN school_type_subjects USING (subject_id, school_type_id) JOIN user_tracks USING (user_id, subject_id) JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id, date_tasks_mission_id) WHERE mandatory_qty >= 1 AND subject_type = 'Hakhel' AND start_date <= $date AND end_date >= $date AND date_tasks_mission_id = $date_tasks_mission_id AND school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " AND user_id = $user_id AND (is_bonus = 0 OR is_bonus = " . intval($bonus[$user_id][$date_tasks_mission_id]) . ')');

      mq('INSERT IGNORE INTO date_tasks_mission_marks (user_id, date_tasks_mission_id, subject_id, mission_value, mission_name, mark_date) SELECT user_id, date_tasks_mission_id, subject_id, mission_value, mission_name, ' . unixtojd() . " FROM users JOIN subjects JOIN school_type_subjects USING (subject_id, school_type_id) JOIN user_tracks USING (user_id, subject_id) JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id) WHERE subject_type = 'Hakhel' AND start_date <= $date AND end_date >= $date AND date_tasks_mission_id = $date_tasks_mission_id AND school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " AND user_id = $user_id");
    }

    mq("DELETE FROM medal_marks USING medal_marks JOIN subjects USING (subject_id) JOIN medals_subjects_totals USING (medal_ord, subject_id) LEFT JOIN (SELECT SUM(mission_value) missions, subject_id, user_id, MAX(mark_date) date_awarded FROM date_tasks_mission_marks JOIN users USING (user_id) JOIN subjects USING (subject_id) WHERE user_id = $user_id GROUP BY subject_id, user_id) missions_done USING (subject_id, user_id) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " AND user_id = $user_id AND (missions_required_total > missions OR missions IS NULL) AND subject_type = 'Hakhel'");
    mq("INSERT IGNORE INTO medal_marks (medal_ord, subject_id, user_id, date_awarded) SELECT medal_ord, missions_done.subject_id, user_id, date_awarded FROM (SELECT SUM(mission_value) missions, subject_id, user_id, MAX(mark_date) date_awarded FROM date_tasks_mission_marks JOIN users USING (user_id) JOIN subjects USING (subject_id) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " AND user_id = $user_id AND subject_type = 'Hakhel' GROUP BY subject_id, user_id) missions_done JOIN medals_subjects_totals ON (missions_done.subject_id = medals_subjects_totals.subject_id AND missions >= missions_required_total)");
    mq("INSERT IGNORE INTO rank_marks (rank_ord, user_id, date_promoted) SELECT rank_ord, user_id, date_awarded date_promoted FROM (SELECT COUNT(*) medals, user_id, MAX(date_awarded) date_awarded FROM medal_marks JOIN users USING (user_id) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " AND user_id = $user_id GROUP BY user_id) medals_done JOIN ranks ON (medals >= medals_required)");
  }
}

if($deletes = gra('deletes')) {
  foreach($deletes as $user_id => $data) {
    $user_id = intval($user_id);
    foreach($data as $date_tasks_mission_id) {
      $date_tasks_mission_id = intval($date_tasks_mission_id);

      mq("DELETE FROM date_tasks_marks USING users JOIN date_tasks_marks USING (user_id) JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) WHERE date_tasks_mission_id = $date_tasks_mission_id AND school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " AND user_id = $user_id");

      mq("DELETE FROM date_tasks_mission_marks USING users JOIN date_tasks_mission_marks USING (user_id) WHERE date_tasks_mission_id = $date_tasks_mission_id AND school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " AND user_id = $user_id");
    }
  }
}

$user_id = gri('user_id', -1);

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, last, first, username");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Hakhel Marks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Hakhel Marks')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_marks_hakhel.php" method="get" accept-charset="UTF-8">
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
<FORM action="admin_marks_hakhel.php" method="get" accept-charset="UTF-8">
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

<FORM action="admin_marks_hakhel.php" method="post" accept-charset="UTF-8">

<H3><?=T_('New entries')?></H3>
<TABLE class="pretty_grid">
<THEAD>
<TR>
<TH colspan="4"><?=T_('Soldier Name')?></TH>
</TR>
<TR>
<TH><?=T_('Mission Name')?></TH>
<TH><?=T_('Ladder/Year')?></TH>
<TD><?=T_('New Entry')?></TD>
</TR>
<TR><TD colspan="4" style="border: none;">&nbsp;</TD></TR>
</THEAD>
<? while($user_row = mysql_fetch_assoc($users)): ?>

<?
$result = mq("
SELECT subject_name, inst_name, date_tasks_missions.mission_name, mission_number, date_tasks_mission_id, track_name, level
FROM users
     JOIN subjects
     JOIN school_type_subjects USING (subject_id, school_type_id)
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
     LEFT JOIN date_tasks_mission_marks USING (user_id, subject_id, date_tasks_mission_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN tracks USING (track_id)
WHERE user_id = {$user_row['user_id']} AND subject_type = 'Hakhel'
      AND date_tasks_mission_marks.date_tasks_mission_id IS NULL
      AND start_date <= $date AND end_date >= $date
ORDER BY subject_name, subject_id, mission_number, date_tasks_missions.mission_name, date_tasks_mission_id
");
?>

<TBODY>
<TR>
<TH><A HREF="admin_user_track.php?action=edit&amp;user_id=<?=$user_row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=T_("Edit Soldier's ladders/years")?></A></TH>
<TH colspan="3">
<? if($user_row['class_grade'] != ''): ?><?=T_('Platoon')?>: <?=es($user_row['class_grade'])?>-<?=es($user_row['class_sub'])?><BR><?endif;?>
<?=T_('Soldier Name')?>: <!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first'])?> <?=es($user_row['last'])?> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?> #<?=es($user_row['user_serial'])?>
</TH>
</TR>

<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
<TH><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name']), ', ', es($row['mission_name']), ' #', $row['mission_number']?></TH>
<TH><?=T_('Ladder')?>: <?=$row['track_name']?> <?=T_('Year')?>: <?=$row['level']?></TH>
<TD>
<LABEL><?=T_('Completed')?>? <INPUT type="checkbox" name="missions[<?=$user_row['user_id']?>][]" value="<?=$row['date_tasks_mission_id']?>" onClick="if(!this.checked) this.parentNode.parentNode.getElementsByTagName('input')[2].checked = false;"></LABEL>
<INPUT type="hidden" name="bonus[<?=$user_row['user_id']?>][<?=$row['date_tasks_mission_id']?>]" value="0">
<LABEL><?=T_('With bonus')?>? <INPUT type="checkbox" name="bonus[<?=$user_row['user_id']?>][<?=$row['date_tasks_mission_id']?>]" value="1" onClick="if(this.checked) this.parentNode.parentNode.getElementsByTagName('input')[0].checked = true;"></LABEL>
</TD>
</TR>
<? endwhile; ?>

<TR><TD colspan="4" style="border: none;">&nbsp;</TD></TR>
</TBODY>
<? endwhile; ?>
</TABLE>

<H3><?=T_('Previous entries')?></H3>
<? @mysql_data_seek($users, 0); ?>
<TABLE class="pretty_grid">
<THEAD>
<TR>
<TH colspan="5"><?=T_('Soldier Name')?></TH>
</TR>
<TR>
<TH><?=T_('Mission Name')?></TH>
<TH><?=T_('Ladder/Year')?></TH>
<TH><?=T_('Points')?></TH>
<TH><?=T_('Date')?></TH>
<TD><?=T_('Existing Entry')?></TD>
</TR>
<TR><TD colspan="5" style="border: none;">&nbsp;</TD></TR>
</THEAD>
<? while($user_row = mysql_fetch_assoc($users)): ?>

<?
$result = mq("
SELECT subject_name, inst_name, date_tasks_mission_marks.mission_name, mission_number, date_tasks_mission_id, track_name, level, mark_date, (SELECT SUM(mark_points) FROM date_tasks_marks JOIN date_tasks USING (date_task_id) WHERE date_tasks_marks.user_id = users.user_id AND date_tasks.date_tasks_mission_id = date_tasks_mission_marks.date_tasks_mission_id) points
FROM users
     JOIN subjects
     JOIN school_type_subjects USING (subject_id, school_type_id)
     JOIN date_tasks_mission_marks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, date_tasks_mission_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN tracks USING (track_id)
WHERE user_id = {$user_row['user_id']} AND subject_type = 'Hakhel' AND
      start_date <= $date AND end_date >= $date
ORDER BY subject_name, subject_id, mission_number, mission_name, date_tasks_mission_id
");
?>

<? if(mysql_num_rows($result)): ?>
<TBODY>
<TR>
<TH><A HREF="admin_user_track.php?action=edit&amp;user_id=<?=$user_row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=T_("Edit Soldier's ladders/years")?></A></TH>
<TH colspan="4">
<? if($user_row['class_grade'] != ''): ?><?=T_('Platoon')?>: <?=es($user_row['class_grade'])?>-<?=es($user_row['class_sub'])?><BR><?endif;?>
<?=T_('Soldier Name')?>: <!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first'])?> <?=es($user_row['last'])?> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?> #<?=es($user_row['user_serial'])?>
</TH>
</TR>

<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
<TH><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name']), ', ', es($row['mission_name']), ' #', $row['mission_number']?></TH>
<TH><?=T_('Ladder')?>: <?=$row['track_name']?> <?=T_('Year')?>: <?=$row['level']?></TH>
<TD><?=floatval($row['points'])?></TD>
<TD><?=dateToHebrew($row['mark_date'])?></TD>
<TD><LABEL><?=T_('Delete')?>? <INPUT type="checkbox" name="deletes[<?=$user_row['user_id']?>][]" value="<?=$row['date_tasks_mission_id']?>"></LABEL></TD>
</TR>
<? endwhile; ?>

<TR><TD colspan="4" style="border: none;">&nbsp;</TD></TR>
</TBODY>
<? endif; ?>
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
