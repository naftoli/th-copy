<?php
require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once '../encrypt.php';
$admin = $_COOKIE['chidon_admin'];
$admin_id = encrypt_decrypt('decrypt', $admin);

$sql = "select * 
        from admins a 
        join chidon_parent_shipping s on s.parent_id = a.admin_id 
        where s.year = $year and a.admin_id = " . $admin_id;
$result = mysql_query($sql);
if (mysql_num_rows($result)) {
    $row = mysql_fetch_assoc($result);
    $address = addslashes($row['admin_address1'] . ' ' . $row['admin_address2'] . ' ' . $row['admin_city'] . ', ' . $row['admin_city'] .
        ' ' . $row['admin_postal'] . ' ' . $row['admin_country']);
    $info = [
        'parent_id' => $row['parent_id'],
        'cost'      => $row['cost'],
        'address'   => $address,
        'usa'       => $row['usa'],
        'paid'      => $row['chidon_shipping_paid']
    ];
    echo json_encode([
        'success' => true,
        'data' => $info
    ]);
} else {
    echo json_encode([
        'success'   => false,
        'error'     => 'Could not find shipping info for this parent.'
    ]);
}