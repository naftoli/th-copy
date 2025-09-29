<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../../api/header/header.php" );
require_once( __DIR__ . '/../../class.globalSettings.php' );
require_once( __DIR__ . '/../../mobile/reg/ajax/encrypt.php' );

// Identify admin from cookie to scope processing guard
$adminEnc = isset($_COOKIE['admin']) ? $_COOKIE['admin'] : 0;
if (!$adminEnc) {
    echo json_encode([
        'success' => false,
        'error' => 'Could not find admin.'
    ]);
    exit;
}
$admin_id = encrypt_decrypt('decrypt', $adminEnc);

$stmtAdd = $MASHPIA_DB->prepare("INSERT IGNORE INTO hachayols_to_give (user_id, year) VALUES (:user, :year)");
$stmtRemove = $MASHPIA_DB->prepare("DELETE FROM hachayols_to_give WHERE user_id = :user AND year = :year");

$toAdd = $_POST['toAdd'];
$toRemove = $_POST['toRemove'];
$year = GlobalSettings::getRegistrationYear();

// Processing guard to avoid concurrent updates that can cause duplicates
function startProcessing() {
    global $MASHPIA_DB, $admin_id;
    $MASHPIA_DB->query("INSERT INTO payment_processing (admin_id) VALUES (" . intval($admin_id) . ")");
}

function endProcessing() {
    global $MASHPIA_DB, $admin_id;
    $MASHPIA_DB->query("DELETE FROM payment_processing WHERE admin_id = " . intval($admin_id));
}

function processingInProgress() {
    global $MASHPIA_DB, $admin_id;
    $stmt = $MASHPIA_DB->query("SELECT 1 FROM payment_processing WHERE admin_id = " . intval($admin_id) . " LIMIT 1");
    return (bool)$stmt->fetchColumn();
}

if (processingInProgress()) {
    echo json_encode([
        'success' => false,
        'error' => 'An update is already being processed. Please wait and try again.'
    ]);
    exit;
}
startProcessing();

$MASHPIA_DB->beginTransaction();
$success = true;

foreach ($toAdd as $user_id) {
    $res = $stmtAdd->execute([
        'user'  => $user_id,
        'year'  => $year
    ]);
    if (!$res) {
        $success = false;
        break;
    }
}
foreach ($toRemove as $user_id) {
    $res = $stmtRemove->execute([
        'user'  => $user_id,
        'year'  => $year
    ]);
    if (!$res) {
        $success = false;
        break;
    }
}

if ($success) {
    $MASHPIA_DB->commit();
    endProcessing();
    echo json_encode(['success' => true]);
} else {
    $MASHPIA_DB->rollBack();
    endProcessing();
    echo json_encode([
        'success' => false,
        'error' => 'Error updating hachayol(s).'
    ]);
}