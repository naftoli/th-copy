<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permissions.";
    exit;
}

// load db handle
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$stmtUser = $MASHPIA_DB->prepare("
    SELECT * FROM users u 
    JOIN admin_auths aa ON aa.id = u.user_id 
    WHERE user_serial = :serial
");

$chayolei = [
    [
        'serial'    => 7745260,
        'paid'      => 50
    ]
];

$chidon = [
    [
        'serial'    => 7755655,
        'size'      => 'Children L',
        'book'      => 3,
        'paid'      => 45
    ]
];
/*
foreach ($chayolei as $user) {
    $serial = $user['serial'];
    $paid = $user['paid'];
    $stmtUser->execute([':serial' => $user['serial']]);
    $user_info = $stmtUser->fetch();
    $user_id = $user_info['user_id'];
    $admin_id = $user_info['admin_id'];
    $registered = $user_info['user_registered'];
    $school_id = $user_info['school_id'];

    echo "checking info for user: " . $user_id . "<br />";
    if ( is_null($registered) || empty($registered) ) updateReg( $user_id );
    checkUserReg( $admin_id, $school_id, $user_id, $paid );
    checkRegCharges( $user_id, $school_id, $paid );
    echo "<br />";
}
*/
foreach ($chidon as $user) {
    $serial = $user['serial'];
    $paid = $user['paid'];
    $size = strtolower($user['size']);
    $book = $user['book'];
    $stmtUser->execute([':serial' => $user['serial']]);
    $user_info = $stmtUser->fetch();
    $user_id = $user_info['user_id'];
    $admin_id = $user_info['admin_id'];
    $registered = $user_info['user_registered'];
    $school_id = $user_info['school_id'];

    echo "checking info for user: " . $user_id . "<br />";
    checkChidonReg( $admin_id, $school_id, $user_id, $paid, $size, $book );
    echo "<br />";
}

function updateReg( $user_id ) {
    global $MASHPIA_DB;
    $stmt = $MASHPIA_DB->prepare("
        UPDATE users SET user_registered = now() WHERE user_id = :id
    ");
    echo "updating user...<br />";
    $stmt->execute([
        ':id'   => $user_id
    ]);
}

function checkUserReg( $admin_id, $school_id, $user_id, $paid ) {
    global $MASHPIA_DB, $year;
    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO user_registration 
        SET 
            admin_id = :admin, 
            school_id = :school, 
            user_id = :user, 
            reg_date = now(),  
            paid = :paid, 
            year = :year
        ON DUPLICATE KEY UPDATE 
            admin_id = :admin, 
            school_id = :school
    ");
    echo "checking user_registration...<br />";
    $stmt->execute([
        ':admin'    => $admin_id,
        ':school'   => $school_id,
        ':user'     => $user_id,
        ':paid'     => $paid,
        ':year'     => $year
    ]);
}

function checkRegCharges( $user_id, $school_id, $paid ) {
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
                trans_id = 0, 
                user_id = :user, 
                school_id = :school, 
                amount = :paid, 
                year = :year,
                date = now(), 
                type = 'chayolei'
        ");
        $stmt->execute([
            ':user'     => $user_id,
            ':school'   => $school_id,
            ':paid'     => $paid,
            ':year'     => $year
        ]);
    }
}

function checkChidonReg( $admin_id, $school_id, $user_id, $paid, $size, $book ) {
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
              type = 'chidon'
    ");
    echo "checking chidon registration...<br />";
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
                trans_id = 0, 
                user_id = :user, 
                school_id = :school, 
                amount = :paid, 
                year = :year,
                date = now(), 
                type = 'chidon'
        ");
        $stmt->execute([
            ':user'     => $user_id,
            ':school'   => $school_id,
            ':paid'     => $paid,
            ':year'     => $year
        ]);
    }
    // check chidon table
    $stmt = $MASHPIA_DB->prepare("
        SELECT * FROM th_chidon 
        WHERE
            user_id = :user 
        AND
            year = :year
    ");
    $stmt->execute([
        ':user' => $user_id,
        ':year' => $year
    ]);
    $rows = $stmt->fetchAll();
    if ( empty( $rows ) ) {
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO th_chidon 
            SET
                user_id = :user, 
                year = :year, 
                size = :size, 
                book = :book,
                school_id = :school, 
                parent_id = :admin,
                reg_date = now()
        ");
        $stmt->execute([
            ':user'     => $user_id,
            ':year'     => $year,
            ':school'   => $school_id,
            ':admin'    => $admin_id,
            ':size'     => $size,
            ':book'     => $book
        ]);
    } else {
        $stmt = $MASHPIA_DB->prepare("
            UPDATE th_chidon 
            SET
                size = :size, 
                book = :book, 
                school_id = :school, 
                parent_id = :admin
            WHERE
                 user_id = :user AND year = :year
        ");
        $stmt->execute([
            ':user'     => $user_id,
            ':year'     => $year,
            ':school'   => $school_id,
            ':admin'    => $admin_id,
            ':size'     => $size,
            ':book'     => $book
        ]);
    }
}