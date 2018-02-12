<?
$task = $_POST['task'];
$user_id = $_POST['user_id'];
$label = $_POST['label'];
$is_task = $_POST['is_task'];

if (!is_numeric($label) || !is_numeric($user_id)) {
	echo "Incorrect User ID or Category ID";
	exit;
}

if (empty($task)) {
	echo "Task cannot be empty.";
	exit;
}

//if tasks is master task, delete all tasks and subtasks
//otherwise just delete all subtasks
require_once '../db.php';
require_once '../class.achosCustomization.php';

$ac = new AchosCustomization();
$ac->setStudent( $user_id );
if ($ac->deleteTask( $task, $label, $is_task ) ) {
	echo 1;
} else {
	echo 'error deleting.';
}
