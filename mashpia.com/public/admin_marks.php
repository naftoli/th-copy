<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$group = gr('group', 'mission');

if(!empty($action)) switch($action) {
  case 'view':
    $start_date = jewishtojd(gri('start_month', 0), gri('start_day', 0), gri('start_year', 0));
    $end_date = jewishtojd(gri('end_month', 0), gri('end_day', 0), gri('end_year', 0));
    break;

  case 'save':
    $marks = gra('marks');
    foreach($marks['user_id'] as $user_id => $tasks) {
      foreach($tasks['task_id'] as $task_id => $dates) {
        foreach($dates['date'] as $date => $active) {
          $user_id = intval($user_id);
          $task_id = intval($task_id);
          $date = intval($date);
          switch($active) {
            case 'on':
              mq("INSERT IGNORE INTO marks (task_id, user_id, mark_date, mark_description, mark_level, mark_track_id, mark_points, mark_quantity) (SELECT task_id, user_id, $date, description, level, track_id, points, quantity FROM task_active JOIN user_tracks USING (track_id, level) JOIN users USING (user_id, school_type_id) WHERE task_id = $task_id AND user_id = $user_id)");
              break;

            case 'off':
              mq("DELETE FROM marks WHERE task_id = $task_id AND user_id = $user_id AND mark_date = $date");
              break;

            default:
              user_error('unknown active', E_USER_ERROR);
              break;
          }
        }
      }
    }
    $message = T_('Marks saved');
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}

if(!isset($start_date)) $start_date = unixtojd()-7;
if(!isset($end_date)) $end_date = unixtojd()-1;

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Marks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript">
function countChecks(el) {
  var pattern = new RegExp("marks\\[user_id\\]\\[(\\d*)\\]\\[task_id\\]\\[(\\d*)\\]");
  var matches=pattern.exec(el.name);
  var user_id = matches[1];
  var task_id = matches[2];

  pattern.compile("marks\\[user_id\\]\\[" + user_id + "\\]\\[task_id\\]\\[" + task_id + "\\]\\[date\\]\\[\\d*\\]");

  var count = 0;
  for(var i = 0; i < el.form.elements.length; i++) {
    if(pattern.test(el.form.elements[i].name) && el.form.elements[i].type == 'checkbox') {
      if(el.form.elements[i].checked) count++;
    }
  }

  el.form.elements["marks[user_id][" + user_id + "][task_id][" + task_id + "][count]"].value = count;
}

function setChecks(el) {
  var pattern = new RegExp("marks\\[user_id\\]\\[(\\d*)\\]\\[task_id\\]\\[(\\d*)\\]\\[count\\]");
  var matches=pattern.exec(el.name);
  var user_id = matches[1];
  var task_id = matches[2];

  pattern.compile("marks\\[user_id\\]\\[" + user_id + "\\]\\[task_id\\]\\[" + task_id + "\\]\\[date\\]\\[\\d*\\]");

  var count = parseInt('0'+el.value, 10);
  for(var i = 0; i < el.form.elements.length; i++) {
    if(pattern.test(el.form.elements[i].name) && el.form.elements[i].type == 'checkbox') {
      el.form.elements[i].checked = (count-- > 0);
    }
  }

  countChecks(el);
}

function toggleCheck(id, el) {
  if(document.getElementById(id).style.display == '') {
    document.getElementById(id).style.display = 'none';
    el.innerHTML = '+';
  } else {
    document.getElementById(id).style.display = '';
    el.innerHTML = '&minus;';
  }
}
</SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Marks')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_marks.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>"><BR>
<?=T_("Note: Only tasks with an institution type matching this institution's institution type will be shown or calculated.")?>
</P>
</FORM>

<HR>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>

<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?><P><A HREF="admin_school.php"><?=T_('Back to Institution list')?></A></P><?endif;?>
<P><A HREF="admin_class.php?school_id=<?=$school_id?>"><?=T_('Back to Platoon list')?></A></P>

<FORM action="admin_marks.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="action" value="view">

<?=T_('Show Platoon')?>: <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?while($class_row = mysql_fetch_assoc($class_result)):?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT><BR>

