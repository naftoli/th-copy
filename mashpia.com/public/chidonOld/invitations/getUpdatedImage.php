<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = file_get_contents('php://input');
$info = json_decode($info, true);
$id = $info['id'];

$stmt = $MASHPIA_DB->prepare('SELECT IFNULL(chidon_photo, 0) as img FROM th_chidon WHERE year = :year AND user_id = (
    SELECT user_id FROM users WHERE user_serial = :id)
');
$stmt->execute([
    ':year' => $year,
    ':id' => $id
]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result['img']) {
    echo json_encode([
        'success'   => true,
        'img' => $result['img']
    ]);
} else {
    $stmt->debugDumpParams();
    echo json_encode([
        'success'   => false,
        'img' => 0
    ]);
}