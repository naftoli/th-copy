<? $dual_auth = true; ?>
<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
require_once('file_save.php');

if(!empty($admin_user)) {
  assure_id_school('school_id');
  $school_id = gri('school_id', -1);
  $class_id = gri('class_id', -2);
  $user_id = gri('user_id', -2);
} else {
  $school_id = $user['school_id'];
  $class_id = $user['class_id'];
  $user_id = $user['user_id'];
}
$date = gri('date', unixtojd());

$mission = gr('mission');
if($mission !== '' && $mission != 'current' && $mission != 'next') {
  @list($subject_id, $mission_number) = split('/', gr('mission', '-1/-1'));
  $subject_id = intval($subject_id);
  $mission_number = floatval($mission_number);
} else {
  $subject_id = -1;
  $mission_number = -1;
}

$show_printed = gri('show_printed', 0);

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id >= 0 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, last, first, username");

$mission_result = mq('SELECT subject_id, subject_name, inst_name, mission_name, mission_number, MIN(start_date) start_date, MAX(end_date) end_date FROM subjects JOIN institutions USING (inst_id) JOIN school_type_subjects USING (subject_id) JOIN date_tasks_missions USING (school_type_id, subject_id) WHERE'  . (empty($admin_user) ? " school_type_id = {$user['school_type_id']} AND" : ($admin_user['auth'] != 'super' ? ' inst_id IN (' . implode(',', $admin_user['inst_ids']) . ') AND' : '')) . " subject_type != 'school_points' AND mission_number IS NOT NULL GROUP BY inst_name, subject_name, subject_id, mission_number, mission_name HAVING start_date <= $date AND end_date >= $date ORDER BY inst_name, subject_name, subject_id, mission_number, mission_name");

function isWhen($row) {
  global $date;
  // -1 = ended
  // 0 = now
  // 1 = future
  if($row['end_date'] < $date) {
    return -1;
  } elseif($row['start_date'] > $date) {
    return 1;
  } else {
    return 0;
  }
}

if($mission === '' || $class_id == -2 || $user_id == -2 || gr('mark_printed')):
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Soldier Report'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles_wwtc.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
</HEAD>
<BODY>
<DIV class="header">
<DIV class="noprint">

<?if(!empty($admin_user) && ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1)):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_report_hakhel.php" method="get" accept-charset="UTF-8">
<P>
<?if($admin_user['auth'] == 'super'):?>
<LABEL><?=T_('Effective Date')?>: <INPUT type="text" name="date_disp" READONLY value="<?=es(dateToHebrew($date))?>" onClick="getDate(this.form, 'date', true);"></LABEL><INPUT type="hidden" name="date" value="<?=$date?>"><BR>
<?else:?>
<INPUT type="hidden" name="date" value="<?=$date?>">
<?endif;?>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>"><BR>
</P>
</FORM>

<HR>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
</DIV>
</DIV>
</BODY>
</HTML>
<?else:?>
<FORM action="admin_report_hakhel.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="date" value="<?=$date?>">
<?if(gr('mark_printed')):?>
<INPUT class="submit" type="submit" value="<?=T_('Start Over')?>">
<P><?=T_('Marked the following reports as printed:')?></P>
</FORM>
</DIV>
</DIV>
<P>
<?else:?>
<?if(!empty($admin_user)):?>
<LABEL><?=T_('Show Platoon')?> <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($class_row = mysql_fetch_assoc($class_result)): ?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<LABEL><?=T_('Show Soldier')?> <SELECT name="user_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($user_row = mysql_fetch_assoc($user_result)): ?>
<OPTION value="<?=$user_row['user_id']?>" <?=$user_row['user_id'] == $user_id ? 'SELECTED' : ''?>><?=$class_id < 0 && $user_row['class_grade'] != '' ? es($user_row['class_grade'] . '-' . $user_row['class_sub']) . ': ' : ''?><?=es($user_row['last'])?>, <?=es($user_row['first'])?> (<?=es($user_row['username'])?>)</OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<?endif;?>
<LABEL><?=T_('Which Mission')?> <SELECT name="mission">
  <OPTION value="current" <?=$mission == 'current' ? 'selected' : ''?>>&lt;Current mission for each soldier&gt;
  <OPTION value="next" <?=$mission == 'next' ? 'selected' : ''?>>&lt;Next mission for each soldier&gt;
  <? while($row = mysql_fetch_assoc($mission_result)): ?>
    <OPTION VALUE="<?=$row['subject_id']?>/<?=$row['mission_number']?>" <?=$subject_id == $row['subject_id'] && $mission_number == $row['mission_number'] ? 'SELECTED' : '' ?>><?=!empty($admin_user) && $admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name']), ', ', es($row['mission_name']), ' #', $row['mission_number']?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL><BR>
