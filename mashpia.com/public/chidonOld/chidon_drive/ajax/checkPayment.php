<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

$sql = "select * from th_chidon_parent_purchases where authorize_id > 1 and admin_id = " . $admin_id;
$result = mysql_query($sql);
if (mysql_num_rows($result) > 0) {
    $row = mysql_fetch_assoc($result);
    echo json_encode([
        'success'   => true,
        'payment'   => [
            'id'    => $row['th_chidon_parent_purchase_id'],
            'amount'    => $row['amount']
        ]
    ]);
    exit;
}
echo json_encode([
    'success' => false
]);