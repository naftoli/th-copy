<?php
require '../../../db.php';
$admin = mysql_real_escape_string($_POST['admin']);

require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

$names = array();
$sql = "select u.user_id, u.first, u.last from users u
        join admin_auths aa on aa.id = u.user_id
        where aa.admin_id = " . $admin;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $names[$row['user_id']][] = $row['first'] . ' ' . $row['last'];
}
echo json_encode($names);
?>