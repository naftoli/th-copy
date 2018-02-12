<? 
$admin_auth = array('school','user'); 
require('header.php'); 

function getId() {
	$check = "
		SELECT date_tasks_mission_id
		FROM `date_tasks_missions` 
		ORDER BY date_tasks_mission_id DESC
		LIMIT 1";
	$res = mysql_query($check);
	$row = mysql_fetch_row($res);
	$number = $row[0];
	
	$check2 = "
		SELECT date_tasks_mission_id
		FROM `date_tasks_mission_marks` 
		ORDER BY date_tasks_mission_id DESC
		LIMIT 1";
	$res2 = mysql_query($check2);
	$row2 = mysql_fetch_row($res2);
	$number2 = $row2[0];
	
	//echo "Date Tasks Mission: " . $number . "<br />";
	//echo "Date Tasks Mission Mark: " . $number2 . "<br />";
	
	//return greater number
	return $number > $number2 ? ++$number : ++$number2;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Add Medals</title>
<script language='javascript' type='text/javascript'>
function showAlert() {
	alert('The updating process may take some time so please be patient. Thanks.');
}
</script>
</head>

<body>
<? include('admin_header.php'); ?>
<h1>Add Medals</h1>
<?
if ($admin->school_id > 0) {

	if (isset($_POST['go']) && isset($_POST['subjects'])) {
	
		//print_r($_POST);
		//exit;
		
		//get user_id
		$id = $_POST['child'];		
		$subjects = $_POST['subjects'];
		$medals = array();
		foreach ($subjects as $subject) {
			$medals[] = $_POST[$subject];
		}
				
		//get bogus mission number
		$number = getId();
		$date = unixtojd();
		
		//lock table to get last autoincrement id and make sure no new records are created
		$sql = "lock tables date_tasks_missions write, date_tasks_mission_marks write, user_tracks read, medals_subjects read";
		mysql_query($sql) or die(mysql_error());
		
		$inserted = 0;

		//create all bogus missions
		for ($i = 0; $i < count($subjects); $i++) {
			
			//make sure child is enrolled to subject
			$sql = "select enrolled from user_tracks where user_id = $id and subject_id = $subjects[$i]";
			$result = mysql_query($sql) or die(mysql_error());
			$row = mysql_fetch_row($result);
			$enrolled = $row[0];
			if (!$enrolled)
				continue;
			
			//find out how many missions child needs to get medal
			$sql = "select sum(missions_required) as total from medals_subjects where subject_id = $subjects[$i] and medal_ord <= $medals[$i]";
			//echo $sql . "<br />";
			$result = mysql_query($sql) or die(mysql_error());
			$row = mysql_fetch_row($result);
			$missions = intval($row[0]);
			//echo "Missions required: " . $missions . "<br />";
			
			//find out how many missions have been accomplished already
			$sql = "
				SELECT count( * ) AS finished_missions
				FROM date_tasks_mission_marks
				WHERE user_id = $id
				AND subject_id = $subjects[$i]";
			//echo $sql . "<br />";
			$query = mysql_query($sql);
			$row = mysql_fetch_row($query);
			$finished = $row[0];
			//echo "Missions accomplished: " . $finished . "<br />";
			
			//if missions accomplished are less than mission required, update mission marks
			$missing = 0;
			if ($finished < $missions) {
				$missing = $missions - $finished;
				//echo "Needed missions: " . $missing . "<br />";
				for ($j = 0; $j < $missing; $j++) {
					//echo "Subject: " . $subjects[$i] . " Medal: " . $medals[$i] . "<br />";
					$sql = "insert into date_tasks_mission_marks (user_id, date_tasks_mission_id, subject_id, mark_date) values ($id, $number, $subjects[$i], $date)";
					//echo $sql . "<br />";
					if ($result = mysql_query($sql))
						$inserted++;
					$number++;
				}				
			}
			else {
				continue;
			}
		}
		
		if ($inserted > 0) {
			echo "Updated Successfully.";
			//update autoincrement field in date_tasks_mission table
			$sql = "alter table date_tasks_missions auto_increment = $number";
			//echo $sql . "<br />";
			mysql_query($sql) or die(mysql_error());
		}
		else {
			echo "Child has already enough missions marked for his medals.";
		}
				
		//release lock
		$sql = "unlock tables";
		mysql_query($sql);
		
			
		//update medals/ranks
		require_once("classes/medal_updater.php");
		require_once("classes/rank_updater.php");
		
		$medal_updater = new medal_updater();
		$medal_updater->update_medal_two($id);

		$rank_updater = new rank_updater();
		$rank_updater->update_rank_two($id);
		
	} 
	else {
		
		?>
		<p>Please use the following form to update the child's medals.</p>
		<form action='add_medals.php' method='post'>
		Choose child:<br />
		<select name='child'>
		<?
		
		$sql = "select user_id, last, first from users where school_id = " . $admin->school_id . " order by last, first";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			echo "<option value='" . $row['user_id'] . "'>" . $row['last'] . ", " . $row['first'];
		}
		?>
		</select><br /><br />
		Click on the subject(s) that you would like to update and indicate which medal to give.<br />
		<i>Please note: If the child is not enrolled to the subject that you wish to update, he/she will NOT be updated for that subject.</i><br />
		<br />
		<table>
		<?
		//get medals
		$medals = array();
		$sql = "select * from medals order by medal_ord";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$medals[$row['medal_ord']] = $row['medal_name'];
		}

		//get subjects
		$sql = "select * from subjects where subject_type NOT IN ('school_points', 'home_points') and subject_id not in (27, 91)";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			echo "<tr><td><input type='checkbox' name='subjects[]' value='" . $row['subject_id'] . "'></td><td>" . $row['subject_name'] . "</td>";
			echo "<td><select name='" . $row['subject_id'] . "'><option value='0' selected='selected'>Please choose</option>";
			foreach ($medals as $k => $v) {
				echo "<option value='$k'>$v</option>";
			}
			echo "</select></td></tr>";
		}
		?>
		</table>		
		<br /><input type='submit' value='update' name='go' onclick='showAlert()'>
		</form>
		<?
	}	
} else {
	echo "no permission to view this page";
}
?>
</body>
</html>