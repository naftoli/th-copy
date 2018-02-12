<? $admin_auth = array('school', 'class', 'team', 'user'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');

$school_type_id = gri('school_type_id', -1);
$subject_id = gri('subject_id', -1);
$level = gri('level', -1);
$track_id = gri('track_id', -1);

$tracks_result = mq('SELECT track_id, track_name FROM tracks ORDER BY track_name');

if($subject_id != -1) $subject = mysql_result(mq("SELECT subject_name FROM subjects WHERE subject_id = $subject_id"), 0);
if($track_id != -1) $track = mysql_result(mq("SELECT track_name FROM tracks WHERE track_id = $track_id"), 0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Growth Plan'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Growth Plan')?></H1>

<? $school_type_result = mq('SELECT school_type_id, school_type_name FROM school_types ORDER BY school_type_name'); ?>

<FORM action="admin_view_date_tasks.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select School Type')?>:
<SELECT name="school_type_id">
  <? while($row = mysql_fetch_assoc($school_type_result)): ?>
    <OPTION VALUE="<?=$row['school_type_id']?>" <?=$school_type_id == $row['school_type_id'] ? 'SELECTED' : '' ?>><?=es($row['school_type_name'])?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<?if($school_type_id == -1):?>
<?=T_('Please select a School Type.')?>
<?else:?>
<? $subject_result = mq("SELECT subjects.subject_id, subjects.subject_name, institutions.inst_name FROM subjects JOIN school_type_subjects USING (subject_id) LEFT JOIN institutions USING (inst_id) WHERE school_type_id = $school_type_id ORDER BY institutions.inst_name, subjects.subject_name"); ?>

<FORM action="admin_view_date_tasks.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Subject')?>:
<SELECT name="subject_id">
  <? while($row = mysql_fetch_assoc($subject_result)): ?>
    <OPTION VALUE="<?=$row['subject_id']?>" <?=$subject_id == $row['subject_id'] ? 'SELECTED' : '' ?>><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL>
<INPUT type="hidden" name="school_type_id" value="<?=$school_type_id?>">
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<?if($subject_id == -1):?>
<?=T_('Please select a Subject.')?>
<?else:?>

<TABLE class="grid" id="track_level">
<TR>
  <TH rowspan="<?=mysql_num_rows($tracks_result)+2?>"><?=T_('Ladder')?></TH>
  <TH colspan="13"><?=T_('Year')?></TH>
</TR>
<TR>
    <TH></TH>
    <? foreach(range(3, 14) as $each_level): ?>
      <TH><?=$each_level?></TH>
    <? endforeach; ?>
</TR>
<? while($track_row = mysql_fetch_assoc($tracks_result)): ?>
  <TR>
    <TH><?=es($track_row['track_name'])?></TH>
    <? foreach(range(3, 14) as $each_level): ?>
      <TD style="text-align: <?=$align_start?>;" <?= $level==$each_level && $track_id==$track_row['track_id'] ? 'id="selected"' : '' ?>><A HREF="admin_view_date_tasks.php?school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$subject_id?>&amp;level=<?=$each_level?>&amp;track_id=<?=$track_row['track_id']?>"><?=mysql_result(mq("SELECT COUNT(*) FROM date_tasks_missions WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND level = $each_level AND track_id = {$track_row['track_id']}"), 0)?> <?=T_('Missions')?><BR><?=mysql_result(mq("SELECT COUNT(*) FROM date_tasks_missions JOIN date_tasks USING (date_tasks_mission_id) WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND level = $each_level AND track_id = {$track_row['track_id']}"), 0)?> <?=T_('Tasks')?></A></TD>
    <? endforeach; ?>
  </TR>
<? endwhile; ?>
</TABLE>
<HR>

<?if($track_id == -1 || $level == -1): ?>

<?=T_('Please select a ladder and year.')?>

<?else:?>

<H2 style="text-align: center;"><?=T_('Subject')?>: <?=es($subject)?> - <?=T_('Ladder')?>: <?=es($track)?> - <?=T_('Year')?>: <?=es($level)?></H2>

<? $result = mq("SELECT mission_name, mission_description, date_tasks_mission_id, start_date, end_date, date_task_id, ord, name, description, mandatory_qty, optional_qty, label_name, quantity, points FROM date_tasks JOIN date_tasks_missions USING (date_tasks_mission_id) LEFT JOIN labels USING (label_id) WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND level = $level AND track_id = $track_id ORDER BY school_type_id, subject_id, level, track_id, start_date, end_date, mission_name, date_tasks_mission_id, ord, name, date_task_id"); ?>

<TABLE id="tasks" class="task_grid grid" style="empty-cells: show; border-collapse: separate;">
<?
  $old_row = $row = mysql_fetch_assoc($result);
  if($row) do {
?>
<TR>
  <TH>
    <?=T_('Mission name')?>:<BR>
    <?=T_(es($row['mission_name']))?><BR>
    <?=T_('Mission description')?>:<BR>
    <?=T_(es($row["mission_description"]))?><BR>
    <?=T_('Start Date')?>:<BR>
    <?=es(dateToHebrew($row['start_date']))?><BR>
    <?=T_('End Date')?>:<BR>
    <?=es(dateToHebrew($row['end_date']))?><BR>
  </TH>
<?
    $old_row = $row;
    do {
?>
<TD>
  <?=T_('Task Name')?>:<BR>
  <?=T_(es($row["name"]))?><BR>
  <?=T_('Description')?>:<BR>
  <?=T_(es($row["description"]))?><BR>
  <?=T_('Points')?>: <?=floatval($row['points'])?><BR>
  <?=T_('Mandatory Reps')?>: <?=$row['mandatory_qty']?><BR>
  <?=T_('Optional Reps')?>: <?=$row['optional_qty']?><BR>
  <?=T_('Label')?>: <?=$row['label_name']?><BR>
  <?=T_('Quantity')?>: <?=$row['quantity']?><BR>
</TD>
<?
      $row = mysql_fetch_assoc($result);
    } while($row && $old_row['date_tasks_mission_id'] == $row['date_tasks_mission_id']);
?>
</TR>
<?
  } while($row);
?>
</TABLE>
<?endif;?>
<?endif;?>
<?endif;?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
