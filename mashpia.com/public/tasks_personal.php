<? $req_school_type_setting = array('managed_personal', 'self_managed', 'personal_only'); ?>
<? require('header.php'); ?>
<? require_once('calendar.php'); ?>
<?
$action = gr('action', 'list');
switch($action) {
  case 'add':
    $result = mq("SELECT -1 task_id, -1 subject_id, '' name, 'daily' rep_type, " . unixtojd() . " start_date, NULL end_date, 1 every, NULL rep_param1, NULL rep_param2, " . ms(implode(",", range(3,14))) . " levels, '' description");
    $tasks_ae_row = mysql_fetch_assoc($result);
    break;

  case 'edit':
    $task_id = gri('task_id', -1);
    $tasks_ae_result = mq("SELECT task_id, subject_id, name, rep_type, start_date, end_date, every, rep_param1, rep_param2, levels, description FROM user_tasks WHERE task_id = $task_id");
    $tasks_ae_row = mysql_fetch_assoc($tasks_ae_result);
    break;

  case 'delete':
    $task_id = gri('task_id', -1);
    mq("DELETE FROM user_tasks WHERE task_id = $task_id AND user_id = {$user['user_id']}");
    if(mysql_affected_rows()) $message = T_('Task deleted.');
    $action = 'list';
    break;

  case 'edit2':
    function verify_task_id($task_id) {
      global $user;
      return mysql_fetch_assoc(mq("SELECT task_id FROM user_tasks WHERE task_id = $task_id AND user_id = {$user['user_id']}"));
    }
  case 'add2':
    //common
    $subject_id = gri('subject_id', -1);

    if(mysql_num_rows(mq("SELECT school_type_subjects.subject_id FROM school_type_subjects WHERE school_type_subjects.school_type_id = {$user['school_type_id']} AND school_type_subjects.subject_id = $subject_id"))) {
      $owner_sql = "user_id = {$user['user_id']}, subject_id = $subject_id, levels = " . ms(implode(',', gra('levels'))) . ', description = ' . ms(gr('description'));
      $task_ae_table = 'user_tasks';
      include('tasks_ae2.php');
    }
    $action = 'list';
    break;

  case 'import':
    break;

  case 'import2':
    mq("INSERT INTO user_tasks SELECT NULL task_id, {$user['user_id']} user_id, subject_id, name, MAX(description) description, rep_type, start_date, end_date, every, rep_param1, rep_param2, GROUP_CONCAT(task_active.level SEPARATOR ',') levels FROM tasks JOIN school_types JOIN school_type_subjects USING (school_type_id, subject_id) JOIN task_active USING (task_id) WHERE task_active.school_type_id = " . gri('school_type_id') . ' AND task_active.track_id = ' .  gri('track_id') . ' AND school_types.school_type_setting = ' . ms($user['settings']) . ' AND task_id IN (' . implode(',', array_filter(gra('task_imports'), 'is_numeric')) . ') GROUP BY task_id, name, rep_type, every, rep_param1, rep_param2, start_date, end_date');

    $message = T_('Tasks added.');
    $action = 'list';
    break;

  case 'list':
    break;

  default:
    user_error('unknown action ', E_USER_ERROR);
    break;
}
if($action=='list') {
  $tasks = mq("SELECT user_tasks.task_id, subjects.subject_name, institutions.inst_name, user_tasks.name FROM user_tasks LEFT JOIN subjects USING (subject_id) LEFT JOIN institutions USING (inst_id) WHERE user_id = {$user['user_id']} ORDER BY institutions.inst_name, subjects.subject_name, user_tasks.name, user_tasks.task_id");
} else {
  $subject_result = mq("SELECT subjects.subject_id, subjects.subject_name, institutions.inst_name FROM subjects JOIN school_type_subjects ON (subjects.subject_id = school_type_subjects.subject_id AND school_type_subjects.school_type_id = {$user['school_type_id']}) LEFT JOIN institutions USING (inst_id) ORDER BY institutions.inst_name, subjects.subject_name");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Custom Tasks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="tasks_ae.js"></script>
<?include('tasks_ae.inc');?>
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
<TR>
<TH></TH>
<TD CLASS="special"><? include('specials.php'); ?></TD>
<TH></TH>
</TR>
<TR>
<TD CLASS="tasks"><? include('todo.php'); ?></TD>
<TD CLASS="middle form form_<?= $align_start ?>">

<?if($action == 'import'):?>

<H3><?=T_('Add predefined tasks')?></H3>

<?if(is_null($school_type_id = gri('school_type_id'))):?>

<P><?=T_('Choose the tzivos hashem type')?>:</P>
<UL>
<? $result = mq('SELECT school_type_id, school_type_name FROM school_types WHERE school_type_setting =' . ms($user['settings']) . ' ORDER BY school_type_name, school_type_id'); ?>
<? while($row = mysql_fetch_assoc($result)): ?>
<LI><A HREF="tasks_personal.php?action=import&amp;school_type_id=<?=$row['school_type_id']?>"><?=es($row['school_type_name'])?> <?=$next_Arr?></A>
<? endwhile; ?>
</UL>

<?elseif(is_null($subject_id = gri('subject_id'))):?>

<P><?=T_('Choose the subject')?>:</P>
<UL>
<? $result = mq("SELECT subjects.subject_id, subjects.subject_name, institutions.inst_name FROM subjects JOIN school_type_subjects ON (subjects.subject_id = school_type_subjects.subject_id AND school_type_subjects.school_type_id = $school_type_id) LEFT JOIN institutions USING (inst_id) ORDER BY institutions.inst_name, subjects.subject_name"); ?>
<? while($row = mysql_fetch_assoc($result)): ?>
<LI><A HREF="tasks_personal.php?action=import&amp;school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$row['subject_id']?>"><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?> <?=$next_Arr?></A>
<? endwhile; ?>
</UL>

<?elseif(is_null($track_id = gri('track_id'))):?>

<P><?=T_('Choose the ladder')?>:</P>
<UL>
<? $result = mq('SELECT track_id, track_name FROM tracks ORDER BY track_name, track_id'); ?>
<? while($row = mysql_fetch_assoc($result)): ?>
<LI><A HREF="tasks_personal.php?action=import&amp;school_type_id=<?=$school_type_id?>&amp;subject_id=<?=$subject_id?>&amp;track_id=<?=$row['track_id']?>"><?=es($row['track_name'])?> <?=$next_Arr?></A>
<? endwhile; ?>
</UL>

<?else:?>

<?
$result = mq("SELECT task_id, name, rep_type, every, rep_param1, rep_param2, start_date, end_date, MAX(description) description FROM tasks JOIN school_types JOIN school_type_subjects USING (school_type_id, subject_id) JOIN task_active USING (task_id) WHERE school_type_id = $school_type_id AND subject_id = $subject_id AND task_active.school_type_id = $school_type_id AND task_active.track_id = $track_id AND (end_date IS NULL OR end_date >= " . unixtojd() . ') GROUP BY task_id, name, rep_type, every, rep_param1, rep_param2, start_date, end_date'); ?>

<FORM action="tasks_personal.php" method="post" accept-charset="UTF-8" name="task_imports">
<P>
<INPUT type="hidden" name="action" value="import2">
<INPUT type="hidden" name="track_id" value="<?=$track_id?>">
<INPUT type="hidden" name="school_type_id" value="<?=$school_type_id?>">

<? while($row = mysql_fetch_assoc($result)): ?>
<LABEL>
<INPUT type="checkbox" name="task_imports[]" value="<?=$row['task_id']?>">
<A NAME="task_import_<?=$row['task_id']?>" class="hover"><SPAN style="color: red;">+</SPAN>
<SPAN class="box">
<SPAN style="white-space: nowrap;"><B><?=T_('Repeats every')?>:</B> <?=displayRepeat($row['rep_type'], $row['every'], $row['rep_param1'], $row['rep_param2'])?></SPAN><BR>
<SPAN style="white-space: nowrap;"><B><?=T_('Starting')?>:</B> <?=dateToHebrew($row['start_date'])?></SPAN><BR>
<SPAN style="white-space: nowrap;"><B><?=T_('Ending')?>:</B> <?=is_null($row['end_date']) ? T_('No end date') : dateToHebrew($row['end_date'])?></SPAN><BR><BR>
<B><?=T_('Description')?>:</B><BR><?=$row['description']?><BR>
</SPAN>
<?=es($row['name'])?></A></LABEL><BR>
<? endwhile; ?>

</P>

<P class="center_spans">
<SPAN><A HREF="#" onClick="for(i=0; i&lt; document.forms['task_imports'].length; i++) document.forms['task_imports'][i].checked=true; return false;"><?=T_('Select all')?></A></SPAN> |
<SPAN><A HREF="#" onClick="for(i=0; i&lt; document.forms['task_imports'].length; i++) document.forms['task_imports'][i].checked=false; return false;"><?=T_('Select none')?></A></SPAN> |
<SPAN><A HREF="#" onClick="for(i=0; i&lt; document.forms['task_imports'].length; i++) document.forms['task_imports'][i].checked=!document.forms['task_imports'][i].checked; return false;"><?=T_('Toggle')?></A></SPAN><BR>
<SPAN><INPUT type="submit" value="<?=T_('Add selected tasks')?>"></SPAN>
</P>

</FORM>
<?endif;?>

<?elseif($action != 'list'):?>

<P><A HREF="tasks_personal.php"><?=T_('Cancel')?></A></P>

<FORM action="tasks_personal.php" method="post" accept-charset="UTF-8" name="tasks">
<TABLE>
<TR>
  <TH><LABEL for="subject_id">*<?=T_('Subject')?>:</LABEL></TH>
    <TD><SELECT name="subject_id" id="subject_id">
      <? while($row = mysql_fetch_assoc($subject_result)): ?>
        <OPTION VALUE="<?=$row['subject_id']?>" <?=$tasks_ae_row['subject_id'] == $row['subject_id'] ? 'SELECTED' : '' ?>><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?></OPTION>
      <? endwhile; ?>
    </SELECT></TD>
</TR>
<TR>
  <TH></TH>
  <TD>*<?=T_('The list of subjects available depends on your tzivos hashem type.')?></TD>
<? include('tasks_ae.php'); ?>
<TR>
  <TH><LABEL for="description"><?=T_('Description')?>:</LABEL></TH>
  <TD><TEXTAREA name="description" id="description" rows="3" cols="30"><?=es($tasks_ae_row['description'])?></TEXTAREA></TD>
</TR>
<TR>
  <TH>*<?=T_('Years')?>:</TH>
  <TD>
    <? foreach(range(3, 14) as $level): ?>
     <LABEL style="white-space: nowrap;"><INPUT type="checkbox" name="levels[]" value="<?=$level?>" <?=in_array($level, explode(',', $tasks_ae_row['levels'])) ? 'CHECKED' : ''?>><?=$level?>&nbsp; &nbsp;</LABEL>
    <? endforeach; ?>
  </TD>
</TR>
<TR>
  <TH></TH>
  <TD>*<?=T_("A custom ladder will only show up if it's subject is enabled in 'Manage Ladders and Years', and, if the year selected there matches the years enabled in these checkboxes.")?></TD>
</TR>
<TR>
  <TH></TH>
  <TD>
    <INPUT type="hidden" name="action" value="<?=$action?>2">
    <INPUT type="hidden" name="tasks[<?=$tasks_ae_row['task_id']?>][include]" value="YES">
    <INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
  </TD>
</TR>
</TABLE>
</FORM>
<?else:?>
<P CLASS="center_spans">
<SPAN><A HREF="tasks_personal.php?action=add"><?=T_('Add a customized task')?></A></SPAN> |
<SPAN><A HREF="tasks_personal.php?action=import"><?=T_('Add predefined tasks')?></A></SPAN>
</P>

<?if(mysql_num_rows($tasks)):?>
<TABLE class="dashed_lines">
<TR>
  <TH><?=T_('Subject')?></TH>
  <TH><?=T_('Name')?></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($tasks)): ?>
<TR>
    <TD><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?></TD>
    <TD><?=es($row['name'])?></TD>
    <TD><A HREF="tasks_personal.php?action=edit&amp;task_id=<?=$row['task_id']?>"><?=T_('Edit Task')?></A></TD>
    <TD><A HREF="tasks_personal.php?action=delete&amp;task_id=<?=$row['task_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Task')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<?endif;?>
<?endif;?>

</TD>
<TD CLASS="menu menu_<?=$align_end?>"><? include('menu_tasks.php'); ?></TD>
</TR>
</TABLE>
</DIV>
</BODY>
</HTML>
