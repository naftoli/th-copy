<?php
require '../db.php';
require '../class.bpSummary.php';

$school_id = mysql_real_escape_string($_POST['school']);
$campaigns = $_POST['campaigns'];

$users = array();
$sql = "select user_id from users u 
        join lines_learned ll using (user_id)
        where campaign_id in (" . implode(',', $campaigns) . ")
        and u.school_id = " . $school_id . "
        group by u.user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row['user_id'];
}

$error = 0;
foreach ($campaigns as $campaign) {
    $bps = new BpSummary( $campaign, 'user' );
    foreach ($users as $user) {
        if (! $bps->updateSummary($user) ) {
            $error = 1;
        }
    }
}

echo $error;