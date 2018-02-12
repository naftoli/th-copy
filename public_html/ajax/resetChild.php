<?
$id = $_POST['user_id'];
require_once '../db.php';
require_once '../class.tasksCustomizationNew.php';
$tc = new TasksCustomizationNew;
if ($tc->resetChild($id)) {
    echo "You have successfully reset this child.";
} else {
    echo "Unsuccessful.";
}
?>