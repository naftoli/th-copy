<?php 
// 33423717910291281408 Mendel Simon user_id=6053 
// 33820207850950519808 Yoel Benjamin user_id=38

require_once ("../header.php");
require_once('../file_save.php');
require_once('../calendar.php');

$current_date = unixtojd();

$user_id = $_GET['user_id'];
$subject_id = $_GET['subject_id'];

$sqlSelect = "SELECT 	subject_name, 
						subject_image_id, 
						inst_name, 
						date_tasks_missions.mission_name, 
						mission_number, 
						subject_id, 
						start_date, 
						end_date, 
						date_tasks_mission_id, 
						track_id, 
						track_name, 
						level, 
						school_type_id, 
						school_type_name, 
						date_tasks_mission_marks.date_tasks_mission_id done, 
						mark_override,
						(SELECT COUNT(*) FROM date_tasks WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id) tasks,
						(SELECT COUNT(*) FROM date_tasks JOIN date_tasks_marks USING (date_task_id) WHERE date_tasks.date_tasks_mission_id = date_tasks_missions.date_tasks_mission_id AND user_id = " . $user_id . ") marked_tasks ";
						
$sqlFrom = " FROM subjects ";

$sqlJoin = " JOIN school_subjects USING (subject_id) ";
$sqlJoin = $sqlJoin . " JOIN school_type_subjects USING (subject_id) ";
$sqlJoin = $sqlJoin . " JOIN users USING (school_id, school_type_id) ";
$sqlJoin = $sqlJoin . " JOIN user_tracks USING (user_id, subject_id) ";
$sqlJoin = $sqlJoin . " JOIN date_tasks_missions USING (school_type_id, subject_id, level, track_id) ";
$sqlJoin = $sqlJoin . " LEFT JOIN date_tasks_mission_marks USING (user_id, subject_id, date_tasks_mission_id) ";
$sqlJoin = $sqlJoin . " LEFT JOIN institutions USING (inst_id) ";
$sqlJoin = $sqlJoin . " LEFT JOIN tracks USING (track_id) ";
$sqlJoin = $sqlJoin . " LEFT JOIN school_types USING (school_type_id) ";

$sqlWhere = " WHERE 	user_id = " . $user_id . "  ";
$sqlWhere = $sqlWhere . " AND 	school_id = 5 ";
$sqlWhere = $sqlWhere . " AND 	enrolled = 1 ";
$sqlWhere = $sqlWhere . " AND 	start_date <= 2455212 ";
$sqlWhere = $sqlWhere . " AND 	end_date >= 2454711 ";
$sqlWhere = $sqlWhere . " AND 	user_registered IS NOT NULL ";
$sqlWhere = $sqlWhere . " AND 	subject_id = " . $subject_id . " ";

$sqlOrderBy = " ORDER BY inst_name, subject_ord, subject_name, mission_number, mission_name ";
$sqlStatement = $sqlSelect . $sqlFrom . $sqlJoin . $sqlWhere . $sqlOrderBy;	
$results = mysql_query($sqlStatement);

 
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<head>

	<body>		
		<label style="position:absolute; left:0px;">SUBJECT</label>
		<br />
		
		<label style="position:absolute; left:0px;">NAME</label>
		<br />		
		
		<table>

<?
			$top = 200;
			
			while ($row = mysql_fetch_assoc($results)) {	
			
				echo "\n<tr>";
			
				echo "\n<td style='position:absolute; left:0px; top:" . $top . "px;'>" . $row['subject_name'] . "</td>";
	
				echo "\n</tr>";
				
				$top = $top + 20;
			}
?>
		</table>
	</body>

</head>

	