<?if(!empty($admin_user)):?>
<LABEL><?=T_('Include reports that were already printed')?>: <INPUT type="checkbox" name="show_printed" value="1" <?=$show_printed ? 'checked' : ''?>></LABEL><BR>
<?endif;?>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>

<HR>
<H2><?=T_('Hakhel Missions Printing Instructions')?>:</H2>
<P>
<?=T_('<B>Current Mission Report:</B> Will print a current mission report for each soldier. (eg. for one soldier it will print mission report #1 and for another soldier it will print mission report #2)')?>
</P>
<P>
<?=T_('<B>Next Mission Report:</B> Will print the next mission report for each soldier. (This report will be helpful during school vacation - when soldiers will not be in school when they finish their current mission.)')?>
</P>
<P>
<?=T_('<B>IMPORTANT NOTE:</B> in order for this to work, after you print a report you MUST mark it as printed.')?>
</P>
<?if(!empty($admin_user)):?>
<HR>
<P>
After printing click to
<INPUT type="submit" name="mark_printed" value="mark these reports as printed" style="background-color: red; font-weight: bold; color: white;"> (so that by default they will no longer print next time you generate a report).
</P>
<P>
<?=T_("<B>VERY IMPORTANT:</B> after printing the PDF press back on the browser, and then press the 'mark these reports as printed' button and do <EM>NOT</EM> change any of the settings (Which Mission, Show Platoon, Include reports.., etc) above.")?>
</P>
<P>
<?=T_("If you change the settings before pressing the mark button it will mark a <EM>different</EM> list of reports than what you printed!")?>
</P>
<P>
<?=T_("You don't have to mark the reports as printed if you don't want to, but if you do, make sure not to change the settings.")?>
</P>
<?endif;?>
</FORM>
</DIV>
</DIV>
</BODY>
</HTML>
<?endif;?>
<?endif;?>
<?endif;?>
<?if($mission !== '' && $class_id != -2 && $user_id != -2): ?>
<?
$localhost = 'http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$today_split = dateToHebrewSplit($date);

$input = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>

<report name="hakhel_report">
<images>
  <background url="/home/mashpia/jreport/images/hakhel/background.png" />
</images>

<reportcards>
EOT;

$users = mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, dob, dob_he_offset, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, inst_logo_id, school_type_id,
       rank_ord, rank_name, rank_image_id, rank_color,
       date_tasks_mission_id, date_tasks_missions.mission_name, mission_number, mission_description, start_date, end_date, subject_id, IF(date_tasks_missions_printed.date_tasks_mission_id IS NULL, 0, 1) printed
FROM users
     JOIN subjects
     JOIN school_type_subjects USING (subject_id, school_type_id)
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
     LEFT JOIN schools USING (school_id, inst_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
     LEFT JOIN date_tasks_mission_marks USING (user_id, subject_id, date_tasks_mission_id)
     LEFT JOIN date_tasks_missions_printed USING (user_id, date_tasks_mission_id)
WHERE school_id = $school_id" .
      ($class_id != -1 ? " AND class_id = $class_id" : '') .
      ($user_id != -1 ? " AND user_id = $user_id" : '') .
      ($subject_id != -1 && $mission_number != -1 ? " AND subject_id = $subject_id AND mission_number = $mission_number" : ' AND date_tasks_mission_marks.date_tasks_mission_id IS NULL') .
"     AND subject_type = 'Hakhel' AND
      start_date <= $date AND end_date >= $date
 ORDER BY class_grade, class_sub, last, first, user_id, mission_number, start_date, end_date, date_tasks_mission_id"
);

