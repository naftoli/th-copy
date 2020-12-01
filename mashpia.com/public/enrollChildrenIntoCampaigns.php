<?php
$admin_auth = ['school'];
require 'header.php';

if ($admin_user['auth'] != 'super') {
    echo 'No Permission';
    exit;
}

$ids = [];
$school_id = $_GET['school_id'];
$sql = "select user_id from users where school_id = " . mysql_real_escape_string( $school_id );
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $ids[] = $row['user_id'];
}

require_once 'class.campaignEnrollment.php';

// update child to be enrolled in all campaigns
foreach ($ids as $user_id) {
    try {
        $c = new CampaignEnrollment($user_id);
        $c->enroll();
    } catch (EnrollmentException $e) {
        echo $e->getMessage();
    }
}
echo "done.";