<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'programs';
require_once('admin_ui.php');
$subject_id = gri('subject_id', -1); //for the ui menu

require_once('calendar.php');
$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);

$defaultStartDate = dateThisYear(2, 1);
$defaultDays = dateThisYear(13, 29, $defaultStartDate, 1) - $defaultStartDate;
$defaultWeeks = floor($defaultDays/7);
$defaultDays = $defaultDays - $defaultWeeks*7;

$mode = gr('mode');

if(!empty($action)) switch($action) {
  case 'save':
    foreach(gra('tanya') as $user_id => $data) {
      $user_id = intval($user_id);
      $length_days = intval($data['weeks'])*7 + intval($data['days']);
      if($length_days) {
        $track = max(1, min(intval($data['track']), 20));
        $year = max(1, min(intval($data['year']), 8));
        $tanya_start_date = intval($data['tanya_start_date']);
        if(!$tanya_start_date) $tanya_start_date = unixtojd();
        $length_days_offset = intval($data['weeks_offset'])*7 + intval($data['days_offset']);
        $lines_offset = intval($data['lines_offset']);
        $lines_done = max(intval($data['lines_done']), $lines_offset);
        $pledges = floatval($data['pledges']);
        $collected = floatval($data['collected']);

        mq("INSERT INTO tanya_users (user_id, track, year, tanya_start_date, length_days, length_days_offset, lines_done, lines_offset, pledges, collected) SELECT user_id, $track track, $year year, $tanya_start_date tanya_start_date, $length_days length_days, $length_days_offset length_days_offset, $lines_done lines_done, $lines_offset lines_offset, $pledges pledges, $collected collected FROM users WHERE user_id = $user_id AND school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . " ON DUPLICATE KEY UPDATE track = $track, year = $year, tanya_start_date = $tanya_start_date, length_days = $length_days, length_days_offset = $length_days_offset, lines_done = $lines_done, lines_offset = $lines_offset, pledges = $pledges, collected = $collected");
      } else {
        mq("DELETE FROM tanya_users USING tanya_users JOIN users USING (user_id) WHERE user_id = $user_id AND school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : ''));
      }
    }
    $message = T_("Soldier's ladders edited");

    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$tracks_result = mq("SELECT track_id, track_name FROM tracks ORDER BY track_name ");
$goals_result = mq('SELECT track, lines_goal FROM tanya_goals ORDER BY track');

$edit_result = mq("SELECT users.user_id, users.first, users.last, users.username, class_grade, class_sub, track, year, lines_done, lines_offset, tanya_start_date, length_days, length_days_offset, pledges, collected FROM users LEFT JOIN tanya_users USING (user_id) LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . ' ORDER BY users.last, users.first, users.username');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_("Tanya Setup"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<STYLE type="text/css">
<? if($mode != '' && $mode != 'ladder'): ?>
#tanya_user td:first-child+td+td+td+td+td, th:first-child+th+th+th+th+th {
  display: none;
}
#tanya_user td:first-child+td+td+td+td+td+td, th:first-child+th+th+th+th+th+th {
  display: none;
}
#tanya_user td:first-child+td+td+td+td+td+td+td+td, th:first-child+th+th+th+th+th+th+th+th {
  display: none;
}
<? endif; ?>
<? if($mode != '' && $mode != 'ladder' && $mode != 'ladder_only'): ?>
#tanya_user td:first-child+td+td+td+td+td+td+td, th:first-child+th+th+th+th+th+th+th {
  display: none;
}
<? endif; ?>
<? if($mode != '' && $mode != 'year'): ?>
#tanya_user td:first-child+td, th:first-child+th {
  display: none;
}
<? endif; ?>
<? if($mode != '' && $mode != 'dates'): ?>
#tanya_user td:first-child+td+td, th:first-child+th+th {
  display: none;
}
#tanya_user td:first-child+td+td+td, th:first-child+th+th+th {
  display: none;
}
#tanya_user td:first-child+td+td+td+td, th:first-child+th+th+th+th {
  display: none;
}
<? endif; ?>
<? if($mode != '' && $mode != 'pledge'): ?>
#tanya_user td:first-child+td+td+td+td+td+td+td+td+td, th:first-child+th+th+th+th+th+th+th+th+th {
  display: none;
}
#tanya_user td:first-child+td+td+td+td+td+td+td+td+td+td, th:first-child+th+th+th+th+th+th+th+th+th+th {
  display: none;
}
<? endif; ?>

