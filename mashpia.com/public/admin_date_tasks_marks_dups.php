<? $admin_auth = array('school','user'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
require_once('file_save.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
$end_date = gri('end_date', unixtojd());
if(!$end_date || ($end_date > unixtojd() && !(isset($admin_user) && $admin_user['auth'] == 'super'))) $end_date = unixtojd();
$start_date = gri('start_date', $end_date-31);
if(!$start_date) $start_date = $end_date-31;
if($start_date > $end_date) { //swap them
  $temp = $start_date;
  $start_date = $end_date;
  $end_date = $temp;
  unset($temp);
}

$user_code = gri('user_codes', 1);
$class_id = gri('class_id', -1);
$user_id = gri('user_id', -1);
$subject_id = gri('subject_id', -2); //-2 means no (including all) subject selected, so don't show tasks

if($auth_mode == 'user') check_school_setting($user_id, 'home_school');

if(!gr('no_save')) {
  if($auth_mode == 'user') {
    $save_user_id = $user_id;
  } else {
    $save_user_id = gri('save_user_id', -1);
    if(!mysql_num_rows(mq("SELECT user_id FROM users WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " AND user_id = $save_user_id"))) $save_user_id = -1;
  }

  if(($missions = gra('missions')) && $save_user_id != -1) {
    foreach($missions as $date_tasks_mission_id => $data) {
      $date_tasks_mission_id = intval($date_tasks_mission_id);
      $override = isset($data['override']);
/*
      if(!mysql_num_rows(mq("
  SELECT date_tasks_mission_id
  FROM subjects
       JOIN school_subjects USING (subject_id)
       JOIN school_type_subjects USING (subject_id)
       JOIN users USING (school_id, school_type_id)
       JOIN user_tracks USING (user_id, subject_id)
       JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
  WHERE user_id = $save_user_id
        AND school_id = $school_id
  " .  ($class_id != -1 ? " AND class_id = $class_id" : '') . "
        AND enrolled = 1
        AND start_date <= $end_date
        AND end_date >= $start_date
        AND user_registered IS NOT NULL
        AND date_tasks_mission_id = $date_tasks_mission_id
  "))) continue; //this checks both $date_tasks_mission_id and $save_user_id
*/
      $prev_entries = mysql_result(mq("SELECT COUNT(*) FROM date_tasks_marks JOIN date_tasks USING (date_task_id) WHERE user_id = $save_user_id AND date_tasks_mission_id = $date_tasks_mission_id AND mark_inactive = 0"), 0);

      foreach($data['task_ids'] as $date_task_id => $data) {
        $date_task_id = intval($date_task_id);
        $done = count(array_filter($data['done']));
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;

        if(!$done && !$quantity) {
          mq("DELETE FROM date_tasks_marks WHERE date_task_id = $date_task_id AND user_id = $save_user_id");
        } else {
          mq("INSERT INTO date_tasks_marks (date_task_id, user_id, mark_date, done_qty, mark_description, mark_points, mark_quantity, mark_inactive) SELECT date_task_id, $save_user_id user_id, " . unixtojd() . " mark_date, LEAST(IF(quantity IS NULL, $done, $quantity/quantity), mandatory_qty+optional_qty) done_qty, IF(description = '', name, description) description, IF(quantity IS NULL, LEAST($done, mandatory_qty+optional_qty)*points, LEAST($quantity, quantity*(mandatory_qty+optional_qty))*(points/quantity)) mark_points, $quantity mark_quantity, " . ($user_code && !$prev_entries ? 1 : 0) . " FROM date_tasks WHERE date_task_id = $date_task_id ON DUPLICATE KEY UPDATE done_qty = VALUES(done_qty), mark_description = VALUES(mark_description), mark_points = VALUES(mark_points), mark_quantity = VALUES(mark_quantity), mark_inactive = VALUES(mark_inactive)");
        }
      }

      $mission_done = mysql_fetch_assoc(mq("
  SELECT date_tasks_mission_id, NOT EXISTS (
          SELECT *
          FROM date_tasks
               LEFT JOIN date_tasks_marks ON (date_tasks.date_task_id = date_tasks_marks.date_task_id AND date_tasks_marks.user_id = $save_user_id)
          WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND (IFNULL(done_qty, 0) < mandatory_qty OR (quantity IS NOT NULL AND IFNULL(mark_quantity, 0) < quantity*mandatory_qty))
         ) done
  FROM date_tasks_missions
  WHERE  date_tasks_mission_id = $date_tasks_mission_id
      "));

      if($mission_done['done'] || $override) {
        mq("INSERT INTO date_tasks_mission_marks (user_id, date_tasks_mission_id, subject_id, mission_value, mission_name, mark_date, mark_override) SELECT $save_user_id user_id, date_tasks_mission_id, subject_id, mission_value, mission_name, " . unixtojd() . " mark_date, " . ($override && !$mission_done['done'] ? '1' : '0') . " mark_override FROM date_tasks_missions WHERE date_tasks_mission_id = $date_tasks_mission_id ON DUPLICATE KEY UPDATE mission_value = VALUES(mission_value), mission_name = VALUES(mission_name), mark_override = VALUES(mark_override)");
      } else {
        mq("DELETE FROM date_tasks_mission_marks WHERE user_id = $save_user_id AND date_tasks_mission_id = $date_tasks_mission_id");
      }

      //if any are inactive, create a code
      mq("INSERT IGNORE INTO user_codes (user_id, code_id, code_id_prefix, admin_id) SELECT $save_user_id user_id, " . str_pad($save_user_id, 9, '0', STR_PAD_LEFT) . str_pad($date_tasks_mission_id, 10, '0', STR_PAD_LEFT) . " code_id, 5 code_id_prefix, {$admin_user['admin_id']} admin_id FROM dual WHERE EXISTS (SELECT * FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN users USING (user_id) JOIN schools USING (school_id) JOIN subjects USING (subject_id) WHERE user_id = $save_user_id AND date_tasks_mission_id = $date_tasks_mission_id AND mark_inactive = 1)");

      //if no inactives, delete the code
      mq("DELETE FROM user_codes WHERE user_id = $save_user_id AND code_id = " . str_pad($save_user_id, 9, '0', STR_PAD_LEFT) . str_pad($date_tasks_mission_id, 10, '0', STR_PAD_LEFT) . " AND code_id_prefix = 5 AND NOT EXISTS (SELECT * FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN users USING (user_id) JOIN schools USING (school_id) JOIN subjects USING (subject_id) WHERE user_id = $save_user_id AND date_tasks_mission_id = $date_tasks_mission_id AND mark_inactive = 1)");

      //delete if the mission has no marked tasks
      mq("DELETE FROM user_mission_entries WHERE user_id = $save_user_id AND entry_type = 'date_tasks_missions' AND entry_id = $date_tasks_mission_id AND NOT EXISTS (SELECT * FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN users USING (user_id) JOIN schools USING (school_id) JOIN subjects USING (subject_id) WHERE user_id = $save_user_id AND date_tasks_mission_id = $date_tasks_mission_id)");
    }

    mq("DELETE FROM medal_marks USING medal_marks JOIN subjects USING (subject_id) JOIN medals_subjects_totals USING (medal_ord, subject_id) LEFT JOIN (SELECT SUM(mission_value) missions, subject_id, user_id, MAX(mark_date) date_awarded FROM date_tasks_mission_marks JOIN users USING (user_id) JOIN subjects USING (subject_id) WHERE user_id = $save_user_id GROUP BY subject_id, user_id) missions_done USING (subject_id, user_id) WHERE user_id = $save_user_id AND (missions_required_total > missions OR missions IS NULL) AND subject_type != 'Tanya'");
    mq("INSERT IGNORE INTO medal_marks (medal_ord, subject_id, user_id, date_awarded) SELECT medal_ord, missions_done.subject_id, user_id, date_awarded FROM (SELECT SUM(mission_value) missions, subject_id, user_id, MAX(mark_date) date_awarded FROM date_tasks_mission_marks JOIN users USING (user_id) JOIN subjects USING (subject_id) WHERE user_id = $save_user_id GROUP BY subject_id, user_id) missions_done JOIN medals_subjects_totals ON (missions_done.subject_id = medals_subjects_totals.subject_id AND missions >= missions_required_total)");
    mq("INSERT IGNORE INTO rank_marks (rank_ord, user_id, date_promoted) SELECT rank_ord, user_id, date_awarded date_promoted FROM (SELECT COUNT(*) medals, user_id, MAX(date_awarded) date_awarded FROM medal_marks JOIN users USING (user_id) WHERE user_id = $save_user_id GROUP BY user_id) medals_done JOIN ranks ON (medals >= medals_required)");
  }

  if(($user_entry = array_filter(gra('user_entry'), 'is_numeric')) && $save_user_id != -1) {
    mq("DELETE FROM user_mission_entries WHERE user_id = $save_user_id AND entry_type = 'date_tasks_missions' AND entry_id IN (" . implode(',', $user_entry) . ")");
  }

  if(($alt_missions = array_filter(array_keys(gra('alt_missions')), 'is_numeric')) && $save_user_id != -1) {
    mq("DELETE FROM date_tasks_marks USING date_tasks_marks JOIN date_tasks USING (date_task_id) WHERE user_id = $save_user_id AND date_tasks_mission_id IN (" . implode(',', $alt_missions) . ")");
    mq("DELETE FROM date_tasks_mission_marks USING date_tasks_mission_marks WHERE user_id = $save_user_id AND date_tasks_mission_id IN (" . implode(',', $alt_missions) . ")");
    mq("DELETE FROM user_mission_entries WHERE user_id = $save_user_id AND entry_type = 'date_tasks_missions' AND entry_id IN (" . implode(',', $alt_missions) . ")");
    $alt_mission_code = array();
    foreach($alt_missions as $alt_mission) {
      $alt_mission_code[] = str_pad($save_user_id, 9, '0', STR_PAD_LEFT) . str_pad($date_tasks_mission_id, 10, '0', STR_PAD_LEFT);
    }
    mq("DELETE FROM user_codes WHERE user_id = $save_user_id AND code_id IN (" . implode(',', $alt_mission_code) . ") AND code_id_prefix = 5");
  }
}

if(is_null($user_id = gri('user_id'))) {
  $user = mysql_fetch_assoc(mq("SELECT user_id FROM users JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" . ($class_id >= 0 ? " AND users.class_id = $class_id" : '') . ' ORDER BY class_grade, class_sub, last, first, username LIMIT 1'));
  $user_id = $user ? $user['user_id'] : -1;
}
$user_row = mysql_fetch_assoc(mq("SELECT user_id, username, user_serial, first, first_he, last, last_he, class_grade, class_sub FROM users LEFT JOIN classes USING (school_id, class_id) WHERE user_id = $user_id AND school_id = $school_id"));

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Process Reports'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
<SCRIPT type="text/javascript">
function setChildCheckboxes(el, nameRegex, set) {
  var pattern = new RegExp(nameRegex);
  var els = el.getElementsByTagName('input');

  for(var i = 0; i < els.length; i++) {
    if(pattern.test(els[i].name) && els[i].type == 'checkbox') {
      els[i].checked = set;
    }
  }
}

/*
 value =
   1: set on
   0: set off
  -1: toggle
*/

function setFormCheckboxes(form, nameRegex, value, parentClass) {
  var pattern = new RegExp(nameRegex);

  for(var i = 0; i < form.elements.length; i++) {
    if(pattern.test(form.elements[i].name) && form.elements[i].type == 'checkbox' && (!parentClass || form.elements[i].parentNode.parentNode.parentNode.className.indexOf(parentClass) >= 0)) {
      form.elements[i].checked = (value == -1 ? !form.elements[i].checked : value);
    }
  }
}
</SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body <?=$align_start?>">
<H1><?=T_('Process Reports')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($admin_user['auth'] == 'super' || $auth_mode == 'school' && count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_date_tasks_marks_dups.php" method="get" accept-charset="UTF-8">
<P>
<?if($admin_user['auth'] == 'super'):?>
<LABEL><?=T_('Start Date')?>:<INPUT type="text" name="start_date_disp" READONLY value="<?=es(dateToHebrew($start_date))?>" onClick="getDate(this.form, 'start_date');"></LABEL><INPUT type="hidden" name="start_date" value="<?=$start_date?>"><BR>
<LABEL><?=T_('End Date')?>:<INPUT type="text" name="end_date_disp" READONLY value="<?=es(dateToHebrew($end_date))?>" onClick="getDate(this.form, 'end_date');"></LABEL><INPUT type="hidden" name="end_date" value="<?=$end_date?>"><BR>
<?else:?>
<INPUT type="hidden" name="start_date" value="<?=$start_date?>">
<INPUT type="hidden" name="end_date" value="<?=$end_date?>">
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
<DIV class="infobox">
</DIV>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>

<?elseif(!$user_row):?>
<?=T_('User not found.')?>
<?else:?>
<FORM action="admin_date_tasks_marks_dups.php" method="get" accept-charset="UTF-8">
<DIV class="infobox2">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="start_date" value="<?=$start_date?>">
<INPUT type="hidden" name="end_date" value="<?=$end_date?>">

<?if($admin_user['auth'] == 'super' || $auth_mode == 'school'):?>
<?=T_('Show Platoon')?> <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($class_row = mysql_fetch_assoc($class_result)): ?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT><BR>
<?elseif($auth_mode == 'user'):?>
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<?endif;?>
<? $subjects_result = mq("SELECT DISTINCT subject_id, subject_name FROM schools JOIN subjects USING (inst_id) JOIN school_subjects USING (school_id, subject_id) WHERE school_id = $school_id ORDER BY subject_ord, subject_name"); ?>
<LABEL><?=T_('Select Campaign')?>:
<SELECT name="subject_id" id="subject_id">
  <OPTION value="-1">&lt;All&gt;
  <? while($row = mysql_fetch_assoc($subjects_result)): ?>
    <OPTION VALUE="<?=$row['subject_id']?>" <?=$subject_id == $row['subject_id'] ? 'SELECTED' : '' ?>><?=es($row['subject_name'])?></OPTION>
      <? endwhile; ?>
</SELECT></LABEL><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</DIV>
</FORM>

<?if($subject_id != -2):?>
<FORM action="admin_date_tasks_marks_dups.php" method="post" accept-charset="UTF-8" name="edit">

<?if($admin_user['auth'] == 'super' || $auth_mode == 'school'):?>
<DIV style="float: <?=$align_end?>; width: 220px; border: 1px solid #09355c; padding: 10px;">
<? $next = false; ?>
<? $users = mq("SELECT users.user_id, first, last, username, class_grade, class_sub FROM users LEFT JOIN classes USING (class_id) WHERE users.school_id = $school_id" . ($class_id >= 0 ? " AND users.class_id = $class_id" : '') . ' AND user_registered IS NOT NULL ORDER BY class_grade, class_sub, last, first, username'); ?>
<? while($row = mysql_fetch_assoc($users)): ?>
  <DIV style="margin-bottom: 1em;"><LABEL>
    <? if($row['user_id'] == $user_id): ?>
      <? $next = true; ?>
      <B>&nbsp; &nbsp; &nbsp; &nbsp;
    <? else: ?>
      <INPUT type="radio" name="user_id" value="<?=$row['user_id']?>" <?=$next ? ' checked' : ''?> style="vertical-align: middle;">
      <? $next = false; ?>
    <? endif; ?>
   <?=$row['class_grade'] != '' ? es($row['class_grade'] . '-' . $row['class_sub']) . ': ' : ''?><?=es($row['last'])?>, <?=es($row['first'])?>
    <? if($row['user_id'] == $user_id): ?></B><? endif; ?>
  </LABEL></DIV>
<? endwhile; ?>
<INPUT class="submit" type="submit" name="no_save" value="<?=T_("Don't save, just edit selected soldier")?>" style="font-size: 10px;">
</DIV>
<? endif; ?>

<DIV style="margin-<?=$align_end?>: 250px;">
<TABLE cellpadding="10" style="width: 100%; border: 1px solid #08355c;">
<TR style="background-color: #cccccc;">
  <TH><?=T_('Report For')?></TH>
  <TH><?=T_('Other Weeks')?></TH>
  <TH><?=T_('Un/Check All')?></TH>
</TR>
<TR>
<TD>
<!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first'])?> <?=es($user_row['last'])?> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?><BR>
<? if($user_row['class_grade'] != ''): ?><?=es($user_row['class_grade'])?>-<?=es($user_row['class_sub'])?><?endif;?><BR>
#<?=es($user_row['user_serial'])?><BR>
<A HREF="admin_user_track.php?action=edit&amp;user_id=<?=$user_row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>&amp;back=<?=urlencode("admin_date_tasks_marks_dups.php?subject_id=$subject_id&start_date=$start_date&edit_date=$end_date&")?>&amp;back_text=<?=urlencode(T_('Back to Process Reports'))?>"><?=T_("Edit Soldier's ladders/years/campaigns")?></A>
</TD>
<TD style="border-width: 0px 1px; border-color: #cdcdcd; border-style: none solid;">
<? $result = mq("SELECT start_date, end_date, report_name FROM (SELECT start_date, end_date, report_name FROM reports WHERE report_type = 'mission_cover_sheet' AND visibility != 'none' AND start_date < $start_date ORDER BY start_date DESC LIMIT 3) s ORDER BY start_date"); ?>
<? while($row = mysql_fetch_assoc($result)): ?>
<A href="admin_date_tasks_marks_dups.php?school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>&amp;user_id=<?=$user_id?>&amp;start_date=<?=$row['start_date']?>&amp;end_date=<?=$row['end_date']?>" style="white-space: nowrap;" dir="RTL"><?=es($row['report_name'])?> <?=es(dateToHebrew($row['start_date']))?></A><BR>
<? endwhile; ?>

<? $row = mysql_fetch_assoc(mq("SELECT report_name FROM reports WHERE report_type = 'mission_cover_sheet' AND visibility != 'none' AND start_date = $start_date")); ?>
<SPAN style="white-space: nowrap;"><?=es($row['report_name'])?> <?=es(dateToHebrew($start_date))?></SPAN><BR>

<? $result = mq("SELECT start_date, end_date, report_name FROM reports WHERE report_type = 'mission_cover_sheet' AND visibility != 'none' AND start_date > $start_date ORDER BY start_date LIMIT 3"); ?>
<? while($row = mysql_fetch_assoc($result)): ?>
<A href="admin_date_tasks_marks_dups.php?school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>&amp;user_id=<?=$user_id?>&amp;start_date=<?=$row['start_date']?>&amp;end_date=<?=$row['end_date']?>" style="white-space: nowrap;" dir="RTL"><?=es($row['report_name'])?> <?=es(dateToHebrew($row['start_date']))?></A><BR>
<? endwhile; ?>
</TD>
<TD>
    <A HREF="#" onClick="setFormCheckboxes(document.forms['edit'], 'user_entry\\[\\]', 1); return false;"><?=T_('Approve')?></A> / <A HREF="#" onClick="setFormCheckboxes(document.forms['edit'], 'user_entry\\[\\]', 0); return false;"><?=T_('Un-Approve')?></A> <?=T_('Kiosk Entries')?><BR>

    <A HREF="#" onClick="if(confirm('<?=esq(T_('If there are kisok entries in this report, this will  override them.\n\nContinue?'))?>')) setFormCheckboxes(document.forms['edit'], 'missions\\[\\d+\\]\\[task_ids\\]\\[\\d+\\]\\[done\\]\\[\\]', 1); return false;"><?=T_('Check')?></A> / <A HREF="#" onClick="if(confirm('<?=esq(T_('If there are kisok entries in this report, this will  override them.\n\nContinue?'))?>')) setFormCheckboxes(document.forms['edit'], 'missions\\[\\d+\\]\\[task_ids\\]\\[\\d+\\]\\[done\\]\\[\\]', 0); return false;"><?=T_('Uncheck')?></A> <?=es(T_('All Mission & Bonus Tasks'))?><BR>

    <A HREF="#" onClick="if(confirm('<?=esq(T_('If there are kisok entries in this report, this will  override them.\n\nContinue?'))?>')) setFormCheckboxes(document.forms['edit'], 'missions\\[\\d+\\]\\[task_ids\\]\\[\\d+\\]\\[done\\]\\[\\]', 1, 'mission'); return false;"><?=T_('Check')?></A> / <A HREF="#" onClick="if(confirm('<?=esq(T_('If there are kisok entries in this report, this will  override them.\n\nContinue?'))?>')) setFormCheckboxes(document.forms['edit'], 'missions\\[\\d+\\]\\[task_ids\\]\\[\\d+\\]\\[done\\]\\[\\]', 0, 'mission'); return false;"><?=T_('Uncheck')?></A> <?=T_('All Mission Tasks')?><BR>

    <A HREF="#" onClick="if(confirm('<?=esq(T_('If there are kisok entries in this report, this will  override them.\n\nContinue?'))?>')) setFormCheckboxes(document.forms['edit'], 'missions\\[\\d+\\]\\[task_ids\\]\\[\\d+\\]\\[done\\]\\[\\]', 1, 'bonus'); return false;"><?=T_('Check')?></A> / <A HREF="#" onClick="if(confirm('<?=esq(T_('If there are kisok entries in this report, this will  override them.\n\nContinue?'))?>')) setFormCheckboxes(document.forms['edit'], 'missions\\[\\d+\\]\\[task_ids\\]\\[\\d+\\]\\[done\\]\\[\\]', 0, 'bonus'); return false;"><?=T_('Uncheck')?></A> <?=T_('All Bonus Tasks')?><BR>
</TD>
</TR>
</TABLE>
<BR><BR>
<?
$result = mq("
SELECT subject_name, subject_image_id, inst_name, date_tasks_missions.mission_name, mission_number, subject_id, start_date, end_date, date_tasks_mission_id, track_id, track_name, level, school_type_id, school_type_name, date_tasks_mission_marks.date_tasks_mission_id done, mark_override,
       (SELECT COUNT(*) FROM date_tasks WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id) tasks,
       (SELECT COUNT(*) FROM date_tasks JOIN date_tasks_marks USING (date_task_id) WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND user_id = {$user_row['user_id']}) marked_tasks
FROM subjects
     JOIN school_subjects USING (subject_id)
     JOIN school_type_subjects USING (subject_id)
     JOIN users USING (school_id, school_type_id)
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
     LEFT JOIN date_tasks_mission_marks USING (user_id, subject_id, date_tasks_mission_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN tracks USING (track_id)
     LEFT JOIN school_types USING (school_type_id)
WHERE user_id = {$user_row['user_id']}
      AND school_id = $school_id
      AND enrolled = 1
      AND start_date <= $end_date
      AND end_date >= $start_date
      AND user_registered IS NOT NULL" .
      ($subject_id != -1 ? " AND subject_id = $subject_id" : '')
. "
ORDER BY inst_name, subject_ord, subject_name, mission_number, mission_name
");
?>
<? if(mysql_num_rows($result)): ?>
<TABLE class="missions" cellspacing="0" cellpadding="0">
<? while($mission_row = mysql_fetch_assoc($result)): ?>

<? $alt_missions = mq("
SELECT subjects.subject_name, subjects.subject_image_id, inst_name, date_tasks_missions.mission_name, mission_number, date_tasks_missions.subject_id, start_date, end_date, date_tasks_missions.date_tasks_mission_id, track_id, track_name, level, school_type_id, school_type_name, date_tasks_mission_marks.date_tasks_mission_id done, mark_override
FROM date_tasks_missions
     LEFT JOIN date_tasks_mission_marks ON (user_id = {$user_row['user_id']} AND date_tasks_missions.date_tasks_mission_id = date_tasks_mission_marks.date_tasks_mission_id)
     LEFT JOIN subjects ON (date_tasks_missions.subject_id = subjects.subject_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN tracks USING (track_id)
     LEFT JOIN school_types USING (school_type_id)
WHERE date_tasks_missions.date_tasks_mission_id != {$mission_row['date_tasks_mission_id']}
  AND date_tasks_missions.subject_id = {$mission_row['subject_id']}
  AND " . (is_null($mission_row['mission_number']) ? "start_date = {$mission_row['start_date']} AND end_date = {$mission_row['end_date']}" : "mission_number = {$mission_row['mission_number']}") . "
  AND (
   EXISTS (SELECT * FROM date_tasks_mission_marks WHERE date_tasks_mission_marks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND user_id = {$user_row['user_id']})
  OR
   EXISTS (SELECT * FROM date_tasks_marks JOIN date_tasks USING (date_task_id) WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND user_id = {$user_row['user_id']})
  )
");
?>

<? while($row = (($alt_mission = mysql_fetch_assoc($alt_missions)) ? $alt_mission : $mission_row)): ?>
<TBODY class="mission_name <?if(mysql_num_rows($alt_missions)):?>alt_mission alt_mission_name<?endif;?>">
<? if($alt_mission): ?>
<TR>
<TD colspan="3">
  <P style="color: red;"><?=T_('This child has now moved to a new ladder, year, or Tzivos Hashem type, and has mission entries under the old settings.')?></P>

  <?if($mission_row['done'] || $mission_row['marked_tasks']):?>
    <P style="color: red; font-size: 150%; font-weight: bold;"><?=T_('Warning: There are entries in both the old, and the new mission.')?></P>
  <?endif;?>
  <?if(mysql_num_rows($alt_missions) > 1):?>
    <P style="color: red; font-size: 150%; font-weight: bold;"><?=T_('Warning: This mission has been entered multiple times, please check the missions carefully.')?></P>
  <?endif;?>

  <?if($mission_row['done'] || $mission_row['marked_tasks'] || mysql_num_rows($alt_missions) > 1):?>
    <P style="color: red; font-size: 150%; font-weight: bold;"><?=T_('You need to fix this, there should only be entries in one mission.')?></P>
  <?else:?>
    <P><?=T_('This is not an error. If you would like to re-enter the mission school down to see the current mission. If you are re-entering the mission, please be sure to delete the old one.')?></P>
  <?endif;?>

  <P><?=T_('To delete a mission, un-check all the mission and bonus tasks, and set any quantity fields to 0.')?></P>

<!--   <BR><LABEL><INPUT type="checkbox" name="alt_missions[<?=$alt_mission['date_tasks_mission_id']?>]" value="1"><?=T_('Delete?')?></LABEL><BR><BR> -->

  <P>
  <?=T_('The mission below was entered using the following settings:')?>
  </P>
  <UL>
  <LI <?=$mission_row['track_id'] != $alt_mission['track_id'] ? 'style="color: red;"' : ''?>><?=T_('Ladder')?>: <?=$alt_mission['track_name']?>
  <LI <?=$mission_row['level'] != $alt_mission['level'] ? 'style="color: red;"' : ''?>><?=T_('Year')?>: <?=$alt_mission['level']?>
  <LI <?=$mission_row['school_type_id'] != $alt_mission['school_type_id'] ? 'style="color: red;"' : ''?>><?=T_('TH Type')?>: <?=$alt_mission['school_type_name']?>
  </UL>
</TD>
</TR>
<? elseif(mysql_num_rows($alt_missions)): ?>
<TR>
<TD colspan="3">
    <?=T_('Current Mission:')?>
  <UL>
  <LI><?=T_('Ladder')?>: <?=$mission_row['track_name']?>
  <LI><?=T_('Year')?>: <?=$mission_row['level']?>
  <LI><?=T_('TH Type')?>: <?=$mission_row['school_type_name']?>
  </UL>
</TD>
</TR>
<? endif; ?>
<TR>
  <TH><?=!is_null($row['subject_image_id']) ? linkImgFile($row['subject_image_id']) : ''?></TH>
  <TH>
    <?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name'] . ' - ' . (is_null($row['mission_number']) ? '' : '#' . floatval($row['mission_number'])) . ' ' . $row['mission_name'])?>
    <!--<BR>
    <?=T_('Ladder')?>: <?=$row['track_name']?><BR>
    <?=T_('Year')?>: <?=$row['level']?><BR>
    <?=T_('TH Type')?>:&nbsp;<?=$row['school_type_name']?>
    -->
  </TH>
  <TH>
    <? $user_entry = mysql_fetch_assoc(mq("SELECT user_id FROM user_mission_entries WHERE user_id = {$user_row['user_id']} AND entry_id = {$row['date_tasks_mission_id']} AND entry_type = 'date_tasks_missions'")); ?>
    <?if($user_entry):?>
      <LABEL><INPUT type="checkbox" name="user_entry[]" value="<?=$row['date_tasks_mission_id']?>"> <?=T_('Approve Kiosk Entries')?></LABEL>
    <?endif;?>
  </TH>
</TR>
</TBODY>

<? $tasks = mq("SELECT date_tasks.date_task_id, name, mandatory_qty, optional_qty, quantity, points, done_qty, mark_quantity, label_name, label_image_id FROM date_tasks LEFT JOIN labels USING (label_id) LEFT JOIN date_tasks_marks ON (date_tasks.date_task_id = date_tasks_marks.date_task_id AND user_id = {$user_row['user_id']}) WHERE date_tasks_mission_id = {$row['date_tasks_mission_id']} AND mandatory_qty > 0 ORDER BY ord"); ?>
<? $bonus_tasks = mq("SELECT date_tasks.date_task_id, name, mandatory_qty, optional_qty, quantity, points, done_qty, mark_quantity, label_name, label_image_id FROM date_tasks LEFT JOIN labels USING (label_id) LEFT JOIN date_tasks_marks ON (date_tasks.date_task_id = date_tasks_marks.date_task_id AND user_id = {$user_row['user_id']}) WHERE date_tasks_mission_id = {$row['date_tasks_mission_id']} AND optional_qty > 0 AND (mandatory_qty <= 0 OR quantity IS NULL) ORDER BY ord"); ?>
<? $task_ids = array(); ?>
<TBODY class="tasks mission <?if(mysql_num_rows($alt_missions)):?>alt_mission<?endif;?>">
<TR>
<TH></TH>
<TH colspan="2">
  <DIV style="float: right;">
  <LABEL><INPUT type="checkbox" name="missions[<?=$row['date_tasks_mission_id']?>][override]" value="1" <?=$row['mark_override'] ? 'CHECKED' : ''?> onClick="return !this.checked || confirm('<?=T_('This will award the mission even if all the mandatory tasks have not been done.\n\nAre you sure?')?>');"> <?=T_('Override mission requirements')?></LABEL><BR>
  <LABEL><INPUT type="checkbox" onClick="<?if($user_entry):?>if(confirm('<?=esq(T_('This will override the kiosk entries in this mission.\n\nContinue?'))?>'))<?endif;?> setChildCheckboxes(this.parentNode.parentNode.parentNode.parentNode.parentNode, 'missions\\[\\d+\\]\\[task_ids\\]\\[\\d+\\]\\[done\\]\\[\\]', this.checked);"> <?=T_('Check/Uncheck all mission tasks')?></LABEL>
  </DIV>
  <H3><?=T_('Mission Tasks')?></H3>
</TH>
<? while($task = mysql_fetch_assoc($tasks)):?>
  <? $task_ids[] = $task['date_task_id']; ?>
  <?if(is_null($task['quantity'])):?>
    <?for($i = 0; $i < $task['mandatory_qty']; $i++):?>
      <TR>
      <TD><?=!is_null($task['label_image_id']) ? linkImgFile($task['label_image_id']) : ''?></TD>
      <TD><LABEL for="missions_<?=$row['date_tasks_mission_id']?>_<?=$task['date_task_id']?>_<?=$i?>"><?=es($task['name'])?></LABEL></TD>
      <TD><INPUT type="checkbox" name="missions[<?=$row['date_tasks_mission_id']?>][task_ids][<?=$task['date_task_id']?>][done][]" id="missions_<?=$row['date_tasks_mission_id']?>_<?=$task['date_task_id']?>_<?=$i?>" value="1" <?=$task['done_qty'] > $i ? 'CHECKED' : ''?>> <?=sprintf(($task['points'] == 1 ? T_('%s mile') : T_('%s miles')),  floatval($task['points']))?></TD>
      </TR>
    <?endfor;?>
  <?else:?>
    <TR>
    <TD><?=!is_null($task['label_image_id']) ? linkImgFile($task['label_image_id']) : ''?></TD>
    <TD><LABEL for="missions_<?=$row['date_tasks_mission_id']?>_<?=$task['date_task_id']?>"><?=es($task['name'])?></LABEL></TD>
    <TD>
      <INPUT type="text" name="missions[<?=$row['date_tasks_mission_id']?>][task_ids][<?=$task['date_task_id']?>][quantity]" id="missions_<?=$row['date_tasks_mission_id']?>_<?=$task['date_task_id']?>" size="5" maxlength="5" value="<?=$task['mark_quantity']?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));"> (<?=T_('Mission Req')?>: <?=$task['quantity']*$task['mandatory_qty']?>)<BR>
      <?=sprintf(($task['points'] == 1 ? T_('Quota: %s mile') : T_('Quota: %s miles')),  floatval($task['points']))?>, <? $each = @round($task['points']/($task['quantity']*$task['mandatory_qty']), 2); ?> <?=sprintf(($each == 1 ? T_('%s mile each') : T_('%s miles each')), $each)?>
    </TD>
    </TR>
  <?endif;?>
<? endwhile; ?>
</TBODY>
<? if(mysql_num_rows($bonus_tasks)): ?>
<TBODY class="tasks bonus <?if(mysql_num_rows($alt_missions)):?>alt_mission<?endif;?>">
<TR>
<TH></TH>
<TH colspan="2">
  <DIV style="float: right;">
  <LABEL><INPUT type="checkbox" onClick="<?if($user_entry):?>if(confirm('<?=esq(T_('This will override the kiosk entries in this mission.\n\nContinue?'))?>'))<?endif;?> setChildCheckboxes(this.parentNode.parentNode.parentNode.parentNode.parentNode, 'missions\\[\\d+\\]\\[task_ids\\]\\[\\d+\\]\\[done\\]\\[\\]', this.checked);"><?=T_('Check/Uncheck all bonus tasks')?></LABEL>
  </DIV>
  <H3><?=T_('Bonus Tasks')?></H3>
</TH>
</TR>
<? while($task = mysql_fetch_assoc($bonus_tasks)):?>
  <? $task_ids[] = $task['date_task_id']; ?>
  <?if(is_null($task['quantity'])):?>
    <?for($i = 0; $i < $task['optional_qty']; $i++):?>
      <TR>
      <TD><?=!is_null($task['label_image_id']) ? linkImgFile($task['label_image_id']) : ''?></TD>
      <TD><LABEL for="missions_b_<?=$row['date_tasks_mission_id']?>_<?=$task['date_task_id']?>_<?=$i?>"><?=es($task['name'])?></LABEL></TD>
      <TD><INPUT type="checkbox" name="missions[<?=$row['date_tasks_mission_id']?>][task_ids][<?=$task['date_task_id']?>][done][]" id="missions_b_<?=$row['date_tasks_mission_id']?>_<?=$task['date_task_id']?>_<?=$i?>" value="1" <?=$task['done_qty'] > $i + $task['mandatory_qty'] ? 'CHECKED' : ''?>> <?=sprintf(($task['points'] == 1 ? T_('%s Mile') : T_('%s Miles')),  floatval($task['points']))?></TD>
      </TR>
    <?endfor;?>
  <?else:?>
    <TR>
    <TD><?=!is_null($task['label_image_id']) ? linkImgFile($task['label_image_id']) : ''?></TD>
    <TD><LABEL for="missions_b_<?=$row['date_tasks_mission_id']?>_<?=$task['date_task_id']?>"><?=es($task['name'])?></LABEL></TD>
    <TD>
      <INPUT type="text" name="missions[<?=$row['date_tasks_mission_id']?>][task_ids][<?=$task['date_task_id']?>][quantity]" id="missions_b_<?=$row['date_tasks_mission_id']?>_<?=$task['date_task_id']?>" size="5" maxlength="5" value="<?=$task['mark_quantity']?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));"><BR>
      <?=sprintf(($task['points'] == 1 ? T_('Quota: %s mile') : T_('Quota: %s miles')),  floatval($task['points']))?>, <? $each = @round($task['points']/($task['quantity']*$task['mandatory_qty']), 2); ?> <?=sprintf(($each == 1 ? T_('%s mile each') : T_('%s miles each')), $each)?>
    </TD>
    </TR>
  <?endif;?>
<? endwhile; ?>
</TBODY>
<? endif; ?>
<TBODY class="spacer <?if(mysql_num_rows($alt_missions)):?><?=$alt_mission ? 'alt_spacer' : 'post_alt_spacer'?><?endif;?>"><TR><TD colspan="3">&nbsp;
<? foreach($task_ids as $task_id): ?>
  <INPUT type="hidden" name="missions[<?=$row['date_tasks_mission_id']?>][task_ids][<?=$task_id?>][done][]" value="0">
<? endforeach; ?>
</TD></TR></TBODY>
<?if(!$alt_mission) $mission_row = false;?>
<? endwhile; ?>
<? endwhile; ?>
</TABLE>

<P>
<LABEL><INPUT type="radio" name="user_code" value="1" <?=$user_code ? 'CHECKED' : ''?>><?=T_('Grant Soldier an achievment card in his/her inbox')?></LABEL> (<?=T_('If there are previous or kiosk entries, then it will not generate an electronic achievment card.')?><BR>
<LABEL><INPUT type="radio" name="user_code" value="0" <?=!$user_code ? 'CHECKED' : ''?>><?=T_('Mark tasks completed without granting an achievment card')?></LABEL><BR>
<INPUT type="submit" value="<?=T_('Save and edit next Soldier')?>"> (<?=T_('Note: Once a rank has been awarded for completing missions it will not be revoked if the mission entry is subsequently removed.')?>)<BR>
</P>

<? else: ?>

<P>
<?=sprintf(T_('There are no missions for this soldier, this is because of the following reasons: The soldiers has not enrolled to this campaign, or they are not on a ladder, or they are not in a year. %sClick Here%s to modify their profile.'),"<A HREF='admin_user_track.php?action=edit&amp;user_id={$user_row['user_id']}&amp;school_id=$school_id&amp;class_id=$class_id'>", '</A>')?><BR>
<BR>
<INPUT type="submit" value="Edit next soldier">
</P>

<? endif; ?>

<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<?if($auth_mode == 'user'):?>
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<?else:?>
<INPUT type="hidden" name="save_user_id" value="<?=$user_id?>">
<?endif;?>
<INPUT type="hidden" name="start_date" value="<?=$start_date?>">
<INPUT type="hidden" name="end_date" value="<?=$end_date?>">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
</DIV>

</FORM>
<?endif;?>

<?endif;?>
</DIV>
</BODY>
</HTML>
