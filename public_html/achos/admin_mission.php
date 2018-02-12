<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
$action = gr('action');
$subject_id = gri('subject_id', -1);
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 mission_id, '' mission_name, 0 mission_points, NULL start_month, NULL start_day, NULL end_month, NULL end_day");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    $mission_name = gr('mission_name');
    $mission_points = grf('mission_points', 0);

    $result = mq('SELECT 1 FROM missions WHERE mission_name = ' . ms($mission_name) . " AND subject_id = $subject_id");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new mission, this name is already used.');
      $result = mq('SELECT -1 mission_id, ' . ms($mission_name) . " mission_name, $mission_points mission_points, " . gri('start_month', 0) . ' start_month, ' . gri('start_day', 0) . ' start_day, ' . gri('end_month', 0) . ' end_month, ' . gri('end_day', 0) . ' end_day');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';
    } else {
      mq('INSERT INTO missions (mission_name, mission_points, subject_id, start_month, start_day, end_month, end_day) VALUES (' . ms($mission_name) . ", $mission_points, $subject_id, " . gri('start_month', 0) . ', ' . gri('start_day', 0) . ', ' . gri('end_month', 0) . ', ' . gri('end_day', 0) . ')');
      $mission_id = mysql_insert_id();
      $active = array();
      $message = T_('Mission added');
    }
    break;

  case 'delete':
    mq('DELETE FROM missions WHERE mission_id = ' . gri('mission_id', -1));
    mq('DELETE FROM mission_active WHERE mission_id = ' . gri('mission_id', -1));
    mq('DELETE FROM mission_tasks WHERE mission_id = ' . gri('mission_id', -1));
    $message = T_('Mission deleted');
    break;

  case 'edit':
    $mission_id = gri('mission_id', -1);
    $result = mq("SELECT mission_id, mission_name, mission_points, start_month, start_day, end_month, end_day FROM missions WHERE mission_id = $mission_id");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $mission_id = gri('mission_id', -1);
    $mission_name = gr('mission_name');
    $mission_points = grf('mission_points', 0);

    $result = mq('SELECT 1 FROM missions WHERE mission_name = ' . ms($mission_name) . " AND subject_id = $subject_id AND mission_id != $mission_id");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit mission, this name is already used.');
      $result = mq("SELECT $mission_id mission_id, " . ms($mission_name) . " mission_name, $mission_points mission_points, " . gri('start_month', 0) . ' start_month, ' . gri('start_day', 0) . ' start_day, ' . gri('end_month', 0) . ' end_month, ' . gri('end_day', 0) . ' end_day');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      mq('UPDATE missions SET mission_name = ' . ms($mission_name) . ", mission_points = $mission_points, subject_id = $subject_id, start_month = " . gri('start_month', 0) . ', start_day = ' . gri('start_day', 0) . ', end_month = ' . gri('end_month', 0) . ', end_day = ' . gri('end_day', 0) . " WHERE mission_id = $mission_id");
      $message = T_('Mission edited');
    }

  case 'active':
    $mission_id = gri('mission_id', -1);
    $result = mq("SELECT school_type_id, level, track_id FROM mission_active WHERE mission_id = $mission_id");
    $active = array();
    while($row = mysql_fetch_assoc($result)) {
      $active[$row['school_type_id']][$row['level']][$row['track_id']] = true;
    }
    break;

  case 'active2':
    $mission_id = gri('mission_id', -1);
    $used = array();
    foreach(gra('mission_active') as $school_type_id => $levels) {
      $school_type_id = intval($school_type_id);
      if(!is_array($levels)) $levels = array();
      foreach($levels as $level => $tracks) {
        $level = max(3, min(intval($level), 14));
        if(!is_array($tracks)) $tracks = array();
        foreach($tracks as $track_id => $unused) {
          $track_id = intval($track_id);
          mq("INSERT IGNORE INTO mission_active (mission_id, school_type_id, level, track_id) VALUES ($mission_id, $school_type_id, $level, $track_id)");
          $used[] = "($school_type_id, $level, $track_id)";
        }
      }
    }
    mq("DELETE FROM mission_active WHERE mission_id = $mission_id" . ($used ? ' AND (school_type_id, level, track_id) NOT IN (' . implode(',', $used) . ')' : ''));
    $message = T_('Mission Active edited');

  case 'tasks':
    $mission_id = gri('mission_id', -1);
    $mission_tasks = array();
    $result = mq("SELECT task_id FROM mission_tasks WHERE mission_id = $mission_id");
    while($row = mysql_fetch_assoc($result)) {
      $mission_tasks[$row['task_id']] = true;
    }
    break;

  case 'tasks2':
    $mission_id = gri('mission_id', -1);
    $task_ids = array();
    foreach(gra('mission_tasks') as $task_id => $unused) {
      $task_id = intval($task_id);
      $task_ids[] = $task_id;
      mq("INSERT IGNORE INTO mission_tasks (mission_id, task_id, reps) VALUES ($mission_id, $task_id, 0)");
    }
    mq("DELETE FROM mission_tasks WHERE mission_id = $mission_id" . (!empty($task_ids) ? ' AND task_id NOT IN (' . implode(',', $task_ids) . ')' : ''));
    $message = T_('Mission Tasks edited');

  case 'reps':
    $mission_id = gri('mission_id', -1);
    $mission_reps = mq("SELECT tasks.task_id, mission_tasks.reps, subjects.subject_name, institutions.inst_name, tasks.name FROM mission_tasks JOIN tasks USING (task_id) JOIN subjects USING (subject_id) JOIN institutions USING (inst_id) WHERE mission_tasks.mission_id = $mission_id ORDER BY institutions.inst_name, subjects.subject_name, tasks.name, tasks.task_id");
    break;

  case 'reps2':
    $mission_id = gri('mission_id', -1);
    foreach(gra('mission_reps') as $task_id => $reps) {
      $task_id = intval($task_id);
      $reps = max(0, min(intval($reps), 65535));
      mq("UPDATE mission_tasks SET reps = $reps WHERE mission_id = $mission_id AND task_id = $task_id");
    }
    $message = T_('Mandatory requirements for Mission Tasks edited');
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}

