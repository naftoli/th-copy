<?php
$admin_auth = ['school'];
require_once '../../header.php';
require_once '../../api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo "Not authorized";
    exit;
}

$jsonData = file_get_contents('php://input');
$info = json_decode($jsonData, true)['info'];

$reg_charge_id = $info['reg_charge_id'];
$stmt = $MASHPIA_DB->prepare("SELECT * FROM registration_charges WHERE registration_charge_id = ?");
$stmt->execute([$reg_charge_id]);
$reg_charge = $stmt->fetch(PDO::FETCH_ASSOC);

switch ($reg_charge['type']) {
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

function refundCharge($reg_charge_id) {
    $stmt = $MASHPIA_DB->prepare("UPDATE registration_charges SET refunded = 1 WHERE registration_charge_id = ?");
    $res = $stmt->execute([$reg_charge_id]);
    return $res;
}

function unregisterChayolei($reg_charge) {
    $success = true;
    $MASHPIA_DB->beginTransaction();
    $stmt1 = $MASHPIA_DB->prepare("UPDATE users SET user_registered = NULL WHERE user_id = ?");
    $res1 = $stmt1->execute([$reg_charge['user_id']]);
    $stmt2 = $MASHPIA_DB->prepare("DELETE FROM user_registration WHERE user_id = ? AND year = ?");
    $res2 = $stmt2->execute([$reg_charge['user_id'], $reg_charge['year']]);
    $res3 = refundCharge($reg_charge['registration_charge_id']);
    if (!$res1 || !$res2 || !$res3) {
        $success = false;
    }
    if ($success) {
        $MASHPIA_DB->commit();
    } else {
        $MASHPIA_DB->rollBack();
    }
    return $success;
}

function unregisterChidon($reg_charge) {
    $success = true;
    $MASHPIA_DB->beginTransaction();
    $stmt1 = $MASHPIA_DB->prepare("UPDATE th_chidon SET reg_date = NULL WHERE user_id = ?");
    $res1 = $stmt1->execute([$reg_charge['user_id']]);
    $res2 = refundCharge($reg_charge['registration_charge_id']);
    if (!$res1 || !$res2) {
        $success = false;
    }
    if ($success) {
        $MASHPIA_DB->commit();
    } else {
        $MASHPIA_DB->rollBack();
    }
    return $success;
}