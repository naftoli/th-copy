<?php
// ini_set('display_errors',1);
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$admin = mysql_real_escape_string( $_POST['admin_id'] );
$info = json_decode( $_POST['info'] );

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $admin);

$stmt = $MASHPIA_DB->prepare("
    DELETE FROM chidon_user_goals 
    WHERE year = :year AND admin_id = :admin
");

$stmt1 = $MASHPIA_DB->prepare("
    INSERT INTO chidon_user_goals 
    SET year = :year, 
        admin_id = :admin, 
        user_id = :user, 
        goal = :goal 
");

$stmt2 = $MASHPIA_DB->prepare("
    UPDATE th_chidon 
    SET rohr_subsidy = :subsidy 
    WHERE user_id = :user AND year = :year
");

$success = true;
$MASHPIA_DB->beginTransaction();
$res = $stmt->execute([
    ':year'     =>  $year, 
    ':admin'    =>  $admin_id
]);
if ( !$res ) {
    $MASHPIA_DB->rollBack();
    echo json_encode([
        'success'   => false, 
        'error'     => "Could not save your info."
    ]);
    exit;
}

foreach ( $info as $child ) {
    if ( $child->add ) {
        $res1 = $stmt1->execute([
            ':year'     =>  $year, 
            ':admin'    =>  $admin_id, 
            ':user'     =>  $child->id, 
            ':goal'     =>  intval( $child->goal )
        ]);
        $res2 = $stmt2->execute([
            ':subsidy'  => $child->rohr ? 1 : 0, 
            ':year' =>  $year, 
            ':user' =>  $child->id
        ]);
        if ( !( $res1 && $res2 ) ) {
            // echo $stmt1->debugDumpParams();
            // echo $stmt2->debugDumpParams();
            $success = false;
            break;
        }
    } else {
        $res = $stmt->execute([
            ':year'     =>  $year, 
            ':admin'    =>  $admin_id, 
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