$subject_result = mq('SELECT subjects.subject_id, subjects.subject_name, institutions.inst_name FROM subjects LEFT JOIN institutions USING (inst_id) ORDER BY institutions.inst_name, subjects.subject_name');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Missions'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="tasks_ae.js"></script>
<SCRIPT type="text/javascript">
function change(school_type_id, level, track_id) {
  if(school_type_id == '') school_type_id = '\\d*';
  if(level == '') level = '\\d*';
  if(track_id == '') track_id = '\\d*';
  var el = document.forms['task'].elements;
  var pattern = new RegExp("mission_active\\[" + school_type_id + "\\]\\[" + level + "\\]\\[" + track_id + "\\]");
  for(var i = 0; i<el.length; i++) {
    if(pattern.test(el[i].name) && !el[i].disabled) {
      el[i].checked = !el[i].checked;
    }
  }
}

function copy(old_school, new_school, type) {
  var el = document.forms['task'].elements;
  var pattern = new RegExp("(mission_active\\[)" + old_school + "(\\]\\[\\d*\\]\\[\\d*\\])");
  for(var i = 0; i<el.length; i++) {
    if((matches=pattern.exec(el[i].name)) && !el[i].disabled) {
      el[matches[1] + new_school + matches[2]].checked = el[i].checked;
    }
  }
}
</SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Missions')?></H1>

<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<FORM action="admin_mission.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Subject')?>:
<SELECT name="subject_id" id="subject_id">
  <? while($row = mysql_fetch_assoc($subject_result)): ?>
    <OPTION VALUE="<?=$row['subject_id']?>" <?=$subject_id == $row['subject_id'] ? 'SELECTED' : '' ?>><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?></OPTION>
      <? endwhile; ?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<?if($subject_id == -1):?>
<?=T_('Please select a Subject.')?>
<?else:?>

<?if($edit_row):?>

<FORM action="admin_mission.php" method="post" accept-charset="UTF-8" name="tasks">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<INPUT type="hidden" name="mission_id" value="<?=$edit_row['mission_id']?>">
<LABEL><?=T_('Name')?>: <INPUT type="text" name="mission_name" maxlength=255 value="<?=es($edit_row['mission_name'])?>"></LABEL><BR>
<LABEL><?=T_('Points required')?>: <INPUT type="text" name="mission_points" maxlength="9" value="<?=floatval($edit_row['mission_points'])?>" onChange="this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 999999.99));"></LABEL><BR>
<LABEL><?=T_('Start Month')?>:
    <SELECT NAME="start_month" DIR='rtl'>
      <? selectMonth($edit_row['start_month']); ?>
    </SELECT>
</LABEL><BR>
<LABEL><?=T_('Start Day')?>:
    <SELECT NAME="start_day" DIR='rtl'>
      <? selectDay($edit_row['start_day']); ?>
    </SELECT>
