<? require_once('calendar.php'); ?>
<? if(!isset($cal_url)) $cal_url = 'tasks.php?'; ?>
<? if(!isset($cal_onclick)) $cal_onclick = ''; ?>
<DIV CLASS="cal">
<TABLE>
<?
$date = gri('date', unixtojd());
if($date < 347998) $date = 347998;
if($date > 4000075) $date = 4000075;

$dates = array_filter(explode(',', gr('dates')), 'is_numeric');

if(isset($_GETPOST['year']) && isset($_GETPOST['month'])) {
  $today = cal_to_jd(CAL_JEWISH, $_GETPOST['month'], 1, $_GETPOST['year']);
  } else {
  $temp = cal_from_jd($date, CAL_JEWISH);
  $today = cal_to_jd(CAL_JEWISH, $temp['month'], 1, $temp['year']);
}
//$today is specifically the JD of the FIRST day of the month

//@list($day, $month, $year, $year2) = mb_split(' ',  dateToHebrew($today));
list($day, $month, $year, $year2) = explode(' ',  dateToHebrew($today));
if(!empty($year2)) {
  $month = "$month $year";
  $year = $year2;
}

$today_cal = cal_from_jd($today, CAL_JEWISH);

$last_month = cal_from_jd($today - 1, CAL_JEWISH);
$last_month_day = cal_to_jd(CAL_JEWISH, $last_month['month'], 1, $last_month['year']);
$next_month = cal_from_jd($today + 40, CAL_JEWISH); //sometime in the middle of next month
$next_month_day = cal_to_jd(CAL_JEWISH, $next_month['month'], 1, $next_month['year']);
$last_year = cal_to_jd(CAL_JEWISH, $today_cal['month'], 1, $today_cal['year']-1);
$next_year = cal_to_jd(CAL_JEWISH, $today_cal['month'], 1, $today_cal['year']+1);

echo "<CAPTION><A TITLE='" . es(T_('Previous Year')) . "' HREF='{$cal_url}date=$last_year'> <IMG SRC='images/d-arrow_8_{$align_start}.png' ALT='$prev_Arr'></A> <A TITLE='" . es(T_('Previous Month')) . "' HREF='{$cal_url}date=$last_month_day'> &nbsp;&nbsp;<IMG SRC='images/arrow_8_{$align_start}.gif' ALT='$prev_Arr'></A> <SPAN DIR='rtl'>$month $year</SPAN> <A TITLE='" . es(T_('Next Month')) . "' HREF='{$cal_url}date=$next_month_day'><IMG SRC='images/arrow_8_{$align_end}.gif' ALT='$next_Arr'> &nbsp;&nbsp;</A> <A TITLE='" . es(T_('Next Year')) . "' HREF='{$cal_url}date=$next_year'><IMG SRC='images/d-arrow_8_{$align_end}.png' ALT='$next_Arr'> </A></CAPTION>\n";
?>
<TR><TH>א</TH><TH>ב</TH><TH>ג</TH><TH>ד</TH><TH>ה</TH><TH>ו</TH><TH>ש</TH></TR>
<TR>
<?
$w = 0;

for($i=0; $i<jddayofweek($today); $i++) {
  echo "<TD></TD>";
  $w++;
}

for($i=0; $i<cal_days_in_month2(CAL_JEWISH, $today_cal['month'], $today_cal['year']); $i++) {
  if($w >= 7) {
    echo "</TR>\n<TR>";
    $w = 0;
  }
  $w++;
  //list($day, $month, $year) = mb_split(' ',  mb_convert_encoding(jdtojewish($today + $i,true), 'UTF-8', 'ISO-8859-8'));
  list($day, $month, $year) = explode(' ',  mb_convert_encoding(jdtojewish($today + $i,true), 'UTF-8', 'ISO-8859-8'));
  echo "<TD " . (in_array($today+$i, $dates) ? 'class="enabled_day"' : ($today+$i == $date ? 'class="picked_day"' : ($today+$i == unixtojd() ? 'class="today"' : ''))) . "><A HREF='{$cal_url}date=" . ($today+$i) . "' onClick='$cal_onclick'>$day</A></TD>";
}
?>
</TR>
</TABLE>
</DIV>
