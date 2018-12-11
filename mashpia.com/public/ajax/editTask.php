<?
require_once '../db.php';
require_once '../class.editTasks.php';

$params = explode(":", $_POST['id']);
$school = $params[0];
$old = $params[1];
$new = $_POST['value'];

$t = new EditTasks($school);
if ($t->edit($old, $new)) 
	echo $new;
else 
	echo $old;
?>
