<?php
require '../../../db.php';

$admin = mysql_real_escape_string( $_POST['admin'] );
require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

$sql = "select admin_country from admins where admin_id = " . $admin;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$country = strtolower($row['admin_country']);

$val = 0;
switch ($country) {
    case '':
    case 'usa':
        $val = 1;
        break;
    case 'canada':
        $val = 2;
        break;
    default:
        $val = 3;
        break;
}

echo $val;
?>