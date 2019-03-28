<?php
require 'db.php';

$users = array();
$sql = "select user_id from users where school_id = 466";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

require 'class.campaignEnrollment.php';
foreach ($users as $user) {
    $c = new CampaignEnrollment($user);
    $c->enroll();
}