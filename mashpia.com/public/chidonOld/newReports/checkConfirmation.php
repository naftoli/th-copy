<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$year = mysql_real_escape_string($_POST['year']);

// get all the schools connected to this admin
$schools = $admin_user['auths']['school'];

$confirmed = false;
$sql = "select * from chidon_confirmations where year = $year and school_id in (" . implode(',', $schools) . ")";
$result = mysql_query($sql);
if (mysql_num_rows($result) == count($schools)) $confirmed = true;

echo json_encode([
    'alreadyConfirmed'  => $confirmed
]);