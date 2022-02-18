<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../../../api/models/Admin.php';

//***************** LOAD CURRENT YEAR **********************/
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once __DIR__ . '/../../../classes/authorize/CustomerProfile.php';
require_once __DIR__ . '/../../../classes/authorize/PaymentProfile.php';
use classes\authorize\CustomerProfile as Customer;
use classes\authorize\PaymentProfile as Payment;

//******************* GLOBAL VARIABLES ***********************/
$admin_id = $_POST['admin_id'];
$admin_email = $_POST['admin_email'];
$payment_id = intval($_POST['card_id']);
$to_charge = isset($_POST['cart_total']) ? intval($_POST['cart_total']) : 0;
$ccInfo = isset($_POST['cc']) ? $_POST['cc'] : [];
$shipping = isset($_POST['shipping']) ? $_POST['shipping'] : null;
$cart = $_POST['cart'];
$sweaters = isset($_POST['sweaters']) ? $_POST['sweaters'] : [];
$addresses = isset($_POST['addresses']) ? $_POST['addresses'] : [];
$users = [];
$user_info = [];
$reg_cost = 0;
$iyun = false;
$celebBoxes = 0;
$celebBoxShipping = 0;
$sweater_info = [];
$sweater_shipping = 0;
$emailMsg = '';

//******************* SQL QUERIES ***********************/
$sql = "update th_chidon set paid = :paid, date_paid = now(), paid_by = :admin where year = :year and user_id = :user";
$sqlReg = $MASHPIA_DB->prepare($sql);

$sql = "update th_chidon set khk_trip = 1 where year = :year and user_id = :user";
$sqlKhk = $MASHPIA_DB->prepare($sql);

$sql = "update admins set chidon_shipping_paid = :amount where admin_id = :admin";
$sqlShipping = $MASHPIA_DB->prepare($sql);

$sql = "insert into extra_purchases set 
        year = :year, 
        item = 'celeb_box', 
        amount = :total, 
        admin_id = :admin, 
        shipping_amount = :shipping";
$sqlCelebBox = $MASHPIA_DB->prepare($sql);

$sql = "insert into extra_purchases set 
        year = :year, 
        item = 'sweater', 
        amount = 1, 
        size = :size, 
        type_of_sweater = :type, 
        admin_id = :admin, 
        shipping_amount = :shipping";
$sqlSweater = $MASHPIA_DB->prepare($sql);

$sql = "insert into purchase_addresses set 
        purchase_id = :purchase_id, 
        address = :address, 
        city = :city, 
        state = :state, 
        zip = :zip, 
        country = :country";
$sqlAddress = $MASHPIA_DB->prepare($sql);

//******************* CUSTOM FUNCTIONS ***********************/
function processCart() {
    global $cart, $users, $celebBoxes, $celebBoxShipping, $user_info, $iyun, $reg_cost;

    $sweater_types = ['mother_sweater', 'father_sweater', 'bubby_sweater', 'zaidy_sweater'];

    foreach ($cart as $item) {
        if (strpos($item['desc'], 'reg') !== false) {
            $regInfo = explode('_', $item['desc']);
            $user_id = $regInfo[1];
            $users[$user_id] = floatval($item['value']);
            $reg_cost .= $users[$user_id];
        } else if ($item['desc'] == 'num_celeb_boxes') {
            $celebBoxes = intval($item['value']);
        } else if ($item['desc'] == 'celeb_box_ship') {
            $celebBoxShipping = intval($item['value']);
        } else if (in_array($item['desc'], $sweater_types)) {
            $sweaters[$item['desc']] = intval($item['value']);
        } else if ($item['desc'] == 'names') {
            $user_info = $item['value'];
        } else if ($item['desc'] == 'iyun' && intval($item['value'])) {
            $iyun = true;
        }
    }
}

function setSweaterInfo() {
    global $cart, $sweaters, $sweater_info, $sweater_shipping;

    // find out size and shipping info for sweaters purchased
    foreach ($sweaters as $sweater) {
        $type = $sweater['type'];
        $num = $sweater['amount'];
        for ($i = 1; $i <= $num; $i++) {
            $size = $type . '_' . $i . '_size';
            $ship = $type . '_' . $i . '_ship';
            foreach ($cart as $item) {
                if ($item['desc'] == $size) {
                    $sweater_info[$type][$i]['size'] = $item['value'];
                } else if ($item['desc'] == $ship) {
                    $sweater_info[$type][$i]['ship'] = $item['value'];
                    $sweater_shipping += intval($item['value']);
                }
            }
        }
    }
}

function addNewCard() {
    global $admin_id, $ccInfo;

    $admin = \Admin::find('first', ['admin_id' => $admin_id]);
    if ($admin) {
        $props = [
            'cc-number' => $ccInfo['num'],
            'cc-exp' => $ccInfo['exp'],
            'x_card_code' => $ccInfo['cvv']
        ];
        $newCard = $admin->createPaymentProfile($props);
        if (is_object($newCard)) {
            return $newCard->customerPaymentProfileId;
        }
    }
    return false;
}

