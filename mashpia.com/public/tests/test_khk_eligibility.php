<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$eligibility = KHK::getKHKEligibility([ 22886 ], 5783);
echo "<pre>"; print_r($eligibility); echo "</pre>";