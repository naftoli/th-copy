<?
function isRepeatToday($type, $today, $start, $every, $param1, $param2) {
  switch($type) {

    case 'daily':
      return (($today - $start) % $every) == 0;
      break;

    case 'weekly':
      return ((1 << JDDayOfWeek($today, 0)) & $param1) &&
             ((($today - $start) % ($every*7)) < 7);
      break;

    case 'monthly_date':
      $cal_today = cal_from_jd($today, CAL_JEWISH);

      //deal with months that have 29 days
      $adjust = 0;
      if($param1 == 30 && cal_days_in_month2(CAL_JEWISH, $cal_today['month'], $cal_today['year']) == 29) {
       $param1 = 29;
       $cal_start = cal_from_jd($start, CAL_JEWISH);
       if($cal_start['day'] == 30) $adjust = 1; //because start date is on the 30th, which is after $param1 of 29, it _appears_ that we missed the first event (on the 29th, since we only started on the 30th) so add 1
      }

      return $cal_today['day'] == $param1 && ((monthDiffJewish($start, $today) + $adjust) % $every == 0);
      break;

    case 'yearly':
      $cal_start = cal_from_jd($start, CAL_JEWISH);
      $cal_today = cal_from_jd($today, CAL_JEWISH);

      return (
         ($today == JewishToJD($param2, $param1, $cal_today['year']) || $today == JewishToJD($param2, $param1, $cal_today['year']-1))
         &&
         (($cal_today['year'] - $cal_start['year']
           - ($start > JewishToJD($param2, $param1, $cal_start['year']) ? 1 : 0) //adjust for event date before start date
           - ($param1 == 30 && $cal_today['day'] == 1 ? 1 : 0) //adjust year difference if we jump over to the next year because of chaser months
           ) % $every == 0)
        );

      /*
      //alternate code to do the same thing, but needs to take into account rules for Adar I and Adar II:
      if ($param1 == 30 && $cal_today['day'] == 1) {
        $cal_yesterday = cal_from_jd($today-1, CAL_JEWISH);
      } else {
        $cal_yesterday = NULL;
      }

      return (
        (($cal_today['month'] == $param2 && $cal_today['day'] == $param1) ||
        ($cal_yesterday && $cal_yesterday['month'] == $param2 && $cal_yesterday['day'] == 29))
       &&
        (( $cal_today['year'] - $cal_start['year']
           - ($cal_start['month'] > $param2 || ($cal_start['month'] == $param2 && $cal_start['day'] > $param1) ? 1 : 0) //adjust for event date before start date
           - ($cal_yesterday ? $cal_today['year'] - $cal_yesterday['year'] : 0) //adjust year difference if we jump over to the next year because of chaser months adjustment
        ) % $every == 0)
       );
      */
      break;
    default:
      user_error("Unknown repeat $type", E_USER_ERROR);
      break;
  }
}

//from is (usually) earlier then to, and both are Julian dates
function monthDiffJewish($from, $to) {
      $cal_from = cal_from_jd($from, CAL_JEWISH);
      $cal_to = cal_from_jd($to, CAL_JEWISH);
      return round((
        cal_to_jd(CAL_JEWISH, $cal_to['month'], $cal_from['day'], $cal_to['year'])
        - $from) //make day of month of to match from
       / (765433/25920)) //average length of month, from Metonic cycle
        - ($cal_to['day'] < $cal_from['day'] ? 1 : 0); //adjust if day of month in to is earlier then from
}

function dateToHebrew($date) {
  if(is_null($date)) {
    return '';
  } else {
    $str = mb_convert_encoding(jdtojewish($date, true, CAL_JEWISH_ADD_GERESHAYIM), 'UTF-8', 'ISO-8859-8');
    return $str;
  }
}

function dateToHebrewNoGr($date) {
  if(is_null($date))
    return '';
  else
    return mb_convert_encoding(jdtojewish($date, true, CAL_JEWISH), 'UTF-8', 'ISO-8859-8');
}

function dateToHebrewCommaYear($date) {
  if(is_null($date)) return '';
  @list($day, $month, $year, $year2) = mb_split(' ', dateToHebrew($date));
  if(!empty($year2)) {
    $month = "$month $year";
  }
  return "$day $month, $year";
}

function dateToHebrewNoYear($date) {
  if(is_null($date)) return '';
  @list($day, $month, $year, $year2) = mb_split(' ', dateToHebrew($date));
  /*
  if(!empty($year2)) {
    $month = "$month $year";
  }
  return "$day $month";
  */
  if(!empty($year2)) {
     $month = "$month $year";
     $year = $year2;
   }
   return "$day $month, $year";
}
/*
function dateToHebrewShortYear($date) {
  if(is_null($date)) return '';
  @list($day, $month, $year, $year2) = mb_split(' ', dateToHebrew($date));
  /*
  if(!empty($year2)) {
    $month = "$month $year";
  }
  $year = mb_substr($year, 3);
  return "$day $month $year";
  */
  /*
  if(!empty($year2)) {
     $month = "$month $year";
     $year = $year2;
   }
   $year = mb_substr($year, 3);
   return "$day $month $year";
}
*/
function dateToHebrewShortYear($julian) {
		$str = jdtojewish($julian, true, CAL_JEWISH_ADD_GERESHAYIM);
		$str1 = iconv ('WINDOWS-1255', 'UTF-8', $str); // convert to utf-8
		return $str1;
	}

