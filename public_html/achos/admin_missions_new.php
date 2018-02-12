<?php
	$admin_auth = array('school'); 
	require('header.php'); 

	require_once('calendar.php');
	require_once('file_save.php');
	$ui_type = 'reports';
	require_once('admin_ui.php');

	assure_id_school('school_id');
	$school_id = gri('school_id', -1);
	$class_id = gri('class_id', -1);
	$user_id = gri('user_id', -1);

	$class_result = mq("SELECT
							class_id, class_grade, class_sub 
						FROM 
							classes 
						WHERE 
							school_id = $school_id ORDER BY class_grade, class_sub");
	$user_result = mq("SELECT
							class_grade, class_sub, user_id, username, first, last, user_start_date
						FROM 
							users
						LEFT JOIN 
							classes 
						USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id >= 0 ? " AND class_id = $class_id" : '') . " 
						ORDER BY class_grade, class_sub, last, first, username limit 3");

	$end_date = gri('end_date', unixtojd()+30);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Mission Report'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
	</HEAD>
	<BODY>
	<?php include('admin_header.php'); ?>
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
			<DIV class="body">
				<DIV class="sub_menu">
				<?php
					if(!empty($message))
					{
				?>
						<H2><?=$message?></H2>
				<?php
					}
				?>
			</DIV>
			
			<DIV class="noprint">
				<H1><?=T_('Reports')?></H1>
				<?php
					if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1)
					{
						$school_result = mq('SELECT 
												school_id, school_name, inst_name
											FROM 
												schools 
											JOIN institutions 
											USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' 
											WHERE 
												school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' 
											ORDER BY inst_name, school_name'); ?>
											
						<FORM action="admin_missions_new.php" method="get" accept-charset="UTF-8">
							<P>
								<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
								<?php
									while($school_row = mysql_fetch_assoc($school_result))
									{
								?>
										<option value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'selected' : ''?>>
											<?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?>
										</option>
								<?php
									}
								?>
								</SELECT></LABEL>
							<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
							</P>
						</FORM>
				<?php
					}
				?>
			</DIV>
		<?php
			if($school_id == -1)
			{
				echo T_('Please select an Institution.');
			}
			else
			{
		?>
			<DIV class="ui_body">
				<DIV class="ui_menu">
					<?ui_menu();?>
				</DIV>
				<DIV class="content">
				<H2 class="noprint"><?php echo T_('Mission Report'); ?></H2>
					<DIV class="infobox noprint">
					</DIV>
					<FORM action="admin_missions_new.php" method="get" accept-charset="UTF-8">
						<P class="noprint">
							<?php
								$class_result = mq("SELECT 
														class_id, class_grade, class_sub 
													FROM 
														classes 
													WHERE 
														school_id = $school_id ORDER BY class_grade, class_sub");
							?>
							<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
							<?php 
								echo T_('Choose Platoon'); 
							?>: <SELECT name="class_id">
									<OPTION value="-1">&lt;<?=T_('All')?>&gt;
									<?php
										while($class_row = mysql_fetch_assoc($class_result))
										{
									?>
											<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>>
												<?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?>
											</OPTION>
									<?php
										}
									?>
</SELECT><BR>
<LABEL><?=T_('End Date')?>:<INPUT type="text" name="end_date_disp" READONLY value="<?=es(dateToHebrew($end_date))?>" onClick="getDate(this.form, 'end_date', true);"></LABEL><INPUT type="hidden" name="end_date" value="<?=$end_date?>"> <?=T_('Usually, last day of school or term.')?><BR>
<INPUT class="submit" type="submit" name="view" value="<?=T_('Go')?>">
</P>
</FORM>
<?
$ranks = mysql_fetch_column(mq("SELECT rank_ord, rank_name, medals_required FROM ranks ORDER BY rank_ord"));

while($user = mysql_fetch_assoc($user_result)):

$start_date = max(unixtojd() - 21, intval($user['user_start_date']));

echo "<input type='hidden' name='START DATE' value='" . $start_date . "'>\n";
echo "<input type='hidden' name='END DATE' value='" . $end_date . "'>\n";

$missions = mq("
SELECT subject_id, subject_name, inst_name, date_tasks_missions.date_tasks_mission_id, date_tasks_missions.mission_name, mission_description, mission_number, date_tasks_missions.mission_value
FROM subjects
     JOIN school_subjects USING (subject_id)
     JOIN school_type_subjects USING (subject_id)
     JOIN users USING (school_id, school_type_id)
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
     LEFT JOIN date_tasks_mission_marks USING (user_id, subject_id, date_tasks_mission_id)
     LEFT JOIN institutions USING (inst_id)
WHERE user_id = {$user['user_id']}
      AND date_tasks_mission_marks.date_tasks_mission_id IS NULL -- maybe convert to not exists
      AND start_date <= $end_date
      AND end_date >=  $start_date
      AND NOT EXISTS (SELECT * FROM date_tasks JOIN date_tasks_marks USING (date_task_id) JOIN date_tasks_missions date_tasks_missions_alt USING (date_tasks_mission_id) WHERE date_tasks_missions_alt.subject_id = date_tasks_missions.subject_id AND (date_tasks_missions_alt.mission_number = date_tasks_missions.mission_number OR (date_tasks_missions.mission_number IS NULL AND date_tasks_missions_alt.start_date = date_tasks_missions.start_date AND date_tasks_missions_alt.end_date = date_tasks_missions.end_date)) AND date_tasks_missions_alt.date_tasks_mission_id != date_tasks_missions.date_tasks_mission_id AND user_id = {$user['user_id']})
ORDER BY inst_name, subject_name, subject_id, mission_number, start_date, mission_name
");

$subject_missions = array();
while($row = mysql_fetch_assoc($missions))
{
	if(!isset($subject_missions[$row['subject_id']]))
	{
		$subject_missions[$row['subject_id']]['available'] = 0;
	}
	else
	{
		$subject_missions[$row['subject_id']]['available'] += $row['mission_value'];
	}
}

@mysql_data_seek($missions, 0);

$result = mq("
SELECT subject_id, SUM(mission_value) mission_value
FROM date_tasks_mission_marks
WHERE user_id = {$user['user_id']}
GROUP BY subject_id
");
while($row = mysql_fetch_assoc($result)) {
  if(isset($subject_missions[$row['subject_id']])) $subject_missions[$row['subject_id']]['done'] = $row['mission_value'];
}

$subject_missions_table = '';
foreach($subject_missions as $subject_id => &$data) {
  if(!isset($data['done'])) $data['done'] = 0;
  if($subject_missions_table) $subject_missions_table .= ' UNION ALL';
  $subject_missions_table .= " SELECT $subject_id subject_id, " . ($data['available']+$data['done']) . ' missions';
}
unset($data);

$medals_possible = mysql_fetch_column_tuple(mq("
SELECT subject_id, medals_subjects_totals.medal_ord, medal_name, missions_required, missions_required_total
FROM
     users JOIN
     ($subject_missions_table) subject_missions
     JOIN medals_subjects_totals USING (subject_id)
     JOIN medals USING (medal_ord)
     LEFT JOIN medal_marks USING (user_id, medal_ord, subject_id)
WHERE user_id = {$user['user_id']}
      AND medal_marks.medal_ord IS NULL
      AND missions_required_total <= missions
ORDER BY subject_id, medals_subjects_totals.medal_ord
"));

$current_rank = mysql_result(mq("SELECT MAX(rank_ord) FROM rank_marks WHERE user_id = {$user['user_id']}"), 0);
$current_medals = mysql_result(mq("SELECT COUNT(*) FROM medal_marks WHERE user_id = {$user['user_id']}"), 0);
?>

<H2><?=es($user['class_grade'] . '-' . $user['class_sub'])?> <?=es($user['first'] . ' ' . $user['last'])?></H2>
<P>
<?
foreach($ranks as $rank_ord => $data) {
  if($rank_ord <= $current_rank) continue;
  if($data['medals_required'] > $current_medals+count($medals_possible)) break;
  echo sprintf(T_('You could be a %s if you earn %s more medals by %s.'), $data['rank_name'], $data['medals_required']-$current_medals, es(dateToHebrew($end_date))) . '<BR>';
}
?>
</P>
<?php
	$medal_names = array('White', 'Red', 'Orange', 'Yellow', 'Green', 'Blue', 'Purple', 'Brown', 'Grey', 'Black');
	foreach ($medal_names as $name) {
		$num_of_medals[$name] = 0;	
		$total_medals[$name] = 0;
	}

	$old_subject_id = -1; 
	while($mission = mysql_fetch_assoc($missions))
	{
		if($old_subject_id != $mission['subject_id'])
		{
			if($old_subject_id != -1) {
				echo '</OL>';
			}
			$old_subject_id = $mission['subject_id'];
			$mission_counter = $subject_missions[$mission['subject_id']]['done'];
			echo '<H3>' . es($mission['subject_name']) . '</H3>';			
						
			if(empty($medals_possible[$mission['subject_id']]))
			{
				//echo sprintf(T_('You can do the following %u missions by %s:'), $subject_missions[$mission['subject_id']]['available'], es(dateToHebrew($end_date)));
			}
			else 
			{
				$data = current($medals_possible[$mission['subject_id']]);
				
				//echo sprintf(T_('You can earn the <B>%s</B> medal by doing the following %s missions by %s:'), 
				//	es($data['medal_name']), ($data['missions_required_total']-$subject_missions[$mission['subject_id']]['done']),
				//	es(dateToHebrew($end_date)));
				
				echo $data['medal_name'] . " medal";
				$num_of_medals[$data['medal_name']]++;
				$total_medals[$data['medal_name']]++;
			}			
			echo '<OL>';
		}
		//echo 'mission counter: '.$mission_counter-- .'<br />';
		$mission_counter++;
		if(!empty($medals_possible[$mission['subject_id']]))
			{
				  $data = current($medals_possible[$mission['subject_id']]);
				  if($data !== false && $mission_counter > $data['missions_required_total'])
				  {
					$data = next($medals_possible[$mission['subject_id']]);				
					echo '</OL>';
					if($data !== false)
					{
						//echo sprintf(T_('Then you could earn the <B>%s</B> medal by doing the following %s missions by %s:'),
						//	es($data['medal_name']), floatval($data['missions_required']), es(dateToHebrew($end_date)));
						echo $data['medal_name'] . " medal";
					}
					else
					{
						//echo sprintf(T_('Then to prepare for the next medal, you could also do the following additional missions by %s:'),
						//es(dateToHebrew($end_date)));
					}
					echo '<OL>';
				  }
			}			
	}
?>
</OL>
<?
foreach ($num_of_medals as $k => $v) {
	echo "Total $k medals: " . $v . "<br />";
	$total_medals[$k] += $v;
}
?>
<HR class="noprint">
<BR class="onepage_after">
<?endwhile;?>
<?
foreach ($total_medals as $k => $v) {
	echo "Grand Total $k medals: " . $v . "<br />";
}
?>
<BR style="clear: both;">
</DIV>
</DIV>
<? } ?>
</DIV>
</DIV>
<DIV class="noprint">
<? include('admin_footer.php'); ?>
</DIV>
</BODY>
</HTML>
