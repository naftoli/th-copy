<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once("../../api/header/header.php");

$admin = isset($_COOKIE['admin']) ? $_COOKIE['admin'] : 0;
if (!$admin) {
    echo json_encode([
        'success' => false,
        'error' => 'Could not find admin.'
    ]);
    exit;
}

require_once '../../class.globalSettings.php';
require_once '../../mobile/reg/ajax/encrypt.php';

$year = intval(GlobalSettings::getCurrentYear());
$admin_id = encrypt_decrypt('decrypt', $admin);

$hachayols = [];
if ($year < 5786) {
    $stmt = $MASHPIA_DB->prepare("
        SELECT user_id FROM users 
        WHERE hachayol = 1 
        AND user_id IN (
            SELECT id FROM admin_auths 
            WHERE admin_id = :admin_id
        )
    ");
    $stmt->execute([
        'admin_id' => $admin_id
    ]);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $hachayols[] = intval($row['user_id']);
    }
} else {
    // same qry as the checkHachayol function in Soldier.php
    $stmt = $MASHPIA_DB->prepare("
        SELECT h.user_id FROM hachayols_to_give h 
        JOIN users u ON h.user_id = u.user_id 
        JOIN admin_auths aa ON h.user_id = aa.id 
        WHERE aa.admin_id = :admin_id 
        AND h.year = :year
    ");
    $stmt->execute([
        'admin_id' => $admin_id,
        'year' => $year
    ]);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $hachayols[] = intval($row['user_id']);
    }
}

echo json_encode([
    'success'   => true,
    'hachayols' => $hachayols
]);