<?
require_once('streams.php');
require_once('gettext.php');

//defaults
if(empty($lang)) $lang = 'en';
$dir = "LTR";
$prev_Arr = "&lArr;";
$next_Arr = "&rArr;";
$prev_arr = "&larr;";
$next_arr = "&rarr;";
$align_start = "left";
$align_end = "right";

$x2196 = '&#x2196;'; //thin angle up-left
$x2197 = '&#x2197;'; //thin angle up-right
$x2198 = '&#x2198;'; //thin angle down-left
$x2199 = '&#x2199;'; //thin angle down-right

$x21d6 = '&#x21d6;'; //double angle up-left
$x21d7 = '&#x21d7;'; //double angle up-right
$x21d8 = '&#x21d8;'; //double angle down-left
$x21d9 = '&#x21d9;'; //double angle down-right
	@include("langs/$lang.php");
$gettext = new gettext_reader(new FileReader("langs/$lang.mo"));

$langs = array('en' => 'English', 'he' => 'עברית', 'yi' => 'יידיש');

if(!function_exists('T_')) {
	function T_($str) {

	//  include_once('upsidedown.php');
	//  return upsidedown($str);

	  global $gettext;
	  return $gettext->translate($str);
	}
}

if(!function_exists('TDv_')) {
function TDv_($str) {
  global $lang;
  if($lang == 'en') return $str;
  $row = mysql_fetch_assoc(mq('SELECT text_transl FROM translations_varchar WHERE lang = ' . ms($lang) . ' AND text = ' . ms($str)));
  return $row ? $row['text_transl'] : $str;
}
}

if(!function_exists('TDt_')) {
function TDt_($str) {
  global $lang;
  if($lang == 'en') return $str;
  $row = mysql_fetch_assoc(mq('SELECT text_transl FROM translations_text WHERE lang = ' . ms($lang) . ' AND text = ' . ms($str)));
  return $row ? $row['text_transl'] : $str;
}
}

$weekdays = array(T_('Sunday'), T_('Monday'), T_('Tuesday'), T_('Wednesday'), T_('Thursday'), T_('Friday'), T_('Shabbos'));
$weekdays_short = array(T_('Sun'), T_('Mon'), T_('Tue'), T_('Wed'), T_('Thu'), T_('Fri'), T_('Shab'));
?>