</LABEL><BR>
<LABEL><?=T_('End Month')?>:
    <SELECT NAME="end_month" DIR='rtl'>
      <? selectMonth($edit_row['end_month']); ?>
    </SELECT>
</LABEL><BR>
<LABEL><?=T_('End Day')?>:
    <SELECT NAME="end_day" DIR='rtl'>
      <? selectDay($edit_row['end_day']); ?>
    </SELECT>
</LABEL><BR>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save and edit Active') : T_('Add new and edit Active')?>">
</P>
</FORM>

<A HREF="admin_mission.php?subject_id=<?=$subject_id?>"><?=T_('Cancel')?></A>

<?elseif(isset($active)):?>

<?
$school_types_result = mq("SELECT school_type_id, school_type_name FROM school_types JOIN school_type_subjects USING (school_type_id) WHERE subject_id = $subject_id ORDER BY school_type_name");
$tracks_result = mq('SELECT track_id, track_name FROM tracks ORDER BY track_name');
?>

<FORM action="admin_mission.php" method="post" accept-charset="UTF-8" name="task">
<P>
<INPUT type="hidden" name="action" value="active2">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<INPUT type="hidden" name="mission_id" value="<?=$mission_id?>">
<INPUT type="submit" value="<?=T_('Save Active and edit Tasks')?>">
</P>
<TABLE class="grid">
<CAPTION><?=T_('Mission Active')?></CAPTION>
<? $old_school_type_id = ''; ?>
<? while($school_type_row = mysql_fetch_assoc($school_types_result)): ?>
<TR>
  <TH colspan="14">
  <? if($old_school_type_id): ?>
      <INPUT type="button" value="<?=T_('Copy previous Tzivos Hashem Type')?>" onClick="copy(<?=$old_school_type_id?>, <?=$school_type_row['school_type_id']?>);"><BR>
  <? endif; ?>
  <? $old_school_type_id = $school_type_row['school_type_id']; ?>
  <?=es($school_type_row['school_type_name'])?>
  </TH>
</TR>
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
  <TH></TH>
</TR>
<? $first_track = true; ?>
<? while($track_row = mysql_fetch_assoc($tracks_result)): ?>
<TR>
  <? if($first_track): ?>
    <?$first_track = false;?>
    <TH rowspan=<?=mysql_num_rows($tracks_result)?>>Ladder</TH>
  <? endif;?>
  <TH><?=es($track_row['track_name'])?></TH>
  <? foreach(range(3, 14) as $level): ?>
    <TD>
      <INPUT type="checkbox" name="mission_active[<?=$school_type_row['school_type_id']?>][<?=$level?>][<?=$track_row['track_id']?>]" <?=isset($active[$school_type_row['school_type_id']][$level][$track_row['track_id']]) ? 'CHECKED' : ''?>>
    </TD>
  <? endforeach; ?>
  <TH>
    <INPUT type="button" value="<?=T_('Flip')?>" onClick="change(<?=$school_type_row['school_type_id']?>,'',<?=$track_row['track_id']?>);">
    </TH>
</TR>
<? endwhile; ?>
<?mysql_data_seek($tracks_result, 0);?>
<TR>
  <TH></TH>
  <TH></TH>
  <? foreach(range(3, 14) as $level): ?>
  <TH>
    <INPUT type="button" value="<?=T_('Flip')?>" onClick="change(<?=$school_type_row['school_type_id']?>,<?=$level?>,'');">
  </TH>
  <? endforeach; ?>
  <TH>
    <INPUT type="button" value="<?=T_('Flip')?>" onClick="change(<?=$school_type_row['school_type_id']?>,'','');">
  </TH>
</TR>
<TR>
  <TH colspan="14">&nbsp;</TH>
</TR>
<? endwhile; ?>
</TABLE>
<P>
<INPUT type="submit" value="<?=T_('Save Active and edit Tasks')?>">
</P>
</FORM>

<A HREF="admin_mission.php?subject_id=<?=$subject_id?>"><?=T_('Cancel')?></A>

<?elseif(isset($mission_tasks)):?>
<H3><?=T_('Mission Tasks')?></H3>

