<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonYear();

$stmt = $MASHPIA_DB->prepare("
    INSERT IGNORE into th_chidon_chaps_needed (year, school_id, needed) 
    VALUES 
        (:year, :school, :needed) 
    ON DUPLICATE KEY UPDATE
        needed = :needed
");

$success = true;
$info = $_POST['info'];
$MASHPIA_DB->beginTransaction();
foreach ( $info as $details ) {
    $res = $stmt->execute([
        ':year'     =>  $year,
        ':school'   =>  $details['school_id'], 
        ':needed'   =>  $details['numSupers']
    ]);
    if ( !$res ) {
        $success = false;
        break;
    }
}
if ( $success ) {
    $MASHPIA_DB->commit();
} else {
    $MASHPIA_DB->rollBack();
}
echo json_encode([
    'success' => $success
]);