function dateToHebrewSplit($date) {
  if(is_null($date)) return '';
  @list($day, $month, $year, $year2) = mb_split(' ', dateToHebrew($date));
  /*
  if(!empty($year2)) {
    $month = "$month $year";
  }
  return array($day, $month, $year);
  */
  if(!empty($year2)) {
     $month = "$month $year";
     $year = $year2;
   }
   return array($day, $month, $year);
}

function dateToHebrewSplitNoGr($date) {
  if(is_null($date)) return '';
  @list($day, $month, $year, $year2) = mb_split(' ', dateToHebrewNoGr($date));
  if(!empty($year2)) {
    $month = "$month $year";
  }
  $year = mb_substr($year, 3);
  return array($day, $month, $year);
}

function dayToHebrew($val) {
  @list($day, $month, $year, $year2) = mb_split(' ', dateToHebrew(cal_to_jd(CAL_JEWISH, 1, $val, 5768))); //this month has 30 days
  return $day;
}

function monthToHebrew($val) {
  @list($day, $month, $year, $year2) = mb_split(' ', dateToHebrew(cal_to_jd(CAL_JEWISH, $val, 1, 5768))); //this year has Adar II
  if(!empty($year2)) {
    $month = "$month $year";
  }
  return $month;
}

function yearToHebrew($val) {
  @list($day, $month, $year, $year2) = mb_split(' ', dateToHebrew(cal_to_jd(CAL_JEWISH, 1, 1, $val)));
  if(!empty($year2)) {
    $year = $year2;
  }
  return $year;
}

function selectDay($today) {
  for($i = 1; $i <= 30; $i++) {
    echo "<OPTION value='$i'" . ($i == $today ? ' SELECTED' : '') . ">" . es(dayToHebrew($i)) . "</OPTION>\n";
  }
}

function selectMonth($today) {
  for($i = 1; $i <= 13; $i++) {
    echo "<OPTION value='$i'" . ($i == $today ? ' SELECTED' : '') . ">" . es(monthToHebrew($i)) . "</OPTION>\n";
  }
}

function selectYear($today) {
  for($i = 5767; $i <= 5767+120; $i++) { //will this program still be around in 120 years?
    echo "<OPTION value='$i'" . ($i == $today ? ' SELECTED' : '') . ">" . es(yearToHebrew($i)) . "</OPTION>\n";
  }
}

//covert yyyy-mm-dd to julian date
function dateToJD($date) {
  @list($year, $month, $day) = split('-', $date);
  $jd = cal_to_jd(CAL_GREGORIAN, intval($month), intval($day), intval($year));
  return $jd ? $jd : NULL;
}

function calcAge($date) {
  if(!$date) return '';
  $today = cal_from_jd(unixtojd(), CAL_JEWISH);
  $date = cal_from_jd($date, CAL_JEWISH);
  $age = $today['year'] - $date['year'];
  if($date['month'] > $today['month'] || ($date['month'] == $today['month'] && $date['day'] > $today['day'])) $age--;
  return $age;
}

function displayRepeat($rep_type, $every, $rep_param1, $rep_param2) {
  $ret = $every . ' ';
  switch($rep_type) {
    case 'daily':
      $ret .= T_('day(s)');
      break;
    case 'weekly':
      $ret .= T_('week(s) on');
      $weekdays = array();
      if($rep_param1 & 1<<0) $weekdays[] = T_('Sunday');
      if($rep_param1 & 1<<1) $weekdays[] = T_('Monday');
      if($rep_param1 & 1<<2) $weekdays[] = T_('Tuesday');
      if($rep_param1 & 1<<3) $weekdays[] = T_('Wednesday');
      if($rep_param1 & 1<<4) $weekdays[] = T_('Thursday');
      if($rep_param1 & 1<<5) $weekdays[] = T_('Friday');
      if($rep_param1 & 1<<6) $weekdays[] = T_('Shabbos');
      $ret .= ' ' . implode(', ', $weekdays);
      break;
    case 'monthly_date':
      $ret .= T_('month(s) on') . ' ' . dayToHebrew($rep_param1);
      break;
    case 'monthly_week':

      break;
    case 'yearly':
      $ret .= T_('years on') . ' ' . dayToHebrew($rep_param1) . ' ' . monthToHebrew($rep_param2);
      break;
    default:
      user_error("Unknown repeat $rep_type", E_USER_ERROR);
      break;
  }
  return $ret;
}
?>
