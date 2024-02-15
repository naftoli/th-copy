<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$input = json_decode(file_get_contents('php://input'), true);
$school_id = $input['school'];

/**
 * Once a school presses ‘Confirm’:
 * it should automatically lock editing the Limmud evaluation Report on that schools account, it should be ‘view only’.
 * The ‘Confirm Eligibility’ button should change to say “Already Confirmed”.
 * It should say “Confirmed” by this school on the report for HQ.
 * It should open registration for this child, provided that it’s officially open.
 * It should lock Inputting Marks for test 3
 */

$error = false;
$success = addToConfirmations($school_id, $year);
echo json_encode([
    'success'   => $success,
    'error'     => 'Failed to confirm eligibility.',
    'error_msg' => $error
]);

function addToConfirmations($school_id, $year) {
    global $MASHPIA_DB, $error;
    $sql = "INSERT IGNORE INTO chidon_confirmations (school_id, year) VALUES (:school, :year)";
    $stmt = $MASHPIA_DB->prepare($sql);
    if ($stmt->execute([
        ':school'   => $school_id,
        ':year'     => $year,
    ])) {
        return true;
    } else {
        $error = $stmt->errorInfo();
        return false;
    }
}