function processFee() {
    global $year, $admin_id, $to_charge, $payment_id;

    if (! $payment_id) $payment_id = addNewCard();
    if ($payment_id) {
        $sql = "select authorize_customer_profile_id from admins where admin_id = " . $admin_id;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $customer_id = $row['authorize_customer_profile_id'];

        $cp = new Customer($customer_id);
        $response = $cp->chargeCard($to_charge, $payment_id, null, null, 'Chidon Payment ' . $year . ' for Parent: ' . $admin_id);
        return $response;
    } else return false;
}

function processReg() {
    global $admin_id, $year, $sqlReg, $users;

    if (! $users) return true; // nothing to save so all is good
    else {
        // register users
        $success = true;
        foreach ($users as $user_id => $amount) {
            $res = $sqlReg->execute([
                ':paid' => $amount,
                ':admin' => $admin_id,
                ':year' => $year,
                ':user' => $user_id
            ]);
            if (!$res) {
                $success = false;
                break;
            }
        }
        return $success;
    }
}

function processKhk() {
    global $sqlKhk, $year;

    $success = true;
    if (isset($_COOKIE['khk_trip'])) {
        foreach ($_COOKIE['khk_trip'] as $user_id) {
            $res = $sqlKhk->execute([
                ':year' => $year,
                ':user' => $user_id
            ]);
            if (!$res) {
                $success = false;
                break;
            }
        }
    }
    return $success;
}

function updateShipping() {
    global $admin_id, $sqlShipping, $shipping;

    $updated = true;
    if (! is_null($shipping)) {
        $updated = $sqlShipping->execute([
            ':amount'   => $shipping,
            ':admin'    => $admin_id
        ]);
    }
    return $updated;
}

function processCelebBoxes() {
    global $year, $admin_id, $addresses, $sqlCelebBox, $sqlAddress, $MASHPIA_DB, $celebBoxes, $celebBoxShipping;

    if (!$celebBoxes) return true; // no need to process anything so there's no issues
    else {
        $res = $sqlCelebBox->execute([
            ':year'     => $year,
            ':total'    => $celebBoxes,
            ':admin'    => $admin_id,
            ':shipping' => $celebBoxShipping
        ]);
        if ($res && $celebBoxShipping) {
            $purchase_id = $MASHPIA_DB->lastInsertId();
            $addressInfo = $addresses['celeb_box'];
            $res2 = $sqlAddress->execute([
                ':purchase_id'  => $purchase_id,
                ':address'      => $addressInfo['address'],
                ':city'         => $addressInfo['city'],
                ':state'        => $addressInfo['state'],
                ':zip'          => $addressInfo['zip'],
                ':country'      => 'USA'
            ]);
            if ($res2) return true;
        }
        else if ($res && !$celebBoxShipping) return true;
        else if (!$res) return false;
    }
    return false;
}

function processSweaters() {
    global $admin_id, $year, $addresses, $sqlSweater, $sqlAddress, $MASHPIA_DB, $sweaters, $sweater_info;

    // update db
    $success = true;
    foreach ($sweaters as $sweater) {
        $type = $sweater['type'];
        $num = $sweater['amount'];
        $typeInfo = explode('_', $type);
        foreach ($sweater_info[$type] as $details) {
            $res = $sqlSweater->execute([
                ':year'     => $year,
                ':size'     => $details['size'],
                ':type'     => $typeInfo[0],
                ':admin'    => $admin_id,
                ':shipping' => $details['ship']
            ]);
            if (!$res) {
                $success = false;
                break 2;
            } else if (intval($details['ship'])) {
                $purchase_id = $MASHPIA_DB->lastInsertId();
                $key = $type . '_' . $num;
                $addressInfo = isset($addresses[$key]) ? $addresses[$key] : false;
                if (!$addressInfo) {
                    $success = false;
                    break 2;
                } else {
                    $res2 = $sqlAddress->execute([
                        ':purchase_id'  => $purchase_id,
                        ':address'      => $addressInfo['address'],
                        ':city'         => $addressInfo['city'],
                        ':state'        => $addressInfo['state'],
                        ':zip'          => $addressInfo['zip'],
                        ':country'      => 'USA'
                    ]);
                    if (!$res2) {
                        $success = false;
                        break 2;
                    }
                }
            }
        }
    }
    return $success;
}

