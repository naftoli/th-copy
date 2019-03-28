<?
require_once '../db.php';
require_once '../class.editTasks.php';

$params = explode(":", $_POST['id']);
$admin_id = $params[0];
$old = $params[1];
$new = $_POST['value'];

$t = new EditTasks($admin_id, 'parent');
if ($t->edit($old, $new)) 
	echo $new;
else 
	echo $old;
?>