#tanya_user td:first-child+td+td+td+td, th:first-child+th+th+th+th {
  display: none;
}

</STYLE>
<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
<SCRIPT type="text/javascript">
var goals = [];
<?while($row = mysql_fetch_assoc($goals_result)):?>
goals[<?=$row['track']?>] = <?=$row['lines_goal']?>;
<?endwhile;?>

function yearsGoal(user_id) {
  var els = document.forms['user_tracks'].elements;
  var track = parseInt('0' + els['tanya[' + user_id + '][track]'].value, 10);
  var year = parseInt('0' + els['tanya[' + user_id + '][year]'].value, 10);
  var lines_offset = parseInt('0' + els['tanya[' + user_id + '][lines_offset]'].value, 10);
  return (goals[track] - lines_offset)/8*year+lines_offset;
}

function updateYearsGoal(user_id, notify) {
  var notify = (notify == null);
  var year_goal = yearsGoal(user_id);
  var el = document.getElementById('goal_' + user_id);
  var lines_done = parseInt('0' + document.forms['user_tracks'].elements['tanya[' + user_id + '][lines_done]'].value, 10);

  el.innerHTML = year_goal ? year_goal.toFixed(1) : '';
  el.style.color = lines_done > year_goal ? 'red' : '';
  if(notify && lines_done >= Math.floor(year_goal)) alert('<?=es(esq(T_('Notice: Soldier has met or exceeded his goal for this year.\n\nConsider changing his ladder.')))?>'); //Note: expression for message is different - includes "met goal", not just "exceeded".
  return (lines_done > year_goal);
}

