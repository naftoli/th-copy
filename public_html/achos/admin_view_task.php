<? $admin_auth = array('school', 'class', 'team', 'user'); ?>
<? require('header.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('View Missions and Tasks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<STYLE type="text/css">
.grid td {
  text-align: <?=$align_start?>;
}
</STYLE>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('View Missions and Tasks')?></H1>
<? if(!gri('school_type_id')):
  $result = mq('SELECT school_type_id, school_type_name FROM school_types ORDER BY school_type_name');
?>
  <H3><?=T_('Tzivos Hashem Type')?>:</H3>
  <UL>
  <? while($row = mysql_fetch_assoc($result)): ?>
    <LI><A HREF="admin_view_task.php?school_type_id=<?=$row['school_type_id']?>"><?=es($row['school_type_name'])?></A>
  <? endwhile; ?>
  </UL>
<? else: ?>
<?
$school_type_id = gri('school_type_id');
$result = mq("SELECT school_type_name FROM school_types WHERE school_type_id = $school_type_id");
if(!mysql_num_rows($result)) user_error('unknown tzivos hashem type', E_USER_ERROR);
?>
<H2><A HREF="admin_view_task.php"><?=es(mysql_result($result, 0))?></A> <?=$next_Arr?>

<? if(!gri('subject_id')):
  $result = mq("SELECT subject_id, subject_name, inst_name FROM subjects JOIN school_type_subjects USING (subject_id) JOIN institutions USING (inst_id) WHERE school_type_id = $school_type_id"  . ($admin_user['auth'] != 'super' ? ' AND inst_id IN (' . implode(',', $admin_user['inst_ids']) . ')' : '') . ' ORDER BY inst_name, subject_name');
?>
  </H2><H3><?=T_('Subject')?>:</H3>
  <UL>
  <? while($row = mysql_fetch_assoc($result)): ?>
    <LI><A HREF="admin_view_task.php?school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$row['subject_id']?>"><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name'])?></A>
  <? endwhile; ?>
  </UL>
<? else: ?>
<?
$subject_id = gri('subject_id');
$result = mq("SELECT subject_name, inst_name FROM subjects JOIN school_type_subjects USING (subject_id) JOIN institutions USING (inst_id) WHERE school_type_id = $school_type_id AND subject_id = $subject_id"  . ($admin_user['auth'] != 'super' ? ' AND inst_id IN (' . implode(',', $admin_user['inst_ids']) . ')' : ''));
if(!mysql_num_rows($result)) user_error('unknown subject', E_USER_ERROR);
?>
<A HREF="admin_view_task.php?school_type_id=<?=$school_type_id?>"><?=$admin_user['auth'] == 'super' ? es(mysql_result($result, 0, 1)) . ' - ' : ''?><?=es(mysql_result($result, 0))?></A> <?=$next_Arr?>

<? if(!gri('mission_id')):
  $result = mq("SELECT mission_id, mission_name FROM missions JOIN school_type_subjects USING (subject_id) WHERE school_type_id = $school_type_id AND subject_id = $subject_id ORDER BY mission_name");
?>
  </H2><H3><?=T_('Mission')?>:</H3>
  <UL>
  <? while($row = mysql_fetch_assoc($result)): ?>
    <LI><A HREF="admin_view_task.php?school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$subject_id?>&amp;mission_id=<?=$row['mission_id']?>"><?=es($row['mission_name'])?></A>
  <? endwhile; ?>
  </UL>
<? else: ?>
<?
$mission_id = gri('mission_id');
$result = mq("SELECT mission_name FROM missions JOIN school_type_subjects USING (subject_id) WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND mission_id = $mission_id");
if(!mysql_num_rows($result)) user_error('unknown mission', E_USER_ERROR);
?>
<A HREF="admin_view_task.php?school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$subject_id?>"><?=es(mysql_result($result, 0))?></A> <?=$next_Arr?>

<? if(!gri('task_id')):
  $result = mq("SELECT name, task_id FROM tasks JOIN mission_tasks USING (task_id) JOIN missions USING (mission_id, subject_id) JOIN school_type_subjects USING (subject_id) WHERE EXISTS (SELECT task_id FROM task_active WHERE task_active.task_id = tasks.task_id AND task_active.school_type_id = school_type_subjects.school_type_id) AND EXISTS (SELECT mission_id FROM mission_active WHERE mission_active.mission_id = mission_tasks.mission_id AND mission_active.school_type_id = school_type_subjects.school_type_id) AND school_type_id = $school_type_id AND subject_id = $subject_id AND mission_id = $mission_id ORDER BY name");
?>
  </H2><H3><?=T_('Task')?>:</H3>
  <UL>
  <? while($row = mysql_fetch_assoc($result)): ?>
    <LI><A HREF="admin_view_task.php?school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$subject_id?>&amp;mission_id=<?=$mission_id?>&amp;task_id=<?=$row['task_id']?>"><?=es($row['name'])?></A>
  <? endwhile; ?>
  </UL>
<? else: ?>
<?
$task_id = gri('task_id');
$result = mq("SELECT name FROM tasks JOIN mission_tasks USING (task_id) JOIN missions USING (mission_id, subject_id) JOIN school_type_subjects USING (subject_id) WHERE EXISTS (SELECT task_id FROM task_active WHERE task_active.task_id = tasks.task_id AND task_active.school_type_id = school_type_subjects.school_type_id) AND EXISTS (SELECT mission_id FROM mission_active WHERE mission_active.mission_id = mission_tasks.mission_id AND mission_active.school_type_id = school_type_subjects.school_type_id) AND school_type_id = $school_type_id AND subject_id = $subject_id AND mission_id = $mission_id AND task_id = $task_id");
if(!mysql_num_rows($result)) user_error('unknown mission', E_USER_ERROR);
?>
<A HREF="admin_view_task.php?school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$subject_id?>&amp;task_id=<?=$task_id?>"><?=es(mysql_result($result, 0))?></A> <?=$next_Arr?>

</H2>

<?
$result = mq("SELECT description, points, level, track_id FROM mission_tasks JOIN mission_active USING (mission_id) JOIN task_active USING (task_id, school_type_id, level, track_id) JOIN missions USING (mission_id) JOIN school_type_subjects USING (subject_id, school_type_id) WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND mission_id = $mission_id AND task_id = $task_id");
$task_active = array();
while($row = mysql_fetch_assoc($result)) {
  $task_active[$row['track_id']][$row['level']] = array('description'=>$row['description'],'points'=>$row['points']);
}
$tracks_result = mq('SELECT track_id, track_name FROM tracks ORDER BY track_name');
?>
<TABLE class="grid">
<TR>
  <TH></TH>
  <TH></TH>
<TH colspan="12"><?=T_('Year')?></TH>
</TR>
<TR>
  <TH></TH>
  <TH></TH>
<? foreach(range(3, 14) as $level): ?>
    <TH><?=$level?></TH>
<? endforeach; ?>
</TR>
<? $first_track = true; ?>
<? while($track_row = mysql_fetch_assoc($tracks_result)): ?>
<? if(isset($task_active[$track_row['track_id']])): ?>
<TR>
  <? if($first_track): ?>
  <?$first_track = false;?>
  <TH rowspan=<?=mysql_num_rows($tracks_result)?>>Track</TH>
  <? endif;?>
  <TH><?=es($track_row['track_name'])?></TH>
  <? foreach(range(3, 14) as $level): ?>
    <TD>
  <?=isset($task_active[$track_row['track_id']][$level]['points']) ? floatval($task_active[$track_row['track_id']][$level]['points']) . '<BR>' . es($task_active[$track_row['track_id']][$level]['description']) : T_('N/A') ?>
    </TD>
  <? endforeach; ?>
</TR>
<? endif; ?>
<? endwhile; ?>
</TABLE>
<? endif; ?>
<? endif; ?>
<? endif; ?>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
