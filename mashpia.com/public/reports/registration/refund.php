<?php
$admin_auth = ['school'];
require 'header.php';

if ($admin_user['auth'] != 'super') exit;

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

$id = $_POST['id'];
$amount = floatval(str_replace('$', '', $_POST['amount']));
$reason = $_POST['reason'];
$type = $_POST['type'];
$serial = $_POST['serial'];
$duplicate = $_POST['duplicate'];

$stmt = $MASHPIA_DB->prepare("
    update registration_charges 
    set refunded = 1, refund_reason = :reason, 
    where registration_charge_id = :id and amount = :amount 
");

// all or nothing updates to db
$MASHPIA_DB->beginTransaction();

$res = $stmt->execute([
    ':id' => $id,
    ':amount' => $amount,
    ':reason' => $reason
]);

$updated = $duplicate; // if not duplicate, then we need to update the other db
if ($res && !$duplicate) {
    // check type
    switch ($type) {
        case 'chayolei':
        case 'THE':
            $updated = updateChayolei();
            break;
        case 'HACH':
            $updated = updateHachayol();
            break;
        case 'LDE':
        case 'LDE:MYSLDS-10':
        case 'LDE:AKLDS-10:AKLDBC-20':
            $updated = updateChidon();
            break;
        case 'KHKE':
            $updated = updateKHK();
            break;
        case 'RRYSD':
        case 'RRYDA':
        case 'RRHVN':
        case 'RRKHK':
        case 'RRSUSA':
        case 'RRSCAN':
        case 'RRSINT':
            $updated = updateChidonReg();
            break;
        case 'YB1':
        case 'YB2':
        case 'YB3':
        case 'YB4':
        case 'YB5':
            $updated = updateYahadus();
            break;
        case 'shipping':
        case 'THAKUSA':
        case 'THAKCAN':
        case 'THAKINT':
        case 'THMSUSA':
        case 'THMSCAN':
        case 'THMSINT':
        default:
            $updated = true;
            break;
    }
}

if ($res && $updated) $MASHPIA_DB->commit();
else $MASHPIA_DB->rollBack();

echo json_encode([
    'success' => $res && $updated,
    'error_msg' => $stmt->errorInfo(),
    'error' => 'There was an error updating the database.'
]);

function updateChayolei() {
    global $MASHPIA_DB, $year, $serial;

    $stmt = $MASHPIA_DB->prepare("
        UPDATE users 
        SET user_registered = null 
        WHERE user_serial = :serial
    ");
    $res1 = $stmt->execute([
        ':serial' => $serial
    ]);
    $stmt = $MASHPIA_DB->prepare("
        DELETE FROM user_registration 
        WHERE year = :year 
        AND user_id = (
            SELECT user_id 
            FROM users 
            WHERE user_serial = :serial
        )
    ");
    $res2 = $stmt->execute([
        ':year' => $year,
        ':serial' => $serial
    ]);

    return $res1 && $res2;
}

function updateHachayol() {
    global $MASHPIA_DB, $serial;

    $stmt = $MASHPIA_DB->prepare("
        UPDATE users 
        SET hachayol = 0  
        WHERE user_serial = :serial
    ");
    $res = $stmt->execute([
        ':serial' => $serial
    ]);

    return $res;
}

function updateChidon() {
    global $MASHPIA_DB, $year, $serial;

    $stmt = $MASHPIA_DB->prepare("
        DELETE FROM th_chidon 
        WHERE year = :year
        AND user_id = (
            SELECT user_id
            FROM users
            WHERE user_serial = :serial
        )
    ");
    $res = $stmt->execute([
        ':year' => $year,
        ':serial' => $serial
    ]);

    return $res;
}

function updateKHK() {
    global $MASHPIA_DB, $year, $serial;

    $stmt = $MASHPIA_DB->prepare("
        UPDATE th_chidon  
        SET khk_reg = 0 
        WHERE year = :year 
        AND user_id = (
            SELECT user_id
            FROM users
            WHERE user_serial = :serial
        )
    ");
    $res = $stmt->execute([
        ':year' => $year,
        ':serial' => $serial
    ]);

    return $res;
}

function updateChidonReg() {
    global $MASHPIA_DB, $year, $serial;

    $stmt = $MASHPIA_DB->prepare("
        UPDATE th_chidon 
        SET paid = null, 
            date_paid = null, 
            paid_by = null 
        WHERE year = :year
        AND user_id = (
            SELECT user_id
            FROM users
            WHERE user_serial = :serial
        )
    ");
    $res = $stmt->execute([
        ':year' => $year,
        ':serial' => $serial
    ]);

    return $res;
}

function updateYahadus() {
    global $MASHPIA_DB, $year, $serial;

    $stmt = $MASHPIA_DB->prepare("
        DELETE FROM yahadus_book_purchases 
        WHERE year = :year 
        AND user_id = (
            SELECT user_id
            FROM users
            WHERE user_serial = :serial
        ) 
        LIMIT 1
    ");
    $res = $stmt->execute([
        ':year' => $year,
        ':serial' => $serial
    ]);

    return $res;
}