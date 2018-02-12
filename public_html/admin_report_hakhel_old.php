<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
require_once('file_save.php');

assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -2);
$user_id = gri('user_id', -2);
$date = gri('date', unixtojd());

$mission = gr('mission', 'current');
if($mission != 'current' && $mission != 'next') {
  @list($subject_id, $mission_number) = split('/', gr('mission', '-1/-1'));
  $subject_id = intval($subject_id);
  $mission_number = intval($mission_number);
} else {
  $subject_id = -1;
  $mission_number = -1;
}

$show_printed = gri('show_printed', 0);

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id >= 0 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, last, first, username");

$mission_result = mq('SELECT DISTINCT subject_id, subject_name, inst_name, mission_name, mission_number FROM subjects JOIN institutions USING (inst_id) JOIN school_type_subjects USING (subject_id) JOIN date_tasks_missions USING (school_type_id, subject_id) WHERE'  . ($admin_user['auth'] != 'super' ? ' inst_id IN (' . implode(',', $admin_user['inst_ids']) . ') AND' : '') . ' subject_type != \'school_points\' AND mission_number IS NOT NULL ORDER BY inst_name, subject_name, mission_number, mission_name');

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

<H2>Printing Instructions</H2>
<P>
File<?=$next_arr?>Page Set up<BR>
<BR>
Landscape<BR>
Scale 80<BR>
<BR>
Margins: Top, Left, Right, Bottom (all): 0.0<BR>
<BR>
All headers and footers: Blank<BR>
</P>
<HR>

<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_report_hakhel.php" method="get" accept-charset="UTF-8">
<P>
<?if($admin_user['auth'] == 'super'):?>
<LABEL><?=T_('Effective Date')?>:<INPUT type="text" name="date_disp" READONLY value="<?=es(dateToHebrew($date))?>" onClick="getDate(this.form, 'date');"></LABEL><INPUT type="hidden" name="date" value="<?=$date?>"><BR>
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
<?else:?>
<FORM action="admin_report_hakhel.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="date" value="<?=$date?>">

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
<LABEL><?=T_('Which Mission')?> <SELECT name="mission">
  <OPTION value="current" <?=$mission == 'current' ? 'selected' : ''?>>&lt;Current mission for each soldier&gt;
  <OPTION value="next" <?=$mission == 'next' ? 'selected' : ''?>>&lt;Next mission for each soldier&gt;
  <? while($row = mysql_fetch_assoc($mission_result)): ?>
    <OPTION VALUE="<?=$row['subject_id']?>/<?=$row['mission_number']?>" <?=$subject_id == $row['subject_id'] && $mission_number == $row['mission_number'] ? 'SELECTED' : '' ?>><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name']), ', ', es($row['mission_name']), ' #', $row['mission_number']?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL><BR>
<LABEL><?=T_('Include reports that were already printed')?>: <INPUT type="checkbox" name="show_printed" value="1" <?=$show_printed ? 'checked' : ''?>></LABEL><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<H2><?=T_('Hakhel Missions Printing Instructions')?>:</H2>
<P>
<?=T_('<B>Current Mission Report:</B> Will print a current mission report for each soldier. (eg. for one soldier it will print mission report #1 and for another soldier it will print mission report #2)')?>
</P>
<P>
<?=T_('<B>Next Mission Report:</B> Will print the next mission report for each soldier. (This report will be helpful during school vacation - when soldiers will not be in school when they finish their current mission.)')?>
</P>
<P>
<B><?=T_('IMPORTANT NOTE:</B> in order for this to work, after you print a report you MUST mark it as printed.')?>
</P>
<HR>
<?if(gr('mark_printed')):?>
<?=T_('Marked the reports as printed.')?>
<?else:?>
<FORM method="post" action="admin_report_hakhel.php">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="date" value="<?=$date?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<INPUT type="hidden" name="mission" value="<?=es($mission)?>">
<INPUT type="hidden" name="show_printed" value="<?=$show_printed?>">
After printing click to 
<INPUT type="submit" name="mark_printed" value="mark these reports as printed" style="background-color: red; font-weight: bold; color: white;"> (so that by default they will no longer print next time you generate a report).
</P>
</FORM>
<?endif;?>
<?endif;?>
</DIV>
</DIV>
<?
$users = mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, inst_logo_id, school_type_id,
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
     LEFT JOIN date_tasks_mission_marks USING (user_id, subject_id, date_tasks_mission_id)
     LEFT JOIN date_tasks_missions_printed USING (user_id, date_tasks_mission_id)
