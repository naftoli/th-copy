<?php
require '../db.php';
require '../class.bpSummary.php';

$user = mysql_real_escape_string($_POST['user']);
$campaigns = $_POST['campaigns'];

foreach ($campaigns as $campaign) {
    $bps = new BpSummary( $campaign, 'user' );
    if (! $bps->updateSummary($user) ) {
        echo 1;
        exit;
    }
}

echo 0;