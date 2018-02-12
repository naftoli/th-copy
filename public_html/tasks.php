<? require('header.php'); ?>
<?
$action = gr('action');

if(!empty($action)) {
  $task_id = gri('task_id', 0);
  $date = gri('date', unixtojd());

  switch($action) {
    case 'Mark':
      $sql = 'INSERT IGNORE INTO marks (task_id, user_id, mark_date, mark_description, mark_level, mark_track_id, mark_points, mark_quantity)'; //doesn't matter if a duplicate is attempted
      if($task_id > 0) {
        $sql .= "
        SELECT tasks.task_id, user_tracks.user_id, $date mark_date, task_active.description, task_active.level, task_active.track_id, task_active.points, task_active.quantity
        FROM   tasks
               JOIN user_tracks USING (subject_id)
               JOIN task_active
                 ON (tasks.task_id = task_active.task_id
                     AND user_tracks.track_id = task_active.track_id
                     AND user_tracks.level = task_active.level
                     AND task_active.school_type_id = {$user['school_type_id']})
        WHERE  user_tracks.user_id = {$user['user_id']} AND tasks.task_id = $task_id
        ";
      } else {
        $sql .= "
        SELECT -user_tasks.task_id task_id, user_tasks.user_id, $date mark_date, user_tasks.description, user_tracks.level, NULL track_id, 0 points, NULL quantity
        FROM   user_tasks
               JOIN user_tracks USING (subject_id)
        WHERE  user_tasks.user_id = {$user['user_id']} AND user_tasks.task_id = -$task_id
        ";
      }
      mq($sql);
      $message = T_('Marked task as done.');
      break;
    case 'UnMark':
      mq("DELETE FROM marks WHERE task_id = $task_id AND user_id = {$user['user_id']} AND mark_date = $date");
      $message = T_('Marked task as not done.');
      break;
    case 'Desc':
      $desc = gr('desc');
      mq('UPDATE marks SET mark_description = ' . ms($desc) . " WHERE task_id = $task_id AND user_id = {$user['user_id']} AND mark_date = $date");
      $message = T_('Edited description of task.');
      break;
    default:
      user_error('unknown action', E_USER_ERROR);
      break;
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Tasks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<? include('banner.php'); ?>
<DIV CLASS="body">

<? if(isset($message) && $message): ?>
<DIV CLASS="message">
<?= $message ?>
</DIV>
<? endif; ?>

<TABLE CLASS="split" CELLSPACING=0 CELLPADDING=0>
<THEAD>
<TR>
<TH CLASS="tasks"></TH>
<TH CLASS="middle special"><? include('specials.php'); ?></TH>
<TH CLASS="menu"></TH>
</TR>
</THEAD>
<TBODY>
<TR>
<TD CLASS="tasks"><? include('todo.php'); ?></TD>
<TD CLASS="middle"><? include('done.php'); ?></TD>
<TD CLASS="menu menu_<?=$align_end?>"><? include('menu.php'); ?></TD>
</TR>
</TBODY>
</TABLE>
</DIV>
</BODY>
</HTML>
