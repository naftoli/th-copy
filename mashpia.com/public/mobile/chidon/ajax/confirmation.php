<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require 'encrypt.php';

$admin_id = encrypt_decrypt('decrypt', $_POST['admin']);
$sql = "update admins set confirmed_chidon_5781 = 1 where admin_id = " . $admin_id;
if (mysql_query($sql)) {
    echo json_encode([
        'success'   => true
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'There was an error saving your confirmation.',
        'sql'       => $sql
    ]);
}