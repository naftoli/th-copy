<? 
$dual_auth = true;
$admin_auth = array('school'); 

require('header.php'); 
require_once('calendar.php');
require_once('file_save.php');

if (!empty($admin_user)) {
	assure_id_school('school_id');
	$school_id = gri('school_id', -1);
	$class_id = gri('class_id', -2);
	$user_id = gri('user_id', -2);
} 
else {
	$school_id = $user['school_id'];
	$class_id = $user['class_id'];
	$user_id = $user['user_id'];
}

$date = gri('date', unixtojd());

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id >= 0 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, last, first, username");

function isWhen($row) {
	global $date;
	
  // -1 = ended
  // 0 = now
  // 1 = future
	if ($row['end_date'] < $date) {
		return -1;
	} 
	elseif ($row['start_date'] > $date) {
		return 1;
	} 
	else {
		return 0;
	}
}

function hideLow($val, $min, $extra = '') {
  return '';
  return $val >= $min ? $val . $extra : '';
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Soldier Report'), ' ', T_('WWTC'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles_wwtc.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
	</HEAD>
	
	<BODY>
	
		<DIV class="header">
		
			<DIV class="noprint">

				<H1><?=T_('Soldier Report'), ' - ', T_('WWTC')?></H1>

				<H2>Printing Instructions</H2>
				
				<OL>
					<LI>File<?=$next_arr?>Page Set up
					<LI>Landscape
					<LI>Scale 80
					<LI>Margins: Top, Left, Right, Bottom (all): 0.0
					<LI>All headers and footers: Blank<BR>
				</OL>
				
				<HR>

<? if (!empty($admin_user) && ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1)) : ?>
	<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
				<FORM action="admin_report_wwtc.php" method="get" accept-charset="UTF-8">
					<P>
	<? if ($admin_user['auth'] == 'super') : ?>
						<LABEL>
							<?=T_('Effective Date')?>:
							<INPUT type="text" name="date_disp" READONLY value="<?=es(dateToHebrew($date))?>" onClick="getDate(this.form, 'date', true);">
						</LABEL>
						
						<INPUT type="hidden" name="date" value="<?=$date?>">
						
						<BR>
	<? else : ?>
						<INPUT type="hidden" name="date" value="<?=$date?>">
	<? endif; ?>
	
						<LABEL>
							<?=T_('Select Institution')?>: 
							<SELECT name="school_id">
							<? while($school_row = mysql_fetch_assoc($school_result)) : ?>
								<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
							<? endwhile; ?>
							</SELECT>
						</LABEL>
						
						<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
						
						<BR>
					</P>
				</FORM>

				<HR>
				
<? endif; ?>

<? if ($school_id == -1) : ?>
	<?=T_('Please select an Institution.')?>
<? elseif (!empty($admin_user)) : ?>
				<FORM action="admin_report_wwtc.php" method="get" accept-charset="UTF-8">
					<P>
						<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
						<INPUT type="hidden" name="date" value="<?=$date?>">
						<LABEL>
							<?=T_('Show Platoon')?> 
							<SELECT name="class_id">
								<OPTION value="-1">&lt;<?=T_('All')?>&gt;
								<? while ($class_row = mysql_fetch_assoc($class_result)) : ?>
								<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
								<? endwhile; ?>
							</SELECT>
						</LABEL>
						<BR>
						<LABEL>
							<?=T_('Show Soldier')?> 
							<SELECT name="user_id">
								<OPTION value="-1">&lt;<?=T_('All')?>&gt;
								<? while ($user_row = mysql_fetch_assoc($user_result)) : ?>
								<OPTION value="<?=$user_row['user_id']?>" <?=$user_row['user_id'] == $user_id ? 'SELECTED' : ''?>><?=$class_id == -1 && $user_row['class_grade'] != '' ? es($user_row['class_grade'] . '-' . $user_row['class_sub']) . ': ' : ''?><?=es($user_row['last'])?>, <?=es($user_row['first'])?> (<?=es($user_row['username'])?>)</OPTION>
								<? endwhile; ?>
							</SELECT>
						</LABEL>
						<BR>
						<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
					</P>
				</FORM>
<? endif; ?>

			</DIV>
			
		</DIV>
		
