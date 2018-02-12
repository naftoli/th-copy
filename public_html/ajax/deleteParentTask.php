<?
require_once '../db.php';
require_once '../class.editTasks.php';

$admin_id = $_POST['id'];
$task = $_POST['task'];

$t = new EditTasks($admin_id, 'parent');
if ($t->delete($task)) 
	echo json_encode(1);
else 
	echo json_encode(0);
?>