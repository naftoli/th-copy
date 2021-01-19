<?

// the $user_id needs to be passed to the new Birthday($user_id) not to setBirthday()
// also it dosn't handle yidish missions
echo "error: broken script";
die();

require_once 'db.php';

$users = array();
$sql = "select user_id from users where class_id in ( 
		select class_id from classes 
		where school_id = 9 
		and class_era = 0 ) 
		and user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row['user_id'];
}

require_once 'class.birthday.php';
$b = new Birthday;
foreach ($users as $user) {
	$b->setBirthday( $user );
}

$errors = $b->getErrors();
if ($errors) {
	echo "Number of Errors: " . count($errors) . "<br />";
    echo "<pre>";
    print_r($errors);
    echo "</pre>";
}
?>