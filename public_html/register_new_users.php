<?php
require_once 'db.php';
require_once 'class.campaignEnrollment.php';
require_once 'class.birthday.php';
require_once 'class.birthdayYi.php';
require_once 'class.heDob.php';

$users = array(22660);

foreach ($users as $user_id) {
    $sql = "update users set user_registered = now() where user_id = " . $user_id;
    mysql_query($sql);
    
    try {
        $c = new CampaignEnrollment( $user_id );
        $c->enroll();
    } catch (EnrollmentException $e) {
        echo $e->getMessage();
    }
    
    // create birthday missions
    $b = new Birthday( $user_id );
    $b->setBirthday();
    $bi = new BirthdayYi( $user_id );
    $bi->setBirthday();
    
    //set dob for syncing with wp
    $hdob = new HeDob( $user_id );
    $hdob->setHeDob();
}
echo "done.";