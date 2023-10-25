<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

$info = json_decode(file_get_contents('php://input'), true);

// check if there's any shipping fee already charged for any children of this admin
$stmt = $MASHPIA_DB->prepare("
    select * from registration_charges where type in ('RRYSD', 'RRYDA', 'RRHVN') and year = :year and user_id = :user
    ");
$stmt->execute([
    ':year' => $year,
    ':user' => $info['user']
]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (is_array($row)) echo 1;
else echo 0;