$base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL")), 0);
$base_count = mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = $school_id AND user_start_date IS NOT NULL"), 0);
$all_points = mysql_result(mq(totalMarks()), 0);
$all_count = mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_start_date IS NOT NULL"), 0);

$base_tasks_count = mysql_result(mq("SELECT COUNT(*) FROM date_tasks_marks JOIN users USING (user_id) JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN subjects USING (subject_id) WHERE subject_type = 'Hakhel' AND school_id = $school_id"), 0);
$base_missions_count = mysql_result(mq("SELECT COUNT(*) FROM date_tasks_mission_marks JOIN users USING (user_id) JOIN subjects USING (subject_id) WHERE subject_type = 'Hakhel' AND school_id = $school_id"), 0);
$bases = mysql_result(mq("SELECT COUNT(*) schools FROM schools"), 0);
$all_tasks_count = mysql_result(mq("SELECT COUNT(*) FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN subjects USING (subject_id) WHERE subject_type = 'Hakhel'"), 0);
$all_missions_count = mysql_result(mq("SELECT COUNT(*) FROM date_tasks_mission_marks JOIN subjects USING (subject_id) WHERE subject_type = 'Hakhel'"), 0);

$old_user_id = -1;

while($user_row = mysql_fetch_assoc($users)):

if($subject_id == -1 && $mission_number == -1) {
  if($user_row['user_id'] != $old_user_id) {
    $user_mission_ord = 0;
    $old_user_id = $user_row['user_id'];
  }
  $user_mission_ord++;

  if($mission == 'current' && $user_mission_ord != 1 || $mission != 'current' && $user_mission_ord != 2) continue;

  if(!$show_printed && $user_row['printed']) continue;
}

if(!empty($admin_user) && gr('mark_printed')) {
  mq("INSERT IGNORE INTO date_tasks_missions_printed (user_id, date_tasks_mission_id) VALUES ({$user_row['user_id']}, {$user_row['date_tasks_mission_id']})");
  echo es($user_row['mission_name']), ' #', $user_row['mission_number'], ' ', $user_row['class_grade'], ($user_row['class_grade']!=='' && $user_row['class_sub']!=='' ? '-' : ''), $user_row['class_sub'], ': ', es(firstInitial($user_row['first_he'] ? $user_row['first_he'] : $user_row['first'])). ' '. es($user_row['last_he'] ? $user_row['last_he'] : $user_row['last']). "<BR>\n";
  continue;
}

$user_miles = mysql_result(mq(totalMarks("WHERE user_id = {$user_row['user_id']}")), 0);
$auction_points = auctionPoints($user_row['user_id']);

$mission_tasks_result = mq("SELECT date_task_id, name, description, mandatory_qty, optional_qty, quantity, points, label_id, label_name, label_description, label_image_id FROM date_tasks LEFT JOIN labels USING (label_id) WHERE date_tasks_mission_id = {$user_row['date_tasks_mission_id']} ORDER BY ord, name, date_task_id");

$missions_completed = mysql_result(mq("SELECT IFNULL(SUM(mission_value), 0) missions_completed FROM date_tasks_mission_marks WHERE user_id = {$user_row['user_id']} AND subject_id = {$user_row['subject_id']}"), 0);

$medals_result = mq("SELECT medal_ord, medal_name, medals_subjects.medal_on_image_id, medals_subjects.medal_off_image_id, IFNULL(missions_required, 0) missions_required FROM medals JOIN medals_subjects USING (medal_ord) WHERE subject_id = {$user_row['subject_id']} ORDER BY medal_ord");

if(!is_null($user_row['class_id'])) {
  $class_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL")), 0);
  $class_count = mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = $school_id AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL"), 0);
}

$medals = array();
$active = 1;

while($row = mysql_fetch_assoc($medals_result)) {
  if($active > 0) {
    if($missions_completed >= $row['missions_required']) {
      $missions_completed -= $row['missions_required'];
    } else {
      $progress = array('name'=>$row['medal_name'], 'complete'=>$missions_completed, 'required'=>$row['missions_required']);
      $active = 0;
    }
  }
  // active == 1 - medal complete, active == 0 - medal in progress; active == -1 - medal not complete
  $medals[] = array('ord' => $row['medal_ord'], 'name' => $row['medal_name'], 'active' => $active, 'image' => $active == 0 ? $row['medal_on_image_id'] : $row['medal_off_image_id']);
  if($active == 0) $active = -1;
}

