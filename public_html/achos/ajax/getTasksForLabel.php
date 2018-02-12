<?
require_once '../db.php';
$user_id = $_POST['user'];
$label = $_POST['label'];

require_once '../class.achosCustomization.php';
$ac = new AchosCustomization();
$ac->setStudent($user_id);
$tasks = $ac->getTasksForLabel($label);

echo json_encode($tasks);
?> 