function getEmailMsg($trans_id) {
    global $iyun, $users, $user_info, $shipping, $celebBoxes, $celebBoxShipping, $sweaters, $sweater_shipping, $to_charge, $reg_cost;

    define('CELEB_BOX_COST', 20);
    define('SWEATER_COST', 25);

    $msg = '';

    if ($users) {
        $msg .= "THANK YOU FOR REGISTERING YOUR CHILD(REN).<br /><br />";
        $msg .= 'Your registration is complete for ';
        $numUsers = count($users);
        $i = 1;
        foreach ($users as $user_id => $amount) {
            $msg .= $user_info[$user_id];
            if ($i++ < $numUsers) $msg .= ' and ';
        }
        $msg .= ".<br />";
        if ($iyun) $msg .= 'The Iyun part of the final will be online on Sunday, 10 Adar 2 (March 13).<br />';
        $msg .= "<br />";
    }

    // check if there was payment
    if ($to_charge) {
        if (! $users) {
            $msg .= "THANK YOU FOR YOUR PURCHASES.<br /><br />";
        }
        $msg .= "Your payment of $" . $to_charge . " was received. Your transaction ID for your records is: " .
            $trans_id . ".<br /><br />";

        $msg .= "<b>A Summary of your charges is as follows:</b><br /><br />";
        if ($users) $msg .= "Total Registration Charges: $" . (floatval($reg_cost) + intval($shipping)) . "<br />";
        if ($celebBoxes) $msg .= "Total Celebration Box(es) Charge: $" . ($celebBoxes * CELEB_BOX_COST + $celebBoxShipping) . "<br />";
        if ($sweaters) {
            $total = 0;
            foreach ($sweaters as $type => $amount) {
                $total += intval($amount) * SWEATER_COST;
            }
            $msg .= "Total Sweaters Charge: $" . ($total + $sweater_shipping) . "<br />";
        }
        $msg .= "<br /><b>Grand Total: $" . $to_charge . "</b>.<br /><br />";
    }

    $msg .= "Please continue to review for the Chidon Final on Wednesday, 6 Adar 2 (March 9).<br /><br />
        If you have any questions, please email chidon@tzivoshashem.org.<br /><br />Much Hatzlocha!<br />Chidon HQ";

    $msg .= "<br /><br /><footer style='font-size: 9px; color: grey;'>Our Address: <address>792 Eastern Parkway Brooklyn, NY 11213</address><br /><br />
            To Unsubscribe please click <a href='http://mashpia.com/unsubscribe.php'>here</a></footer>";

    return $msg;
}

function sendEmail($msg) {
    global $admin_email;

    if ($admin_email) {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: Chidon Headquarters <chidon@tzivoshashem.org>';
        $headers[] = 'Reply-to: Chidon Headquarters <chidon@tzivoshashem.org>';
        $headers[] = 'Bcc: chidonreg@gmail.com';
        if (isset($_COOKIE['myshliach']) && intval($_COOKIE['myshliach'])) $headers[] = 'Cc: chidon@myshliach.com';
        return mail($admin_email, 'Chidon Confirmation', $msg, implode("\r\n", $headers));
    }
    return false;
}

//******************* PROGRAM STARTS HERE ***********************/
processCart();
setSweaterInfo();

// process everything
$MASHPIA_DB->beginTransaction();
// first do all the db stuff and only save if payment goes through
$registered = processReg();
$khk = processKhk();
$shippingUpdated = updateShipping();
$celebBoxesProcessed = processCelebBoxes();
$sweatersProcessed = processSweaters();

$info = [];
$trans_id = 0;
if ($registered && $khk && $shippingUpdated && $celebBoxesProcessed && $sweatersProcessed) {
    if ($to_charge) {
        $payment = processFee();
        if (! $payment) {
            $MASHPIA_DB->rollBack();
            $info['success'] = false;
            $info['error'] = 'There seems to have been an issue with your new card.';
            echo json_encode($info);
            exit;
        }
        if (is_array($payment)) {
            $trans_id = $payment['transactionResponse']['transId'];
            // payment went through so commit to db
            $MASHPIA_DB->commit();
            $info['success'] = true;
            $msg = 'Congratulation! You have successfully registered your child(ren) and / or ordered your additional purchase(s).' . "\r\n" .
                'Your card has been charged $' . $to_charge . '. Your transaction ID for your record is: ' . $trans_id . '.' . "\r\n" .
                'You should receive an email confirmation shortly with all the details.' . "\r\n" .
                'If you do not receive an email, please check your SPAM folder'. "\r\n" . 'Thank You!';
            $info['msg'] = $msg;
        } else {
            $MASHPIA_DB->rollBack();
            $info['success'] = false;
            $info['error'] = $payment;
        }
    } else {
        $MASHPIA_DB->commit();
        $info['success'] = true;
        $msg = 'Congratulation! You have successfully registered your child(ren).';
    }
} else {
    $MASHPIA_DB->rollBack();
    $info['success'] = false;
    $info['error'] = 'There was an error saving your registration(s) and / or your extra purchase(s). Please try again. If this continues, please send an email to chidon@tzivoshashem.org';
}
// send email confirmation
if ($info['success']) {
    $msg = getEmailMsg($trans_id);
//    echo $msg;
    if (!sendEmail($msg)) {
        $header = 'From: chidon@tzivoshashem.org';
        @mail('naftoli@tzivoshashem.org', 'Error Emailing Chidon Confirmation', $msg, $header);
    }
}

echo json_encode($info);