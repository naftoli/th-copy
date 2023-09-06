<?php $debug = false;
// enable debuging
if ($_GET['debug']) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}
/********************** AUTHENTICATION ******************/
$admin_auth = array('school');
require( dirname(__FILE__) . '/header.php' );

$ui_type = 'programs';
require_once( dirname(__FILE__) . '/admin_ui.php' );
require_once( dirname(__FILE__) . '/calendar.php' );
$action = gr( 'action' );
assure_id_school('school_id');
// GET/POST paramaters
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$subject_id = gri('subject_id', -1);

/********************** UPDATE THE SYSTEM ******************/
if( !empty($action) ) switch($action) {
	case 'save':
		$result = mq(
             " SELECT users.user_id FROM users LEFT JOIN classes USING (school_id, class_id) "
            ." WHERE user_registered IS NOT NULL AND school_id = $school_id " . 
            ( $class_id != -1 ? " AND class_id = $class_id " : '') 
        );
        $tracks = gra( 'tracks' ); // get the tracks from the GET/POST headers
        // get the start dates for the user
		$start_dates = gra( 'user_start_date' );
		while ( $row = mysql_fetch_assoc( $result ) ) {
			if ( isset( $tracks[ $row[ 'user_id' ] ] ) ) {
				foreach( $tracks[ $row['user_id'] ] as $subject_id => $data ) {
					$subject_id = intval( $subject_id );
					$track = intval( $data['track'] ) + 2;
					$level = max(6, min(intval($data['level']), 14));
					if($data['track'] == -1) {
						mq("UPDATE user_tracks SET enrolled = 0 WHERE user_id = {$row['user_id']} AND subject_id = $subject_id");
					} else {
						mq("INSERT INTO user_tracks SET user_id = {$row['user_id']}, subject_id = $subject_id, track_id = $track, level = $level ON DUPLICATE KEY UPDATE track_id = $track");
					}
				}
			}
		}
		$message = T_("Soldier's ladders edited");
		break;
	default:
		user_error('unknown action', E_USER_ERROR);
		break;
}

$class_result		= mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$tracks_result 		= mq("SELECT track_id, track_name FROM tracks ORDER BY track_name ");
$subjects_result	= mq("SELECT subject_name, subject_id, inst_name FROM subjects LEFT JOIN institutions USING (inst_id) WHERE subject_type NOT IN ('school_points', 'home_points') ORDER BY subject_name, subject_id");

$edit_result = false;
if (isset($_GETPOST['show']) && $_GETPOST['show'] == 'form') {
	$edit_result = mq(
         " SELECT users.user_id, users.first, users.last, users.username, user_start_date, "
        ." class_grade, class_sub, institutions.inst_name, subjects.subject_name, subjects.subject_id, "
        ." user_tracks.track_id, user_tracks.level, user_tracks.enrolled FROM users "
        ." JOIN subjects ON (subject_type NOT IN ('school_points', 'home_points', 'Tanya')) "
        ." JOIN school_type_subjects USING (subject_id, school_type_id) "
        ." LEFT JOIN classes USING (school_id, class_id) "
        ." LEFT JOIN user_tracks USING (subject_id, user_id) "
        ." LEFT JOIN institutions USING (inst_id) "
        ." WHERE user_registered IS NOT NULL AND school_id = $school_id " 
        . ($class_id != -1 ? " AND class_id = $class_id" : '') 
        . ($subject_id != -1 ? " AND subject_id = $subject_id" : '') 
        . ($admin_user['auth'] != 'super' ? ' AND institutions.inst_id IN (' . implode(',', $admin_user['inst_ids']) . ')' : '') 
        .' ORDER BY classes.class_grade, classes.class_sub, users.last, users.first, users.username, ' 
        ." institutions.inst_name, subjects.subject_name;"
    );
}

// find out if we are in the week prior to shabbos mevorchim
$jdNow = unixtojd();
$showTehillimQuota = 0;
require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
$heNow = jdtojewish($jdNow);
$heNowArr = explode("/", $heNow);
//if ($heNowArr[0] == 13) $year++;
$sm = calculateSM( $year );

// structure, val => schools pre-closed
$closed_schools = [
	2458160 => [255]
];

