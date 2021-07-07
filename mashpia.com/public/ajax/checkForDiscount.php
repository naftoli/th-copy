<?php
header('Access-Control-Allow-Origin: *');  // CORS
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$school_id = mysql_real_escape_string($_REQUEST['school_id']);
$discount = 0;
$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM discounts WHERE year = :year AND school_id = :school
");
$stmt->execute([
    ':year'     => $year,
    ':school'   => $school_id
]);
$row = $stmt->fetch();
if ($row['discount']) $discount = $row['discount'];
echo json_encode([
    'success'   => true,
    'discount'  => $discount
]);
