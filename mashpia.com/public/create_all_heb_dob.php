<?
require_once 'db.php';
require_once 'class.birthday.php';
require_once 'class.birthdayYi.php';

$b = new Birthday;
$b->setBirthday();
$by = new BirthdayYi;
$by->setBirthday();

$errors = $b->getErrors();
if ($errors) {
	echo "Number of Errors: " . count($errors) . "<br />";
    echo "<pre>";
    print_r($errors);
    echo "</pre>";
}
?>