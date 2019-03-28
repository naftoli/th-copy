<?php
require_once 'db.php';
$users = array();
$sql = "select user_id from users where school_type_id in (12,13)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

require_once 'class.birthday.php';
foreach ($users as $user) {
    $b = new Birthday($user);
    $b->setBirthday();
    $errors = $b->getErrors();
    if ($errors) {
        echo "Number of Errors: " . count($errors) . "<br />";
        echo "<pre>";
        print_r($errors);
        echo "</pre>";
    }
}
?>