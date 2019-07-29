<?php
require_once 'db.php';
require_once 'class.campaignEnrollment.php';
require 'class.birthday.php';
require 'class.birthdayYi.php';
require 'class.heDob.php';

$users = [59457,59458];
foreach ($users as $user_id) {
    // register users and create private rank
    $sql = "update users set user_registered = now(), user_start_date = " . unixtojd() . " where user_id = " . $user_id;
    $sql2 = "update registration_charges set school_id = 269, type = 'chayolei', amount = 0, date = now(), year = 5779, user_id = " . $user_id;
    $sql3 = "insert into rank_marks set rank_ord = 1, user_id = " . $user_id . ", date_promoted = " . unixtojd();
    mysql_query( $sql );
    mysql_query( $sql2 );
    mysql_query( $sql3 );

    // update child to be enrolled in all campaigns
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