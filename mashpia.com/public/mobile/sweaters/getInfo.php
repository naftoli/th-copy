<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';

$info = file_get_contents('php://input');
$data = json_decode($info, true);

// Check if the JSON decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    // Handle JSON decoding error
    echo json_encode([
        'success' => false,
        'message' => 'Error decoding JSON data'
    ]);
    exit;
}

$admin = encrypt_decrypt('decrypt', $data['family_id']);

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// Get the total raised for the sweater campaign
if ($admin > 0) {
    $sql = "select IFNULL(amount, 0) as total from family_raised where year = $year and admin_id = $admin";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $total = $row['total'];
    $times = floor($total / 150);
    if ($total < 150) {
        echo json_encode([
            'success'   => false,
            'error'     => 'You are not eligible to get any sweaters, b/c the total raised is less than 150.'
        ]);
        exit;
    }

    // check if the admin has already chosen the sweaters
    $sweaters = [];
    $sql = "select * from family_sweaters where year = $year and admin_id = $admin";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $sweaters[] = $row;
    }

    $children = [];
    $sql = "select * from users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        join admin_auths aa on aa.id = u.user_id 
        where aa.admin_id = $admin  
        and aa.auth = 'user' 
        and u.user_registered > 0 
        and c.class_grade != '8' 
        and u.school_id not in (61, 269, 612)";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $children[] = $row;
    }

    // get all ranks
    $sql = "SELECT * FROM ranks";
    $result = mysql_query($sql);
    $ranks = [];
    while ($row = mysql_fetch_assoc($result)) {
        $ranks[$row['rank_ord']] = $row['rank_name'];
    }

    echo json_encode([
        'success'   => true,
        'amount'    => $total,
        'times'     => $times,
        'sweaters'  => $sweaters,
        'message'   => 'You are eligible to get ' . $times . ' sweaters.',
        'children'  => $children,
        'ranks'     => $ranks
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'You are not authorized to view this page.'
    ]);
}