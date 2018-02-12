<?php
ini_set('display_errors',1);
require_once 'db.php';
require_once 'class.heDob.php';
require_once 'class.birthday.php';
require_once 'class.birthdayYi.php';
/*
$users = array();
$sql = "select user_id from users where user_registered > 0 and school_id = 110";
$result = mysql_query($sql);
while ( $row = mysql_fetch_assoc($result) ) {
	$users[] = $row['user_id'];
}
*/
$users = array(52458); // seeing where the "interesting" user info comes from
foreach ($users as $user_id) {
	$h = new HeDob( $user_id, true );
	$h->setHeDob();
	// create birthday missions
    $b = new Birthday( $user_id );
    $b->setBirthday();
    $bi = new BirthdayYi( $user_id );
    $bi->setBirthday();
}
echo "Done";
?>