foreach ($sm as $val) {
//    echo "Now - " . $jdNow . " SM: " . $val . "<br />";
	if ($jdNow >= ($val - 6) && $jdNow <= $val && !in_array($school_id, $closed_schools[$val])) {
		$showTehillimQuota = 1;
		break;
	}
}
?>
<!DOCTYPE html>
<html DIR="<?=$dir?>">
	<head>
		<title><?=T_("Soldier's Ladders/Years"), ' - ', T_('Tzivos Hashem Management System')?></title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<script type="text/javascript">
		// Popup window code
		function newPopup(url) {
			popupWindow = window.open(
				url,
				'popUpWindow',
				'height=400,width=200,left=10,top=10,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no,status=yes'
			);
		}
		</script>
	</head>
	<body>
		<?include('admin_header.php');?>
		<div class="ui_<?=$ui_type?> <?=$align_start?>">
			<div class="body">
				<div class="sub_menu">
					<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
				</div>
				<h1><?=T_('Campaigns')?></h1>
				<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) { ?>
					<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
					<form action="admin_users_track.php" method="GET" accept-charset="UTF-8">
						<p>
							<label><?=T_('Select Institution')?>:
								<select name="school_id">
									<? while($school_row = mysql_fetch_assoc($school_result)) { ?>
										<option value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'selected' : ''?>><?=es($school_row['school_name'])?></option>
									<? } ?>
								</select>
							</label>
							<input class="submit" type="submit" value="<?=T_('Go')?>">
						</p>
					</form>
				<? } //  end if user has more then one school?>
	
				<div class="infobox" style="line-height: 1.2">
					<i>This feature (to change ladders) is only available the week before Shabbos Mevorchim, starting the Sunday of that week.</i><br />
					Click <a href="https://docs.google.com/document/d/1fGI4Dmd3hoD03dJhrSXOoThzEcb_IjIi22c-vLQorWg/edit?usp=sharing">here</a> for the SMT Manual.
				</div>
				<br />
	
				<? if($school_id == -1) { ?>
					<?=T_('Please select an Institution.')?>
				<? } else { ?>
					<div class="ui_body">
						<div class="ui_menu">
							<?ui_menu();?>
						</div>
						<div class="content">
							<div class="infobox2">
								<h2><?=T_("Soldier's Ladders/Years")?></h2>
								<form action="admin_users_track.php" method="GET" accept-charset="UTF-8">
									<p>
										<input type="hidden" name="school_id" value="<?=$school_id?>">
										<label><?=T_('Show only Platoon')?>:
											<select name="class_id">
												<option value="-1">&lt;<?=T_('All')?>&gt;
												<? while($class_row = mysql_fetch_assoc($class_result)) { ?>
													<option value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></option>
												<? } ?>
												</select>
										</label><BR>
										<input type='hidden' name='show' value='form' />
										<input class="submit" type="submit" value="<?=T_('Go')?>">
									</p>
								</form>
							</div>
	
							<?if($edit_result) { ?>
								<form action="admin_users_track.php" method="POST">
								<?php if ($showTehillimQuota) { ?>
									<p>
										<input type="submit" value="<?=T_('Save')?>">
										<input type="reset" value="<?=T_('Undo Changes')?>">
									</p>
								<?php } ?>
									<div>
										<input type="hidden" name="action" value="save">
										<input type="hidden" name="school_id" value="<?=$school_id?>">
										<input type="hidden" name="class_id" value="<?=$class_id?>">
										
										<table class="list list_<?=$align_start?>">
											<thead>
												<tr>
													<th><?=T_('Soldier')?></th>
													<?if($subject_id==-1) {?>
													<th><?=T_('Subject')?></th>
													<? } ?>
													<th><?=T_('Ladder')?></th>
												</tr>
											</thead>
											<!--
											<TR>
											  <TH<?if($subject_id==-1):?> colspan="2"<?endif;?>><?=T_('Change all')?>:</TH>
											  <TH>
												<SELECT name="track_all" disabled>
												  <OPTION value="-1">&lt;<?=T_('Subject Disabled')?>&gt;
												<?while($track_row = mysql_fetch_assoc($tracks_result)):?>
												<? if ($track_row['track_name'] == 6) break; ?>
													<? if ($track_row['track_name'] == 10) continue; ?>
												  <OPTION value="<?=$track_row['track_id']?>"><?=es($track_row['track_name'])?></OPTION>
												<?endwhile;?>
												<?mysql_data_seek($tracks_result, 0);?>
												</SELECT><BR>
												<INPUT type="button" value="<?=T_('Change All')?>" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('tracks')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[track]')==this.form.elements[i].name.length-7) this.form.elements[i].selectedIndex = this.form.elements['track_all'].selectedIndex;}">
											  </TH>
											  <!--
											  <TH>
												<INPUT type="text" name="level_all" maxlength="2" size="2" onChange="this.value = Math.max(3, Math.min(parseInt('0'+this.value, 10), 14));"><BR><INPUT type="button" value="<?=T_('Change All')?>" onClick="for(var i=0; i&lt;this.form.elements.length; i++) { if(this.form.elements[i].name.indexOf('tracks')==0 &amp;&amp; this.form.elements[i].name.lastIndexOf('[level]')==this.form.elements[i].name.length-7) { this.form.elements[i].value = this.form.elements['level_all'].value; this.form.elements[i].onchange();}}">
											  </TH>
											  <TH></TH>
											  -->
												<!--
											</TR>
											-->
											<? $old_user_id = -1; ?>
											<? while($row = mysql_fetch_assoc($edit_result)) { ?>
												<? if ($row['subject_id'] != 1) continue; ?>
												<tr>
													<? if($old_user_id != $row['user_id']) {?>
														<td><?=$row['class_grade'], '-', $row['class_sub'], ': ', $row['first'], ' ', $row['last']?></td>
													<? } else { ?>
														<td style="border-bottom: none; border-top: none;"></td>
													<? } ?>
													<? if($subject_id==-1) {?>
														<td><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name'])?></td>
													<? } // if subject_id is -1
													$today = jdtojewish(unixtojd());
													$arrToday = explode('/', $today);
													$month = $arrToday[0];
													
													if ($month == 13) $month = 1;
													else $month++;
													
													$sqlTrack = "SELECT * FROM user_tracks WHERE subject_id = 1 AND enrolled = 1 AND user_id = " . $row['user_id'];
													$resultTrack = mysql_query($sqlTrack);
													$rowTrack = mysql_fetch_assoc($resultTrack);
													
													$sqlInfo = "SELECT * FROM tehillim_ladders WHERE ladder = " . $rowTrack['track_id'] . " AND age = " . $rowTrack['level'] . " AND month = " . $month;
													$resultInfo = mysql_query($sqlInfo);
                                                    $rowInfo = mysql_fetch_assoc($resultInfo);
                                                    
                                                    $level_query = mysql_query(
                                                        "SELECT level FROM user_tracks WHERE user_id = '".$row['user_id']."' AND level IS NOT NULL LIMIT 1"
                                                    );
                                                    $level = mysql_num_rows($level_query) ? mysql_fetch_assoc( $level_query )['level'] : 6;
													?>
													<td>
														<select name="tracks[<?=$row['user_id']?>][<?=$row['subject_id']?>][track]" <?php if (!$showTehillimQuota) echo "disabled"?>>
															<option value="-1">&lt;<?=T_('Subject Disabled')?>&gt;
															<? while($track_row = mysql_fetch_assoc($tracks_result)) { ?>
																<? if ($track_row['track_name'] == 6) break; ?>
																<? if ($track_row['track_name'] == 10) continue; ?>
																<option value="<?=$track_row['track_id']?>" <?=$track_row['track_id'] == ($rowTrack['track_id']-2) ? 'SELECTED' : ''?>><?=es($track_row['track_name'])?></option>
															<? } // end while loop?>
															<? mysql_data_seek($tracks_result, 0); ?>
														</select>
														<br />
														<div style="font-size: 12px;">
															Kapitelach: <?=$rowInfo['kapitelach']?><br />
															Minutes: <?=$rowInfo['minutes']?><br />
															Year/Age: <?=$level?>
														</div>
													</td>
													<input type="hidden" name="tracks[<?=$row['user_id']?>][<?=$row['subject_id']?>][level]" value="<?=es($level)?>" />
												</tr>
											<? } ?>
										</table>
										<?php if ($showTehillimQuota) { ?>
										<p>
											<input type="submit" value="<?=T_('Save')?>">
											<input type="reset" value="<?=T_('Undo Changes')?>">
										</p>
										<?php } ?>
									</div>
								</form>
							<? } ?>
							<br style="clear: both;">
						</div>
					</div>
				<? } ?>
			</div>
		</div>
		<? include('admin_footer.php'); ?>
	</body>
</html>
