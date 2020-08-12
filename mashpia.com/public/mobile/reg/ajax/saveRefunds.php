<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';

$total = $_POST['total'];
$admin = encrypt_decrypt('decrypt', $_COOKIE['admin']);

// get parent cc info

// process refund

// set flag in db to indicate parent received refund

echo json_encode([
    'success'   =>  false,
    'info'      =>  $admin
]);