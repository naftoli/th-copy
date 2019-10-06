<?php
if ( !isset( $_REQUEST['key'] ) || $_REQUEST['key'] != 'Chidon@5780!' ) {
    echo json_encode([
        'succes'    =>  false, 
        'error'     =>  "Access Forbidden."
    ]);
    exit;
}

$username = $_REQUEST['username'];
$password = $_REQUEST['password'];

if ( !$username || !$password ) {
    echo json_encode([
        'success'   =>  false,
        'error'     =>  'You must provide a username and password.'
    ]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

// authenticate user
$stmt = $MASHPIA_DB->prepare("
    SELECT admin_id, admin_address1, admin_address2, admin_city, admin_state, admin_postal, admin_country FROM admins WHERE username = :username AND password = :pass
");
$stmt->execute([
    ':username' =>  $username, 
    ':pass'     =>  $password
]);
$row = $stmt->fetch();
if ( $row ) {
    // send back encrypted admin id with year
    // require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
    // require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

    // $year = GlobalSettings::getChidonYear();
    // $admin_id = $row['admin_id'];
    // $admin = encrypt_decrypt('encrypt', $admin_id);

    // echo json_encode([
    //     'success'   =>  true,
    //     'data'      =>  [
    //         'admin' =>  $admin, 
    //         'year'  =>  $year
    //     ]
    // ]);
    $parent = $row;
    $admin_id = $row['admin_id'];

    // get info for chidon site 
    // make sure to only pull up children from grade 4 
    // make sure that the school and child registered for this year
    $stmt = $MASHPIA_DB->prepare("
        SELECT u.first, u.last, u.first_he, u.last_he, u.gender, u.dob, s.school_id, s.school_name, c.class_grade FROM users u 
        JOIN schools s USING (school_id) 
        JOIN classes c ON c.class_id = u.class_id  
        WHERE u.chidon = 1 
        AND u.user_id in (
            SELECT id FROM admin_auths 
            WHERE admin_id = :id 
            AND (role_id = 1 OR auth = 'user')
        )
    ");
    $stmt->execute([
        ':id'   => $admin_id, 
    ]);
    $rows = $stmt->fetchAll();

    $chidon_year = GlobalSettings::getChidonYear();

    $children = [];
    foreach ( $rows as $row ) {
        // make sure child is in grade 4 and up
        if ( intval( $row['class_grade'] ) < 4 ) continue;

        // make sure school has already registered for the year
        $school_id = $row['school_id'];
        $user_id = $row['user_id'];

        $stmt = $MASHPIA_DB->prepare("
            SELECT * FROM school_registrations 
            WHERE year = :year 
            AND school_id = :school
        ");
        $stmt->execute([
            ':year'     =>  $chidon_year,
            ':school'   =>  $school_id 
        ]);
        $rows = $stmt->fetchAll();
        if ( !$rows ) continue;
        
        // make sure child hasn't already registered for chidon
        $stmt = $MASHPIA_DB->prepare("
            SELECT * FROM registration_charges 
            WHERE user_id = :user 
            AND year = :year
            AND type = 'chidon' 
        ");
        $stmt->execute([
            ':user' =>  $user_id, 
            ':year' =>  $chidon_year
        ]);
        $rows = $stmt->fetchAll();
        if ( $rows ) continue;

        $chidon_cost = GlobalSettings::getChidonCost( $school_id );
        $row['chidon_cost'] = $chidon_cost;
        $children[] = $row;
    }

    $info['parent'] = $admin;
    $info['children'] = $children;

    echo json_encode([
        'success'   =>  true,
        'data'      =>  $info
    ]);
} else {
    echo json_encode([
        'success'   =>  false, 
        'error'     =>  'Your username or password is incorrect.'
    ]);
}