<?
$today_cal = cal_from_jd($end_date, CAL_JEWISH);
$lastw_cal = cal_from_jd($start_date, CAL_JEWISH);
?>
<?=T_('From')?>:
    <SELECT NAME="start_day" DIR='rtl'>
      <? selectDay($lastw_cal['day']); ?>
    </SELECT>
    <SELECT NAME="start_month" DIR='rtl'>
      <? selectMonth($lastw_cal['month']); ?>
    </SELECT>
    <SELECT NAME="start_year" DIR='rtl'>
      <? selectYear($lastw_cal['year']); ?>
    </SELECT>
<BR>
<?=T_('To')?>:
    <SELECT NAME="end_day" DIR='rtl'>
      <? selectDay($today_cal['day']); ?>
    </SELECT>
    <SELECT NAME="end_month" DIR='rtl'>
      <? selectMonth($today_cal['month']); ?>
    </SELECT>
    <SELECT NAME="end_year" DIR='rtl'>
      <? selectYear($today_cal['year']); ?>
    </SELECT>
<BR>
<LABEL><INPUT type="radio" name="group" value="task" <?=$group=='task' ? 'CHECKED' : ''?>><?=T_('Show all tasks')?></LABEL>
<LABEL><INPUT type="radio" name="group" value="mission" <?=$group=='mission' ? 'CHECKED' : ''?>><?=T_('Group by Mission')?></LABEL>
<BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<? if($action != 'view' || !$start_date || !$end_date): ?>
<?=T_('Please select a date range.')?>
<? else: ?>
<?
$result = mq("
SELECT   tasks.*, task_active.description, task_active.points, marks.task_id mark, users.user_id, subjects.subject_name, subjects.subject_id, users.username, users.first, users.last, date.num," .
($group == 'mission' ? 'missions.mission_id, missions.mission_name,' : '') . "
         (tasks.start_date <= num
          AND (tasks.end_date >= num
               OR tasks.end_date IS NULL)) active
FROM     (users
         JOIN schools USING (school_id)
         JOIN user_tracks USING (user_id)
         JOIN tasks USING (subject_id)
         JOIN task_active USING (task_id, track_id, level, school_type_id))" .
         ($group == 'mission' ? '
          JOIN mission_tasks USING (task_id)
          JOIN missions USING (mission_id)
          JOIN mission_active USING (mission_id, school_type_id, level, track_id)' : '') . "
         JOIN (" . rangeRows($start_date, $end_date) . ") date
         LEFT JOIN marks
           ON (tasks.task_id = marks.task_id
               AND marks.mark_date = date.num
               AND marks.user_id = users.user_id)
         LEFT JOIN (subjects JOIN school_type_subjects USING (subject_id) JOIN institutions USING (inst_id))
           ON (tasks.subject_id = subjects.subject_id AND users.school_type_id = school_type_subjects.school_type_id)
WHERE   (tasks.start_date <= $end_date
          AND (tasks.end_date >= $start_date
               OR tasks.end_date IS NULL))
         AND users.school_id = $school_id
         " . ($class_id >= 0 ? "AND users.class_id = $class_id" : '') . "
         AND (institutions.inst_id IS NULL OR institutions.inst_id = schools.inst_id)
ORDER BY users.last, users.first, users.username, subjects.subject_name, " . ($group == 'mission' ? 'missions.mission_name, ' : '') . "tasks.name, tasks.task_id, date.num
");

$max_cols = 6;
$width = floor(100/$max_cols);
$colors = array('#ccffcc', '#ccffff', '#ff99cc', '#ffff99');

?>
<FORM action="admin_marks.php" method="post" accept-charset="UTF-8" name="marks">
<DIV>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<INPUT type="hidden" name="action" value="save">

<TABLE CLASS="pretty_grid marks">
<?
$tabindex = 0;
$old_row = $row = mysql_fetch_assoc($result);

if($row) do { //loop users
  reset($colors);
  $seen_tasks = array();
?>
  <TR class="double2">
    <TH COLSPAN="<?=$max_cols+1?>" STYLE="font-size: 125%; padding: .5em 1px; background-color: #ffcc99;">
      <?=es($row['last'])?>, <?=es($row['first'])?>
    </TH>
  </TR>
<?
  do { //loop missions within a user
    if(!next($colors)) reset($colors);
    $cols = 0;
?>
    <TR class="double">
      <TH style="white-space: nowrap; background-color: <?=current($colors)?>;"><?=es($row['subject_name']) . ($group == 'mission' ? '<BR>' . es($row['mission_name']) : '')?></TH>
<?
      do { //loop tasks within a mission
        $old_row = $row;
        $row_out = '';
        $show_row_out = false;
        $checked = 0;
        do { //loop dates within a task
          if($row['active'] && isRepeatToday($row['rep_type'], $row['num'], $row['start_date'], $row['every'], $row['rep_param1'], $row['rep_param2'])) {
              $row_out .= "<input type='hidden' name='marks[user_id][{$row['user_id']}][task_id][{$row['task_id']}][date][{$row['num']}]' value='off'>";
              $row_out .= "<input type='checkbox' name='marks[user_id][{$row['user_id']}][task_id][{$row['task_id']}][date][{$row['num']}]' value='on' " . ($row['mark'] ? 'CHECKED' : '') . ' TITLE="' . es(dateToHebrew($row['num'])) . "\" onClick='countChecks(this);'>\n";
              $show_row_out = true;
              if($row['mark']) $checked++;
          } else {
              $row_out .= '<INPUT type="checkbox" DISABLED>' . "\n";
          }
          $row = mysql_fetch_assoc($result);
        } while($row && $old_row['user_id'] == $row['user_id'] && $old_row['subject_id'] == $row['subject_id'] && ($group != 'mission' || $old_row['mission_id'] == $row['mission_id']) && $old_row['task_id'] == $row['task_id']);

        if($show_row_out) {
          if(++$cols > $max_cols) {
            echo "</TR>\n<TR><TH style='background-color: " . current($colors) . ";'></TH>\n";
            $cols = 1;
          }
?>
          <TD style="width: <?=$width?>%; background-color: <?=current($colors)?>;">
            <DIV STYLE="color: green;"><?=es($old_row['name'])?> (<?=floatval($old_row['points'])?>)</DIV>
            <DIV STYLE="color: blue;"><?=es($old_row['description'])?>&nbsp;</DIV>
            <? if(isset($seen_tasks[$old_row['task_id']])): ?>
              <?=T_('Duplicate of task already displayed.')?>
            <? else: ?>
              <? $seen_tasks[$old_row['task_id']] = true; ?>
              <A HREF="#" style="vertical-align: 30%;" onClick="toggleCheck('marks_u<?=$old_row['user_id']?>t<?=$old_row['task_id']?>', this); return false;">+</A>
              <INPUT type="text" value="<?=$checked?>" tabindex="<?=++$tabindex?>" size=2 style="width: 2em;" name="marks[user_id][<?=$old_row['user_id']?>][task_id][<?=$old_row['task_id']?>][count]" onChange="setChecks(this);">
              <A HREF="#" style="vertical-align: 30%; font-size: 150%;" onClick="el=document.forms['marks'].elements['marks[user_id][<?=$old_row['user_id']?>][task_id][<?=$old_row['task_id']?>][count]']; el.value=parseInt('0'+el.value, 10)+1; el.onchange(); return false;">&uarr;</A>&nbsp;<!--
              --><A HREF="#" style="vertical-align: 30%; font-size: 150%;" onClick="el=document.forms['marks'].elements['marks[user_id][<?=$old_row['user_id']?>][task_id][<?=$old_row['task_id']?>][count]']; el.value=parseInt('0'+el.value, 10)-1; el.onchange(); return false;">&darr;</A>
              <DIV style="display: none;" ID="marks_u<?=$old_row['user_id']?>t<?=$old_row['task_id']?>"><?=$row_out?></DIV>
            <? endif; ?>
          </TD>
<?
        }
      } while($row && $old_row['user_id'] == $row['user_id'] && $old_row['subject_id'] == $row['subject_id'] && ($group != 'mission' || $old_row['mission_id'] == $row['mission_id']));
    while($cols++ < $max_cols)
      echo "<TD style='width: $width%; background-color: " . current($colors) . ";'><BR><BR><BR></TD>\n";
    echo "</TR>\n";
  } while($row && $old_row['user_id'] == $row['user_id']);
} while($row);
else
  echo '<TR><TD>' . T_('No tasks for the selected institution, platoon, and date range.') . '</TD></TR>';
?>
</TABLE>
<INPUT class="submit" type="submit" value="<?=T_('Save')?>">
</DIV>
</FORM>
<? endif; ?>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
