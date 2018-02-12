<?php
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

$sm = calculateSM( 5777 );
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

	require_once 'class.adminSchools.php';
	$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
	$schools = $as->getSchools();
	
	$users = array();
	require_once 'class.schoolsUsers.php';
	foreach ($schools as $id => $name) {
		$su = new SchoolsUsers( $id );
		$users[$name] = $su->getUsers();
	}
	
	$sorted = array();
	foreach ($users as $school => $info) {
		foreach ($info as $user) {
			$grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);		
			$sorted[$school][$grade][] = $user;
		}
	}
	//echo "<pre>"; print_r($sorted); echo "</pre>";
	
	$date = $_POST['date'];
	//echo $date;
	//exit;
	if ($date == 0) {
		header("Location: mark_tehillim.php");
		exit;	
	}
	
	$tehillim = array();
	foreach ($sorted as $school => $info) {
		foreach ($info as $grade => $other) {
			foreach ($other as $user) {
				$sql = "select u.school_type_id, u.lang_id, ut.level, ut.track_id 
						from user_tracks ut 
						join users u using (user_id) 
						where ut.subject_id = 1 
						and ut.user_id = " . $user['user_id'];
				//echo $sql . "<br />";
				//if ($user['user_id'] == 23245) echo $sql; 
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
					//echo $sql . "<br />";
					$result = mysql_query($sql);
					$row = mysql_fetch_assoc($result);
					$mission_id = $row['date_tasks_mission_id'];
					
					if ($mission_id) {
						$tasks = array();
						$sql = "select * from date_tasks where date_tasks_mission_id = " . $mission_id;
						//echo $sql . "<br />"; exit;
						$result = mysql_query($sql);
						while ($row = mysql_fetch_assoc($result)) {
							$tasks[] = $row;
						}
						$tehillim[$user['user_id']] = $tasks;
					}
				}
			}
		}
	}
	//echo "<pre>"; print_r($tehillim); echo "</pre>";
	
	if (isset($_POST['marks'])) {
		//echo count($_POST['kapitelach']);
		//echo "<br />" . count($_POST['minutes']);
		//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
		$qrys = array();
		$types = array('kapitelach', 'minutes');
		foreach ($types as $key => $type) {
			foreach ($_POST[$type] as $user => $task) {
				foreach ($task as $id => $val) {
					if ($val == '') $val = 0;
					if (is_numeric($val)) {
						//find out if mark exists
						$sql = "select * from date_tasks_marks 
								where date_task_id = " . $id . " 
								and user_id = " . $user;
						$result = mysql_query($sql);
						if (mysql_num_rows($result) > 0) {
							$sql = "update date_tasks_marks 
									set done_qty = " . (int) mysql_real_escape_string($val) . " 
									where date_task_id = " . $id . " 
									and user_id = " . $user;
						} else {
							$sql = "insert into date_tasks_marks 
									set date_task_id = " . $id . ",  
									user_id = " . $user . ", 
									mark_date = " . $date . ", 
									done_qty = " . (int) mysql_real_escape_string($val) . ", 
									mark_description = \"" . $tehillim[$user][$key]['description'] . "\", 
									mark_points = " . $tehillim[$user][$key]['points'];
						}
						$qrys[] = $sql;
					}
				}
			}
		}
		//echo "<pre>"; print_r($qrys); echo "</pre>"; exit;
		foreach ($qrys as $qry) {
			//echo $qry;
			mysql_query($qry);
		}
	}

	//get marked info
	$marked = array();
	foreach ($sorted as $school => $info) {
		foreach ($info as $grade => $other) {
			foreach ($other as $user) {
				if (isset($tehillim[$user['user_id']])) {
					$sql = "select done_qty from date_tasks_marks where user_id = " . $user['user_id'] . " and date_task_id = " . 
						$tehillim[$user['user_id']][0]['date_task_id'];
					$result = mysql_query($sql);
					if (mysql_num_rows($result) > 0) {
						$row = mysql_fetch_assoc($result);
						$marked[$user['user_id']]['kap'] = $row['done_qty'];
					} else {
						// check if any marks were put in for a different ladder
						$sql = "select done_qty from date_tasks_marks dtm 
								join date_tasks dt using (date_task_id)
								join date_tasks_missions dtmm using (date_tasks_mission_id) 
								where dtm.user_id = " . $user['user_id'] . "
								and dtmm.start_date = " . $date . " 
								and dt.grid_id = " . $tehillim[$user['user_id']][0]['grid_id'];
						$result = mysql_query($sql);
						if (mysql_num_rows($result) > 0) {
							$row = mysql_fetch_assoc($result);
							$marked[$user['user_id']]['kap'] = $row['done_qty'];
						}
					}
					
					$sql = "select done_qty from date_tasks_marks where user_id = " . $user['user_id'] . " and date_task_id = " . 
						$tehillim[$user['user_id']][1]['date_task_id'];
					$result = mysql_query($sql);
					if (mysql_num_rows($result) > 0) {
						$row = mysql_fetch_assoc($result);
						$marked[$user['user_id']]['min'] = $row['done_qty'];
					} else {
						// check if any marks were put in for a different ladder
						$sql = "select done_qty from date_tasks_marks dtm 
								join date_tasks dt using (date_task_id)
								join date_tasks_missions dtmm using (date_tasks_mission_id) 
								where dtm.user_id = " . $user['user_id'] . "
								and dtmm.start_date = " . $date . " 
								and dt.grid_id = " . $tehillim[$user['user_id']][1]['grid_id'];
						$result = mysql_query($sql);
						if (mysql_num_rows($result) > 0) {
							$row = mysql_fetch_assoc($result);
							$marked[$user['user_id']]['min'] = $row['done_qty'];
						}
					}
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
			<form action='mark_tehillim.php' method='post'>
			<input type="hidden" name="marks" value="1" />
			<input type="hidden" name="date" value="<?=$_POST['date']?>" />
			<input type='submit' name='submit' value='save' />
			<br /><br />
			<?
			foreach ($sorted as $school => $info) { ?>
				<table>
					<caption><?=$school?></caption>
					<tr>
						<th>Grade</th>
						<th>Student</th>
						<th>Kapitelach</th>
						<th>Minutes</th>
					</tr>
				<? foreach ($info as $grade => $other) {
					foreach ($other as $user) {
						if (isset($tehillim[$user['user_id']])) {
							$kap = $tehillim[$user['user_id']][0]['date_task_id'];
							$min = $tehillim[$user['user_id']][1]['date_task_id'];
							$kapVal = isset($marked[$user['user_id']]['kap']) ? $marked[$user['user_id']]['kap'] : 0;
							$minVal = isset($marked[$user['user_id']]['min']) ? $marked[$user['user_id']]['min'] : 0;
							echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . ' ' . $user['last'] . "</td><td>" . 
								"<input type='text' size='5' name='kapitelach[" . $user['user_id'] . "][" . $kap . "]' " . 
								($kapVal ? 'value=' . $kapVal : '') . " /></td><td>" . 
								"<input type='text' size='5' name='minutes[" . $user['user_id'] . "][" . $min . "]' " . 
								($minVal ? 'value=' . $minVal : '') . " /></td></tr>";
						}
					}
				} ?> 
			</table>
			<input type="submit" name="submit" value="save" />
			</form>
		<? }
		} else { ?>
			<form action="mark_tehillim.php" method="post">
				<select name="date">
					<option value='0'>Choose Shabbos Mevorchim</option>
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
				<input type="submit" name="submit" value="Submit" />
			</form>
		<? } ?>
	</body>
</html>