<FORM action="admin_mission.php" method="post" accept-charset="UTF-8" name="task">
<P>
<INPUT type="hidden" name="action" value="tasks2">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<INPUT type="hidden" name="mission_id" value="<?=$mission_id?>">
<INPUT type="submit" value="<?=T_('Save')?>">
</P>
<?$tasks_result = mq("SELECT DISTINCT tasks.task_id, tasks.name FROM tasks JOIN task_active USING (task_id) JOIN mission_active USING (school_type_id, level, track_id) WHERE tasks.subject_id = $subject_id ORDER BY tasks.name, tasks.task_id");?>
<TABLE class="pretty_grid">
<TR>
  <TH><INPUT type="checkbox" onClick="for(i=0; i&lt;this.form.elements.length; i++) if(this.form.elements[i].type=='checkbox') this.form.elements[i].checked=this.checked;"></TH>
  <TH><?=T_('Task Name')?></TH>
</TR>
<? while($row = mysql_fetch_assoc($tasks_result)): ?>
<TR>
    <TD><INPUT type="checkbox" name="mission_tasks[<?=$row['task_id']?>]" <?=isset($mission_tasks[$row['task_id']]) ? 'CHECKED' : ''?>></TD>
    <TD><A HREF="admin_task.php?action=edit&amp;task_id=<?=$row['task_id']?>&amp;subject_id=<?=$subject_id?>"><?=es($row['name'])?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<P>
<INPUT type="submit" value="<?=T_('Save')?>">
</P>
</FORM>

<A HREF="admin_mission.php?subject_id=<?=$subject_id?>"><?=T_('Cancel')?></A>

<?elseif(isset($mission_reps)):?>
<H3><?=T_('Mission Mandatory requirements')?></H3>

<FORM action="admin_mission.php" method="post" accept-charset="UTF-8" name="task">
<P>
<INPUT type="hidden" name="action" value="reps2">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<INPUT type="hidden" name="mission_id" value="<?=$mission_id?>">
<INPUT type="submit" value="<?=T_('Save')?>">
</P>
<TABLE class="pretty_grid">
<TR>
  <TH><?=T_('Mandatory requirements')?> (0 - 65535)</TH>
  <TH><?=T_('Subject')?></TH>
  <TH><?=T_('Task Name')?></TH>
</TR>
<? while($row = mysql_fetch_assoc($mission_reps)): ?>
<TR>
    <TD><INPUT type="text" name="mission_reps[<?=$row['task_id']?>]" size="5" maxlength="5" value="<?=$row['reps']?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));"></TD>
    <TD><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?></TD>
    <TD><?=es($row['name'])?></TD>
</TR>
<? endwhile; ?>
</TABLE>
<P>
<INPUT type="submit" value="<?=T_('Save')?>">
</P>
</FORM>

<A HREF="admin_mission.php?subject_id=<?=$subject_id?>"><?=T_('Cancel')?></A>

<?else:?>

<?
$sql = "SELECT missions.mission_id, subjects.subject_name, institutions.inst_name, missions.mission_name FROM missions LEFT JOIN subjects USING (subject_id) LEFT JOIN institutions USING (inst_id) WHERE subject_id = $subject_id ORDER BY subject_name, mission_name, mission_id";
echo "<input type='hidden' name='SQL' value='" . $sql . "'>\n";
$result = mq("SELECT missions.mission_id, subjects.subject_name, institutions.inst_name, missions.mission_name FROM missions LEFT JOIN subjects USING (subject_id) LEFT JOIN institutions USING (inst_id) WHERE subject_id = $subject_id ORDER BY subject_name, mission_name, mission_id");
?>

<A HREF="admin_mission.php?action=add&amp;subject_id=<?=$subject_id?>"><?=T_('Add new Mission')?></A>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('Subject')?></TH>
  <TH><?=T_('Name')?></TH>
  <TH></TH>
  <TH></TH>
  <TH></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?></TD>
    <TD><?=es($row['mission_name'])?></TD>
    <TD><A HREF="admin_mission.php?action=edit&amp;mission_id=<?=$row['mission_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Edit Mission')?></A></TD>
    <TD><A HREF="admin_mission.php?action=active&amp;mission_id=<?=$row['mission_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Edit Active for Mission')?></A></TD>
    <TD><A HREF="admin_mission.php?action=tasks&amp;mission_id=<?=$row['mission_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Edit Tasks for Mission')?></A></TD>
    <TD><A HREF="admin_mission.php?action=reps&amp;mission_id=<?=$row['mission_id']?>&amp;subject_id=<?=$subject_id?>"><?=T_('Edit Mandatory requirements for Mission Tasks')?></A></TD>
    <TD><A HREF="admin_mission.php?action=delete&amp;mission_id=<?=$row['mission_id']?>&amp;subject_id=<?=$subject_id?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Mission')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
