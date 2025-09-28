<?php
ini_set('display_errors',1);
ini_set('max_execution_time', 600);

require_once 'db.php';
require_once 'class.heDob.php';
require_once 'class.birthdayEn.php';
require_once 'class.birthdayYi.php';
require_once 'class.birthdayHe.php';
require_once 'class.globalSettings.php';
$year = GlobalSettings::getBirthdayYear();

$users = [];
$sql = "select user_id from users u 
        join user_registration ur using (user_id) 
        where user_registered > 0 
        and ur.year = " . $year;
$result = mysql_query($sql);
while ( $row = mysql_fetch_assoc($result) ) {
	$users[] = $row['user_id'];
}

$updated = 0;
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
    $updated++;
}
echo "Updated $updated users";
?>