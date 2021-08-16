<?
$id = $_POST['id'];
require_once '../db.php';
require_once '../class.birthdayEn.php';
require_once '../class.birthdayYi.php';
require_once '../class.birthdayHe.php';

$b = new BirthdayEn($id);
$b->setBirthday();
$errors = $b->getErrors();

$bi = new BirthdayYi($id);
$bi->setBirthday();
$errors2 = $bi->getErrors();

$bh = new BirthdayHe($id);
$bh->setBirthday();
$errors3 = $bh->getErrors();

if ($errors || $errors2 || $errors3) {
    if ($errors)
        echo json_encode($errors);
    else if ($errors2)
        echo json_encode($errors2);
    else
        echo json_encode($errors3);
} else {
	echo json_encode($id);
}
?>