WHERE school_id = $school_id" .
      ($class_id != -1 ? " AND class_id = $class_id" : '') .
      ($user_id != -1 ? " AND user_id = $user_id" : '') .
      ($subject_id != -1 && $mission_number != -1 ? " AND subject_id = $subject_id && mission_number = $mission_number" : ' AND date_tasks_mission_marks.date_tasks_mission_id IS NULL') .
"     AND subject_type = 'Hakhel' AND
      start_date <= $date AND end_date >= $date
 ORDER BY class_grade, class_sub, last, first, user_id, mission_number, start_date, end_date, date_tasks_mission_id"
);

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

if(gr('mark_printed')) {
  mq("INSERT IGNORE INTO date_tasks_missions_printed (user_id, date_tasks_mission_id) VALUES ({$user_row['user_id']}, {$user_row['date_tasks_mission_id']})");
  continue;
}

$mission_tasks_result = mq("SELECT date_task_id, name, description, mandatory_qty, optional_qty, quantity, points, label_id, label_name, label_description, label_image_id FROM date_tasks LEFT JOIN labels USING (label_id) WHERE date_tasks_mission_id = {$user_row['date_tasks_mission_id']} ORDER BY ord, name, date_task_id");

$missions_completed = mysql_fetch_column(mq("SELECT subjects.subject_id, IFNULL(SUM(mission_value), 0) missions_completed FROM subjects LEFT JOIN date_tasks_mission_marks ON (subjects.subject_id = date_tasks_mission_marks.subject_id AND user_id = {$user_row['user_id']}) WHERE subject_type = 'Hakhel' GROUP BY subjects.subject_id"), 0);
?>
<DIV class="page">
<HR class="noprint">
<DIV dir="rtl" class="bsd">בס"ד</DIV>
<TABLE class="layout">
<TR>
  <TD rowspan="4" style="vertical-align: top;">
    <DIV class="title" style="padding-top: 0px;">
      <H1><IMG src="images_hakhel/Hakhel.png" alt="<?=T_('Hakhel')?>"></H1>
      <H2><IMG src="images_wwtc/Mission_Report.png" alt="<?=T_('Mission Report')?>"></H2>
    </DIV>
    <DIV class="box_dark directives">
      <DIV class="directives_title"><DIV style="float: left; text-align: center; font-size: 60%;">Report<BR>#<?=$user_row['mission_number']?></DIV><IMG class="directives_title_img" src="images_hakhel/Gemillas_Chasadim.png" alt="<?=T_('Gemillas Chasadim')?>"><BR style="clear: both;"></DIV>
      <?
      $medals_result = mq("SELECT subject_id, medal_ord, medal_name, medals_subjects.medal_on_image_id, medals_subjects.medal_off_image_id, IFNULL(missions_required, 0) missions_required FROM medals JOIN subjects JOIN medals_subjects USING (subject_id, medal_ord) WHERE subject_type = 'Hakhel' ORDER BY subject_id, medal_ord");
      $medals = array();
      $progress = array();
      $active = 1;
      while($row = mysql_fetch_assoc($medals_result)) {
        if($active > 0) {
          if($missions_completed[$row['subject_id']] >= $row['missions_required']) {
            $missions_completed[$row['subject_id']] -= $row['missions_required'];
            $active = 1;
          } else {
            $progress[] = array('name'=>$row['medal_name'], 'complete'=>$missions_completed[$row['subject_id']], 'required'=>$row['missions_required']);
            $active = 0;
          }
        }
        // active == 1 - medal complete, active == 0 - medal in progress; active == -1 - medal not complete
        $medals[] = array('name' => $row['medal_name'], 'active' => $active, 'image' => $active == 0 ? $row['medal_on_image_id'] : $row['medal_off_image_id']);
        if($active == 0) $active = -1;
      }
      ?>

      <DIV class="medals medals_<?=$align_start?>">
        <? @mysql_data_seek($medals_result, 0); ?>
        <? foreach(array_reverse($medals) as $medal): ?>
          <DIV><IMG src="file_view.php?id=<?=$medal['image']?>" alt="<?=es($medal['name']), ' ', T_('Medal'), $medal['active'] == 1 ? ' ' . T_('Awarded') : ($medal['active'] == 0 ? ' ' . T_('In Progress') : '')?>"></DIV>
        <? endforeach; ?>
        <DIV style="font-size: 60%;"><?=T_('Current Hakhel Rank')?></DIV>
      </DIV>
      <DIV class="directives_tasks directives_tasks_<?=$align_start?>">
        <DIV class="box dates dates_<?=$align_start?>">
          <IMG src="images_wwtc/calendar.png" alt=""> <H3><IMG src="images_wwtc/Mission_Dates.png" alt="<?=T_('Mission Dates')?>"></H3><BR>
          <DIV style="margin: 0px;" dir="rtl">
          <?=es(dateToHebrewCommaYear(unixtojd()))?> - <?=es(dateToHebrewCommaYear($user_row['end_date']))?>
          </DIV>
        </DIV>
        <DIV class="box">
        <H3 style="padding-bottom: 0px;"><IMG src="images_wwtc/clipboard.png" alt="" class="directives_img" style="padding: 0px;"> <IMG src="images_hakhel/Mission_Directives.png" alt="<?=T_('Mission Directives')?>" style="height: .13in;"></H3>
        <OL>
        <LI>Choose from one of the six Gmach tasks, or come up with your own idea of how you will be Doresh Tov Lzulas. (Have it approved and write it in the last box.)
        <LI>When you complete a task, check the task you completed, fill in the date it was done, and have your Parents/Teacher initial.
        <LI>After 7 days of completing one of the Gmach tasks, hand in your mission report. You will then receive a mission achievement card to scan, making you one step closer to achieving your next medal.
        <LI>You will then receive your next mission report. After completing 5 mission reports you receive your first medal.
        </OL></DIV>
      <DIV style="clear: <?=$align_start?>; line-height: 1px; height: 1px;"></DIV>
      </DIV>
    </DIV>
        <H2><IMG src="images_hakhel/Hakhel_Medal_Progress_Bar.png" alt="<?=T_('Hakhel Medal Progress Bar')?>"></H2>
        <DIV class="box medal">
          <? foreach($progress as $progress_each): ?>
            <?=T_('Missions to'), ' ', $progress_each['name'], ' ', T_('medal')?>:<BR>
            <IMG src="images_wwtc/medal.png" alt="" class="medal_img" style="float: <?=$align_start?>">
            <DIV><? for($i=1; $i <= ceil($progress_each['required']); $i++): ?><IMG src="images_wwtc/progress_<?= $progress_each['complete'] >= $i ? 'on' : 'off' ?>.png" style="width: <?=80/ceil($progress_each['required'])?>%;" alt=""><? endfor; ?></DIV>
          <? endforeach; ?>
        </DIV>

    <DIV class="box parents parents_<?=$align_start?>" style="font-size: 90%;">
      <BR>
      <?=T_("Program Director's Signature")?>
    </DIV>

  </TD>
  <TD class="soldier soldier_<?=$align_start?>" style="width: 50%; vertical-align: top;">
    <DIV class="box_dark title">
      <IMG src="images_wwtc/Rank_Private.png" alt=""><BR><IMG src="images_wwtc/Soldier.png" alt="<?=T_('Soldier')?>"><BR><IMG src="images_wwtc/Profile.png" alt="<?=T_('Profile')?>">
    </DIV>
    <DIV class="bars">
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_on"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_on"></DIV>
      <DIV class="bars_off"></DIV>
    </DIV>
    <DIV class="box det">
      <DIV class="name"><?=es(firstInitial($user_row['first']))?> <?=es($user_row['last'])?> <!--(<?=es($user_row['username'])?>)--> <?=es(firstInitial($user_row['first_he']))?> <?=es($user_row['last_he'])?></DIV>
      <EM><?=T_('Rank')?>:</EM> Private<BR>
      <EM><?=T_('Serial')?> #:</EM> <?=$user_row['user_serial']?><BR>
      <EM><?=T_('Platoon')?>:</EM> <?=$user_row['class_grade'], $user_row['class_grade']!=='' && $user_row['class_sub']!=='' ? '-' : '', $user_row['class_sub']?><BR>
      <EM><?=T_('Teacher')?>:</EM> <?=$user_row['class_teacher']?><BR>
      <EM><?=T_('Total Miles')?>:</EM> <?=number_format($user_miles = mysql_result(mq(totalMarks("WHERE user_id = {$user_row['user_id']}")), 0), 0)?><BR>
    </DIV>
  </TD>
  <TD class="base base_<?=$align_end?>" style="width: 50%; vertical-align: top;">
    <DIV class="army_kid">
      <? if($user_row['gender'] == 'M'): ?>
        <IMG src="images_wwtc/Army-Kids-Boy.png" alt="[<?=T_('TH Army Boy')?>]">
      <? elseif($user_row['gender'] == 'F'): ?>
        <IMG src="images_wwtc/Army-Kids-Girl.png" alt="[<?=T_('TH Army Girl')?>]">
      <? endif; ?>
    </DIV>
    <DIV class="box_dark title">
      <?=!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id']) : ''?><BR><SPAN><IMG src="images_wwtc/Base.png" alt="<?=T_('Base')?>"></SPAN><BR><IMG src="images_wwtc/Profile.png" alt="<?=T_('Profile')?>">
    </DIV>
    <DIV class="bars">
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_on"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_off"></DIV>
      <DIV class="bars_on"></DIV>
      <DIV class="bars_off"></DIV>
    </DIV>
    <DIV class="box det">
      <EM><?=T_('TH Base Number')?>:</EM> <?=$user_row['school_number']?><BR>
      <EM><?=T_('Base')?>:</EM> <?=$user_row['school_name']?><BR>
      <EM><?=T_('Total Platoon Mileage')?>:</EM> <?=number_format($base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL")), 0), 0)?><BR>
      <EM><?=T_('Platoon Average')?>:</EM> <?=is_null($user_row['class_id']) ? T_('N/A') : @number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL")), 0) / mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = $school_id AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL"), 0), 2)?><BR>
      <EM><?=T_('Base Average')?>:</EM> <?=@number_format($base_points / ($base_count = mysql_result(mq("SELECT COUNT(*) FROM users WHERE school_id = $school_id AND user_start_date IS NOT NULL"), 0)), 2)?><BR>
      <EM><?=T_('Army Average')?>:</EM> <?=@number_format(($all_points = mysql_result(mq(totalMarks()), 0)) / ($all_count = 2392 /* mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_start_date IS NOT NULL"), 0)*/), 2) ?><BR>
    </DIV>
  </TD>
