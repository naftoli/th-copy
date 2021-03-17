<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$admin = mysql_real_escape_string($_POST['admin']);
$amount = mysql_real_escape_string($_POST['amount']);

$sql = "update th_chidon_parent_purchases set refund = " . floatval($amount) . ", refunded = now() where authorize_id > 1 and admin_id = " . $amount;
if (mysql_query($sql)) {
    echo json_encode([
        'success'   => true
    ]);
} else {
    echo json_encode([
        'success'   => false
    ]);
}