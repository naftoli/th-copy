<DIV CLASS="todo">
<? require_once('calendar.php'); ?>
<?
$date = gri('date', unixtojd());

$result = mq(
($user['settings'] == 'personal_only' ? '' : "
(SELECT   CAST(tasks.task_id AS SIGNED) task_id, tasks.subject_id, tasks.name, tasks.rep_type, tasks.start_date, tasks.end_date, tasks.every, tasks.rep_param1, tasks.rep_param2, subjects.subject_id, subjects.subject_name, institutions.inst_name, missions.mission_id, missions.mission_name, task_active.points
FROM     tasks
         JOIN user_tracks USING (subject_id)
         JOIN users USING (user_id)
         JOIN school_type_subjects USING (subject_id, school_type_id)
         JOIN task_active USING (task_id, track_id, level, school_type_id)
         LEFT JOIN subjects USING (subject_id)
         LEFT JOIN institutions USING (inst_id)
         LEFT JOIN (mission_active
                   JOIN mission_tasks USING (mission_id)
                   JOIN missions USING (mission_id))
           USING (subject_id, school_type_id, level, track_id, task_id)
         LEFT JOIN marks
           ON (tasks.task_id = marks.task_id
               AND marks.mark_date = $date
               AND marks.user_id = users.user_id)
WHERE    marks.task_id IS NULL
         AND users.user_id = {$user['user_id']}
         AND tasks.start_date <= $date
         AND (tasks.end_date >= $date
              OR tasks.end_date IS NULL)
)") .
($user['settings'] != 'personal_only' && $user['settings'] != 'managed' ? ' UNION ALL ' : '') .
($user['settings'] == 'managed' ? '' :
"(
SELECT   -user_tasks.task_id task_id, user_tasks.subject_id, user_tasks.name, user_tasks.rep_type, user_tasks.start_date, user_tasks.end_date, user_tasks.every, user_tasks.rep_param1, user_tasks.rep_param2, subjects.subject_id, subjects.subject_name, institutions.inst_name, NULL mission_id, NULL mission_name, 0 points
FROM     user_tasks
         JOIN users USING (user_id)
         JOIN user_tracks USING (subject_id, user_id)
         JOIN school_type_subjects USING (subject_id, school_type_id)
         LEFT JOIN subjects USING (subject_id)
         LEFT JOIN institutions USING (inst_id)
         LEFT JOIN marks
           ON (-user_tasks.task_id = marks.task_id  -- user_tasks will have less rows than marks, so make the negative on it
               AND marks.mark_date = $date
               AND marks.user_id = users.user_id)
WHERE    marks.task_id IS NULL
         AND FIND_IN_SET(user_tracks.level, user_tasks.levels)
         AND users.user_id = {$user['user_id']}
         AND user_tasks.start_date <= $date
         AND (user_tasks.end_date >= $date
              OR user_tasks.end_date IS NULL)
)")
. " ORDER BY inst_name, subject_name, mission_name, name, task_id"
);
?>
<H1><A name="TaskTodo_"></A><?= T_('Tasks') ?></H1>
<TABLE CELLSPACING=0 CELLPADDING=0 CLASS="<?=$align_start?>">
<?
$old_subject_id = -1;
$old_mission_id = -1;
$previous_task_id = '';
while($row = mysql_fetch_assoc($result)):
  if(!isRepeatToday($row['rep_type'], $date, $row['start_date'], $row['every'], $row['rep_param1'], $row['rep_param2'])) continue;
  if($row['subject_id'] != $old_subject_id || $row['mission_id'] != $old_mission_id) {
    echo '<TR><TH COLSPAN=2>' . es($row['inst_name']) . ' - ' . es($row['subject_name']) . (!is_null($row['mission_id']) ? ' - ' : '') . es($row['mission_name']) . "</TH></TR>\n";
    $old_subject_id = $row['subject_id'];
    $old_mission_id = $row['mission_id'];
  }
?>
  <TR>
    <TD><A name="TaskTodo_<?=$row['task_id']?>"></A><?=$row['points']?>; <?=es($row['name'])?></TD>
    <TD CLASS="arrow"><A title="<?=T_('Mark as done')?>" href="tasks.php?task_id=<?=$row['task_id']?>&amp;date=<?= $date ?>&amp;action=Mark#TaskTodo_<?=$previous_task_id?>" <?= $date > unixtojd()+2 ? 'onClick="return confirm(\'' . es(T_('This date is in the future.\n\nAre you sure?')) . '\');"' : '' ?>><?= $next_arr ?></A></TD>
  </TR>
<? $previous_task_id = $row['task_id']; ?>
<? endwhile; ?>

<? if(empty($old_subject_id)): ?>
<TR><TD CLASS="empty"><?= T_('No uncompleted tasks for this day.') ?></TD></TR>
<? endif; ?>

</TABLE>
</DIV>
