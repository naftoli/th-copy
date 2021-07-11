<?php
header('Access-Control-Allow-Origin: *');  // CORS
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getRegistrationYear();
$school_id = $_REQUEST['school_id'] ? $_REQUEST['school_id'] : 0;
$user_id = $_REQUEST['user_id'] ? $_REQUEST['user_id'] : 0;
$discount = 0;

if ($school_id > 0) {
    $stmt = $MASHPIA_DB->prepare("
        SELECT * FROM discounts WHERE year = :year AND school_id = :school AND used is null
    ");

    $stmt->execute([
        ':year' => $year,
        ':school' => $school_id
    ]);
} else if ($user_id > 0) {
    $stmt = $MASHPIA_DB->prepare("
        SELECT * FROM discounts WHERE year = :year AND user_id = :user AND used is null
    ");

    $stmt->execute([
        ':year' => $year,
        ':user' => $user_id
    ]);
}
$row = $stmt->fetch();
if ($row['amount']) $discount = $row['amount'];
echo json_encode([
    'success' => true,
    'discount' => $discount
]);
