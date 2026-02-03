<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../api/header/db.php';
require_once __DIR__ . '/../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$input = json_decode(file_get_contents('php://input'), true);
$school_id = $input['school_id'];
$open_registration = $input['open_registration'];

$sql = "UPDATE schools SET open_reg_5786 = :open_registration WHERE school_id = :school_id";
$stmt = $MASHPIA_DB->prepare($sql);
$res = $stmt->execute(['open_registration' => $open_registration, 'school_id' => $school_id]);
echo json_encode(['success' => $res]);