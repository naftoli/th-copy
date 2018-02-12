<?php
require '../../../db.php';

$admin = mysql_real_escape_string( $_POST['admin'] );
$toRegister = $_POST['toRegister'];

require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

$children = array();
$sql = "select * from admin_auths where admin_id = " . $admin . " and role_id = 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if (in_array($row['id'], $toRegister))
        $children[] = $row['id'];
}

$info = array();
$sql = "select school_id from users where user_id in (" . implode(',', $children) . ") and school_id is not null";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row['school_id'];
}

$values = array_count_values($info);
echo json_encode($values);
?>