<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$user_id = 0;

if (isset($_GET['serial'])) {
    $serial = $_GET['serial'];
    $sql = "select user_id from users where user_serial = $serial";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $user_id = $row['user_id'];
}

$eligibility = KHK::getKHKEligibility([ $user_id ], 5783);
echo "<pre>"; print_r($eligibility); echo "</pre>";