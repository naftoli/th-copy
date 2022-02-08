<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/models/Admin.php';

//***************** LOAD CURRENT YEAR **********************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/PaymentProfile.php';
use classes\authorize\CustomerProfile as Customer;
use classes\authorize\PaymentProfile as Payment;

//******************* GLOBAL VARIABLES ***********************/
$admin_id = $_POST['admin_id'];
$payment_id = $_POST['card_id'];
$to_charge = $_POST['cart_total'];
$ccInfo = isset($_POST['cc']) ? $_POST['cc'] : [];
$cart = $_POST['cart'];
$addresses = $_POST['addresses'];

//******************* SQL QUERIES ***********************/
$sql = "update th_chidon set paid = :paid, date_paid = now(), paid_by = :admin where year = :year and user_id = :user";
$sqlReg = $MASHPIA_DB->prepare($sql);

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
function addNewCard() {
    global $admin_id, $ccInfo;
    $admin = \Admin::find('first', ['admin_id' => $admin_id]);
    if ($admin) {
        $props = [
            'cc-number' => $ccInfo['num'],
            'cc-exp' => $ccInfo['exp'],
            'x_card_code' => $ccInfo['cvv']
        ];
        $created = $admin->createPaymentProfile($props);
        if (is_array($created)) {
            return [
                'success' => true,
                'card_id' => $created['customerPaymentProfileId']
            ];
        } else {
            return [
                'success' => false,
                'error' => $created
            ];
        }
    }
    return false;
}

function processFee() {
    global $year, $admin_id, $to_charge, $payment_id;

    if (intval($payment_id) == 0) {
        $newCard = addNewCard();
        if (!$newCard) return 'There was an error creating a new card.';
        else if (isset($newCard['error'])) return $newCard['error'];
        else $payment_id = intval($newCard['card_id']);
    }

    $sql = "select authorize_customer_profile_id from admins where admin_id = " . $admin_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $customer_id = $row['authorize_customer_profile_id'];

    $cp = new Customer( $customer_id );
    $response = $cp->chargeCard( $to_charge, $payment_id, null, null, 'Chidon Payment ' . $year . ' for Parent: ' . $admin_id );
    echo "<pre>"; print_r($response); echo "</pre>";
    return $response;
}

function processReg() {
    global $admin_id, $year, $cart, $sqlReg;

    // get users to register
    $users = [];
    foreach ($cart as $item) {
        if (strpos($item['desc'], 'reg') !== false) {
            $regInfo = implode('_', $item['desc']);
            $user_id = $regInfo[1];
            $users[$user_id] = floatval($item['value']);
        }
    }

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

function updateShipping() {
    global $admin_id, $shipping, $sqlShipping;
    $updated = true;
    if ($shipping) {
        $updated = $sqlShipping->execute([
            ':amount'   => $shipping,
            ':admin'    => $admin_id
        ]);
    }
    return $updated;
}

function processCelebBoxes() {
    global $year, $admin_id, $cart, $addresses, $sqlCelebBox, $sqlAddress, $MASHPIA_DB;

    $total = 0;
    $ship = 0;
    foreach ($cart as $item) {
        if ($item['desc'] == 'num_celeb_boxes') {
            $total = intval($item['value']);
        } else if ($item['desc'] == 'celeb_box_ship') {
            $ship = intval($item['value']);
        }
    }

    if (!$total) return true; // no need to process anything so return true meaning there's no issues
    else {
        $res = $sqlCelebBox->execute([
            ':year'     => $year,
            ':total'    => $total,
            ':admin'    => $admin_id,
            ':shipping' => $ship
        ]);
        if ($res && $ship) {
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
        else if ($res && !$ship) return true;
        else if (!$res) return false;
    }
    return false;
}

function processSweaters() {
    global $admin_id, $year, $cart, $addresses, $sqlSweater, $sqlAddress, $MASHPIA_DB;

    $sweaters = [];
    $numSweaters = [];

    // find out which sweaters were purchases and how many
    foreach ($cart as $item) {
        $types = ['mother', 'father', 'bubby', 'zaidy'];
        foreach ($types as $type) {
            $sweater_type = $type . '_sweater';
            if ($item['desc'] == $sweater_type) {
                $numSweaters[$sweater_type] = intval($item['value']);
            }
        }
    }

    // find out size and shipping info for sweaters purchased
    foreach ($numSweaters as $type => $num) {
        for ($i = 1; $i <= $num; $i++) {
            $size = $type . '_' . $i . '_size';
            $ship = $type . '_' . $i . '_ship';
            foreach ($cart as $item) {
                if ($item['desc'] == $size) {
                    $sweaters[$type][$i]['size'] = $item['value'];
                } else if ($item['desc'] == $ship) {
                    $sweaters[$type][$i]['ship'] = $item['value'];
                }
            }
        }
    }

    // update db
    $success = true;
    foreach ($sweaters as $type => $more) {
        $typeInfo = explode('_', $type);
        foreach ($more as $num => $details) {
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

function sendEmail($trans_id) {
    global $admin_id, $to_charge;


}

$MASHPIA_DB->beginTransaction();
// first do all the db stuff and only save if payment goes through
$registered = processReg();
$shippingUpdated = updateShipping();
$celebBoxesProcessed = processCelebBoxes();
$sweatersProcessed = processSweaters();

$info = [];
$trans_id = 0;
if ($registered && $shippingUpdated && $celebBoxesProcessed && $sweatersProcessed) {
    if (intval($to_charge) > 0) {
        $payment = processFee();
        if (is_array($payment)) {
            $trans_id = $payment['transactionResponse']['transId'];
            // payment went through so commit to db
            $MASHPIA_DB->commit();
            $info['success'] = true;
            $msg = 'Congratulation! You have successfully registered your child(ren) and / or ordered your additional purchase(s).\n 
                Your card has been charged $' . $to_charge . '.\nYou should receive an email confirmation shortly with all the details.\n Thank You!';
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
    $info['error'] = 'There was an error saving your registration(s) and / or your extra purchase(s). Please try again. If this continues, please contact us @ 718-907-8884';
}
// send email confirmation
if ($info['success']) sendEmail($trans_id);

echo json_encode($info);