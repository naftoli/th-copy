<?php
session_start();

$admin_auth = array('user'); 
$ui_type = 'child';

require('header.php');

if (isset($_POST['submit'])) {
	//get requested subjects
	$subjects = array();
	$sql = "select * from subjects where subject_type NOT IN ('school_points', 'home_points') and subject_id not in (27, 91)";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$subjects[$row['subject_id']] = $row['subject_name'];
	}
	
	//get child info
	$sql = "select u.last, u.first, s.school_name, s.school_address1, s.school_city, s.school_state, s.school_postal, c.class_grade, c.class_sub
			from users as u, schools as s, classes as c 
			where u.school_id = s.school_id 
			and u.class_id = c.class_id 
			and u.user_id = " . $_POST['id'];
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$grade = $row['class_grade'];
	if ($row['class_sub'] != '') 
		$grade .= "-" . $row['class_sub'];
	$address = $row['school_name'] . "<br />" . $row['school_address1'] . "<br />" . $row['school_city'] . ", " . $row['school_state'] . "  " . $row['school_postal'] . "<br />";
	$child = $row['first'] . " " . $row['last'] . " from " . $row['school_name'] . " in Grade " . $grade . " needs the following:<br />";
	$rank =  "Rank card and Rank book for the " . $_POST['rank'] . " rank.<br />";
	$medals = "The following medals:<br />";
	if (isset($_POST['subjects'])) {
		foreach ($_POST['subjects'] as $subject) {
			$medals .= $subjects[$subject] . " - " . $_POST[$subject] . " medal<br />";
		}
	}
	
	//mail request to TH
	$to = "cth@tzivoshashem.org";
	$subject = "Medal / Rank Request";
	$message = $address . $child . $rank . $medals;
	$headers = 'MIME-Version: 1.0' . "\r\n" . 
				'Content-type: text/html; charset=utf-8' . "\r\n" . 
				'From: mashpia@mashpia.com' . "\r\n" .
				'Reply-To: naftolir@gmail.com' . "\r\n";
	
	if (mail($to, $subject, $message, $headers)) {
		$msg = "Your request has been sent.";
	} else {
		$msg = "Error sending request, please contact Tzivos Hashem.";
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/DIV/html4/sDIVict.dtd">


<HTML>

	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Parent Request Form</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>
	
	<BODY>
		<? include("admin_header.php"); ?>
		<? include_once("db.php"); ?>
		<h1>Rank / Medal Request Form</h1>
		<?
		if (isset($msg)) {
			echo $msg;
		} else { 
		?>
		<p>Please use the following form to request any missing rank cards, rank books, or medals.</p>
		<form action='parent_request.php' method='post'>
		<input type='hidden' name='id' value='<?=$_GET['id']?>'>
		Please send me the following:<br />
		<select name='rank'>
			<option value=''>Choose one</option>
			<?
			//get ranks
			$sql = "select * from ranks order by rank_ord";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				echo "<option value='" . $row['rank_name'] . "'>" . $row['rank_name'] . "</option>";
			}
			?>
		</select>
		Rank Card and Rank Book<br /><br />
		Click on the subject(s) that you need a medal for, and indicate which medal to give.<br />
		<br />
		<table>
		<?
		//get medals
		$medals = array();
		$sql = "select * from medals order by medal_ord";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$medals[] = $row['medal_name'];
		}

		//get subjects
		$sql = "select * from subjects where subject_type NOT IN ('school_points', 'home_points') and subject_id not in (27, 91)";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			echo "<tr><td><input type='checkbox' name='subjects[]' value='" . $row['subject_id'] . "'></td><td>" . $row['subject_name'] . "</td>";
			echo "<td><select name='" . $row['subject_id'] . "'><option value='0' selected='selected'>Please choose</option>";
			foreach ($medals as $v) {
				echo "<option value='$v'>$v</option>";
			}
			echo "</select></td></tr>";
		}
		?>
		</table>
		<br />
		<input type='submit' name='submit' value='submit'>
		</form>
		<? } ?>
	</BODY>
	
</HTML>
