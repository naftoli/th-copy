<?php
$admin_auth = array('user', 'school'); 
require('header.php');

$action = gr('action', 'edit');
$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
if ( isset( $_POST['user_id'] ) ) 
	$user_id = $_POST['user_id'];
else 
	$user_id = gri('user_id', -1);
session_start();
$_SESSION["child_id"] = $user_id;

//print_r( $admin_user['auths']['user'] );
//get children of admin
$users = $admin_user['auths']['user'];
if ( $user_id == -1 ) {
	$user_id = $users[0];
}

//if ($auth_mode == 'user') 
	//check_school_setting($user_id, 'home_school');

//$user_row = mysql_fetch_assoc(mq("SELECT username, first, last, school_id, school_type_id, school_type_name FROM users LEFT JOIN school_types USING (school_type_id) WHERE user_id = $user_id AND school_id = $school_id"));
$user_row = mysql_fetch_assoc(mq("
	SELECT username, first, last, users.school_id, child_type_id, child_type_name, school_type_id, school_type_name, class_grade, class_sub  
	FROM users LEFT JOIN school_types USING (school_type_id) 
	LEFT JOIN child_types USING (child_type_id) 
	join classes on (users.class_id = classes.class_id) 
	WHERE user_id = $user_id "));
if (!$user_row) 
	user_error('can not locate user', E_USER_ERROR);
	
$edit_result = false;

if (!empty($action)) {

	switch($action) {
		case 'edit':
		    $sql2 = "
            SELECT institutions.inst_name, subjects.subject_name, subjects.subject_id, user_tracks.track_id, user_tracks.level, enrolled 
            FROM subjects 
            JOIN school_subjects USING (subject_id) 
            JOIN school_type_subjects 
                ON (subject_type NOT IN ('school_points', 'home_points') 
                    AND subjects.subject_id = school_type_subjects.subject_id 
                    AND school_type_subjects.school_type_id = {$user_row['school_type_id']}) 
            LEFT JOIN user_tracks ON (subjects.subject_id = user_tracks.subject_id AND user_id = $user_id) 
            LEFT JOIN institutions USING (inst_id) 
            WHERE subject_type NOT IN ('school_points', 'home_points')  
			and subjects.subject_id = 1 
            AND school_id = {$user_row['school_id']} 
            ORDER BY institutions.inst_name, subjects.subject_name";
            //echo $sql2;
			$edit_result = mq($sql2);
		 
		 /*
			$edit_result = mq("
			SELECT institutions.inst_name, subjects.subject_name, subjects.subject_id, user_tracks.track_id, user_tracks.level, enrolled 
			FROM subjects 
			JOIN school_subjects USING (subject_id) 
			JOIN user_tracks ON (subjects.subject_id = user_tracks.subject_id AND user_id = $user_id) 
			LEFT JOIN institutions USING (inst_id) 
			WHERE subject_type NOT IN ('school_points', 'home_points', 'Tanya') 
			AND school_id = {$user_row['school_id']} 
			ORDER BY institutions.inst_name, subjects.subject_name");
		  * 
		  */
		break;

		case 'edit2':
			//var_dump(gra('subject'));
			foreach(gra('subject') as $subject_id => $data) {
				$subject_id = intval($subject_id);
				$track = intval($data['track']);
				//$level = max(6, min(intval($data['level']), 14));
				//$enrolled = intval($data['enrolled']);
				
				if ($data['track'] == -1) 
					mq("DELETE FROM user_tracks WHERE user_id = $user_id AND subject_id = $subject_id");
				else 
					//mq("INSERT INTO user_tracks SET user_id = $user_id, subject_id = $subject_id, track_id = $track, level = $level, enrolled = $enrolled ON DUPLICATE KEY UPDATE track_id = VALUES(track_id)");
					mq("UPDATE user_tracks set track_id = $track where user_id = $user_id and subject_id = $subject_id");
									
				//if enrolling into yoma depagra we need to create birthday mission				
				//if ($subject_id == 40) {
				//	require_once 'class.birthday.php';
				//	$b = new Birthday($user_id);
				//	$b->setBirthday();
				//}
				mq("DELETE FROM user_tracks USING user_tracks LEFT JOIN school_type_subjects ON (user_tracks.subject_id = school_type_subjects.subject_id AND school_type_subjects.school_type_id = {$user_row['school_type_id']}) WHERE user_id = $user_id AND school_type_subjects.subject_id IS NULL");
			}
			
			$message = T_("Soldier's ladders edited");
		break;

		default:
			user_error('unknown action', E_USER_ERROR);
		break;
	}
	
}

$tracks_result = mq("SELECT track_id, track_name FROM tracks where track_id not in (1,2,8,9,10) ORDER BY CAST(track_name AS SIGNED), track_name");
$subject_tracks_result = mysql_fetch_column_tuple(mq("SELECT subject_id, track_id FROM date_tasks_missions GROUP BY subject_id, track_id ORDER BY subject_id"));
$subject_level_result = mysql_fetch_column(mq("SELECT subject_id, MIN(level) min_level, MAX(level) max_level FROM date_tasks_missions GROUP BY subject_id ORDER BY subject_id"));

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_("Shabbos Mevorchim Tehillim Ladders"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript">
// Popup window code
function newPopup(url) {
	popupWindow = window.open(
		url,'popUpWindow','height=400,width=200,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes')
}
</script>		
	</HEAD>
	
	<BODY>
		<?include('admin_header.php');?>
		
		<DIV CLASS="body <?=$align_start?>">
		
			<H1><?=T_("Shabbos Mevorchim Tehillim Ladders")?></H1>
			<!--
			<DIV class="infobox">
				<?=T_('Instructions')?>
				<BR>
<!--				<DL>
					<DT>
						<?=T_('For all campaigns except Tehillim and Tanya choose from ladder 1 or 2')?>
						<DD>
							<OL style="margin: 0px; padding: 0px;">
								<LI><?=T_('Ladder One = Basic missions, suggested fro children up to grade 2')?>
								<LI><?=T_('Ladder Two = Advanced missions, suggested for children grade 3 and up')?>
							</OL>
				</DL>
				<P>
								<br />Click <a href="JavaScript:newPopup('http://www.mashpia.com/chart.html');">here</a> to use a chart to help you 
								decide what year to put your child on to.
				</P>
				<?//=sprintf(T_('For Tehillim please %sprint the yearly growth plan%s for your grade and get each child to choose from one of ten ladders that will work best for them.'), "<A href='admin_print_pdf.php?school_id=$school_id&amp;action=print&amp;class_id=$class_id&amp;user_id=$user_id&amp;type=tbp_growth_planner'>", '</A>')?>			
			</DIV>
			-->
			<? if (!empty($message)):?>
				<H2><?=$message?></H2>
			<?endif;?>
			
			<? if (gr('back')) : ?>
				<A HREF="<?=es(gr('back'))?>school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>&amp;user_id=<?=$user_id?>"><?=es(gr('back_text'))?></A>
			<? //elseif ($auth_mode != 'user') : ?>
			<? elseif (empty($admin_user['auths']['user'])) : ?>
				<A HREF="admin_user.php?school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=T_('Back to Soldier list')?></A>
			<? endif; ?>
			
			<B>Campaign - שבת מברכים תהילים</B>
			
			<?
			if (isset( $users ) && !empty( $users )) {
				$usersNames = array();
				$sqlChildren = "select user_id, first, last from users where user_id in (" . implode(',', $users) . ")";
				$sqlResult = mysql_query( $sqlChildren );
				while ($row = mysql_fetch_assoc($sqlResult)) {
					$usersNames[$row['user_id']] = $row['first'] . ' ' . $row['last'];
				}
				echo "<form action='admin_parent_user_track.php' method='post'>";
				echo "<select name='user_id'>";
				foreach ($usersNames as $id => $name) {
					echo "<option value=" . $id;
					if ($id == $user_id) echo " selected='selected' ";
					echo ">" . $name . "</option>";
				}
				echo "<input type='submit' name='submit' value='go' />";
				echo "</form>";
			}
			
			?>
			
			<? if ($user_row && $edit_result) : ?>
				<h3>
					Choose the best ladder for <?=$user_row['first'] . ' ' . $user_row['last']?> - Grade <?=$user_row['class_grade']?>
				</h3>
				
				<div style="padding: 10px;">
					<img src="downloads/tehillim/<?=$user_row['class_grade']?>.jpg" />
				</div>
				<!--
				<H2>
					<?=es(sprintf(T_('For: %s %s'), $user_row['first'], $user_row['last']))?>
				</H2>
				
				<P>
					<?=T_('Mission Type')?>: <?=es($user_row['child_type_name'])?>
					<BR>
				</P>
				-->
				<FORM action="admin_parent_user_track.php" method="post" accept-charset="UTF-8" name="user_tracks">
					<DIV style="line-height: 1.6">
						<INPUT type="hidden" name="action" value="edit2">
						<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
						
						
							<?while($row = mysql_fetch_assoc($edit_result)):?>
							<? if ($row['subject_id'] != 1) continue; ?>
							 
							<SELECT name="subject[<?=$row['subject_id']?>][track]"<?//=$disabled;?> style="width: 100px;">
								<?while($track_row = mysql_fetch_assoc($tracks_result)):?>
								<?if(isset($subject_tracks_result[$row['subject_id']]) && !isset($subject_tracks_result[$row['subject_id']][$track_row['track_id']])) continue;?>
								<? if ($row['subject_id'] != 1 && $track_row['track_id'] != 1) continue; ?>
								<OPTION value="<?=$track_row['track_id']?>" <?=$track_row['track_id'] == $row['track_id'] ? 'SELECTED' : ''?>>Ladder <?=es($track_row['track_name'])?></OPTION>
								<?endwhile;?>
								<?mysql_data_seek($tracks_result, 0);?>
							</SELECT>

							<? 
							if ($row['subject_id'] == 1) {
							    if ($row['level'] && $row['track_id']) {
							    	$now = unixtojd();
							    	$sm = calculateSM( 5775 );
									foreach ($sm as $key => $value) {
										if ($value > $now) {
											$start = $sm[$key];
											break;
										}
									}
									
									$months = array(
							            1   =>  'Cheshvon', 
							            2   =>  'Kislev', 
							            3   =>  'Teves', 
							            4   =>  'Shevat', 
							            5   =>  'Adar', 
							            6   =>  'Adar II', 
							            7   =>  'Nissan',  
							            8   =>  'Iyar', 
							            9   =>  'Sivan', 
							            10  =>  'Tamuz', 
							            11  =>  'Av', 
							            12  =>  'Elul', 
							            13  =>  'Tishrei'
							        );
									
    								$sql = "
    									select dt.ord, dt.quantity, dtm.speed from date_tasks dt 
    									join date_tasks_missions dtm using (date_tasks_mission_id) 
    									where dtm.subject_id = 1 
    									and dtm.level = " . $row['level'] . " 
    									and dtm.track_id = " . $row['track_id'] . " 
    									and dtm.start_date = " . $start . "       
    									group by name, quantity";
    								$result = mysql_query($sql);
    								while ($row = mysql_fetch_assoc($result)) {
    									if ($row['ord'] == 1) {
    										$kapitlach = $row['quantity'];
    									} else if ($row['ord'] == 2) {
    										$minutes = $row['quantity'];
    									}
										$speed = $row['speed'];
    								}
    								if (isset($kapitlach) && isset($minutes)) {
    									echo " <b>" . $months[$key] . " Quota:</b> $kapitlach kapitlach; $minutes minutes <b>Average Speed:</b> " . $speed . " minutes per kapitul.";
										echo "<div style='font-size: 12px;'>New quota will only change after saving.</div>";
									}	
								}
							}
							?>
							<?endwhile;?>
						</TABLE>
						
						<P>
							<INPUT type="submit" value="<?=T_('Save')?>">
						</P>
					</DIV>
				</FORM>
				
	<? endif; ?>
	</DIV>
	<? include('admin_footer.php'); ?>
	</BODY>
	
</HTML>
