<?
require_once 'db.php';
require_once 'class.birthday.php';
$b = new Birthday;
$b->setBirthday();

$errors = $b->getErrors();
echo "Number of Errors: " . count($errors);
if ($errors) {
    echo "<pre>";
    print_r($errors);
    echo "</pre>";
}
?>