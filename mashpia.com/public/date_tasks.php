<? 
require('header.php');
require_once('calendar.php');
require_once('file_save.php');

$user_row = mysql_fetch_assoc(mq("
SELECT user_id, first, last, first_he, last_he, username, gender, user_address1, user_address2,
       user_city, user_state, user_postal, user_country, user_phone,
       user_serial, user_photo_id, class_id, class_grade, class_sub, class_teacher, team_id,
       team_name, school_name, school_number, school_logo_id, school_logo_kiosk_id, inst_logo_id, school_type_id, rank_name, rank_image_id, rank_color, user_start_date, kiosk_edit
FROM users
     LEFT JOIN schools USING (school_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN classes USING (school_id, class_id)
     LEFT JOIN teams USING (school_id, team_id)
     LEFT JOIN (SELECT user_id, MAX(rank_ord) rank_ord FROM rank_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) rank USING (user_id)
     LEFT JOIN ranks USING (rank_ord)
WHERE user_id = {$user['user_id']}
ORDER BY class_grade, class_sub, last, first
"));

$end_date = unixtojd();
$start_date = max($end_date - 21, intval($user_row['user_start_date']));
$max_entries = 40;

$date_tasks_mission_id = gri('date_tasks_mission_id', -1);

$sql = "SELECT subject_id, subject_name, subject_image_id, inst_name, date_tasks_missions.mission_name, mission_description, mission_number, (SELECT COUNT(*) FROM user_mission_entries WHERE user_id = {$user['user_id']} AND user_mission_entries.subject_id = subjects.subject_id) num_entries, EXISTS (SELECT * FROM user_mission_entries WHERE user_id = {$user['user_id']} AND entry_type = 'date_tasks_missions' AND entry_id = date_tasks_missions.date_tasks_mission_id) prev_entry
FROM subjects
     JOIN school_subjects USING (subject_id)
     JOIN school_type_subjects USING (subject_id)
     JOIN users USING (school_id, school_type_id)
     JOIN user_tracks USING (user_id, subject_id)
     JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id)
     LEFT JOIN institutions USING (inst_id)
     LEFT JOIN tracks USING (track_id)
WHERE user_id = {$user['user_id']}
      AND enrolled = 1
      AND start_date <= $end_date
      AND end_date >= $start_date
      AND user_registered IS NOT NULL
      AND (
        NOT EXISTS (SELECT * FROM date_tasks JOIN date_tasks_marks USING (date_task_id) WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND user_id = {$user['user_id']})
        OR
        EXISTS (SELECT * FROM user_mission_entries WHERE user_id = {$user['user_id']} AND entry_type = 'date_tasks_missions' AND entry_id = date_tasks_missions.date_tasks_mission_id)
      )
      AND NOT EXISTS (SELECT * FROM date_tasks JOIN date_tasks_marks USING (date_task_id) JOIN date_tasks_missions date_tasks_missions_alt USING (date_tasks_mission_id) WHERE date_tasks_missions_alt.subject_id = date_tasks_missions.subject_id AND (date_tasks_missions_alt.mission_number = date_tasks_missions.mission_number OR (date_tasks_missions.mission_number IS NULL AND date_tasks_missions_alt.start_date = date_tasks_missions.start_date AND date_tasks_missions_alt.end_date = date_tasks_missions.end_date)) AND date_tasks_missions_alt.date_tasks_mission_id != date_tasks_missions.date_tasks_mission_id AND user_id = {$user['user_id']})
      AND date_tasks_mission_id = $date_tasks_mission_id
ORDER BY inst_name, subject_name, subject_id, mission_number, mission_name
";
//echo "<input type='hidden' name='SQL' value='" . $sql . "'>\n";
$mission = mysql_fetch_assoc(mq($sql));


// TODO  per subject # missions

if($mission && ($tasks = gra('tasks')) && $user['registered']) {
if($user_row['kiosk_edit'] === '' && ($mission['prev_entry'] || $mission['num_entries'] < $max_entries)) {
  $user_mission_entries = false;
  foreach($tasks as $date_task_id => $data) {
    $date_task_id = intval($date_task_id);
    $done = count(array_filter($data['done']));
    $quantity = isset($data['quantity']) ? intval($data['quantity']) : 0;
    if(!$done && !$quantity) {
      mq("DELETE FROM date_tasks_marks WHERE date_task_id = $date_task_id AND user_id = {$user['user_id']}");
    } else {
      $user_mission_entries = true;
      mq("INSERT INTO date_tasks_marks (date_task_id, user_id, mark_date, done_qty, mark_description, mark_points, mark_quantity) SELECT date_task_id, {$user['user_id']} user_id, " . unixtojd() . " mark_date, LEAST(IF(quantity IS NULL, $done, $quantity/quantity), mandatory_qty+optional_qty) done_qty, IF(description = '', name, description) description, IF(quantity IS NULL, LEAST($done, mandatory_qty+optional_qty)*points, LEAST($quantity, quantity*(mandatory_qty+optional_qty))*(points/quantity)) mark_points, $quantity mark_quantity FROM date_tasks WHERE date_task_id = $date_task_id ON DUPLICATE KEY UPDATE done_qty = VALUES(done_qty), mark_description = VALUES(mark_description), mark_points = VALUES(mark_points), mark_quantity = VALUES(mark_quantity)");
    }
  }

  if($user_mission_entries) {
    mq("INSERT IGNORE user_mission_entries (user_id, entry_id, entry_type, subject_id) VALUES ({$user['user_id']}, $date_tasks_mission_id, 'date_tasks_missions', {$mission['subject_id']})");
  } else {
    mq("DELETE FROM user_mission_entries WHERE user_id = {$user['user_id']} AND entry_id = $date_tasks_mission_id AND entry_type = 'date_tasks_missions'");
  }

  mq("
INSERT IGNORE INTO date_tasks_mission_marks (user_id, date_tasks_mission_id, subject_id, mission_value, mission_name, mark_date)
SELECT {$user['user_id']} user_id, date_tasks_mission_id, subject_id, mission_value, mission_name, " . unixtojd() . " mark_date
FROM date_tasks_missions
WHERE  date_tasks_mission_id = $date_tasks_mission_id AND
       NOT EXISTS (
        SELECT *
        FROM date_tasks
             LEFT JOIN date_tasks_marks ON (date_tasks.date_task_id = date_tasks_marks.date_task_id AND date_tasks_marks.user_id = {$user['user_id']})
        WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND (IFNULL(done_qty, 0) < mandatory_qty OR (quantity IS NOT NULL AND IFNULL(mark_quantity, 0) < quantity*mandatory_qty))
       )
  ");

  mq("DELETE FROM medal_marks USING medal_marks JOIN subjects USING (subject_id) JOIN medals_subjects_totals USING (medal_ord, subject_id) LEFT JOIN (SELECT SUM(mission_value) missions, subject_id, user_id, MAX(mark_date) date_awarded FROM date_tasks_mission_marks JOIN users USING (user_id) JOIN subjects USING (subject_id) WHERE user_id = {$user['user_id']} GROUP BY subject_id, user_id) missions_done USING (subject_id, user_id) WHERE user_id = {$user['user_id']} AND (missions_required_total > missions OR missions IS NULL) AND subject_type != 'Tanya'");
  mq("INSERT IGNORE INTO medal_marks (medal_ord, subject_id, user_id, date_awarded) SELECT medal_ord, missions_done.subject_id, user_id, date_awarded FROM (SELECT SUM(mission_value) missions, subject_id, user_id, MAX(mark_date) date_awarded FROM date_tasks_mission_marks JOIN subjects USING (subject_id) WHERE user_id = {$user['user_id']} GROUP BY subject_id, user_id) missions_done JOIN medals_subjects_totals ON (missions_done.subject_id = medals_subjects_totals.subject_id AND missions >= missions_required_total)");
  mq("INSERT IGNORE INTO rank_marks (rank_ord, user_id, date_promoted) SELECT rank_ord, user_id, date_awarded date_promoted FROM (SELECT COUNT(*) medals, user_id, MAX(date_awarded) date_awarded FROM medal_marks WHERE user_id = {$user['user_id']} GROUP BY user_id) medals_done JOIN ranks ON (medals >= medals_required)");
}
  if($user_row['kiosk_edit'] == 'frozen')
    $update = '?m=frozen';
  elseif(!$mission['prev_entry'] && $mission['num_entries'] >= $max_entries)
    $update = '?m=max';
  elseif($user_row['kiosk_edit'] == '')
    $update = '?m=update';
  else // or if($user_row['kiosk_edit'] == 'off')
    $update = '';
  header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/missions.php' . $update);
}

$tasks = mq("SELECT date_tasks.date_task_id, name, description, mandatory_qty, optional_qty, quantity, points, done_qty, mark_quantity, label_name, label_image_id FROM date_tasks LEFT JOIN labels USING (label_id) LEFT JOIN date_tasks_marks ON (date_tasks.date_task_id = date_tasks_marks.date_task_id AND user_id = {$user_row['user_id']}) WHERE date_tasks_mission_id = $date_tasks_mission_id AND (NOT EXISTS (SELECT * FROM date_tasks_dates WHERE date_task_id = date_tasks.date_task_id) OR EXISTS (SELECT * FROM date_tasks_dates WHERE date_task_id = date_tasks.date_task_id AND nominal_date >= $start_date AND nominal_date <= $end_date)) AND (mandatory_qty > 0 OR quantity IS NOT NULL) ORDER BY ord");

$bonus_tasks = mq("SELECT date_tasks.date_task_id, name, description, mandatory_qty, optional_qty, quantity, points, done_qty, mark_quantity, label_name, label_image_id FROM date_tasks LEFT JOIN labels USING (label_id) LEFT JOIN date_tasks_marks ON (date_tasks.date_task_id = date_tasks_marks.date_task_id AND user_id = {$user_row['user_id']}) WHERE date_tasks_mission_id = $date_tasks_mission_id AND (NOT EXISTS (SELECT * FROM date_tasks_dates WHERE date_task_id = date_tasks.date_task_id) OR EXISTS (SELECT * FROM date_tasks_dates WHERE date_task_id = date_tasks.date_task_id AND nominal_date >= $start_date AND nominal_date <= $end_date)) AND (optional_qty > 0 AND quantity IS NULL) ORDER BY ord");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Enter Mission Tasks'), ' - ', T_('Chayolei Tzivos Hashem')?></TITLE>
		<LINK href="styles_reset.css" rel="stylesheet" type="text/css">
		<LINK href="styles_kiosk.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="modules/jquery.scroll.js"></SCRIPT>
		<LINK href="modules/jquery.scroll.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="modules/jquery.checkbox.js"></SCRIPT>
		<LINK href="modules/jquery.checkbox.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="modules/jquery.keypad.js"></SCRIPT>
		<LINK href="modules/jquery.keypad.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript">
			$(function() {
				$('.scroll-pane').jScrollPane({showArrows:true, scrollbarWidth: 42, arrowSize: 42});
				$('input[type=checkbox]').checkbox();
				$('.keypad').keypad({buttonImage: 'modules/images/keypad_btn.png'});
			});
		</SCRIPT>
	</HEAD>
	
	<body class="blue">
	
		<div id="wrapper">
		
			<div id="header">
			
				<div class="org">
				
					<div class="nav">
					
						<ul>
							<li class="icon_back"><a href="missions.php"><?=T_('Back')?></a>
							<li class="icon_home"><a href="kiosk.php"><?=T_('Home')?></a></li>
							<li class="icon_logout"><a href="logout.php?n=kiosk.php"><?=T_('Logout')?></a></li>
						</ul>
						
					</div> <!-- nav -->
					
					<div class="org_photo">
						<?=!is_null($user_row['school_logo_kiosk_id']) ? linkImgFile($user_row['school_logo_kiosk_id'], null, 100) : (!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'], null, 100) : '')?>
					</div> <!-- org_photo -->
					
					<?=T_('Base')?>: #<?=$user_row['school_number']?><BR>
					<?=es($user_row['school_name'])?><BR>
					<?=es($user_row['rank_name'])?> <?=es($user_row['first'])?> <?=es($user_row['last'])?> <!--(<?=es($user_row['username'])?>)--> <?=es($user_row['first_he'])?> <?=es($user_row['last_he'])?>
					
				</div> <!-- org -->
				
				<noscript>
					<p class="js_alert">Notice: You have javascript disabled.<br>Some parts of the site will not function without javascript.</p>
				</noscript>
				
			</div> <!-- header -->

			<div id="main">
			
				<div id="page_title">
					<?=T_('Enter Mission Tasks')?>
				</div>
				
				<div class="three_column padding_top">
				
					<div class="content">
					
						<div id="slider">
						
							<ul class="report">
							
								<li>
			  
								<?if(!$user['registered']):?>
				
									<div class="slider_title">
										<?=T_('You are not currently registered in Tzivos Hashem.')?><BR>
										<?=T_('Please see the program director at your school.')?>
									</div>
				  
								<?elseif($user_row['kiosk_edit'] == 'off'):?>
				
									<div class="slider_title">
										<?=T_('Kiosk entry is disabled.')?><BR>
										<?=T_('Please see the program director at your school.')?>
									</div>
				  
								<?elseif(!$mission || (!mysql_num_rows($tasks) && !mysql_num_rows($bonus_tasks))):?>
				
									<p class="padding_top"><?=T_('No tasks are available for editing in this mission.')?></p>
				  
								<?else:?>
								
									<div class="slider_title">
										<?=es($mission['subject_name']), ': ', es($mission['mission_name']), is_null($mission['mission_number']) ? '' : ' #' . floatval($mission['mission_number'])?><BR>
										<?=es($mission['mission_description'])?>
									</div>
									
									<div class="scroll-pane">
									<?if(!$mission['prev_entry'] && $mission['num_entries'] >= $max_entries):?><p class="padding_top"><?=T_('You can not make any more entries, you need to turn in your paperwork to your Base Commander, and ask them to sign off on your previous entries.')?></p><?endif;?>
										<FORM action="date_tasks.php" method="post" accept-charset="UTF-8" name="user_tasks">
										<? $task_ids = array(); ?>
										<div class="mainbox">

										<!-- Mission and quantity tasks -->
										<?if(mysql_num_rows($tasks)):?>
										<div class="boxes">
											<div class="title"><?=T_('Mission Tasks')?></div>
											<? while($task = mysql_fetch_assoc($tasks)):?>
												<? $task_ids[] = $task['date_task_id']; ?>
												<?if(is_null($task['quantity'])):?>
											<?for($i = 0; $i < $task['mandatory_qty']; $i++):?>
											<div class="question" style="padding-left: 5px;">
												<div class="checkbox">
													<INPUT type="checkbox" name="tasks[<?=$task['date_task_id']?>][done][]" value="1" <?=$task['done_qty'] > $i ? 'CHECKED' : ''?>>
												</div>
												<P>
													<?=!is_null($task['label_image_id']) ? linkImgFile($task['label_image_id'], null, null, 'style="vertical-align: top; float: ' . $align_start . ';"') : ''?>
													<?=es($task['name'])?><BR>
													<?=es($task['description'])?><BR>
													<?=sprintf(($task['points'] == 1 ? T_('%s mile for completing this task') : T_('%s miles for completing this task')),  floatval($task['points']))?>
												</P>
											</div>
											<?endfor;?>
										<?else:?>		
											<div class="question" style="padding-left: 5px;">
												<div class="input">
													<INPUT type="text" class="keypad" name="tasks[<?=$task['date_task_id']?>][quantity]" size="5" maxlength="5" value="<?=$task['mark_quantity']?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 65535));">
												</div>
												<P>
													<?=!is_null($task['label_image_id']) ? linkImgFile($task['label_image_id'], null, null, 'style="vertical-align: top; float: ' . $align_start . ';"') : ''?>
													<?=es($task['name'])?><BR>
													<?=es($task['description'])?><BR>
													<?=sprintf(($task['points'] == 1 ? T_('Quota: %s mile') : T_('Quota: %s miles')),  floatval($task['points']))?>,
													<? $each = @round($task['points']/($task['quantity']*$task['mandatory_qty']), 2); ?>
													<?=sprintf(($each == 1 ? T_('%s mile each') : T_('%s miles each')), $each)?>
												</P>
											</div>
										<?endif;?>
									<? endwhile; ?>
								</div>
                        <?endif;?>

                        <!-- Bonus tasks -->
											<?if(mysql_num_rows($bonus_tasks)):?>
												<div class="boxes">
													<div class="title">
														<?=T_('Bonus Tasks')?>
													</div>
											<? while($task = mysql_fetch_assoc($bonus_tasks)):?>
												<? $task_ids[] = $task['date_task_id']; ?>
												<?for($i = 0; $i < $task['optional_qty']; $i++):?>
													<div class="question" style="padding-left: 5px;">
														<div class="checkbox">
															<INPUT type="checkbox" name="tasks[<?=$task['date_task_id']?>][done][]" value="1" <?=$task['done_qty'] > $i + $task['mandatory_qty'] ? 'CHECKED' : ''?>>
														</div>
														<P>
															<?=!is_null($task['label_image_id']) ? linkImgFile($task['label_image_id'], null, null, 'style="vertical-align: top; float: ' . $align_start . ';"') : ''?>
															<?=es($task['name'])?><BR>
															<?=es($task['description'])?><BR>
															<?=sprintf(($task['points'] == 1 ? T_('%s mile for completing this task') : T_('%s miles for completing this task')),  floatval($task['points']))?>
														</P>
													</div>
												<?endfor;?>
											<? endwhile; ?>
												</div>
										<?endif;?>
											</div>
											<div class="button button_icons">
										<? foreach($task_ids as $task_id): ?>
												<INPUT type="hidden" name="tasks[<?=$task_id?>][done][]" value="0">
										<? endforeach; ?>
												<INPUT type="hidden" name="date_tasks_mission_id" value="<?=$date_tasks_mission_id?>">
												<div>
													<a href="#" class="icon_save" onClick="$(this).parents('form').get(0).submit(); return false;"><?=T_('Save')?></a>
												</div>
											</div>
										</FORM>
									</div>
								<?endif;?>
								</li>
								
							</ul>
							
						</div> <!-- slider -->
						
					</div> <!-- content -->
					
				</div> <!-- three_column -->
				
			</div> <!-- main -->

			<div id="footer">
				<div class="footer_logo"></div>
				<div class="footer_logout"></div>
			</div>

		</div> <!-- wrapper -->
		
	</body>
</html>