$input .= '
<reportcard url="">
  <soldier' . (!is_null($user_row['rank_image_id']) ? ' url="' . $localhost . '/file_view.php?id=' . $user_row['rank_image_id'] . '"' : '') . ' name="' . es(firstInitial($user_row['first_he'] ? $user_row['first_he'] : $user_row['first'])) . ' ' . es($user_row['last_he'] ? $user_row['last_he'] : $user_row['last']) /* . ' ' . $user_row['username'] */ . '" gender="'. ($user_row['gender'] == 'F' ? 'f' : 'm') . '" chineseauctionmiles="' . number_format($auction_points['cur'], 0) . '" chineseauctionmessage="' . (isset($auction_points['prev']) ? sprintf(T_('%s miles needed to activate %s past miles for upcoming auction.'), number_format($auction_points['left']), number_format($auction_points['prev'])) : '') . '">
  <profile><![CDATA[' . T_('Grade') . ': ' . $user_row['class_grade'] . ($user_row['class_grade']!=='' && $user_row['class_sub']!=='' ? '-' : '') . es($user_row['class_sub']) . '      ' . T_('Age') . ': ' . calcAge(dateToJD($user_row['dob'])+$user_row['dob_he_offset']) . '
' . T_('Teacher') . ': ' . es($user_row['class_teacher']) . '
' . T_('Serial') . ' #: ' . $user_row['user_serial'] . '
' . T_('Total Miles') . ': ' . number_format($user_miles, 0) . '
' . T_('Rank') . ': ' . es($user_row['rank_name']) . '
' . T_('Platoon Average') . ': ' . (is_null($user_row['class_id']) ? T_('N/A') : @number_format($class_points / $class_count, 2)) . '
]]></profile>
  </soldier>
  <base' . (!is_null($user_row['school_logo_id']) ? ' url="' . $localhost . '/file_view.php?id=' . $user_row['school_logo_id'] . '"' : '') .'>
  <profile><![CDATA[' . T_('TH Base') . '#: ' . $user_row['school_number'] . '
' . T_('Base Name') . ': ' . es($user_row['school_name']) . '
' . T_('Officer') . ': ' . '' . '
' . T_('Base Mileage') . ': ' . @number_format($base_points, 2) . '
' . T_('Base Average') . ': ' . @number_format($base_points/$base_count, 2) . '
' . T_('Army Average') . ': ' . @number_format($all_points/$all_count, 2) . ']]></profile>
  </base>
  <mission number="' . $user_row['mission_number'] . '" name="' . es($user_row['mission_name']) . '" startdate="' . es(dateToHebrewCommaYear($date)) . '" enddate="' . es(dateToHebrewCommaYear($user_row['end_date'])) . '" title="' . T_('Hakhel mission report') . ' ' . es($user_row['mission_name']) . ' # ' . es($user_row['mission_number']) . '">
    <directives>
      <col1><![CDATA[' . mysql_result(mq("SELECT message_text FROM messages WHERE message_type = 'hakhel_directives_1'"), 0) . ']]></col1>
      <col2><![CDATA[' . mysql_result(mq("SELECT message_text FROM messages WHERE message_type = 'hakhel_directives_2'"), 0) . ']]></col2>
    </directives>
    <tasks colheader="__' . $today_split[1] . '">
';
$i = 0;
while($task_row = mysql_fetch_assoc($mission_tasks_result)) {
  $i++;
  $input .= '<task' . $i . ' miles="' . floatval($task_row['points']) . '"' . (!is_null($task_row['label_image_id']) ? ' url="' . $localhost . '/file_view.php?id=' . $task_row['label_image_id'] . '"' : '') . '>' . es($task_row['name']) . '</task' . $i . ">\n";
}
$input .=
'    </tasks>
    <mivtzabox logourl="/home/mashpia/jreport/images/hakhel/';

