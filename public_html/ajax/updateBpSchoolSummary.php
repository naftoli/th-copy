<?php
//ini_set('display_errors',1);
require '../db.php';
require '../class.bpSummary.php';

$school = mysql_real_escape_string($_POST['school']);
$campaigns = $_POST['campaigns'];

foreach ($campaigns as $campaign) {
    $bps = new BpSummary( $campaign, 'school' );
    if (! $bps->updateSummary($school) ) {
        echo 1;
        exit;
    }
}
echo 0;