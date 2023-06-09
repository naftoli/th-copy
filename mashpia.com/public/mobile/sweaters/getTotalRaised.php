<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

require $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
$admin = mysql_real_escape_string( $_POST['family_id'] );
$admin = encrypt_decrypt('decrypt', $admin);

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

    echo json_encode([
        'success'   => true,
        'amount'    => $total,
        'times'     => $times,
        'sweaters'  => $sweaters,
        'message'   => 'You are eligible to get ' . $times . ' sweaters.'
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'You are not authorized to view this page.'
    ]);
}