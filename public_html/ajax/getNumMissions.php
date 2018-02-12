<?
require '../db.php';
$user = $_POST['user_id'];
$subject = $_POST['subject_id'];

$sql = "SELECT COUNT( * ) AS total
		from date_tasks_mission_marks  
		WHERE user_id = $user 
		and subject_id = $subject";
//echo $sql;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc($result);
echo $row['total'];
?>