</TR>
<TR style="vertical-align: center;">
  <TD dir="rtl" class="hayom" style="font-size: 200%;">
<?= $user_row['mission_number'] == 1.2 ?
'"א חסיד שורפן"'
:
(
$user_row['mission_number'] == 1.3
?
'"א חסיד איז א לאַמטערנטשיק"'
:
(
$user_row['mission_number'] == 1.4
?
'"חסידים ברעכן דורך די חושך הגלות"'
:
'"א חסיד דורש טובת הזולת"'
)
)
?>
  </TD>
  <TD class="hayom">
    "<?=$user_row['mission_number'] == 1.2 ? T_('A Chossid is willing to do something that will <B>definitely</B> harm himself, on the chance that it <B>might</B> help another Jew.') : ($user_row['mission_number'] == 1.3 ? T_("A Chossid cares for another Yid's Ruchniyus.") : ($user_row['mission_number'] == 1.4 ? T_('A Chossid breaks through the darkness of גלות.') : T_("A Chossid is always on the lookout for ways that he can help another Jew.")))?>"
  </TD>
</TR>
<TR>
<TD colspan="2" style="vertical-align: top;">

<TABLE class="box missions missions_<?=$align_start?>" cellspacing="0">
<THEAD>
<TR>
  <TH class="title" colspan="2"><IMG src="images_hakhel/Gmach_Tasks.png" alt='<?=T_('גמ"ח Tasks')?>'></TH>
  <? for($i = 7; $i > 0; $i --): ?>
    <TH class="<?=!$i ? 'corner' : ''?>" width="10%">כסלו__</TH>
  <? endfor; ?>
