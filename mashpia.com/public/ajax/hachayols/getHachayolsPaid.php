<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once("../../api/header/header.php");

$admin = isset($_COOKIE['admin']) ? $_COOKIE['admin'] : 0;
if (!$admin) {
    echo json_encode([
        'success' => false,
        'error' => 'Could not find admin.'
    ]);
} else {
    require_once '../../class.globalSettings.php';
    require_once '../../mobile/reg/ajax/encrypt.php';

    $year = GlobalSettings::getRegistrationYear();
    $admin_id = encrypt_decrypt('decrypt', $admin);

    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            *
        FROM
            registration_charges
        WHERE
            year = :year AND type = 'HACH' 
                AND admin_id = :admin
    ");
    $stmt->execute([
        'year'  => $year,
        'admin' => $admin_id
    ]);
    $rows = $stmt->fetchAll();
    echo json_encode([
        'success'   => true,
        'num_paid'  => count($rows)
    ]);
}