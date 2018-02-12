<DIV CLASS="done">
<SCRIPT type="text/javascript">
var last_id;
var changed;

function editDesc(task_id) {
  if(last_id) if(!unEditDesc(last_id)) return false;

  last_id = task_id;
  changed = false;
  document.getElementById('disp_' + task_id).style.display = 'none';
  document.getElementById('edit_' + task_id).style.display = '';
  document.forms['form_' + task_id].elements['desc'].focus();

  return true;
}

function changedDesc(task_id) {
  changed = true;
}

function unEditDesc(task_id) {
  if(changed) {
    if(!confirm('<?= es(T_('You will lose your unsaved changes in the task you are editing.\n\n Continue?')) ?>')) return false;
  }
  last_id = null;
  document.getElementById('disp_' + task_id).style.display = '';
  document.getElementById('edit_' + task_id).style.display = 'none';
  document.forms['form_' + task_id].reset();
  return true;
}
</SCRIPT>
<? $points_total = mysql_result(mq("SELECT IFNULL(SUM(mark_points), 0) points_total FROM marks WHERE marks.user_id = {$user['user_id']} AND marks.mark_date = $date"), 0); ?>
<H1><A name="TaskDone_"></A><?= T_('Completed Tasks') ?></H1>
<H2><?=sprintf(T_('Days Points: %s'), $points_total)?></H2>
<?
$date = gri('date', unixtojd());

$result = mq("
(SELECT   CAST(tasks.task_id AS SIGNED) task_id, tasks.subject_id, tasks.name, tasks.rep_type, tasks.start_date, tasks.end_date, tasks.every, tasks.rep_param1, tasks.rep_param2, IF(task_active.task_id IS NOT NULL, 1, 0) user_track, subjects.subject_name, institutions.inst_name, marks.mark_description, marks.mark_points, task_active.points
FROM     tasks
         JOIN marks using (task_id)
         LEFT JOIN subjects
           ON (tasks.subject_id = subjects.subject_id)
         LEFT JOIN institutions USING (inst_id)
         LEFT JOIN (user_tracks JOIN school_type_subjects USING (subject_id) JOIN task_active USING (school_type_id, level, track_id))
           ON (tasks.subject_id = user_tracks.subject_id AND user_tracks.user_id = {$user['user_id']}
               AND tasks.task_id = task_active.task_id
               AND task_active.school_type_id = {$user['school_type_id']}
               " . ($user['settings'] == 'personal_only' ? ' AND 0=1' : '') . ")
WHERE marks.user_id = {$user['user_id']} AND marks.mark_date = $date
) UNION ALL (
SELECT   -user_tasks.task_id task_id, user_tasks.subject_id, user_tasks.name, user_tasks.rep_type, user_tasks.start_date, user_tasks.end_date, user_tasks.every, user_tasks.rep_param1, user_tasks.rep_param2, IF(user_tracks.subject_id IS NOT NULL, 1, 0) user_track, subjects.subject_name, institutions.inst_name, marks.mark_description, marks.mark_points, 0 points
FROM     user_tasks
         JOIN marks
           ON (user_tasks.task_id = -marks.task_id) -- marks is the driver table so make the negative on it, so the index can be used on user_tasks
         LEFT JOIN subjects
           ON (user_tasks.subject_id = subjects.subject_id)
         LEFT JOIN institutions USING (inst_id)
         LEFT JOIN (school_type_subjects JOIN user_tracks USING (subject_id))
           ON (user_tasks.subject_id = user_tracks.subject_id AND user_tracks.user_id = {$user['user_id']} AND school_type_subjects.school_type_id = {$user['school_type_id']}
           " . ($user['settings'] == 'managed' ? ' AND 0=1' : '') . ")
WHERE marks.user_id = {$user['user_id']} AND marks.mark_date = $date
)
ORDER BY inst_name, subject_name, name, task_id
");

$toggle = false;
?>
<TABLE>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR class="<?= (($toggle = !$toggle) ? 'row1' : 'row2') ?>">
  <TH><A title="<?=T_('Un-Mark as done')?>" href="tasks.php?task_id=<?=$row['task_id']?>&amp;date=<?= $date ?>&amp;action=UnMark#TaskTodo_<?=$row['task_id']?>" <?=$row['user_track'] && isRepeatToday($row['rep_type'], $date, $row['start_date'], $row['every'], $row['rep_param1'], $row['rep_param2']) ? ($row['points'] !== $row['mark_points'] ? 'onClick="return confirm(\'' . esq(T_('If you unmark this task you will not have the same number of points if you re-mark it again\n(because it\'s currently worth a different number of points).\n\nAre you sure?')) . '\');"' : '') : 'onClick="return confirm(\'' . esq(T_('If you unmark this task you will NOT be able to re-mark it again\n(because it no longer shows up in your Tasks list).\n\nAre you sure?')) . '\');"'?>><?= $prev_arr ?></A></TH>
  <TD><A name="TaskDone_<?=$row['task_id']?>"></A><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?>; <?=es($row['mark_points'])?>; <?=es($row['name'])?>: <SPAN id="disp_<?=$row['task_id']?>" onClick="editDesc(<?=$row['task_id']?>);" style="cursor: pointer;"><?= es(!empty($row['mark_description']) ? $row['mark_description'] : '---') ?></SPAN>
  <FORM action="tasks.php" method="post" accept-charset="UTF-8" name="form_<?=$row['task_id']?>"><DIV id="edit_<?=$row['task_id']?>" style="display: none;"><TEXTAREA name="desc" cols="30" rows="3" onChange="changedDesc(<?=$row['task_id']?>);"><?= es($row['mark_description']) ?></TEXTAREA><INPUT type="hidden" name="task_id" value="<?=$row['task_id']?>"><INPUT type="hidden" name="action" value="Desc"><INPUT type="hidden" name="date" value="<?=$date?>"><INPUT type="submit" value="<?= es(T_('Save')) ?>"> <INPUT type="button" value="<?= es(T_('Cancel')) ?>" onClick="unEditDesc(<?=$row['task_id']?>);"></DIV></FORM></TD>
</TR>
<? endwhile; ?>
</TABLE>

<? if($user['settings'] == 'personal_only' && !mysql_result(mq("SELECT COUNT(*) FROM user_tasks WHERE user_id = {$user['user_id']}"), 0)): ?>
<DIV class="message_box center_box"><DIV><?=T_('You have no tasks.')?><BR><A HREF="tasks_personal.php"><?=T_('Click Manage tasks to add some.')?></A></DIV></DIV>
<? endif; ?>
</DIV>