</TR>
</THEAD>
<TFOOT>
<TR>
  <TH class="title" colspan="2"><IMG src="images_hakhel/Parent_Teacher_Initial.png" alt="<?=T_('Parent Teacher Initial')?>"></TH>
  <? @mysql_data_seek($missions_result, 0); $i = 0; ?>
  <? for($i = 7; $i > 0; $i --): ?>
    <TH class="<?=!$i ? 'corner' : ''?>">&nbsp;</TH>
  <? endfor; ?>
</TFOOT>
<TBODY>
  <? while($task_row = mysql_fetch_assoc($mission_tasks_result)):?>
    <TR>
      <TH class="label">
        <?=!is_null($task_row['label_image_id']) ? linkImgFile($task_row['label_image_id']) . '<BR>' : ''?>
        <? if(is_null($task_row['quantity'])): ?>
          <?=$task_row['points'], ' ', $task_row['points'] == 1 ? T_('Mile') : T_('Miles')?>
        <? else: ?>
          <?=T_('Quota')?>: <?=$task_row['points']?> <?=T_('mi.')?><BR>
          <?=round($task_row['points']/$task_row['quantity'], 2)?>&nbsp;<?=T_('mi.')?>&nbsp;<?=T_('each')?>
        <? endif; ?>
      </TH>
      <TH class="description"><?=es($task_row['name'])?></TH>
      <? for($i = 7; $i > 0; $i --): ?>
        <TD class="<?=!$i ? 'corner' : ''?>">&nbsp;</TD>
      <? endfor; ?>
    </TR>
  <? endwhile; ?>
