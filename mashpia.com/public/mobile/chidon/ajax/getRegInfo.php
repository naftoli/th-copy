<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $_POST['admin']);

$info = [];
$sql = "select * from th_chidon_zelda where admin_id = " . $admin_id;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['th_chidon_id']] = $row;
}

echo json_encode([
    'info'  => $info
]);