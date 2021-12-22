<?php
//ini_set('display_errors',1);
require __DIR__ . '/../../../api/header/db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$admin = mysql_real_escape_string( $_POST['admin_id'] );
$info = json_decode( $_POST['info'] );

require __DIR__ . '/../../../mobile/reg/ajax/encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin);

$stmt = $MASHPIA_DB->prepare("
    UPDATE th_chidon 
    SET fundraising_goal = :goal, 
        fundraising_minutes = :minutes, 
        fundraising_type = :type,
        show_pic = :pic 
    WHERE user_id = :user AND year = :year
");

$success = true;
$MASHPIA_DB->beginTransaction();
foreach ( $info as $child ) {
    $res = $stmt->execute([
        ':pic'                  =>  $child->show_pic ? 1 : 0,
        ':year'                 =>  $year,
        ':user'                 =>  $child->user_id,
        'fundraising_goal'      =>  $child->amount,
        'fundraising_minutes'   =>  $child->hours,
        'fundraising_type'      =>  $child->track
    ]);
    if ( !$res ) {
        $success = false;
        break;
    }
}
if ( $success ) {
    $MASHPIA_DB->commit();
    echo json_encode([
        'success'   => true
    ]);
} else {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success'   => false, 
        'error'     => "Could not save your info."
    ]);
}