<?php
require_once 'db.php';
require_once 'class.campaignEnrollment.php';
require 'class.birthdayEn.php';
require 'class.birthdayYi.php';
require 'class.birthdayHe.php';
require 'class.heDob.php';

/*
$users = array();
$sql = "select user_id from users where class_id in (3726,3727) and user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($sql)) {
    $users[] = $row['user_id'];
}
*/
$users = [14479,
14540,
14542,
16344,
16345,
16347,
18700,
18701,
19726,
21667,
21668,
54093,
54099,
54100,
54102,
54130,
54133,
54134,
54136,
54138,
54139,
54141,
54142,
54143,
54145,
54147,
54150,
54153,
54412,
59406,
59411];
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
    $b = new BirthdayEn( $user_id );
    $b->setBirthday();
    $bi = new BirthdayYi( $user_id );
    $bi->setBirthday();
    $bh = new BirthdayHe( $user_id );
    $bh->setBirthday();
}
echo "Done.";