<?
$id = $_POST['id'];
require_once '../db.php';
require_once '../class.birthdayEn.php';
require_once '../class.birthdayYi.php';

$b = new BirthdayEn($id);
$b->setBirthday();
$errors = $b->getErrors();

$bi = new BirthdayYi($id);
$bi->setBirthday();
$errors2 = $bi->getErrors();

if ($errors || $errors2) {
    if ($errors)
        echo json_encode($errors);
    else
        echo json_encode($errors2);
} else {
	echo json_encode($id);
}
?>