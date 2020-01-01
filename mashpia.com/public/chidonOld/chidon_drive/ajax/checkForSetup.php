<?php
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

if ( $admin_id ) {
    $stmt = $MASHPIA_DB->prepare("
        SELECT 
            a.admin_id, a.last, COUNT(tc.user_id) AS setup
        FROM
            admins a
                JOIN
            th_chidon tc on (a.admin_id = tc.parent_id) 
        WHERE
            year = :year AND admin_id = :admin 
                AND tc.fundraising_goal > 0 
    ");
    $res = $stmt->execute([
      ':year'   =>  $year, 
      ':admin'  =>  $admin_id
    ]);
    //echo $stmt->debugDumpParams(); exit;
    if ( $res ) {
        $info = $stmt->fetch();
        echo json_encode([
            'success'   =>  true, 
            'info'      =>  $info
        ]);
    } else {
        echo json_encode([
            'success'   =>  false,
            'error'     =>  'Error retrieving admin info.'
        ]);
    }
} else {
    echo json_encode([
        'success'   =>  false,
        'error'     =>  'Invalid Admin ID.'
    ]);
}