<?php
ini_set('display_errors', 1);
$admin_auth = array('school','user'); 
require('header.php'); 
// gets the highest id for date_tasks_mission_id
function getId() {
	// get the top id from the date_tasks_missions table
	$check = "
		SELECT date_tasks_mission_id
		FROM `date_tasks_missions` 
		ORDER BY date_tasks_mission_id DESC
		LIMIT 1";
	$res = mysql_query($check);
	$row = mysql_fetch_row($res);
	$number = $row[0];
	// get the highest id from the date_tasks_mission_id table
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
<title>Add Missions</title>
<style>
	tr, th, td {
		padding: 5px;
	}
	#loading {
		position: absolute;
		top: 10%;
	}
</style>
<script language='javascript' type='text/javascript'>
function showAlert() {
	alert('The updating process may take some time so please be patient. Thanks.');
}
</script>
<script src="jquery.js"></script>
</head>

<body>
<? include('admin_header.php'); ?>
<h1>Add Missions</h1>
<div id="loading"><img src="images/loadingNew.gif" /></div>
<?
if ($admin->school_id > 0) { // if the admin has a school...
	// if the user has submitted some subjects to update
	if (isset($_POST['go']) && isset($_POST['subjects'])) {
	
		//print_r($_POST);
		//exit;
		
		//get user_id
		$id = $_POST['child'];		
		$subjects = $_POST['subjects']; // get the subjects that where sent
				
		//get bogus mission number
		$number = getId(); // get the highest date_tasks_mission_id number plus one
		$date = unixtojd(); // get the current date
		
		//lock table to get last autoincrement id and make sure no new records are created
		$sql = "lock tables date_tasks_missions write, date_tasks_mission_marks write, user_tracks read, medals_subjects read";
		mysql_query($sql) or die(mysql_error());
		
		$inserted = 0; // by default we have not really inserted anything yet

		//create all bogus missions
		for ($i = 0; $i < count($subjects); $i++) {
			
			//make sure child is enrolled to subject
			$sql = "select enrolled from user_tracks where user_id = $id and subject_id = $subjects[$i]";
			$result = mysql_query($sql) or die(mysql_error());
			$row = mysql_fetch_row($result);
			$enrolled = $row[0];
			if (!$enrolled)
				continue;
			
			//get number of total missions wanted
			$total = $_POST["$subjects[$i]"];
			//echo "Total missions wanted: " . $total . "<br />";
			
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
			
			//if missions accomplished are less than mission wanted, update mission marks
			$missing = 0;
			if ($finished < $total) { // if the student has finished less then the total that was entered
				$missing = $total - $finished; // the difference is what is missing
				//echo "Needed missions: " . $missing . "<br />";
				for ($j = 0; $j < $missing; $j++) { // for each one
					//echo "Subject: " . $subjects[$i] . " Medal: " . $medals[$i] . "<br />";
					// add a row to the database
					$sql = "insert into date_tasks_mission_marks (user_id, date_tasks_mission_id, subject_id, mark_date) values ($id, $number, $subjects[$i], $date)";
					//echo $sql . "<br />";
					if ($result = mysql_query($sql))
						$inserted++; // if it went through mark that we inserted something
					$number++; // and increment that id
				}				
			}
			else {
				continue;
			}
		}
		
		if ($inserted > 0) { // if anything was inserted
			echo "Updated Successfully."; // tell the user
			//update autoincrement field in date_tasks_mission table
			$sql = "alter table date_tasks_missions auto_increment = $number";
			//echo $sql . "<br />";
			mysql_query($sql) or die(mysql_error());
		}
		else { // we have not updated anything so give the user this vauge message
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
	else { // if nothing was posted
		
		?>
		<p>Please use the following form to update the child's missions.</p>
		<form action='add_missions.php' method='post'>
		Choose child:<br />
		<select name='child' id='child'>
		<?
		
		$sql = "select user_id, last, first from users where school_id = " . $admin->school_id . " order by last, first";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			if (!isset($id)) $id = $row['user_id'];
			echo "<option value='" . $row['user_id'] . "'>" . $row['last'] . ", " . $row['first'];
		}
		?>
		</select><br /><br />
		Click on the subject(s) that you would like to update and<br /><b>INDICATE HOW MANY TOTAL MISSIONS THE CHILD SHOULD HAVE FOR THAT SUBJECT.</b><br />
		<i>Please note: If the child is not enrolled to the subject that you wish to update, he/she will NOT be updated for that subject.</i><br />
		<br />
		<table>
		<?
		//get subjects
		$sql = "select * from subjects where subject_type NOT IN ('school_points', 'home_points') and subject_id not in (91)";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			echo "<tr class='subject' id='" . $row['subject_id'] . "'><td><input type='checkbox' name='subjects[]' value='" . $row['subject_id'] . "'></td><td>" . $row['subject_name'] . "</td>";
			echo "<td><select name='" . $row['subject_id'] . "'>";
			for ($j = 0; $j < 751; $j++) {
				echo "<option value='$j'>$j</option>";
			}
			echo "</select><td style='text-align: right'><span id='numMissions'></span></td><td>missions already done</td></tr>";
		}
		?>
		</table>		
		<br /><input type='submit' value='update' name='go' onclick='showAlert()'>
		</form>
		<?
	}	
} else { // no school, no permission
	echo "no permission to view this page";
}
?>
</body>
<script>
	// run on page load
	$(function() {
		$("#loading").hide(); // hide the loading wheel
		var user_id = <?=$id?>; // get the user id from php
		getMissionTotal(user_id); // and load his missions
		
		$("#child").change( function() { // should the student change
			var user_id = $(this).val(); // change the id
			getMissionTotal(user_id); // and get that students missions
		});
	});
	// get the total missions for a given user and set them in the UI
	function getMissionTotal(user_id) {
		$("#loading").show(); // show the loading wheel
		$(".subject").each( function() { // for each subject line
			var child = user_id; // reassign the user id
			var subject = $(this).attr('id'); // get the id from the subject
			// post to the server the userid and the subject_id
			$.post('ajax/getNumMissions.php', {user_id : child, subject_id : subject}, function(num) {
				$("#" + subject).find('#numMissions').text(num); // set the correct subject to the result of the ajax request
			});
		});
		$("#loading").hide(); // hide that loading wheel
	}
</script>
</html>