<?
$subject = $_POST['id'];
$school = $_POST['school'];

require_once '../db.php';
require_once '../class.tasksCustomizationNew.php';
$tc = new TasksCustomizationNew;
$tc->enroll($tc->getAllUsers($school), array($subject));
?>