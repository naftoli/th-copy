<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getChidonYear();

$stmt = $MASHPIA_DB->prepare("
    INSERT IGNORE into th_chidon_avgs (year, school_id, grade, avg) 
    VALUES 
        (:year, :school, :grade, :avg) 
    ON DUPLICATE KEY UPDATE
        avg = :avg
");

$success = true;
$avgs = $_POST['avgs'];
$MASHPIA_DB->beginTransaction();
foreach ( $avgs as $details ) {
    foreach ( $details['grades'] as $grade ) {
        $res = $stmt->execute([
            ':year'     =>  $year,
            ':school'   =>  $details['school'], 
            ':grade'    =>  $grade['grade'], 
            ':avg'      =>  $grade['avg']
        ]);
        if ( !$res ) {
            $success = false;
            break 2;
        }   
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