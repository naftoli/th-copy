<?
$admin_auth = array('user'); 
require('header.php');

require_once 'class.achosStudent.php';
$as = new AchosStudent($admin_user['admin_id']);
$user = $as->getStudentID();

require_once 'class.achosCustomization.php';
$ac = new AchosCustomization;
$ac->setStudent($user);
$ac->createNewTask($_POST);
$errors = $ac->getErrors();

if (count($errors) > 0) {
    echo "<pre>";
    print_r($errors);
    echo "</pre>";
} else {
    $msg = urlencode("Congratulations! You have successfully created a new Task.");
    header("Location: newStudentTask.php?msg=$msg");
}
?>