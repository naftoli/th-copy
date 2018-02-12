<?php
require_once 'db.php';
require_once 'class.campaignEnrollment.php';
require_once 'class.birthday.php';
require_once 'class.birthdayYi.php';
require_once 'class.heDob.php';
require_once 'class.globalSettings.php';

$year = GlobalSettings::getRegistrationYear();

$users = array();
$sql = "select user_id from user_registration ur 
        join users u using (user_id)
        where u.user_registered is null
        and ur.year = " . $year;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];      
}
//echo "<pre>"; print_r($users); echo "</pre>"; exit;

foreach ($users as $user_id) {
    $sql = "update users set user_registered = now() where user_id = " . $user_id;
    mysql_query($sql);
    
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
