<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

$jsonData = file_get_contents('php://input');
$reg_charge = json_decode($jsonData, true)['info'];

$MASHPIA_DB->beginTransaction();
switch ($reg_charge['Code']) {
    case 'THE':
        // chayolei registration charge
        $res = unregisterChayolei($reg_charge);
        break;
    case 'LDE':
        // chidon registration charge
        $res = unregisterChidon($reg_charge);
        break;
    default:
        // refund the charge
        $res = refundCharge($reg_charge_id);
        break;
}
echo json_encode(['success' => $res]);
$res = false;
if ($res) {
    $MASHPIA_DB->commit();
} else {
    $MASHPIA_DB->rollBack();
}

function refundCharge($reg_charge_id) {
    global $MASHPIA_DB;
    $stmt = $MASHPIA_DB->prepare("UPDATE registration_charges SET refunded = 1 WHERE registration_charge_id = ?");
    $res = $stmt->execute([$reg_charge_id]);
    return $res;
}

function unregisterChayolei($reg_charge) {
    global $MASHPIA_DB;
    $success = true;
    $stmt1 = $MASHPIA_DB->prepare("UPDATE users SET user_registered = NULL WHERE user_id = (
    select user_id from users where user_serial = ?)");
    $res1 = $stmt1->execute([$reg_charge['User Serial']]);
    $stmt2 = $MASHPIA_DB->prepare("DELETE FROM user_registration WHERE year = ? AND user_id = (
    select user_id from users where user_serial = ?)");
    $res2 = $stmt2->execute([$reg_charge['year'], $reg_charge['User Serial']]);
    $res3 = refundCharge($reg_charge['reg_charge_id']);
    $success = $res1 && $res2 && $res3;
    return $success;
}

function unregisterChidon($reg_charge) {
    global $MASHPIA_DB;
    $success = true;
    $stmt1 = $MASHPIA_DB->prepare("UPDATE th_chidon SET reg_date = NULL WHERE year = ? AND user_id = (
    select user_id from users where user_serial = ?)");
    $res1 = $stmt1->execute([$reg_charge['year'], $reg_charge['User Serial']]);
    $res2 = refundCharge($reg_charge['reg_charge_id']);
    $success = $res1 && $res2;
    return $success;
}