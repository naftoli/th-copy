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
// authenticate user
$stmt = $MASHPIA_DB->prepare("
    SELECT * FROM admins WHERE username = :username AND password = :pass
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
    
    $ids = [];
    $stmt = $MASHPIA_DB->prepare("
        SELECT id FROM admin_auths 
        WHERE admin_id = :id 
        and (role_id = 1 or auth = 'user')
    ");
    $stmt->execute([':id' => $admin_id]);
    $rows = $stmt->fetchAll();
    foreach ( $rows as $row ) {
        $ids[] = $row['id'];
    }

    // get children info 
    $stmt = $MASHPIA_DB->query("
        SELECT * FROM users u 
        JOIN classes c USING (class_id) 
        WHERE u.user_registered > 0 
        AND u.user_id in (" . implode(',', $ids) . ")
    ");
    $rows = $stmt->fetchAll();

    $info['parent'] = $admin;
    $info['children'] = $rows;
} else {
    echo json_encode([
        'success'   =>  false, 
        'error'     =>  'Your username or password is incorrect.'
    ]);
}