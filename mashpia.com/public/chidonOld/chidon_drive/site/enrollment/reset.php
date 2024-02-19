<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/coupons/class.couponCode.php';

$year = GlobalSettings::getChidonYear();
$date = '2024-02-17';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$serial = $_REQUEST['serial'];
if (!$serial) {
    echo "Serial not found.";
    exit;
}

$admin_id = getAdminID($serial);
if (!$admin_id) {
    echo "Admin not found.";
    exit;
}

$authorize_id = 0;
$MASHPIA_DB->beginTransaction();
$a = resetReg($admin_id);
$b = resetCharges($admin_id);
$c = resetFamilyBalances($admin_id);
$d = resetExtraPurchases($admin_id);
$e = resetShipping($admin_id);
$f = resetCoupons($admin_id);
if ($a && $b && $c && $d && $e && $f) {
    $MASHPIA_DB->commit();
    echo "Reset successful.<br />Authorize Profile ID: $authorize_id<br />";
} else {
    $MASHPIA_DB->rollBack();
    echo "Reset failed.";
}

function getAdminID($serial) {
    global $MASHPIA_DB, $authorize_id;

    $sql = "select * from admins where admin_id in (
            select admin_id from admin_auths where id = (
            select user_id from users where user_serial = :serial))";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([
        ':serial' => $serial
    ]);
    $admin = $stmt->fetch();
    $authorize_id = $admin['authorize_customer_profile_id'];

    return $admin['admin_id'];
}

function resetReg($admin_id) {
    global $MASHPIA_DB, $year;

    $sql = "select * from th_chidon where year = :year and user_id in (
            select id from admin_auths where admin_id = :admin)";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([
        ':year' => $year,
        ':admin' => $admin_id
    ]);
    $users = $stmt->fetchAll();

    $stmt = $MASHPIA_DB->prepare("
         update th_chidon 
         set paid = null, date_paid = null, paid_by = null 
         where year = :year and user_id = :user");
    foreach ($users as $user) {
        $res = $stmt->execute([
            ':year' => $year,
            ':user' => $user['user_id']
        ]);
        if (!$res) {
            return false;
        }
    }
    return true;
}

function resetCharges($admin_id) {
    global $MASHPIA_DB, $year, $date;

    $sql = "select registration_charge_id from registration_charges 
            where year = :year and date > :date and type like 'RR%' and user_id in (
            select id from admin_auths where admin_id = :admin)";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([
        ':year' => $year,
        ':admin' => $admin_id,
        ':date' => $date
    ]);
    $charges = $stmt->fetchAll();
    $ids = array_map(function($c) { return $c['registration_charge_id']; }, $charges);

    // delete charges
    if (count($ids) == 0) {
        return true;
    }
    $res = $MASHPIA_DB->query("
        delete from registration_charges 
        where registration_charge_id in (" . implode(',', $ids) . ")");
    return $res;
}

function resetFamilyBalances($admin_id) {
    global $MASHPIA_DB, $year;

    $sql = "select * from family_prepaid_balances where year = :year and admin_id = :admin";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([
        ':year' => $year,
        ':admin' => $admin_id
    ]);
    $balance = $stmt->fetch();
    if (!$balance) {
        return true;
    }

    $stmt = $MASHPIA_DB->prepare("
        update family_prepaid_balances 
        set used = 0, used_2 = 0, refund_amount = null, refund_type = null, paypal = null, accounting_code = null 
        where year = :year and admin_id = :admin");
    $res = $stmt->execute([
        ':year' => $year,
        ':admin' => $balance['admin_id']
    ]);
    if (!$res) {
        return false;
    }

    return true;
}

function resetExtraPurchases($admin_id) {
    global $MASHPIA_DB, $year;

    $sql = "delete from extra_purchases where year = :year and admin_id = :admin";
    $stmt = $MASHPIA_DB->prepare($sql);
    $res = $stmt->execute([
        ':year' => $year,
        ':admin' => $admin_id
    ]);
    return $res;
}

function resetShipping($admin_id) {
    global $MASHPIA_DB, $year;

    $sql = "delete from chidon_parent_shipping where year = :year and parent_id = :admin";
    $stmt = $MASHPIA_DB->prepare($sql);
    $res = $stmt->execute([
        ':year' => $year,
        ':admin' => $admin_id
    ]);
    return $res;
}

function resetCoupons($admin_id) {
    global $MASHPIA_DB, $year;

    $coupon = new CouponCode($MASHPIA_DB, $year);
    // get all serials based of admin_id
    $sql = "select user_serial from users where id in (
            select id from admin_auths where admin_id = :admin and auth = 'user')";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([
        ':admin' => $admin_id
    ]);
    $rows = $stmt->fetchAll();
    $serials = array_map(function($r) { return $r['user_serial']; }, $rows);
    foreach ($serials as $user_serial) {
        if ($coupon->checkForUserCode($user_serial)) $coupon->useUserCode($user_serial);
    }
    return true;
}