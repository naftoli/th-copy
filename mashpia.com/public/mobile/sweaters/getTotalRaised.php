<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
$admin = mysql_real_escape_string( $_POST['family_id'] );
$admin = encrypt_decrypt('decrypt', $admin);

// Get the total raised for the sweater campaign
if ($admin > 0) {
//    $sql = "";
//    $result = mysql_query($sql);
//    $row = mysql_fetch_assoc($result);
//    $total = $row['total'];
    $total = 150;
    if ($total < 150) {
        echo json_encode([
            'success'   => false,
            'error'     => 'You are not eligible to get any sweaters, b/c the total raised is less than 150.'
        ]);
        exit;
    }

    $times = floor($total / 150);
    echo json_encode([
        'success'   => true,
        'amount'    => $total,
        'times'     => $times
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'You are not authorized to view this page.'
    ]);
}