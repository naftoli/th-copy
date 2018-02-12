<? // start the script after the catch all title
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
} else {
	$debug = false;
}

ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

require_once 'class.globalSettings.php';
$year = GlobalSettings::GetCurrentYear();

$sm = calculateSM( $year );
$months = array(
	0	=>	'Tishrei', 
	1	=>	'Cheshvon', 
	2	=>	'Kislev',
	3	=>	'Teves', 
	4	=>	'Shvat', 
	5	=>	'Adar I', 
	6	=>	'Adar II', 
	7	=>	'Nissan', 
	8	=>	'Iyar', 
	9	=>	'Sivan', 
	10	=>	'Tamuz', 
	11	=>	'Av', 
	12	=>	'Elul' 
);
//echo "<pre>"; print_r($sm); echo "</pre>"; exit;
//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;

if (isset($_POST['submit'])) {
	
	if($debug) echo "<pre>";
	if($debug) print_r($_POST);
	if($debug) echo "</pre>";
	
	$date = $_POST['date'];
	$school = $_POST['school'];
	$grade = $_POST['grade'];
	//echo $date;
	//exit;
	if ($date == 0 || $school == 0) {
		header("Location: mark_tehillim2.php" + ($debug ? "?debug=true" : ""));
		exit;
	}
	
	$sql = "select class_grade, class_sub from classes where class_id = " . $grade;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$gradeName = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	
	$users = array();
	$userInfo = array();
	// generate the SQL
	$sql = 	"SELECT * FROM users JOIN classes USING (class_id) "
			."WHERE user_registered > 0 "
			."AND users.school_id = " . $school . " ";
	if($grade) $sql .= "AND users.class_id = " . $grade . " ";
	else $sql .= "AND classes.school_id = '$school' ";
	$sql .=	"ORDER BY class_grade, last, first";

	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$users[] = $row['user_id'];
		$userInfo[$row['user_id']] = $row;
	}
	
	$tehillim = array();
	foreach ($users as $user) {
		$sql = "select u.school_type_id, u.lang_id, ut.level, ut.track_id 
				from user_tracks ut 
				join users u using (user_id) 
				where ut.subject_id = 1 
				and ut.user_id = " . $user;
		//if ($user == 20757) echo $sql . "<br />";
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		if ($row['level'] && $row['track_id']) {
			$level = $row['level'];
			$lang = $row['lang_id'];
			$track = $row['track_id'];
			$type = $row['school_type_id'];
			
			$sql = "select date_tasks_mission_id 
					from date_tasks_missions 
					where subject_id = 1
					and lang_id = " . $lang . " 
					and level = " . $level . " 
					and track_id = " . $track . " 
					and school_type_id = " . $type . " 
					and start_date = " . $date . " 
					and end_date = " . $date;
			//if ($user == 22946) echo $sql . "<br />";
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$mission_id = $row['date_tasks_mission_id'];
			
			if ($mission_id) {
				$tasks = array();
				$sql = "select * from date_tasks where date_tasks_mission_id = " . $mission_id;
				//if ($user == 20757) echo $sql . "<br />"; exit;
				$result = mysql_query($sql);
				while ($row = mysql_fetch_assoc($result)) {
					$tasks[] = $row;
				}
				$tehillim[$user] = $tasks;
			}
		}
	}
	//echo "<pre>"; print_r($tehillim); echo "</pre>";
	$mark = true;
	if (isset($_POST['oldGrade']) && $_POST['oldGrade'] != $grade) $mark = false;
	if (isset($_POST['marks']) && $mark) {
		//echo count($_POST['kapitelach']);
		//echo "<br />" . count($_POST['minutes']);
		//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
		$qrys = array();
		$types = array('kapitelach', 'minutes');
        $grids = array(
            'kapitelach'    => 8001,
            'minutes'       => 8002
        );
		foreach ($types as $key => $type) {
			foreach ($_POST[$type] as $user => $task) {
				foreach ($task as $id => $val) {
					if ($val == '') $val = 0;
					if (is_numeric($val)) {
						//find out if mark exists and get task id of mark
						$sql = "select * from date_tasks_marks dtm
                                join date_tasks dt using (date_task_id) 
								where user_id = " . $user . " 
                                and mark_date = " . $date . "
                                and grid_id = " . $grids[$type];
						$result = mysql_query($sql);
						if (mysql_num_rows($result) > 0) {
                            // get task id
                            $row = mysql_fetch_assoc($result);
                            $task_id = $row['date_task_id'];
							if ($val == 0) {
								// delete mark and mission if value is 0
								$sql = "delete from date_tasks_marks where date_task_id = " . $task_id . " and user_id = " . $user . " and mark_date = " . $date;
								$qrys[] = $sql;
								$sql = "select date_tasks_mission_id from date_tasks where date_task_id = " . $task_id;
								$result = mysql_query($sql);
								$row = mysql_fetch_assoc($result);
								$missionID = $row['date_tasks_mission_id'];
								$sql = "delete from date_tasks_mission_marks where user_id = " . $user . " and date_tasks_mission_id = " . $missionID;
							} else {
								$sql = "update date_tasks_marks 
										set done_qty = " . (int) mysql_real_escape_string($val) . " 
										where date_task_id = " . $task_id . " 
										and user_id = " . $user . "
                                        and mark_date = " . $date;
							}
							$qrys[] = $sql;
						} else {
							if ($val > 0) {
								$sql = "insert into date_tasks_marks 
										set date_task_id = " . $id . ",  
										user_id = " . $user . ", 
										mark_date = " . $date . ", 
										done_qty = " . (int) mysql_real_escape_string($val) . ", 
										mark_description = \"" . $tehillim[$user][$key]['description'] . "\", 
										mark_points = " . $tehillim[$user][$key]['points'];
								$qrys[] = $sql;
                                
                                // find out quota
                                $sql = "select dtm.*, dt.quantity from date_tasks_missions dtm
                                        join date_tasks dt using (date_tasks_mission_id)
                                        where dt.date_task_id = " . $id;
                                $result = mysql_query( $sql );
                                $row = mysql_fetch_assoc( $result );
                                $quota = $row['quantity'];
                                $missionID = $row['date_tasks_mission_id'];
                                
                                // check if mission was marked
                                $sql = "select * from date_tasks_mission_marks
                                        where user_id = " . $user . "
                                        and date_tasks_mission_id = " . $missionID;
                                $result = mysql_query( $sql );
                                
                                // check if quota was reached 
                                if (intval($val) >= $quota && mysql_num_rows( $result ) == 0) {
                                    $sql = "insert into date_tasks_mission_marks
                                            set user_id = " . $user . ",
                                            date_tasks_mission_id = " . $missionID . ",
                                            mission_value = 1.0, 
                                            subject_id = 1,
                                            mission_name = \"" . mysql_real_escape_string( $row['mission_name'] ) . "\",
                                            mark_date = " . $date;
                                    $qrys[] = $sql;
                                } else if (intval($val) < $quota && mysql_num_rows( $result ) == 1) {
                                    $sql = "delete from  date_tasks_mission_marks
                                            where user_id = " . $user . ",
                                            and date_tasks_mission_id = " . $missionID;
                                    $qrys[] = $sql;    
                                }
							}
						}
						//echo $sql . "<br />";
					}
				}
			}
		}
		//echo "<pre>"; print_r($qrys); echo "</pre>"; exit;
		foreach ($qrys as $qry) {
			// echo $qry . "<br />";
			mysql_query($qry);
		}
	}
	
	//get marked info
	$marked = array();
	foreach ($users as $user) {
		if (isset($tehillim[$user])) {
			$sql = "select done_qty from date_tasks_marks where user_id = " . $user . " and date_task_id = " . 
				$tehillim[$user][0]['date_task_id'];
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$marked[$user]['kap'] = $row['done_qty'];
			} else {
				// check if any marks were put in for a different ladder
				$sql = "select done_qty from date_tasks_marks dtm 
						join date_tasks dt using (date_task_id)
						join date_tasks_missions dtmm using (date_tasks_mission_id) 
						where dtm.user_id = " . $user . "
						and dtmm.start_date = " . $date . " 
						and dt.grid_id = " . $tehillim[$user][0]['grid_id'];
				$result = mysql_query($sql);
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					$marked[$user]['kap'] = $row['done_qty'];
				}
			}
			
			$sql = "select done_qty from date_tasks_marks where user_id = " . $user . " and date_task_id = " . 
				$tehillim[$user][1]['date_task_id'];
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) {
				$row = mysql_fetch_assoc($result);
				$marked[$user]['min'] = $row['done_qty'];
			} else {
				// check if any marks were put in for a different ladder
				$sql = "select done_qty from date_tasks_marks dtm 
						join date_tasks dt using (date_task_id)
						join date_tasks_missions dtmm using (date_tasks_mission_id) 
						where dtm.user_id = " . $user . "
						and dtmm.start_date = " . $date . " 
						and dt.grid_id = " . $tehillim[$user][1]['grid_id'];
				$result = mysql_query($sql);
				if (mysql_num_rows($result) > 0) {
					$row = mysql_fetch_assoc($result);
					$marked[$user]['min'] = $row['done_qty'];
				}
			}
		} 
	}
	//echo "<pre>"; print_r( $tehillim ); echo "</pre>"; exit;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Mark Tehillim</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
			caption {
				border-bottom: 1px solid grey;
				margin-bottom: 10px;
			}
			tr, td {
				padding: 3px;
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
		<? include('admin_header.php') ?>
		<h1>Mark Tehillim</h1>
		
		<? if (isset($_POST['submit'])) {
			?>
			<form action='mark_tehillim2.php<?=$debug ? "?debug=true" : ""?>' method='post'>
			<input type="hidden" name="marks" value="1" />
			<input type="hidden" name="date" value="<?=$date?>" />
			<input type="hidden" name="school" value="<?=$school?>" />
			<input type="hidden" name="oldGrade" id="oldGrade" value="<?=$grade?>" />
			Change Grade to: 
			<select name="grade" id="grade">
				<option value='0'>All Grades</option>
				<?php
				require_once 'class.schoolClasses.php';
					$sc = new SchoolClasses($school);
					$classes = $sc->getClasses();
					foreach ($classes as $row) {
						$class = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
						echo "<option value='" . $row['class_id'] . "'";
						if ($row['class_id'] == $grade) echo " selected";
						echo ">" . $class . "</option>";
					}
				?>
			</select>
			<input type="submit" name="submit" value="change" id="changeGrade" />
			<!--<input type='submit' name='submit' value='save' />-->
			<br /><br />
			<table>
				<caption><?=$schools[$school]?></caption>
				<tr>
					<th>Grade</th>
					<th>Student</th>
					<th>Kapitelach</th>
					<th>Minutes</th>
				</tr>
				<?php
				foreach ($users as $user) {
					if (isset($tehillim[$user])) {
						$kap = $tehillim[$user][0]['date_task_id'];
						$min = $tehillim[$user][1]['date_task_id'];
						$kapVal = isset($marked[$user]['kap']) ? $marked[$user]['kap'] : 0;
						$minVal = isset($marked[$user]['min']) ? $marked[$user]['min'] : 0;
						$gradeName = $userInfo[$user]['class_grade'] . (empty($userInfo[$user]['class_sub']) ? '' : '-' . $userInfo[$user]['class_sub']);
						echo "<tr><td>" . $gradeName . "</td><td>" . $userInfo[$user]['first'] . ' ' . $userInfo[$user]['last'] . "</td><td>" . 
							"<input type='text' size='5' name='kapitelach[" . $user . "][" . $kap . "]' " . 
							($kapVal ? 'value=' . $kapVal : '') . " /></td><td>" . 
							"<input type='text' size='5' name='minutes[" . $user . "][" . $min . "]' " . 
							($minVal ? 'value=' . $minVal : '') . " /></td></tr>";
					}
				}
				?>
				<tr>
					<td colspan="4" align="center">
						<input type="submit" name="submit" value="Save" />
					</td>
				</tr>
			</table>
			</form>
		<? } else { ?>
			<form action='mark_tehillim2.php<?=$debug ? "?debug=true" : ""?>' method="post">				
				<select name="school" id="school">
					<?php
					if (count($schools) > 1) {
						echo "<option value='0'>Choose School</option>";
					}
					foreach ($schools as $id => $name) {
						echo "<option value='" . $id . "'>" . $name . "</option>";
					}
					?>
				</select><br />
				<br />				
								
				<select name="grade" id="grade">
					<option value='0'>All Grades</option>
					<?php
					if (count($schools) == 1) {
						$id = key($schools);
						//echo $id;
						require_once 'class.schoolClasses.php';
						$sc = new SchoolClasses($id);
						$classes = $sc->getClasses();
						foreach ($classes as $row) {
							$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
							echo "<option value='" . $row['class_id'] . "'>" . $grade . "</option>";
						}
					}
					?>
				</select><br />
				<br />
				
				<select name="date" required>
					<option value='' disabled selected>Choose Shabbos Mevorchim</option>
					<?
					foreach ($sm as $month => $date) {
						if ($sm[6] == $sm[7]) {
							if ($month == 5) $months[$month] = "Adar";
							if ($month == 6) continue;
						}
						echo "<option value='" . $date . "'>" . $months[$month] . "</option>";
					}
					?>
				</select><br />
				<br />
				<input type="submit" name="submit" value="Submit" />
			</form>
		<? } ?>
	</body>
	<script>
		$("#grade").change( function() {
			$(this).parent().submit();
		});
		
		$("#school").change( function() {
			var school = $(this).val();
			$.get('ajax/getClasses.php', { id : school }, function(info) {
				var grades = JSON.parse( info );
				var html = "<option value='0'>Choose Grade</option>";
				for (var g in grades) {
					html += "<option value='" + g + "'>" + grades[g] + "</option>";
				}
				$("#grade").empty();
				$("#grade").append( html );
			});
		});
		
		$("#changeGrade").click( function() {
			if ($("#oldGrade").val() == $("#grade").val()) {
				alert('You have not changed the grade.');
				return false;
			}
		});
	</script>
</html>