var notify_global = false;
</SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=T_('Campaigns')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_users_tanya.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<DIV class="ui_body">
<DIV class="ui_menu">
<?ui_menu();?>
</DIV>
<DIV class="content">
<DIV class="infobox">
<?
switch($mode) {
  case 'ladder':
    echo '<P>' . T_('The ladders system, helps each school, class or child have a growth plan that suits them best.<BR>In order to establish which ladder your students should be be on, please enter:') . '<OL>' . '<LI>' . T_('The total amount of tanya each child knows') . '<LI>' .
T_('How much Tanya they knew before they joined Tzivos hashem') . '</OL><P>' . T_('PLEASE NOTE: If you are starting this program for your first time, you can leave all your students on ladder 1 for the first month until the children get a handle on the program, at which point you and the children will have a better judgment as to which ladder they should be on.') . '</P>';
    break;

  case 'year':
    echo '<P>' . T_('Select which year the Tanya program is for. The Management system automatically puts new students in year 1 and upgrades students from one year to the next. However if there is any changes that you feel need to be made you can adjust accordingly.') . '</P>';
    break;

  case 'dates':
    echo '<P>' . T_('The program sets the Tanya Dates from Rosh chodesh Cheshvan till chof tes Elul.
However you have the option of starting and ending your program as fits best for your school. In fact you can make a different start and end for each class and even each student.') . '</P>';
    break;

  case 'pledge':
    echo '<P>' . T_('') . '</P>';
    break;

  case 'introduction':
    echo '<P>' . T_('Follow the steps in the links on the left and in just a short while you will have an amazing tanya baal peh program set up for your school.') . '</P>';
    break;
}
?>
</DIV>
<?if($mode != 'introduction'):?>
<DIV class="infobox2">
<H2><?=T_('Tanya Setup')?></H2>
<FORM action="admin_users_tanya.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="mode" value="<?=es($mode)?>">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<LABEL><?=T_('Show only Platoon')?>: <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?while($class_row = mysql_fetch_assoc($class_result)):?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
</DIV>

<?if($edit_result): ?>

<FORM action="admin_users_tanya.php" method="post" accept-charset="UTF-8" name="user_tracks">
<DIV>
<INPUT type="hidden" name="action" value="save">
<INPUT type="hidden" name="mode" value="<?=es($mode)?>">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<TABLE class="list list_<?=$align_start?>" id="tanya_user">
<THEAD>
<TR>
  <TH><?=T_('Soldier')?></TH>
  <TH><?=T_('Year')?> (1-8)</TH>
  <TH><?=T_('Starting Date')?></TH>
  <TH><?=T_('Program Length')?> <A href="#" style="text-decoration: underline;" onClick="alert('<?=es(esq(T_('Leave blank if the soldier is not part of the program')))?>'); return false;">&#9733;</A></TH>
  <TH><?=T_('Program Length Skip')?> <A href="#" style="text-decoration: underline;" onClick="alert('<?=es(esq(T_("If there is a break in the middle of the program, enter how long the break is. It's effective immediately, so enter the value only once the break begins.")))?>'); return false;">&#9733;</A></TH>
  <TH><?=T_('Total lines of Tanya soldier knows by heart')?> <A href="#" style="text-decoration: underline;" onClick="alert('<?=es(esq(T_("How many lines the soldier has learned in total, including before joining Tzivos Hashem. Usually this is changed by scanning a tanya card. This number MUST be larger than or equal to 'Before joining Tzivos Hashem soldier knew'.")))?>'); return false;">&#9733;</A></TH>
  <TH><?=T_('Before joining Tzivos Hashem soldier knew')?> <A href="#" style="text-decoration: underline;" onClick="alert('<?=es(esq(T_("How many lines the soldier learned before the program started. This will proportionally adjust the soldier's yearly goals.")))?>'); return false;">&#9733;</A></TH>
  <TH><?=T_('Ladder')?> (1-20)</TH>
  <TH><?=T_('Goal for this Year (lines)')?></TH>
  <TH><?=T_('Pledges')?></TH>
  <TH><?=T_('Collected')?></TH>
</TR>
</THEAD>
<TR>
  <TH><?=T_('Change all')?>:</TH>
  <TH>
    <INPUT type="text" name="year" value="" maxlength="1" size="1" onChange="if(this.value != '') this.value = Math.max(1, Math.min(parseInt('0'+this.value, 10), 8));"><BR>
    <INPUT type="button" value="Change All" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('tanya')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[year]')==this.form.elements[i].name.length-6 &amp;&amp; this.form.elements[i].name.length-6 >= 0) this.form.elements[i].value = this.form.elements['year'].value; }">
  </TH>
  <TH><SPAN><INPUT type="text" name="tanya_start_date_disp" READONLY size="10" value="<?=es(dateToHebrew($defaultStartDate))?>" onClick="getDate(this.form, 'tanya_start_date', true);"></SPAN><INPUT type="hidden" name="tanya_start_date" value="<?=$defaultStartDate?>"><BR><INPUT type="button" value="Change All" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('tanya')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[tanya_start_date]')==this.form.elements[i].name.length-18 &amp;&amp; this.form.elements[i].name.length-18 >= 0) this.form.elements[i].value = this.form.elements['tanya_start_date'].value; if(this.form.elements[i].name.indexOf('tanya')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[tanya_start_date]_disp')==this.form.elements[i].name.length-23 &amp;&amp; this.form.elements[i].name.length-23 >= 0) this.form.elements[i].value = this.form.elements['tanya_start_date_disp'].value;}"></TH>
  <TH style="white-space: nowrap;">
    <LABEL style="white-space: nowrap;"><?=T_('Weeks')?> <INPUT type="text" name="weeks" value="<?=$defaultWeeks?>" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 55));"></LABEL>
    <LABEL style="white-space: nowrap;"><?=T_('Days')?> <INPUT type="text" name="days" value="<?=$defaultDays?>" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 7));"></LABEL><BR>
    <INPUT type="button" value="Change All" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('tanya')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[weeks]')==this.form.elements[i].name.length-7 &amp;&amp; this.form.elements[i].name.length-7 >= 0) this.form.elements[i].value = this.form.elements['weeks'].value; if(this.form.elements[i].name.indexOf('tanya')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[days]')==this.form.elements[i].name.length-6 &amp;&amp; this.form.elements[i].name.length-6 >= 0) this.form.elements[i].value = this.form.elements['days'].value; }">
  </TH>
  <TH style="white-space: nowrap;">
    <LABEL style="white-space: nowrap;"><?=T_('Weeks')?> <INPUT type="text" name="weeks_offset" value="" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 55));"></LABEL>
    <LABEL style="white-space: nowrap;"><?=T_('Days')?> <INPUT type="text" name="days_offset" value="" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 7));"></LABEL><BR>
    <INPUT type="button" value="Change All" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('tanya')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[weeks_offset]')==this.form.elements[i].name.length-14 &amp;&amp; this.form.elements[i].name.length-14 >= 0) this.form.elements[i].value = this.form.elements['weeks_offset'].value; if(this.form.elements[i].name.indexOf('tanya')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[days_offset]')==this.form.elements[i].name.length-13 &amp;&amp; this.form.elements[i].name.length-13 >= 0) this.form.elements[i].value = this.form.elements['days_offset'].value; }">
  </TH>
  <TH></TH>
  <TH></TH>
  <TH>
    <INPUT type="text" name="track" value="" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(1, Math.min(parseInt('0'+this.value, 10), 20));"><BR>
    <INPUT type="button" value="Change All" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('tanya')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[track]')==this.form.elements[i].name.length-7 &amp;&amp; this.form.elements[i].name.length-7 >= 0) this.form.elements[i].value = this.form.elements['track'].value; }">
  </TH>
  <TH></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? $toggle = 0; ?>
