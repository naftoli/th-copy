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

$stmtUser = $MASHPIA_DB->prepare("
    SELECT * FROM users u 
    JOIN admin_auths aa ON aa.id = u.user_id 
    WHERE user_serial = :serial
");

$stmt = $MASHPIA_DB->query("
    SELECT 
        *
    FROM
        transactions
    WHERE
        description LIKE 'Parent Registration%'
            AND trans_date > '2020-09-07'
");
$rows = $stmt->fetchAll();

// parse rows
$info = [];
foreach ( $rows as $row ) {
    $description = $row['description'];
    // check if description has word chayolei
    if ( $posChayolei = strpos($description, 'chayolei') !== false ) {
        // only check the chayolei reg NOT the chayolei + chidon reg
        if ( strpos($description, 'chidon') === false ) {
            // find out how much was paid in total
            $posPaid = strpos($description, ')');
            $total_paid = substr($description, ($posChayolei + 10), ($posPaid - ($posChayolei + 10)));
            // get user serials
            $pos = strpos($description, '5781:');
            $serials = substr($description, ($pos + 6));
            $arrSerials = explode(',', $serials);
            $paid = $total_paid / count($arrSerials);
            echo $description . "<br />" . "Paid: " . $paid . "<br />";
            // check each child to make sure he / she was registered properly
            foreach ( $arrSerials as $serial ) {
                // get user id using serial and separate serial and school id
                $arrInfo = explode(':', $serial);
                $serial_num = $arrInfo[0];
                $school_id = $arrInfo[1];

                $stmtUser->execute([':serial' => $serial_num]);
                $user = $stmtUser->fetch();
                $user_id = $user['user_id'];
                $admin_id = $user['admin_id'];
                $registered = $user['user_registered'];

                echo "checking info for user: " . $user_id . "<br />";
                if ( is_null($registered) || empty($registered) ) updateReg( $user_id, $row['trans_date'] );
                checkUserReg( $admin_id, $school_id, $user_id, $row['trans_date'], $paid );
                checkRegCharges( $row['trans_id'], $user_id, $school_id, $paid, $row['trans_date'] );
                echo "<br />";
            }
        } else {
            echo $description . "<br />" . $serials . "<br /><br />";
        }
    }
}

function updateReg( $user_id, $date ) {
    global $MASHPIA_DB;
    $stmt = $MASHPIA_DB->prepare("
        UPDATE users SET user_registered = :date WHERE user_id = :id
    ");
    echo "updating user...<br />";
//    $stmt->execute([
//        ':date' => $date,
//        ':id'   => $user_id
//    ]);
}

function checkUserReg( $admin_id, $school_id, $user_id, $date, $paid ) {
    global $MASHPIA_DB, $year;
    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO user_registration 
        SET 
            admin_id = :admin, 
            school_id = :school, 
            user_id = :user, 
            reg_date = :date, 
            paid = :paid, 
            year = :year
        ON DUPLICATE KEY UPDATE 
            admin_id = :admin, 
            school_id = :school
    ");
    echo "checking user_registration...<br />";
//    $stmt->execute([
//        ':admin'    => $admin_id,
//        ':school'   => $school_id,
//        ':user'     => $user_id,
//        ':date'     => $date,
//        ':paid'     => $paid,
//        ':year'     => $year
//    ]);
}

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
//    $stmt->execute([
//        ':user'     => $user_id,
//        ':school'   => $school_id,
//        ':paid'     => $paid,
//        ':year'     => $year
//    ]);
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
                date = :date 
        ");
//        $stmt->execute([
//            ':id'       => $id,
//            ':user'     => $user_id,
//            ':school'   => $school_id,
//            ':paid'     => $paid,
//            ':year'     => $year,
//            ':date'     => $date
//        ]);
    }
}