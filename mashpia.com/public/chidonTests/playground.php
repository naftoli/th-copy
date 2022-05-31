<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once 'class.chidonTests.php';

//$sql = "select user_id from th_chidon_info";
//$result = mysql_query($sql);
//while ($row = mysql_fetch_assoc($result)) {
//    $ids[] = $row['user_id'];
//}
$ids = [18451, 17117, 18319, 62881];

$results = KHK::getKHKEligibility($ids);
$eligibility = $results[0];
$details = $results[1];
echo "<pre>"; print_r($eligibility); print_r($details); echo "</pre>";
echo "KHK Fee: " . KHK::$khkFee;