switch($user_row['mission_number'][0]) {
  case '1':
  case '2':
  case '8':
    $input .= 'TORAH-icon.png';
    break;

  case '3':
    $input .= 'avodah.png';
    break;

  case '4':
    $input .= 'hiskashrus.png';
    break;

  case '5':
    $input .= 'moshiach.png';
    break;

  case '7':
    $input .= 'handshake.png';
    break;
}

$input .= '"><![CDATA[';

switch($user_row['mission_number']) {
  case '1.1':
    $input .= '"א חסיד דורש טובת הזולת"';
    break;

  case '1.2':
    $input .= '"א חסיד שורפן"';
    break;

  case '1.3':
    $input .= '"א חסיד איז א לאַמטערנטשיק"';
    break;

  case '1.4':
    $input .= '"חסידים ברעכן דורך די חושך הגלות"';
    break;

  case '2.1':
  case '2.2':
  case '2.3':
  case '2.4':
    $input .= '<b>ה\' טבת</b>
מבצע בית מלא ספרים
מבצע תורה

<b>כ\' טבת</b>
לימוד ספר המצוות

<b>כ"ד טבת</b>
לימוד תניא בעל פה

<b>ג\' שבט</b>
הפצת המעינות';
    break;

  case '3.1':
  case '3.2':
  case '3.3':
  case '3.4':
    $input .= '<style pdfFontName="Verdana Bold">Week 1.</style> Going to sleep and waking up like a Chossid

<style pdfFontName="Verdana Bold">Week 2.</style> Preparing for Davening

<style pdfFontName="Verdana Bold">Week 3.</style> Davening like a Chossid

<style pdfFontName="Verdana Bold">Week 4.</style> Davening like a Chossid';
    break;

  case '4.1':
  case '4.2':
  case '4.3':
  case '4.4':
    $input .= 'מבצע התקשרות


* לימוד תורתו
* קיום הוראתיו
* ציור פני רבו
* פסוקים של הרבי
* ניגונים של הרבי
* קאפיטל של הרבי
* הרחמן הוא יברך את אדוננו..
* תניא בעל פה ברחוב
';
    break;

  case '7.1':
  case '7.2':
  case '7.3':
  case '7.4':
    $input .= 'נהגו כבוד זה לזה

Week 1 כיבוד חברים

Week 2 כיבוד הורים

Week 3 כיבוד מורים

Week 4 כיבוד חברים הורים ומורים
';
    break;
}

$input .=
    ']]></mivtzabox>
  </mission>
  <stats>
    <col1><![CDATA[<b>' . T_('Base Participation') . '</b>
' . T_('Soldiers') . ': ' . number_format($base_count, 0) . '
' . T_('Tasks Completed') . ': ' . number_format($base_tasks_count, 0) . '
' . T_('Missions Completed') . ': ' . number_format($base_missions_count, 0) . '
]]></col1>
    <col2><![CDATA[<b>' . T_('Army Participation') . '</b>
' . T_('Bases') . ': ' . number_format($bases, 0) . '
' . T_('Soldiers') . ': ' . number_format($all_count, 0) . '
' . T_('Tasks Completed') . ': ' . number_format($all_tasks_count, 0) . '
' . T_('Missions Completed') . ': ' . number_format($all_missions_count, 0) . '
]]></col2>
  </stats>
  <medalbox medal_progress="' . $progress['complete'] . '" medals_required="' . $progress['required'] . '" medal_progress_message="' . T_('Missions to') . ' ' . $progress['name'] . ' ' . T_('medal') . ':">
';

foreach($medals as $medal) {
  $input .= '<medal' . $medal['ord'] . (!is_null($medal['image']) ? ' url="' . $localhost . '/file_view.php?id=' . $medal['image'] . '"' : '') . ' text="' . es($medal['name']) . '" borderbox="' . ($medal['active'] == 0 ? 'true' : 'false') .'" />' . "\n";
}

$input .= '  </medalbox>
</reportcard>
';

endwhile;

$input .= '</reportcards>
</report>
';

if(!gr('mark_printed')) {
  sendReport($input, "hakhel_report_" . date('YmdGis') . '.pdf');
} else {
  echo "</P>\n</BODY>\n</HTML>\n";
}
endif;
?>
