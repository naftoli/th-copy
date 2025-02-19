<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$stmt = $MASHPIA_DB->prepare('SELECT IFNULL(chidon_photo, 0) as img FROM th_chidon WHERE year = :year AND user_id = :id');
$stmt->execute([
    ':year' => $year,
    ':id' => $id
]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result['img']) {
    echo $result['img'];
} else {
    echo 0;
}