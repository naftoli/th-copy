<?
require 'header.php';
require '../db.php';

$user_id = $_SESSION['user_id'];
require_once '../class.achosCustomization.php'; 
$ac = new AchosCustomization();
$ac->setStudent($user_id); 

$ids = array();
foreach ($_POST as $k => $v) {
	if (is_int($k)) {
		$ids[] = mysql_real_escape_string($k);
	}
}

//we need to find all task ids for each tasks/subtasks and add it to user
$taskIDs = $ac->getTaskIDs($ids);
//echo "<pre>"; print_r($taskIDs); echo "</pre>"; exit;

//delete existing taskIDs from user_tasks
//find this year's starting task id
$sql = "select date_task_id from date_tasks where date_tasks_mission_id > 48 order by date_task_id limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$taskID = $row['date_task_id'];	
$sql = "delete from user_tasks where user_id = " . $user_id . " and task_id >= " . $taskID;
//echo $sql;
mysql_query($sql);

require_once '../class.defaults.php';
$d = new Defaults($user_id);
foreach ($taskIDs as $id) {
	$d->addOn($id, 'task');
}

header("Location: goals.php");
?>