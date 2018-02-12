<?
require_once '../db.php';
require_once '../class.editTasks.php';

$school = $_POST['school'];
$task = $_POST['task'];

$t = new EditTasks($school);
if ($t->delete($task)) 
	echo json_encode(1);
else 
	echo json_encode(0);
?>
