<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<style type='text/css'>
tr, th, td {
	border: 1px dashed black;
	padding: 6px;
}
</style>
<body>
<?
require_once("db.php");

$users = array();
$sql = "select user_id, last, first from users";
$result = mysql_query($sql);
$i = 0; //for users counter
while ($row = mysql_fetch_assoc($result)) {
	$users[$i]['id'] = $row['user_id'];
	$users[$i]['name'] = $row['last'] . ", " . $row['first'];
	$i++;
}
//print_r($users);
/*
$toDelete = array();
$j = 0; //for toDelete counter

foreach ($users as $user) { 
	echo "User: " . $user['name'] . "<br />";
	$sql = "SELECT subject_id, subject_name, mission_name, mark_date, COUNT(*) AS total
			FROM date_tasks_mission_marks 
			JOIN users USING (user_id) 
			JOIN subjects USING (subject_id) 
			WHERE user_id = " . $user['id'] . "  
			AND subject_id = 1  
			GROUP BY subject_id, mission_name, mark_date 
			HAVING total > 1 
			ORDER BY subject_id, total DESC , mission_name, mark_date";
	//echo $sql . "<br />";
	$result = mysql_query($sql) or die(mysql_error());
	
	$k = 1;
	while ($row = mysql_fetch_assoc($result)) { 
		$user_id = $user['id'];
		$subject_id = $row['subject_id'];
		$subject_name = $row['subject_name'];
		$date = $row['mark_date'];
		
		echo "<table>";
		echo "<tr>";
		echo "<th>Subject</th>";
		echo "<th>Mission Name</th>";
		echo "<th>Mark Date</th>";
		echo "<th>Mission Number</th>";
		echo "</tr>";
		
		$sql2 = "select * from date_tasks_mission_marks 
				where user_id = $user_id 
				and subject_id = $subject_id 
				and mark_date = $date";
		//echo $sql2 . "<br />";
		$result2 = mysql_query($sql2);
		while ($row2 = mysql_fetch_assoc($result2)) { 
			echo "<tr>";
			echo "<td>$subject_name</td>";
			echo "<td>" . $row2['mission_name'] . "</td>";
			echo "<td>" . jdtogregorian($row2['mark_date']) . "</td>";
			echo "<td>" . $row2['date_tasks_mission_id'] . "</td>";
			echo "</tr>";
			if ($k++ > 1) {
				$toDelete[$j]['user'] = $user_id;
				$toDelete[$j]['id'] = $row2['date_tasks_mission_id'];
				$j++;
			}
		}
		echo "</table>";
	}
}
*/
echo "From this year:<br />";

$toKeep = array();
$toDelete = array();

foreach ($users as $user) { 
	$sql = "SELECT subject_id, subject_name, mission_name, mark_date, SUM( mission_count ) AS total
			FROM date_tasks_mission_marks  
			JOIN users USING (user_id) 
			JOIN subjects USING (subject_id) 
			WHERE user_id = " . $user['id'] . "  
			AND mark_date > 2455800 
			AND mission_name != '' 
			GROUP BY subject_id, mission_name, mark_date 
			HAVING total > 1 
			ORDER BY subject_id, total DESC , mission_name, mark_date";
	//echo $sql . "<br />";
	$result = mysql_query($sql) or die(mysql_error());
	
	while ($row = mysql_fetch_assoc($result)) { 
		$info = "select a.admin_id, school_name from admins a, admin_auths aa, users u, schools s 
				where u.user_id = aa.id 
				and a.admin_id = aa.admin_id 
				and u.school_id = s.school_id 
				and u.user_id = " . $user['id'];
		//echo $info;
		$res_info = mysql_query($info);
		$row_info = mysql_fetch_assoc($res_info);
				
		echo "User: (" . $user['id'] . ") " . $user['name'] . "<br />[Admin id: " . $row_info['admin_id'] . "; School: " . $row_info['school_name'] . "]<br />";
		$user_id = $user['id'];
		$subject_id = $row['subject_id'];
		$subject_name = $row['subject_name'];
		$date = $row['mark_date'];
		
		echo "<table>";
		echo "<tr>";
		echo "<th>Subject</th>";
		echo "<th>Mission Name</th>";
		echo "<th>Mark Date</th>";
		echo "<th>Mission Number</th>";
		echo "</tr>";
		
		$sql2 = "select * from date_tasks_mission_marks 
				where user_id = $user_id 
				and subject_id = $subject_id 
				and mark_date = $date";
		//echo $sql2 . "<br />";
		$result2 = mysql_query($sql2);
		
		while ($row2 = mysql_fetch_assoc($result2)) { 
			echo "<tr>";
			echo "<td>$subject_name</td>";
			echo "<td>&nbsp;" . $row2['mission_name'] . "</td>";
			echo "<td>" . jdtogregorian($row2['mark_date']) . "</td>";
			echo "<td>" . $row2['date_tasks_mission_id'] . "</td>";
			echo "</tr>";
		}
		echo "</table>";
		
		echo "Proper one: ";
		$sql3 = "select dtmm.* from date_tasks_mission_marks dtmm 
				join date_tasks_missions dtm using (date_tasks_mission_id) 
				join users u using (user_id) 
				join user_tracks ut on (ut.user_id = dtmm.user_id and ut.subject_id = dtmm.subject_id)  
				where dtmm.user_id = $user_id 
				and dtmm.subject_id = $subject_id 
				and dtmm.mark_date = $date 
				and u.school_type_id = dtm.school_type_id 
				and ut.level = dtm.level 
				and ut.track_id = dtm.track_id";
		//echo $sql3 . "<br />";
		$result3 = mysql_query($sql3);
		$num = mysql_num_rows($result3);
		if ($num > 0) {
			while ($row3 = mysql_fetch_assoc($result3)) {
				//echo "<pre>";
				//print_r($row3);
				//echo "</pre>";
				echo $subject_name . " - " . $row3['mission_name'] . " - " . jdtogregorian($row3['mark_date']) . " - " . $row3['date_tasks_mission_id'] . "<br />";
				$toKeep[$user_id] = $row3['date_tasks_mission_id'];
			}
		} else { 
			if (!in_array($user_id, $toDelete))
				$toDelete[] = $user_id;
		}
		echo "<br />";
	}
}
echo "---------------------------------------------------------------------------------------------<br /><br />";
echo "<pre>";
print_r($toKeep);
echo "<br />";
print_r($toDelete);
echo "</pre>";
?>
</body>
</html>