</TBODY>
</TABLE>
</TD>
</TR>
<TR style="vertical-align: top;">
  <TD>
    <DIV class="box" style="float: left; width: 60%;">
      <DIV class="message_title"><IMG src="images_wwtc/note_edit.png" alt=""> <IMG class="message_title_img" src="images_wwtc/Message_To_Soldier.png" alt="<?=T_('Message To Soldier')?>"></DIV>
      <DIV class="message_text"><?= mysql_result(mq("SELECT message_text FROM messages WHERE message_type = 'bonus_to_soldier'"), 0) ?></DIV>
    </DIV>
    <DIV class="box" style="float: right; width: 25%;"><BR><BR><IMG src="images_hakhel/gmach-logo.png" alt="[Gmach Logo]" style="width: 100%;"><BR><BR><BR>
    </DIV>
  </TD>
  <TD>
    <DIV class="box total total_<?=$align_start?>"><BR>
      <IMG src="images_wwtc/currency_dollar.png" alt="">
      <DIV class="total_text"><?=T_('Mileage Available<BR>for Chinese Auction')?>:</DIV>
      <DIV class="total_num"><?=number_format($user_miles)?></DIV>
      <BR style="clear: both;"><BR>
    </DIV>
    <DIV class="box_dark stats_<?=$align_start?>"><BR>
      <DIV class="stats_title"><IMG src="images_wwtc/chart.png" alt=""><BR><IMG src="images_wwtc/Stats.png" alt="<?=T_('Stats')?>"></DIV>
      <DIV class="stats">
      <DIV class="stats_<?=$align_start?>">
        <STRONG><?=T_('Base Participation')?></STRONG><BR>
        <?=T_('Soldiers')?>: <?=number_format($base_count, 0)?><BR>
        <?=T_('Tasks Completed')?>: <?=number_format(mysql_result(mq("SELECT COUNT(*) FROM date_tasks_marks JOIN users USING (user_id) JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN subjects USING (subject_id) WHERE subject_type = 'Hakhel' AND school_id = $school_id"), 0), 0)?><BR>
        <?=T_('Missions completed')?>: <?=number_format(mysql_result(mq("SELECT COUNT(*) FROM date_tasks_mission_marks JOIN users USING (user_id) JOIN subjects USING (subject_id) WHERE subject_type = 'Hakhel' AND school_id = $school_id"), 0), 0)?>
      </DIV>
      <DIV class="stats_<?=$align_start?>">
        <STRONG><?=T_('Army Participation')?></STRONG><BR>
        <?=T_('Bases')?>: <?=number_format(mysql_result(mq("SELECT COUNT(*) schools FROM schools"), 0), 0)?><BR>
        <?=T_('Soldiers')?>: <?=number_format($all_count, 0)?><BR>
        <?=T_('Tasks Completed')?>: <?=number_format(mysql_result(mq("SELECT COUNT(*) FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN subjects USING (subject_id) WHERE subject_type = 'Hakhel'"), 0), 0)?><BR>
        <?=T_('Missions completed')?>: <?=number_format(mysql_result(mq("SELECT COUNT(*) FROM date_tasks_mission_marks JOIN subjects USING (subject_id) WHERE subject_type = 'Hakhel'"), 0), 0)?>
      </DIV>
      </DIV>
      <BR style="clear: both;"><BR>
    </DIV>
  </TD>
</TR>
</TABLE>
</DIV>
<? endwhile; ?>
</BODY>
</HTML>
