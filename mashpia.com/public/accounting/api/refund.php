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

if (! isset($_COOKIE['naftoli'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

$jsonData = file_get_contents('php://input');
$reg_charge = json_decode($jsonData, true)['info'];

$codes = ['AKLDBC', 'AKLDS', 'CB', 'CBS', 'CD', 'CT', 'CV', 'HACH', 'KHKE', 'LDE', 'MYSLDS', 'R', 'RRFAM', 
            'RRHVN', 'RRKHK', 'RRSCAN', 'RRSINT', 'RRSUSA', 'RRYDA', 'RRYSD', 'SW', 'SWS', 
            'THAKCAN', 'THAKINT', 'THAKUSA', 'THE', 'THMSCAN', 'THMSINT', 'THMSUSA', 'V', 
            'YB1', 'YB2', 'YB3', 'YB4', 'YB5'];
if (!in_array($reg_charge['Code'], $codes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid code']);
    exit;
}

$MASHPIA_DB->beginTransaction();
if ($reg_charge['action'] != 'refundOnly') {
    switch ($reg_charge['Code']) {
        case 'CB':
        case 'SW':
            $res = removeExtraPurchase($reg_charge);
            break;
        case 'CBS':
        case 'SWS':
            $res = removeExtraPurchaseShipping($reg_charge);
            break;
        case 'HACH':
            $res = removeHachayol($reg_charge['User Serial'], $reg_charge['year']);
            break;
        case 'KHKE':
            $res = removeKHK($reg_charge);
            break;
        case 'LDE':
        case 'RRHVN':
        case 'RRKHK':
        case 'RRYDA':
        case 'RRYSD':
            $res = unregisterChidon($reg_charge);
            break;
        case 'THE':
            $res = unregisterChayolei($reg_charge);
            break;
        case 'YB1':
        case 'YB2':
        case 'YB3':
        case 'YB4':
        case 'YB5':
            $res = removeYahadusBook($reg_charge);
            break;
        default:
            $res = true;
            break;
    }
}

if ($res) {
    $result = removeCharge($reg_charge, $reg_charge['action'] == 'refundOnly');
    if ($result) {
        $MASHPIA_DB->commit();
        echo json_encode(['success' => true, 'message' => 'Refunded charge']);
        exit;
    }
}
// if we get here, the refund failed
$MASHPIA_DB->rollBack();
echo json_encode(['success' => false, 'message' => 'Failed to refund charge']);
exit;

function refundCharge($reg_charge, $refundOnly = false) {
    global $MASHPIA_DB;

    if ($refundOnly) {
        $stmt = $MASHPIA_DB->prepare("UPDATE registration_charges SET refunded = 1, keep_charge = 1 WHERE registration_charge_id = ?");
    } else {
        $stmt = $MASHPIA_DB->prepare("UPDATE registration_charges SET refunded = 1 WHERE registration_charge_id = ?");
    }
    $res = $stmt->execute([$reg_charge['reg_charge_id']]);
    if (! $res) {
        return false;
    }

    $res = Refund::processRefundFromDatabase($reg_charge['trans_id'], null, $MASHPIA_DB);
    if (! $res['success']) {
        return $res;
    }

    return true;
}

function unregisterChayolei($reg_charge) {
    global $MASHPIA_DB;

    $stmt1 = $MASHPIA_DB->prepare("UPDATE users SET user_registered = NULL WHERE user_id = (
    select user_id from users where user_serial = ?)");
    $res1 = $stmt1->execute([$reg_charge['User Serial']]);
    $stmt2 = $MASHPIA_DB->prepare("DELETE FROM user_registration WHERE year = ? AND user_id = (
    select user_id from users where user_serial = ?)");
    $res2 = $stmt2->execute([$reg_charge['year'], $reg_charge['User Serial']]);
    return $res1 && $res2;
}

function unregisterChidon($reg_charge) {
    global $MASHPIA_DB;

    $stmt1 = $MASHPIA_DB->prepare("UPDATE th_chidon SET reg_date = NULL WHERE year = ? AND user_id = (
    select user_id from users where user_serial = ?)");
    $res1 = $stmt1->execute([$reg_charge['year'], $reg_charge['User Serial']]);
    return $res1;
}

function removeHachayol($user_serial, $year) {
    global $MASHPIA_DB;

    $stmt = $MASHPIA_DB->prepare("DELETE FROM hachayols_to_give WHERE year = ? AND user_id = (
        SELECT user_id FROM users WHERE user_serial = ?)");
    $res = $stmt->execute([$year, $user_serial]);
    return $res;
}

function removeExtraPurchase($reg_charge) {
    global $MASHPIA_DB;

    $item = $reg_charge['Code'] == 'CBS' ? 'celeb_box' : $reg_charge['Code'] == 'SWS' ? 'sweater' : null;
    if (! $item) {
        return false;
    }

    $stmt = $MASHPIA_DB->prepare("DELETE FROM extra_purchases WHERE year = ? AND admin_id = (
        SELECT admin_id FROM registration_charges WHERE registration_charge_id = ?) AND item = ? limit 1");
    $res = $stmt->execute([$reg_charge['year'], $reg_charge['reg_charge_id'], $item]);
    return $res;
}

function removeExtraPurchaseShipping($reg_charge) {
    global $MASHPIA_DB;

    $item = $reg_charge['Code'] == 'CBS' ? 'celeb_box' : $reg_charge['Code'] == 'SWS' ? 'sweater' : null;
    if (! $item) {
        return false;
    }

    $stmt = $MASHPIA_DB->prepare("SELECT purchase_id, shipping_amount, use_admin_shipping_address FROM extra_purchases WHERE year = ? AND admin_id = (
        SELECT admin_id FROM registration_charges WHERE registration_charge_id = ?) AND item = ?");
    $res = $stmt->execute([$reg_charge['year'], $reg_charge['reg_charge_id'], $item]);
    if (! $res) {
        return false;
    }

    $row = $stmt->fetch();
    if (! $row) {
        return true;
    }

    $purchase_id = $row['purchase_id'];
    $shipping_amount = $row['shipping_amount'];
    $use_admin_shipping_address = $row['use_admin_shipping_address'];

    if ($shipping_amount > 0) {
        $stmt = $MASHPIA_DB->prepare("UPDATE extra_purchases_shipping SET shipping_amount = 0, use_admin_shipping_address = 0 WHERE purchase_id = ?");
        $res1 = $stmt->execute([$purchase_id]);
        if ($use_admin_shipping_address) {
            $stmt = $MASHPIA_DB->prepare("DELETE FROM purchase_addresses WHERE purchase_id = ?");
            $res2 = $stmt->execute([$purchase_id]);
            return $res1 && $res2;
        }
        return $res1;
    }
    return true;
}

function removeKHK($reg_charge) {
    global $MASHPIA_DB;

    $stmt = $MASHPIA_DB->prepare("UPDATE th_chidon SET khk_reg = 0 WHERE year = ? AND user_id = (
        SELECT user_id FROM users WHERE user_serial = ?)");
    $res = $stmt->execute([$reg_charge['year'], $reg_charge['User Serial']]);
    return $res;
}

function removeYahadusBook($reg_charge) {
    global $MASHPIA_DB;

    $stmt = $MASHPIA_DB->prepare("DELETE FROM yahadus_book_purchases WHERE year = ? AND user_id = (
        SELECT user_id FROM users WHERE user_serial = ?) AND location = 'parent_account' limit 1");
    $res = $stmt->execute([$reg_charge['year'], $reg_charge['User Serial']]);
    return $res;
}