<?while($row = mysql_fetch_assoc($edit_result)):?>
<TR class="<?=($toggle ^= 1) ? 'odd' : 'even'?>">
  <TD><?=$row['class_grade'], '-', $row['class_sub'], ': ', $row['first'], ' ', $row['last']?></TD>
  <TD><INPUT type="text" name="tanya[<?=$row['user_id']?>][year]" value="<?=is_null($row['year']) ? '1' : $row['year']?>" maxlength="1" size="1" onChange="if(this.value != '') this.value = Math.max(1, Math.min(parseInt('0'+this.value, 10), 8)); updateYearsGoal(<?=$row['user_id']?>);"></TD>
  <TD><SPAN><INPUT type="text" name="tanya[<?=$row['user_id']?>][tanya_start_date]_disp" size="10" READONLY value="<?=es(dateToHebrew(is_null($row['tanya_start_date']) ? $defaultStartDate : $row['tanya_start_date']))?>" onClick="getDate(this.form, 'tanya[<?=$row['user_id']?>][tanya_start_date]', true);"></SPAN><INPUT type="hidden" name="tanya[<?=$row['user_id']?>][tanya_start_date]" value="<?=is_null($row['tanya_start_date']) ? $defaultStartDate : $row['tanya_start_date']?>"></TD>
  <TD style="white-space: nowrap;">
    <LABEL style="white-space: nowrap;"><?=T_('Weeks')?> <INPUT type="text" name="tanya[<?=$row['user_id']?>][weeks]" value="<?=is_null($row['length_days']) ? $defaultWeeks : floor($row['length_days']/7)?>" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 55));"></LABEL>
    <LABEL style="white-space: nowrap;"><?=T_('Days')?> <INPUT type="text" name="tanya[<?=$row['user_id']?>][days]" value="<?=is_null($row['length_days']) ? $defaultDays : $row['length_days'] % 7?>" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 7));"></LABEL>
  </TD>
  <TD style="white-space: nowrap;">
    <LABEL style="white-space: nowrap;"><?=T_('Weeks')?> <INPUT type="text" name="tanya[<?=$row['user_id']?>][weeks_offset]" value="<?=is_null($row['length_days_offset']) ? '' : floor($row['length_days_offset']/7)?>" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 55));"></LABEL>
    <LABEL style="white-space: nowrap;"><?=T_('Days')?> <INPUT type="text" name="tanya[<?=$row['user_id']?>][days_offset]" value="<?=is_null($row['length_days_offset']) ? '' : $row['length_days_offset'] % 7?>" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 7));"></LABEL>
  </TD>
  <TD>
    <INPUT type="text" name="tanya[<?=$row['user_id']?>][lines_done]" value="<?=is_null($row['lines_done']) ? '' : $row['lines_done']?>" maxlength="5" size="5" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535)); if(parseInt('0'+this.value, 10) &lt; parseInt('0'+this.form.elements['tanya[<?=$row['user_id']?>][lines_offset]'].value, 10)) { this.value = this.form.elements['tanya[<?=$row['user_id']?>][lines_offset]'].value; alert('<?=es(esq(T_('"Total lines of Tanya soldier knows by heart" can not be less than "Before joining Tzivos Hashem soldier knew". Your entry has been adjusted.\n\nFirst reduce "Before joining Tzivos Hashem soldier knew", and then you can reduce "Total lines of Tanya soldier knows by heart".')))?>') } updateYearsGoal(<?=$row['user_id']?>);">
  </TD>
  <TD>
    <INPUT type="text" name="tanya[<?=$row['user_id']?>][lines_offset]" value="<?=is_null($row['lines_offset']) ? '' : $row['lines_offset']?>" maxlength="5" size="5" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535)); if(parseInt('0'+this.value, 10) &gt; parseInt('0'+this.form.elements['tanya[<?=$row['user_id']?>][lines_done]'].value, 10)) { this.value = this.form.elements['tanya[<?=$row['user_id']?>][lines_done]'].value; alert('<?=es(esq(T_('"Before joining Tzivos Hashem soldier knew" can not be greater than "Total lines of Tanya soldier knows by heart". Your entry has been adjusted.\n\nFirst increase "Total lines of Tanya soldier knows by heart", and then you can increase "Before joining Tzivos Hashem soldier knew".')))?>') } updateYearsGoal(<?=$row['user_id']?>);">
  </TD>
  <TD><INPUT type="text" name="tanya[<?=$row['user_id']?>][track]" value="<?=is_null($row['track']) ? '1' : $row['track']?>" maxlength="2" size="2" onChange="if(this.value != '') this.value = Math.max(1, Math.min(parseInt('0'+this.value, 10), 20)); updateYearsGoal(<?=$row['user_id']?>);"></TD>
  <TD id="goal_<?=$row['user_id']?>">
    N/A
    <SCRIPT type="text/javascript">if(updateYearsGoal(<?=$row['user_id']?>, false)) notify_global = true;</SCRIPT>
  </TD>
  <TD style="white-space: nowrap;">$<INPUT type="text" name="tanya[<?=$row['user_id']?>][pledges]" value="<?=is_null($row['pledges']) ? '' : $row['pledges']?>" maxlength="9" size="5" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 999999.99)).toFixed(2);"></TD>
  <TD style="white-space: nowrap;">$<INPUT type="text" name="tanya[<?=$row['user_id']?>][collected]" value="<?=is_null($row['collected']) ? '' : $row['collected']?>" maxlength="9" size="5" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 999999.99)).toFixed(2);"></TD>
</TR>
<?endwhile;?>
</TABLE>
<?if($mode == 'ladder'):?>
<SCRIPT type="text/javascript">
if(notify_global) alert('<?=es(esq(T_('One (or more) soldiers have exceeded their tanya goals for this year.\n\nPlease review their ladder.')))?>');
</SCRIPT>
<?endif;?>
<P>
<INPUT type="submit" value="<?=T_('Save')?>">
<INPUT type="reset" value="<?=T_('Undo Changes')?>">
</P>
</DIV>
</FORM>
<? endif; ?>
<? endif; ?>
<BR style="clear: both;">
</DIV>
</DIV>
<? endif; ?>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
