<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "No Permissions.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$stmt = $MASHPIA_DB->prepare("
    UPDATE th_chidon_schools SET bus = :bus WHERE year = :year AND school_id = :school
");
$stmt->bindValue(':bus', $_POST['bus'], PDO::PARAM_INT);
$stmt->bindValue(':year', $year, PDO::PARAM_INT);
$stmt->bindValue(':school', $_POST['school_id'], PDO::PARAM_INT);
$stmt->execute();

if ( $stmt->rowCount() ) {
    echo json_encode([
        'success' => true
    ]);
} else {
    echo json_encode([
        'success' => false
    ]);
}