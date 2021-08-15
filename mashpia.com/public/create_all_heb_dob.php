<?

// Birthdays don't handle selecting user ids
// it needs to be in a loop over the user ids that need updating
echo "error: broken script";
die();

require_once 'db.php';
require_once 'class.birthdayEn.php';
require_once 'class.birthdayYi.php';
require_once 'class.birthdayHe.php';

$b = new BirthdayEn;
$b->setBirthday();
$by = new BirthdayYi;
$by->setBirthday();
$bh = new BirthdayHe;
$bh->setBirthday();

$errors = $b->getErrors();
if ($errors) {
	echo "Number of Errors: " . count($errors) . "<br />";
    echo "<pre>";
    print_r($errors);
    echo "</pre>";
}
?>