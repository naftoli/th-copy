<?php
ini_set('display_errors',1);
require_once 'db.php';
require_once 'class.heDob.php';
require_once 'class.birthdayEn.php';
require_once 'class.birthdayYi.php';
require_once 'class.birthdayHe.php';

$users = [];
$sql = "select user_id from users where user_registered > 0";
$result = mysql_query($sql);
while ( $row = mysql_fetch_assoc($result) ) {
	$users[] = $row['user_id'];
}
foreach ($users as $user_id) {
	$h = new HeDob( $user_id, true );
	$h->setHeDob();
	// create birthday missions
    $b = new BirthdayEn( $user_id );
    $b->setBirthday();
    $bi = new BirthdayYi( $user_id );
    $bi->setBirthday();
    $bh = new BirthdayHe( $user_id );
    $bh->setBirthday();
}
echo "Done";
?>