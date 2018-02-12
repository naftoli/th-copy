<?
$id = $_POST['school_id'];
require_once '../db.php';
require_once '../class.tasksCustomizationNew.php';
$tc = new TasksCustomizationNew;
if ($tc->reset($id)) {
    echo "You have successfully set all your students back to their defaults.";
} else {
    echo "Unsuccessful.";
}
?>