<?php
require_once 'db.php';
require_once 'class.campaignEnrollment.php';

$users = array();
$sql = "select user_id from users where school_id = 9 and user_registered is null";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

foreach ($users as $user) {
    $sql = "update users set user_registered = now() where user_id = " . $user;
    mysql_query($sql);
    try {
        $c = new CampaignEnrollment($user);
        $c->enroll();
    } catch (EnrollmentException $e) {
        echo $e->getMessage();
    }
}
echo "Done.";