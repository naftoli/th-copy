<?php
require '../../../db.php';

$admin = mysql_real_escape_string( $_POST['admin'] );

require 'encrypt.php';
$admin = encrypt_decrypt('decrypt', $admin);

$sql = "select * from admins where admin_id = " . $admin;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);

// check email address on homepage
if(isset($_POST['onlyEmail']) && $_POST['onlyEmail'] == "true") {
    echo json_encode([
        "success" => !empty($row['admin_email']),
    ]);
    die();
}
// do the normal check
if (empty($row['admin_email']) ||
    empty($row['admin_address1']) ||
    empty($row['admin_city']) ||
    empty($row['admin_state']) ||
    empty($row['admin_postal']) ||
    empty($row['admin_country']) || 
    (empty($row['admin_phone_home']) && empty($row['admin_phone_mobile']) && empty($row['admin_phone_work']))
    )
    echo 1;
else echo 0;