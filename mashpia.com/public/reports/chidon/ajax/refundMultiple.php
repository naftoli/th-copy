<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$trans_id = mysql_real_escape_string($_POST['trans']);
$amount = mysql_real_escape_string($_POST['amount']);
$admin_id = mysql_real_escape_string($_POST['admin']);

if ($trans_id > 1 && $amount > 0 && $admin_id) {
    $sql = "update th_chidon_parent_purchases set refund = " . floatval($amount) . ", refunded = now() 
            where authorize_id = " . $trans_id . " 
            and admin_id = " . $admin_id;
    if (mysql_query($sql)) {
        echo json_encode([
            'success' => true
        ]);
    } else {
        echo json_encode([
            'success' => false
        ]);
    }
}