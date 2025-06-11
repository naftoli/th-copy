<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../../api/header/header.php" );
require_once( __DIR__ . '/../../class.globalSettings.php' );

$stmtAdd = $MASHPIA_DB->prepare("INSERT IGNORE INTO hachayols_to_give (user_id, year) VALUES (:user, :year)");
$stmtRemove = $MASHPIA_DB->prepare("DELETE FROM hachayols_to_give WHERE user_id = :user AND year = :year");

$toAdd = $_POST['toAdd'];
$toRemove = $_POST['toRemove'];
$year = GlobalSettings::getRegistrationYear();

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
    echo json_encode(['success' => true]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success' => false,
        'error' => 'Error updating hachayol(s).'
    ]);
}