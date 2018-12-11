<?
require_once 'db.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
		<?php
		$sts = array();
		$sql = "select * from school_type_subjects";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$sts[$row['school_type_id']][] = $row['subject_id'];
		}
		//echo "<pre>"; print_r($sts); echo "</pre>";
		//print_r(array_diff($sts[12], $sts[13]));
		
		//get all subjects
		$sbj = "select * from subjects where subject_type NOT IN ('school_points', 'home_points')";
		$sub_res = mysql_query($sbj);
		$subjects = array();
		while ($subject = mysql_fetch_assoc($sub_res)) {
			$subjects[] = $subject['subject_id'];
		}
		echo "<pre>"; print_r($subjects); echo "</pre>"; 
		?>
    </body>
</html>
