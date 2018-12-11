<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');

$startweek = unixtojd()-jddayofweek(unixtojd());
$minday = $startweek-7;
// $limit = 25;

assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$date = gri('date', $startweek);
$user_id = gri('user_id', -1);

$user_row = mysql_fetch_assoc(mq("SELECT username, first, last, first_he, last_he, user_serial, school_type_id, class_grade, class_sub, (SELECT LEAST(IFNULL(MIN(award_date), $minday), $minday) FROM points WHERE user_id = $user_id) min_day FROM users LEFT JOIN classes USING (school_id, class_id) WHERE user_id = $user_id AND school_id = $school_id"));
if(!$user_row) user_error('can not locate user', E_USER_ERROR);

$subjects_result = mq("SELECT subject_id, subject_name FROM subjects JOIN school_type_subjects USING (subject_id) WHERE school_type_id = {$user_row['school_type_id']} AND subject_type = 'school_points' ORDER BY subject_name, subjects.subject_id");

if($edit_points = gra('points')) {
//   $total_points = 0;
  while($row = mysql_fetch_assoc($subjects_result)) {
    for($award_date = $date; $award_date < $date+7; $award_date++) {
      if(empty($edit_points[$row['subject_id']][$award_date]) || !($award_points = floatval($edit_points[$row['subject_id']][$award_date]))/* + $total_points > $limit*/) {
        mq("DELETE FROM points WHERE user_id = $user_id AND award_date = $award_date AND subject_id = {$row['subject_id']}");
      } else {
//         $total_points += $award_points;
        mq("INSERT INTO points SET user_id = $user_id, award_date = $award_date, subject_id = {$row['subject_id']}, award_points = $award_points ON DUPLICATE KEY UPDATE award_points = $award_points");
      }
    }
  }
  @mysql_data_seek($subjects_result, 0);
}

$result = mq("SELECT subject_id, award_date, award_points FROM points WHERE user_id = $user_id AND award_date >= $date AND award_date < $date+7");
while($row = mysql_fetch_assoc($result)) $points[$row['subject_id']][$row['award_date']] = $row['award_points'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('School Points'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript">
function totalPoints(form) {
  var total=0;
  for(i=0; i<form.elements.length; i++) {
    if(form.elements[i].type == 'text') total += parseFloat('0'+form.elements[i].value, 10);
  }
  var disp = document.getElementById('total');
  disp.innerHTML = "<?=T_('Total Points')?>: " + total.toFixed(2) + " <?//T_('Max'), ': ', $limit?>";
/*
  if(total > <?//$limit?>) {
    disp.style.color = "red";
    disp.innerHTML += "<BR><?=T_('Warning: Over point limit.')?>";
  } else {
    disp.style.color = "black";
  }
*/
  return total.toFixed(2);
}
</SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('School Points')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<A HREF="admin_user.php?school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=T_('Back to Soldier list')?></A>

<FORM action="admin_points_school.php" method="get" accept-charset="UTF-8">
<P>
<SELECT name="date" dir="rtl">
<? for($i = $startweek; $i > $user_row['min_day']-7 || $i == $user_row['min_day']; $i-=7): ?>
<OPTION value="<?=$i?>" <?=$i==$date ? 'selected' : ''?>><?=dateToHebrew($i)?> - <?=dateToHebrew($i+6)?>
<? endfor; ?>
</SELECT>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">

<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<HR>

<H2><? if($user_row['class_grade'] != ''): ?><?=T_('Platoon')?>: <?=es($user_row['class_grade'])?>-<?=es($user_row['class_sub'])?><BR><?endif;?>
<?=T_('Soldier Name')?>: <!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first'])?> <?=es($user_row['last'])?> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?> #<?=es($user_row['user_serial'])?></H2>

<FORM action="admin_points_school.php" method="post" accept-charset="UTF-8" name="points" onSubmit="// if(totalPoints(this) > <?//$limit?>) { alert('<?=T_('Over point limit, unable to save.')?>'); return false; } else { return true; }">
<TABLE class="pretty_grid">
<TR>
  <TH><?=es(T_('Subject'))?></TH>
  <? for($i = 0; $i < 7; $i++): ?>
    <TH><?=$weekdays[$i]?><BR><?=dateToHebrew($date+$i)?></TH>
  <? endfor; ?>
</TR>
<? while($row = mysql_fetch_assoc($subjects_result)): ?>
<TR>
  <TH><?=es($row['subject_name'])?></TH>
  <? for($i = $date; $i < $date+7; $i++): ?>
  <TD>
    <? if(false && $admin_user['auth'] != 'super' && $date < $startweek): ?>
      <?=isset($points[$row['subject_id']][$i]) ? floatval($points[$row['subject_id']][$i]) : ''?>
    <? else: ?>
      <INPUT type="text" name="points[<?=$row['subject_id']?>][<?=$i?>]" maxlength="9" size="9" value="<?=isset($points[$row['subject_id']][$i]) ? floatval($points[$row['subject_id']][$i]) : ''?>" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 999999.99)); totalPoints(this.form);">
    <? endif; ?>
  </TD>
  <? endfor; ?>
</TR>
<? endwhile; ?>
</TABLE>
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<INPUT type="hidden" name="date" value="<?=$date?>">
<INPUT type="submit" value="<?=T_('Save')?>"> <SPAN id="total"><?//T_('Max'), ': ', $limit?></SPAN>
</P>
<SCRIPT type="text/javascript">totalPoints(document.forms['points']);</SCRIPT>
</FORM>
</DIV>
</BODY>
</HTML>
