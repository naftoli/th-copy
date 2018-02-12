<?php
ini_set('display_errors',1);
require 'db.php';

$users = array();
$sql = "select user_id from users where user_registered is null and user_id in (
        select user_id from user_registration where year = 5778)";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

require 'class.campaignEnrollment.php';
require 'class.birthday.php';
require 'class.birthdayYi.php';
require 'class.heDob.php';

foreach ($users as $user_id) {
    // enroll into campaigns
    try {
        $c = new CampaignEnrollment($user_id);
        $c->enroll();
    } catch (EnrollmentException $e) {
        echo $e->getMessage();
    }
    
    // set dob for syncing with wp
    $hdob = new HeDob( $user_id );
    $hdob->setHeDob();
        
    // create birthday missions
    $b = new Birthday( $user_id );
    $b->setBirthday();
    $bi = new BirthdayYi( $user_id );
    $bi->setBirthday();
}
echo "Done.";