<?
$users = mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, inst_logo_id, school_type_id,
       rank_ord, rank_name, rank_image_id, rank_color
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
WHERE school_id = $school_id" .
      ($class_id != -1 ? " AND class_id = $class_id" : '') .
      ($user_id != -1 ? " AND user_id = $user_id" : '') .
' ORDER BY class_grade, class_sub, last, first'
);
?>

<? if (mysql_num_rows($users)) : ?>
		<P class="noprint" style="text-align: center;">
			<INPUT type="button" value="<?=T_('Print')?>" onClick="print();">
		</P>
<? endif; ?>

<? while($user_row = mysql_fetch_assoc($users)): ?>
<?
unset($mission_data);
$missions_result = mq("
SELECT * FROM (
SELECT date_tasks_mission_id, mission_name, mission_description, start_date, end_date, subject_id,
       EXISTS (
        SELECT * FROM date_tasks_mission_marks JOIN date_tasks_missions mission_marks USING (date_tasks_mission_id) WHERE date_tasks_mission_marks.user_id = users.user_id AND date_tasks_mission_marks.subject_id = subjects.subject_id AND date_tasks_missions.start_date = mission_marks.start_date AND date_tasks_missions.end_date = mission_marks.end_date ) done
FROM users
     JOIN subjects
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
WHERE user_id = {$user_row['user_id']} AND subject_type = 'WWTC' AND enrolled = 1
      AND (start_date < 2455094 OR start_date > 2455118)
      AND start_date >= 2455088 AND start_date <= 2455448
ORDER BY start_date DESC
LIMIT 12
) rev ORDER BY start_date
");

if(!mysql_num_rows($missions_result)) continue;

while($row = mysql_fetch_assoc($missions_result)) {
  if(isWhen($row) == 0) {
    $mission_data = $row;
    break;
  }
  $prev_mission = array('start_date'=>$row['start_date'], 'end_date'=>$row['end_date']);
}

if(!isset($prev_mission) || !isset($mission_data)) {
  $prev_mission = array('start_date'=>-1, 'end_date'=>-1);
}

if(!isset($mission_data)) {
  $mission_data = array('date_tasks_mission_id'=>-1, 'mission_name'=>'', 'mission_description'=>'', 'start_date'=>0, 'end_date'=>0);
}

$mission_tasks_result = mq("SELECT date_task_id, name, description, mandatory_qty, optional_qty, quantity, points, label_id, label_name, label_description, label_image_id FROM date_tasks LEFT JOIN labels USING (label_id) WHERE date_tasks_mission_id = {$mission_data['date_tasks_mission_id']} ORDER BY ord, name, date_task_id");

$missions_completed = mysql_fetch_column(mq("SELECT subjects.subject_id, IFNULL(SUM(mission_value), 0) missions_completed FROM subjects LEFT JOIN date_tasks_mission_marks ON (subjects.subject_id = date_tasks_mission_marks.subject_id AND user_id = {$user_row['user_id']}) WHERE subject_type = 'WWTC' GROUP BY subjects.subject_id"), 0);
?>

	<DIV class="page">
		<HR class="noprint">
			<DIV dir="rtl" class="bsd">
				בס"ד
			</DIV>
			<TABLE class="layout">
				<TR>
					<TD rowspan="3" style="vertical-align: bottom;">
						<DIV class="title" style="padding-top: 0px;">
							<H1><IMG src="images_wwtc/World_Wide_Tehillim_Club.png" alt="<?=T_('World-Wide Tehillim Club')?>"></H1>
							<H2><IMG src="images_wwtc/Mission_Report.png" alt="<?=T_('Mission Report')?>"></H2>
						</DIV>
						<DIV class="box_dark directives">
							<DIV class="directives_title">
								<IMG src="images_wwtc/clipboard.png" alt="" class="directives_img">
								<IMG class="directives_title_img" src="images_wwtc/Mission_Directives.png" alt="<?=T_('Mission Directives')?>">
							</DIV>
      <?
      $medals_result = mq("SELECT subject_id, medal_ord, medal_name, medals_subjects.medal_on_image_id, medals_subjects.medal_off_image_id, IFNULL(missions_required, 0) missions_required FROM medals JOIN subjects JOIN medals_subjects USING (subject_id, medal_ord) WHERE subject_type = 'WWTC' ORDER BY subject_id, medal_ord");
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
      </DIV>
	  
		<DIV class="directives_tasks directives_tasks_<?=$align_start?>">
			<H3>
				<IMG src="images_wwtc/Mission_Dates.png" alt="<?=T_('Mission Dates')?>">
			</H3>
			<DIV class="box dates dates_<?=$align_start?>">
				<IMG src="images_wwtc/calendar.png" alt="">
			<DIV>
				<SPAN>
					<?=es($mission_data['mission_description'])?>
				</SPAN>
				<BR>
				<?=es(dateToHebrewCommaYear($mission_data['start_date']))?>
			</DIV>
		</DIV>

        <H3 style="padding-bottom: 0px;"><IMG src="images_wwtc/Primary_Mission_Objectives.png" alt="<?=T_('Primary Mission Objectives')?>"></H3>
			<TABLE style="width: 100%;">
        <? $i = 0; ?>
        <? while(($row = mysql_fetch_assoc($mission_tasks_result)) && $row['mandatory_qty'] != 0): ?>
          <?=!($i++%2) ? '<TR class="directives_task_tr"><TD></TD></TR><TR>' : ''?>
          <TD class="box directives_task_td" style="width: 50%;">
            <?=!is_null($row['label_image_id']) ? linkImgFile($row['label_image_id']) : ''?><BR>
            <DIV><?=es($row['label_description'])?></DIV>
            <?=es($row['description'])?>
          </TD>
        <? endwhile; ?>
        </TABLE>
        <H3><IMG src="images_wwtc/Best_Way_To_Do_It.png" alt="<?=T_('Best Way To Do It (Bonus Miles)')?>"></H3>
        <? while($row): ?>
          <DIV class="box directives_task">
            <?=!is_null($row['label_image_id']) ? linkImgFile($row['label_image_id']) : ''?>
            <?=es($row['description'])?>
          </DIV>
        <? $row = mysql_fetch_assoc($mission_tasks_result); ?>
        <? endwhile; ?>
        <? @mysql_data_seek($mission_tasks_result, 0); ?>
        <H3><IMG src="images_wwtc/WWTC_Medal_Progress_Bar.png" alt="<?=T_('WWTC Medal Progress Bar')?>"></H3>
        <DIV class="box medal">
          <? foreach($progress as $progress_each): ?>
            <?=T_('Missions to'), ' ', $progress_each['name'], ' ', T_('medal')?>:<BR>
            <IMG src="images_wwtc/medal.png" alt="" class="medal_img">
            <DIV><? for($i=1; $i <= ceil($progress_each['required']); $i++): ?><IMG src="images_wwtc/progress_<?= $progress_each['complete'] >= $i ? 'on' : 'off' ?>.png" style="width: <?=100/ceil($progress_each['required'])?>%;" alt=""><? endfor; ?></DIV>
          <? endforeach; ?>
        </DIV>

      <DIV style="clear: <?=$align_start?>; line-height: 1px; height: 1px;"></DIV>
      </DIV>
    </DIV>
  </TD>
  <TD class="soldier soldier_<?=$align_start?>" style="width: 50%; vertical-align: top;">
    <DIV class="box_dark title">
      <?=!is_null($user_row['rank_image_id']) ? linkImgFile($user_row['rank_image_id']) : ''?><BR><IMG src="images_wwtc/Soldier.png" alt="<?=T_('Soldier')?>"><BR><IMG src="images_wwtc/Profile.png" alt="<?=T_('Profile')?>">
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
      <EM><?=T_('Rank')?>:</EM> <?=es($user_row['rank_name'])?><BR>
      <EM><?=T_('Serial')?> #:</EM> <?=$user_row['user_serial']?><BR>
      <EM><?=T_('Platoon')?>:</EM> <?=$user_row['class_grade'], $user_row['class_grade']!=='' && $user_row['class_sub']!=='' ? '-' : '', es($user_row['class_sub'])?><BR>
      <EM><?=T_('Teacher')?>:</EM> <?=es($user_row['class_teacher'])?><BR>
      <EM><?=T_('Total Miles')?>:</EM> <?=number_format($user_miles = mysql_result(mq(totalMarks("WHERE user_id = {$user_row['user_id']}")), 0), 2)?><BR>
      <EM><?=T_('Platoon Average')?>:</EM> <?=is_null($user_row['class_id']) ? T_('N/A') : @number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL")), 0) / mysql_result(mq("SELECT COUNT(*) base_count FROM users WHERE school_id = $school_id AND class_id = {$user_row['class_id']} AND user_start_date IS NOT NULL"), 0), 2)?><BR>
      <EM><?=T_('Base Average')?>:</EM> <?=@number_format(($base_points = mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND user_start_date IS NOT NULL")), 0)) / ($base_count = mysql_result(mq("SELECT COUNT(*) base_count FROM users WHERE school_id = $school_id AND user_start_date IS NOT NULL"), 0)), 2)?><BR>
      <EM><?=T_('Army Average')?>:</EM> <?=@number_format(($all_points = mysql_result(mq(totalMarks()), 0)) / ($all_count = mysql_result(mq("SELECT COUNT(*) base_count FROM users WHERE user_start_date IS NOT NULL"), 0)), 2) ?><BR>
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
      <EM><?=T_('Base')?>:</EM> <?=es($user_row['school_name'])?><BR>
      <EM><?=T_('Total Base Mileage')?>:</EM> <?=number_format($base_points, 2)?><BR>
      <EM><?=T_('Average Platoon Participation')?>:</EM> <?=is_null($user_row['class_id']) ? T_('N/A') : @hideLow(number_format(mysql_result(mq("SELECT COUNT(DISTINCT user_id) FROM date_tasks_marks JOIN users USING (user_id) JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN user_tracks USING (user_id, subject_id) JOIN subjects USING (subject_id) WHERE school_id = $school_id AND class_id = {$user_row['class_id']} AND subject_type = 'WWTC' AND start_date = {$prev_mission['start_date']} AND end_date = {$prev_mission['end_date']} AND user_registered IS NOT NULL AND enrolled = 1"), 0) / mysql_result(mq("SELECT COUNT(*) FROM users JOIN user_tracks USING (user_id) JOIN subjects USING (subject_id) WHERE school_id = $school_id AND class_id = {$user_row['class_id']} AND user_start_date <= {$prev_mission['end_date']} AND user_registered IS NOT NULL AND enrolled = 1 AND subject_type = 'WWTC'"), 0) * 100, 0), 50, '%')?><BR>
      <EM><?=T_('Average Base Participation')?>:</EM> <?=@hideLow(number_format(mysql_result(mq("SELECT COUNT(DISTINCT user_id) FROM date_tasks_marks JOIN users USING (user_id) JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN user_tracks USING (user_id, subject_id) JOIN subjects USING (subject_id) WHERE school_id = $school_id AND subject_type = 'WWTC' AND start_date = {$prev_mission['start_date']} AND end_date = {$prev_mission['end_date']} AND user_registered IS NOT NULL AND enrolled = 1"), 0) / mysql_result(mq("SELECT COUNT(*) FROM users JOIN user_tracks USING (user_id) JOIN subjects USING (subject_id) WHERE school_id = $school_id AND user_start_date <= {$prev_mission['end_date']} AND user_registered IS NOT NULL AND enrolled = 1 AND subject_type = 'WWTC'"), 0) * 100, 0), 50, '%')?><BR>
      <EM><?=T_('Average Army Participation')?>:</EM> <?=@hideLow(number_format(mysql_result(mq("SELECT COUNT(DISTINCT user_id) FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN users USING (user_id) JOIN user_tracks USING (user_id, subject_id) JOIN subjects USING (subject_id) WHERE subject_type = 'WWTC' AND start_date = {$prev_mission['start_date']} AND end_date = {$prev_mission['end_date']} AND user_registered IS NOT NULL AND enrolled = 1"), 0) / mysql_result(mq("SELECT COUNT(*) FROM users JOIN user_tracks USING (user_id) JOIN subjects USING (subject_id) WHERE user_start_date <= {$prev_mission['end_date']} AND user_registered IS NOT NULL AND enrolled = 1 AND subject_type = 'WWTC'"), 0) * 100, 0), 50, '%')?>
    </DIV>
  </TD>
</TR>
<TR style="vertical-align: center;">
  <TD dir="rtl" class="hayom">
 "אמירת תהלים בכל יום, גומר זיין דעם תהלים שבת מברכים - דאס דארף מען אפהיטען, דאס איז נוגע איהם, זיינע קינדער און קינדס קינדער".
<BR>
-היום יום כ"ה שבט
  </TD>
  <TD class="hayom">
    "<?=T_("...Saying the entire Tehillim on Shabbos Mevorchim is crucial for you, for your children and your children's children!")?>"<BR>
    -<?=T_('HaYom Yom 25 Shevat')?>
  </TD>
</TR>
<TR>
<TD colspan="2" style="vertical-align: bottom;">

<TABLE class="box missions missions_<?=$align_start?>" cellspacing="0">
	<THEAD>
		<TR>
			<TH class="title" colspan="2">
				<IMG src="images_wwtc/Earn_Miles.png" alt="<?=T_('Earn Miles')?>">
			</TH>
			<? @mysql_data_seek($missions_result, 0); $i = 0; ?>
			<? while($row = mysql_fetch_assoc($missions_result)):?>
			<TH class="<?=++$i == mysql_num_rows($missions_result) ? 'corner' : ''?> <?=$row['date_tasks_mission_id'] == $mission_data['date_tasks_mission_id'] ? 'current' : ''?>">
				<?=es($row['mission_name'])?><BR><?=T_('Mission')?> #<?=$i?>
			</TH>
			<? endwhile; ?>
		</TR>
	</THEAD>
	
	<TFOOT>
		<TR>
			<TH class="title" colspan="2">
				<IMG src="images_wwtc/Mileage.png" alt="<?=T_('Mileage')?>">
			</TH>
			<? @mysql_data_seek($missions_result, 0); $i = 0; ?>
			<? while($row = mysql_fetch_assoc($missions_result)):?>
			<TD class="<?=++$i == mysql_num_rows($missions_result) ? 'corner' : ''?> <?=$row['date_tasks_mission_id'] == $mission_data['date_tasks_mission_id'] ? 'current' : ''?>">
				<? $points = mysql_result(mq("SELECT IFNULL(SUM(mark_points), 0) mark_points FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) WHERE user_id = {$user_row['user_id']} AND start_date = {$row['start_date']} AND end_date = {$row['end_date']} AND subject_id = {$row['subject_id']}"), 0) ?><?=$points == 0 && isWhen($row) == 1 ? '' : floatval($points) . '&nbsp;' . T_('mi.') . ' <BR>/' . floatval(mysql_result(mq("SELECT IFNULL(SUM(points * mandatory_qty), 0) + IFNULL(SUM(points * optional_qty), 0) points FROM date_tasks WHERE date_tasks_mission_id = {$row['date_tasks_mission_id']}"), 0)) . T_('mi.')?>
			</TD>
			<? endwhile; ?>
	</TFOOT>
	
							<TBODY>
  <? $mand_div = false?>
  <? while($task_row = mysql_fetch_assoc($mission_tasks_result)):?>
    <? if(!$mand_div && $task_row['mandatory_qty'] == 0): ?>
    <? $mand_div = true; ?>
								<TR>
									<TH class="label">
										<IMG src="images_wwtc/clipboard.png" alt="">
									</TH>
									<TH class="description">
										<?=T_('Did you complete the mission?')?>
									</TH>
      <? @mysql_data_seek($missions_result, 0); $i = 0; ?>
      <? while($mission_row = mysql_fetch_assoc($missions_result)):?>
									<TD class="<?=++$i == mysql_num_rows($missions_result) ? 'corner' : ''?> <?=$mission_row['date_tasks_mission_id'] == $mission_data['date_tasks_mission_id'] ? 'current' : ''?>">
										<DIV class="quantity">
											<?=$mission_row['done'] ? 'yes' : (isWhen($mission_row) == 1 ? '' : 'no') ?>
										</DIV>
									</TD>
      <? endwhile; ?>
								</TR>
								
    <? endif; ?>
								<TR>
									<TH class="label">
										<?=!is_null($task_row['label_image_id']) ? linkImgFile($task_row['label_image_id']) . '<BR>' : ''?>
        <? if(is_null($task_row['quantity'])): ?>
          <?=floatval($task_row['points']), ' ', $task_row['points'] == 1 ? T_('Mile') : T_('Miles')?>
        <? else: ?>
          <?=T_('Quota')?>: <?=floatval($task_row['points'])?> <?=T_('mi.')?><BR>
          <?=round($task_row['points']/$task_row['quantity'], 2)?>&nbsp;<?=T_('mi.')?>&nbsp;<?=T_('each')?>
        <? endif; ?>
									</TH>
									
									<TH class="description">
										<?=es($task_row['name'])?>
									</TH>
									
      <? @mysql_data_seek($missions_result, 0); $i = 0; ?>
      <? while($mission_row = mysql_fetch_assoc($missions_result)):?>
									<TD class="<?=++$i == mysql_num_rows($missions_result) ? 'corner' : ''?> <?=$mission_row['date_tasks_mission_id'] == $mission_data['date_tasks_mission_id'] ? 'current' : ''?>">
          <? if(!is_null($task_row['label_id'])): ?>
          <? $row = mysql_fetch_assoc(mq("SELECT IFNULL(SUM(mark_points), 0) points, IFNULL(SUM(done_qty), 0) done, IFNULL(SUM(mark_quantity), 0) quantity FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) WHERE user_id = {$user_row['user_id']} AND start_date >= {$mission_row['start_date']} AND end_date <= {$mission_row['end_date']} AND subject_id = {$mission_row['subject_id']} AND label_id = {$task_row['label_id']}")); ?>

										<DIV class="quantity">
											<?= is_null($task_row['quantity']) ? ($row['done'] && $row['done'] >= $task_row['mandatory_qty'] ? 'yes' : (isWhen($mission_row) == 1 ? '' : 'no')) : ($row['quantity'] == 0 && isWhen($mission_row) == 1 ? '' : $row['quantity']) ?>
										</DIV>

										<DIV class="points">
											<?=$row['points'] == 0 && isWhen($mission_row) == 1 ? '' : floatval($row['points']) . '&nbsp;' . T_('mi.')?>
										</DIV>
          <? endif; ?>
									</TD>
      <? endwhile; ?>
								</TR>
  <? endwhile; ?>
							</TBODY>
							
						</TABLE>

					</TD>
					
				</TR>
				
				<TR style="vertical-align: top;">

					<TD>
						<DIV class="box">
							<DIV class="message_title">
								<IMG src="images_wwtc/note_edit.png" alt=""> 
								<IMG class="message_title_img" src="images_wwtc/Message_To_Soldier.png" alt="<?=T_('Message To Soldier')?>">
							</DIV>
							<DIV class="message_text">
								<?= mysql_result(mq("SELECT message_text FROM messages WHERE message_type = 'th_to_soldier'"), 0) ?>
							</DIV>
						</DIV>
					</TD>
					
					<TD>
  
						<DIV class="box goals_<?=$align_start?>">
	
							<DIV class="goals_title">
								<IMG src="images_wwtc/target.png" alt="">
								<BR>
								<IMG src="images_wwtc/Goals.png" alt="<?=T_('Goals')?>">
							</DIV>
	  
							<DIV class="goals">
							
        <? $result = mq("SELECT goal_start, goal_end FROM goals JOIN subjects USING (subject_id) JOIN user_tracks USING (subject_id, track_id, level) WHERE user_id = {$user_row['user_id']} AND school_type_id = {$user_row['school_type_id']} AND subject_type = 'WWTC'"); ?>
		
								<SPAN>
									<?=T_('My Goal for this year')?>
								</SPAN>
								
        <? while($row = mysql_fetch_assoc($result)): ?>
								<BR>
								<?=T_('Year Start')?>: <?=es($row['goal_start'])?> &bull; <?=T_('Year End')?>: <?=es($row['goal_end'])?>
        <? endwhile; ?>
								<BR>
								
								<DIV>
									<BR>
        <? $result = mq("SELECT goal_start, goal_end FROM goals JOIN subjects USING (subject_id) JOIN user_tracks ON (goals.subject_id = user_tracks.subject_id AND goals.track_id = user_tracks.track_id AND goals.level = 14) WHERE user_id = {$user_row['user_id']} AND school_type_id = {$user_row['school_type_id']} AND subject_type = 'WWTC'"); ?>
		
									<SPAN>
										<?=T_('My long term strategy')?>
									</SPAN>
									
        <? while($row = mysql_fetch_assoc($result)): ?>
									<BR>
									<?=T_('By the end of 7th grade')?>: <?=es($row['goal_end'])?>
        <? endwhile; ?>
									<BR style="clear: both;">
								</DIV>
								
							</DIV>
							
						</DIV>
	
						<DIV class="box parents parents_<?=$align_start?>">
							<IMG src="images_wwtc/users4.png" alt=""><?=T_("Parent's<BR>Signature")?>:<BR style="clear: both;">
						</DIV>
						
					</TD>
					
					<TD>
  
						<DIV class="box total total_<?=$align_start?>">
						
      <? $auction_points = auctionPoints($user_row['user_id']); ?>
							<IMG src="images_wwtc/currency_dollar.png" alt="">
	  
							<DIV class="total_text">
								<?=T_('Mileage Available<BR>for Chinese Auction')?>:
							</DIV>
	  
							<DIV class="total_num">
								<?=number_format($auction_points['cur'])?>
							</DIV>
	  
							<BR style="clear: both;">
							
      <? if(isset($auction_points['prev'])): ?>
							<DIV class="total_message">
								<?=sprintf(T_('%s miles needed to activate %s past miles for upcoming auction.'), number_format($auction_points['left']), number_format($auction_points['prev']))?>
							</DIV>
      <? endif; ?>
						</DIV>
						
						<DIV class="box_dark stats_<?=$align_start?>">
						
							<DIV class="stats_title">
								<IMG src="images_wwtc/chart.png" alt="">
								<BR>
								<IMG src="images_wwtc/Stats.png" alt="<?=T_('Stats')?>">
							</DIV>
							
							<DIV class="stats">
							
								<DIV class="stats_<?=$align_start?>">
									<STRONG><?=T_('Base Participation')?></STRONG><BR>
        <?=T_('Soldiers')?>: <?=number_format(mysql_result(mq("SELECT COUNT(*) base_count FROM users JOIN user_tracks USING (user_id) JOIN subjects USING (subject_id) WHERE subject_type = 'WWTC' AND school_id = $school_id AND user_start_date IS NOT NULL AND user_registered IS NOT NULL AND enrolled = 1"), 0), 0)?><BR>
        <? @mysql_data_seek($mission_tasks_result, 0); ?>
        <? while(($row = mysql_fetch_assoc($mission_tasks_result)) && $row['mandatory_qty'] != 0): ?>
          <?=es($row['label_name'])?>: <?=number_format(mysql_result(mq("SELECT IFNULL(SUM(mark_quantity), 0) FROM users JOIN date_tasks JOIN date_tasks_marks USING (date_task_id, user_id) WHERE label_id = 0{$row['label_id']} AND school_id = $school_id") ,0), 0)?><BR>
        <? endwhile; ?>
								</DIV>
								
								<DIV class="stats_<?=$align_start?>">
									<STRONG><?=T_('Army Participation')?></STRONG><BR>
        <?=T_('Bases')?>: <?=number_format(mysql_result(mq("SELECT COUNT(*) schools FROM schools WHERE EXISTS (SELECT * FROM users JOIN user_tracks USING (user_id) JOIN subjects USING (subject_id) WHERE subject_type = 'WWTC' AND users.school_id = schools.school_id AND user_start_date IS NOT NULL AND user_registered IS NOT NULL AND enrolled = 1)"), 0), 0)?><BR>
        <?=T_('Soldiers')?>: <?=number_format(mysql_result(mq("SELECT COUNT(*) base_count FROM users JOIN user_tracks USING (user_id) JOIN subjects USING (subject_id) WHERE subject_type = 'WWTC' AND user_start_date IS NOT NULL AND user_registered IS NOT NULL AND enrolled = 1"), 0), 0)?><BR>
        <? @mysql_data_seek($mission_tasks_result, 0); ?>
        <? while(($row = mysql_fetch_assoc($mission_tasks_result)) && $row['mandatory_qty'] != 0): ?>
          <?=es($row['label_name'])?>: <?=number_format(mysql_result(mq("SELECT IFNULL(SUM(mark_quantity), 0) FROM date_tasks JOIN date_tasks_marks USING (date_task_id) WHERE label_id = 0{$row['label_id']}") ,0), 0)?><BR>
        <? endwhile; ?>
								</DIV>
								
							</DIV>
							
							<BR style="clear: both;">
							
						</DIV>
						
					</TD>
					
				</TR>
				
			</TABLE>
			
		</DIV>
		
<? endwhile; ?>
	</BODY>
	
</HTML>
