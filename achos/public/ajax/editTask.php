<?
$old = $_POST['oldTask'];
$new = $_POST['newTask'];
$user = $_POST['user'];
$label = $_POST['label'];

if (empty($old) || empty($new)) {
	echo "Task name cannot be empty.";
	exit;
}

if (!is_numeric($label) || !is_numeric($user)) {
	echo "Incorrect category or user.";
	exit;
}

require_once '../db.php';
$sql = "update date_tasks 
		set name = '" . mysql_real_escape_string($new) . "' 
		where name = '" . mysql_real_escape_string($old) . "' 
		and created_by = " . mysql_real_escape_string($user) . " 
		and label_id = " . mysql_real_escape_string($label);

if (mysql_query($sql)) {
	echo 1;
} else {
	echo "error editing.";
}
?>