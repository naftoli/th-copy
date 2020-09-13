<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

checkRegCharges( 25045, 150718, 577, 50, '2020-09-10 11:33:25');

function checkRegCharges( $id, $user_id, $school_id, $paid, $date ) {
    global $MASHPIA_DB, $year;
    // check if exist
    $stmt = $MASHPIA_DB->prepare("
        SELECT * FROM registration_charges 
        WHERE 
              user_id = :user 
        AND
              school_id = :school
        AND
              amount = :paid
        AND
              year = :year
        AND
              type = 'chayolei'
    ");
    echo "checking user_registration...<br />";
    $stmt->execute([
        ':user'     => $user_id,
        ':school'   => $school_id,
        ':paid'     => $paid,
        ':year'     => $year
    ]);
    $rows = $stmt->fetchAll();
    if ( empty( $rows ) ) {
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO registration_charges
            SET
                trans_id = :id, 
                user_id = :user, 
                school_id = :school, 
                amount = :paid, 
                year = :year,
                date = :date, 
                type = 'chayolei'
        ");
        $stmt->execute([
            ':id'       => $id,
            ':user'     => $user_id,
            ':school'   => $school_id,
            ':paid'     => $paid,
            ':year'     => $year,
            ':date'     => $date
        ]);
    }
}
