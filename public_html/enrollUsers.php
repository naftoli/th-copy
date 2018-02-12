<?php
require_once 'db.php';
require_once 'class.campaignEnrollment.php';
require 'class.birthday.php';
require 'class.birthdayYi.php';
require 'class.heDob.php';

/*
$users = array();
$sql = "select user_id from users where class_id in (3726,3727) and user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($sql)) {
    $users[] = $row['user_id'];
}
*/
$users = array(19698,51415);
// update child to be enrolled in all campaigns
foreach ($users as $user_id) {
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