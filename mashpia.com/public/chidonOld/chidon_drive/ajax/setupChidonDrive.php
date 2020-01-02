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
        rohr_subsidy = :subsidy, 
        show_pic = :pic 
    WHERE user_id = :user AND year = :year
");

$success = true;
$MASHPIA_DB->beginTransaction();
foreach ( $info as $child ) {
    if ( $child->add ) {
        $res = $stmt->execute([
            ':goal'     =>  intval( $child->goal ),
            ':subsidy'  =>  $child->rohr ? 1 : 0,
            ':pic'      =>  $child->pic ? 0 : 1, 
            ':year'     =>  $year, 
            ':user'     =>  $child->id
        ]);
        if ( !$res ) {
            // echo $stmt1->debugDumpParams();
            // echo $stmt2->debugDumpParams();
            $success = false;
            break;
        }
    } else {
        $res = $stmt->execute([
            ':goal'     =>  null,
            ':subsidy'  =>  0,
            ':pic'      =>  1, 
            ':year'     =>  $year, 
            ':user'     =>  $child->id
        ]);
        if ( !$res ) {
            $success = false;
            break;
        }
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