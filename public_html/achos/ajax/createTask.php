<?
require_once '../db.php';

$label = mysql_real_escape_string($_POST['label']);
$user = mysql_real_escape_string($_POST['user']);

if (!is_numeric($label) || !is_numeric($user)) {
	echo "Incorrect User ID or Category ID";
	exit;
}

$task = mysql_real_escape_string($_POST['task']);
$desc = mysql_real_escape_string($_POST['desc']);

if (empty( $task )) {
	echo "Task name must be entered.";
	exit;
}

$task = array(
	'label'	=>	$label, 
	'task'	=>	$task, 
	'desc'	=>	$desc, 
	'created_by'	=>	$user
);

require_once '../class.achosCustomization.php';
$ac = new AchosCustomization();
$ac->setStudent( $user );
if ($ac->createNewUserTask( $task )) {
	echo 1;
} else {
	echo "unsuccessful";
}
?>