<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/models/Admin.php';

//***************** LOAD CURRENT YEAR **********************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/CustomerProfile.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/PaymentProfile.php';
use classes\authorize\CustomerProfile as Customer;

//******************* Coupon Codes ************************/
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/coupons/class.couponCode.php';
$coupon = new CouponCode($MASHPIA_DB, $year);

//******************* GLOBAL VARIABLES ***********************/
$admin_id = $_POST['admin_id'];
$admin_email = $_POST['admin_email'];
$payment_id = intval($_POST['card_id']);
$shipping_charge = isset($_POST['shipping']) ? intval($_POST['shipping']) : 0;
$to_charge = isset($_POST['cart_total']) ? (intval($_POST['cart_total']) + $shipping_charge) : 0;
$ccInfo = isset($_POST['cc']) ? $_POST['cc'] : [];
$cart = $_POST['cart'];
$sweaters = isset($_POST['sweaters']) ? $_POST['sweaters'] : [];
$addresses = isset($_POST['addresses']) ? $_POST['addresses'] : [];
$users = [];
$user_info = [];
$reg_cost = 0;
//$iyun = false;
$celebBoxes = 0;
$celebBoxShipping = 0;
$sweater_info = [];
$sweater_shipping = 0;
$emailMsg = '';
$couponsArr = json_decode($_POST['coupons']);
$coupons = arrayByField($couponsArr, 'user_id', '');
$raisedArr = json_decode($_POST['raised']);
$raised = arrayByField($raisedArr, 'user_id', 'raised');
$tracksArr = json_decode($_POST['tracks']);
$tracks = arrayByField($tracksArr, 'user_id', 'track');
$trips = json_decode($_POST['trips']);
$ultimate_trip = json_decode($_POST['ultimate_trip']);
$ultimate_info = json_decode($_POST['ultimate_info']);

//******************* SQL QUERIES ***********************/
$sql = "update th_chidon set paid = :paid, date_paid = now(), paid_by = :admin where year = :year and user_id = :user";
$sqlReg = $MASHPIA_DB->prepare($sql);

$sql = "update th_chidon set trip = :trip where user_id = :user";
$sqlTrip = $MASHPIA_DB->prepare($sql);

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
function arrayByField($array, $key, $value) {
    $info = [];
    if (!empty($array)) {
        foreach ($array as $obj) {
            if (!$value) $info[$obj->$key] = (array) $obj;
            else $info[$obj->$key] = $obj->$value;
        }
    }
    return $info;
}

function processCart() {
    global $cart, $users, $celebBoxes, $celebBoxShipping, $user_info, $reg_cost;

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
        } else if ($item['desc'] == 'names' && $item['value']) {
            $user_info = $item['value'];
        }
//        else if ($item['desc'] == 'iyun' && intval($item['value'])) {
//            $iyun = true;
//        }
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
        return $newCard;
    }
    return false;
}

function processFee() {
    global $year, $admin_id, $to_charge, $payment_id;

    if ($payment_id) {
        $admin = \Admin::find('first', ['admin_id' => $admin_id]);
        $cp = new Customer($admin->authorize_customer_profile_id);
        $response = $cp->chargeCard($to_charge, $payment_id, null, null, 'Chidon Payment ' . $year . ' for Parent: ' . $admin_id);
        return $response;
    } else {
        $payment = addNewCard();
        if (is_object($payment)) {
            $payment_id = $payment->customerPaymentProfileId;
            $customer_profile_id = $payment->customerProfileId;
            setcookie('customer_id', $customer_profile_id, 0, '/');
            if ($payment_id && $customer_profile_id) {
                $cp = new Customer($customer_profile_id);
                $response = $cp->chargeCard($to_charge, $payment_id, null, null, 'Chidon Payment ' . $year . ' for Parent: ' . $admin_id);
                return $response;
            } else return false;
        } else {
            return $payment;
        }
    }
}

function processReg() {
    global $admin_id, $year, $sqlReg, $users;

    if (! $users) return false; // need to be registering at least one child
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
    global $MASHPIA_DB, $year, $admin_id, $shipping_charge;

    $sqlInsert = "INSERT INTO chidon_admin_shipping 
                    SET admin_id = :admin, 
                    year = :year, 
                    paid = :amount, 
                    date_paid = now()";
    $stmtInsert = $MASHPIA_DB->prepare($sqlInsert);

    $updated = true;
    if ($shipping_charge) {
        $updated = $stmtInsert->execute([
            'admin'     => $admin_id,
            'year'      => $year,
            'amount'    => $shipping_charge
        ]);
    }
    return $updated;
}

function processCelebBoxes() {
    global $MASHPIA_DB, $year, $admin_id, $addresses, $sqlCelebBox, $sqlAddress, $celebBoxes, $celebBoxShipping;

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
        else if ($res) return true;
    }
    return false;
}

function processSweaters() {
    global $MASHPIA_DB, $admin_id, $year, $addresses, $sqlSweater, $sqlAddress, $sweaters, $sweater_info;

    // update db
    $success = true;
    foreach ($sweaters as $sweater) {
        $type = $sweater['type'];
        $typeInfo = explode('_', $type);
        foreach ($sweater_info[$type] as $idx => $details) {
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
                $key = $type . '_' . $idx;
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

function redeemCoupons() {
    global $users, $coupon;

    // get serial numbers
    $serials = [];
    $user_ids = array_keys($users);
    $sql = "select user_id, user_serial from users where user_id in (" . implode(',', $user_ids) . ")";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $serials[$row['user_id']] = $row['user_serial'];
    }

    // redeem coupons
    foreach ($serials as $user_serial) {
        if ($coupon->checkForUserCode($user_serial)) $coupon->useUserCode($user_serial);
    }
}

function saveTripInfo() {
    global $sqlTrip, $trips;

    $success = true;
    foreach ($trips as $trip) {
        if (! $sqlTrip->execute([
            'trip'  => $trip->trip,
            'user'  => $trip->user_id
        ])) {
            $success = false;
            break;
        }
    }
    return $success;
}

function saveUltimateTripInfo() {
    global $MASHPIA_DB, $year, $ultimate_trip, $ultimate_info;

    $ultimate_info = (array) $ultimate_info;
    $stmt = $MASHPIA_DB->prepare("
        UPDATE th_chidon
        SET
            ultimate_trip = 1, 
            host = :family, 
            host_number = :phone, 
            host_street = :street, 
            host_street_num = :street_num, 
            host_street_num_suffix = :suffix, 
            host_street_apt = :apt, 
            in_zone = :in_zone, 
            between_street1 = :between1, 
            between_street2 = :between2, 
            allergies = :allergies, 
            sandwich = :sandwich, 
            walking_zone = :zone, 
            shoe_size = :shoe, 
            walking = :walk_alone, 
            poll = :chidon_answer
        WHERE
            user_id = :user AND year = :year
    ");

    $success = true;
    if (count($ultimate_trip)) {
        foreach ($ultimate_trip as $user_id) {
            $info = $ultimate_info[$user_id];
            $acc = $info->accomodation;
            $res = $stmt->execute([
                'family' => $acc->family,
                'phone' => $acc->phone,
                'street' => $acc->street,
                'street_num' => $acc->number,
                'suffix' => $acc->suffix,
                'apt' => $acc->apt,
                'in_zone' => $info->in_zone,
                'between1' => $acc->between1,
                'between2' => $acc->between2,
                'allergies' => $info->allergies,
                'sandwich' => $info->sandwich,
                'zone' => $acc->zone,
                'shoe' => $info->shoe,
                'walk_alone' => $info->walk_alone,
                'chidon_answer' => $info->chidon_answer,
                'user'  => $user_id,
                'year'  => $year
            ]);
            if (!$res) {
                $stmt->debugDumpParams();
                $success = false;
                break;
            }
//            $sql = "UPDATE th_chidon
//                SET
//                    ultimate_trip = 1,
//                    host = '" . mysql_real_escape_string($acc->family) . "',
//                    host_number = '" . mysql_real_escape_string($acc->phone) . "',
//                    host_street = '" . mysql_real_escape_string($acc->street) . "',
//                    host_street_num = " . mysql_real_escape_string($acc->number) . ",
//                    host_street_num_suffix = '" . mysql_real_escape_string($acc->suffix) . ",
//                    host_street_apt = '" . mysql_real_escape_string($acc->apt) . "',
//                    in_zone = " . mysql_real_escape_string($info->in_zone) . ",
//                    between_street1 = '" . mysql_real_escape_string($acc->between1) . "',
//                    between_street2 = '" . mysql_real_escape_string($acc->between2) . "',
//                    allergies = '" . mysql_real_escape_string($info->allergies) . "',
//                    sandwich = '" . mysql_real_escape_string($info->sandwich) . "',
//                    walking_zone = '" . mysql_real_escape_string($acc->zone) . "',
//                    shoe_size = '" . mysql_real_escape_string($info->shoe) . "',
//                    walking = " . mysql_real_escape_string($info->walk_alone) . ",
//                    poll = '" . mysql_real_escape_string($info->chidon_answer) . "',
//                WHERE
//                    user_id = " . $user_id . " AND year = " . $year;
//            echo $sql;
//            $res = mysql_query($sql) or die(mysql_error());
        }
    }
    return $success;
}

function extractAddress($info) {
    return $info['address'] . " " . $info['city'] . ", " . $info['state'] . " " . $info['zip'];
}

function getEmailMsg($trans_id) {
    global $users, $user_info, $celebBoxes, $sweaters, $celebBoxShipping, $addresses, $sweater_info, $to_charge, $coupons, $raised, $tracks;

    define('CELEB_BOX_COST', 20);
    define('SWEATER_COST', 25);

    $msg = "Mazal Tov for registering for the Chidon Experience<br /><br />";
    $msg .= "Below is a summary of your registration and purchase(s) where applicable.<br /><br />";

    if ($users) {
        $msg .= "REGISTRATION<br /><br /><blockquote>";
        foreach ($users as $user_id => $amount) {
            $msg .= "Registered " . $user_info[$user_id] . " for: $" . $amount . "<br />";
            $msg .= "Track: " . $tracks[$user_id] . "<br />";
            if (isset($coupons[$user_id]) || isset($raised[$user_id])) {
                $msg .= "Discounts applied:<br /><ul>";
                if (isset($coupons[$user_id])) $msg .= "<li>A $" . $coupons[$user_id]['coupon'] . " coupon has been applied 
                    (" . $coupons[$user_id]['coupon_reason'] . ").</li>";
                if (isset($raised[$user_id])) $msg .= "<li>A $" . $raised[$user_id] . " deduction was applied from what was raised on 
                    the Chidon Drive.</li>";
                $msg .= "</ul>";
            }
        }
        $msg .= "</blockquote><br /><br />";
    }

    if ($celebBoxes || $sweaters) {
        $msg .= "EXTRA PURCHASES<br /><br /><blockquote>";
        if ($celebBoxes) $msg .= "You purchased " . $celebBoxes . " Celebration Boxes for: $" . ($celebBoxes * CELEB_BOX_COST) . "<br />";
        if ($celebBoxShipping) $msg .= "It will be shipped to: " . extractAddress($addresses['celeb_box']) . "<br />";
        else $msg .= "Will be sent to your child's school.<br />";
        if ($sweaters) {
            $msg .= "Sweater(s) Purchased:<br />";
            foreach ($sweater_info as $type => $other)  {
                foreach ($other as $num_sweaters => $sweater) {
                    $size = $sweater['size'];
                    $shipping = intval($sweater['ship']);
                    for ($i = 1; $i <= $num_sweaters; $i++) {
                        $typeStr = str_replace('_', ' ', $type);
                        $msg .= $size . " " . ucwords($typeStr) . " purchased.<br />";
                        if ($shipping) {
                            $key = $type . "_" . $i;
                            $msg .= "Will be shipped to: " . extractAddress($addresses[$key]) . "<br />";
                        } else $msg .= "Will be sent to you child's school.<br />";
                    }
                }
            }
        }
        $msg .= "</blockquote><br />";
    }

    if ($to_charge) $msg .= "You were charged a total of: " . $to_charge . " today. Your transaction ID is: " . $trans_id . ".<br /><br />";

    $msg .= "All purchases are non-refundable.<br /><br />Please continue to review for the Chidon Final.<br /><br />";
    $msg .= "If you have any questions, please email <a href='mailto:chidon@tzivoshashem.org'>chidon@tzivoshashem.org</a><br /><br />";
    $msg .= "Wishing you much continued Nachas,<br /<br />Chidon HQ";

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
        return @mail($admin_email, 'Chidon Confirmation', $msg, implode("\r\n", $headers));
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
$shippingUpdated = updateShipping();
$celebBoxesProcessed = processCelebBoxes();
$sweatersProcessed = processSweaters();
$tripsSaved = saveTripInfo();
redeemCoupons();
$ultimate = saveUltimateTripInfo();

$info = [];
$trans_id = 0;
if ($registered && $shippingUpdated && $celebBoxesProcessed && $sweatersProcessed && $tripsSaved && $ultimate) {
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
        $info['msg'] = 'Congratulation! You have successfully registered your child(ren).';
    }
} else {
    $MASHPIA_DB->rollBack();
    $info['success'] = false;
    $info['error'] = 'There was an error saving your registration(s) and / or your extra purchase(s). Please try again. If this continues, please send an email to chidon@tzivoshashem.org';
    echo "Registered: " . $registered . "<br />";
    echo "Shipping Updated: " . $shippingUpdated . "<br />";
    echo "Celebration Boxes Processed: " . $celebBoxesProcessed . "<br />";
    echo "Sweaters Processed: " . $sweatersProcessed . "<br />";
    echo "Trips Saved: " . $tripsSaved . "<br />";
    echo "Ultimate Trip Info Saved: " . $ultimate;
}
// send email confirmation
if ($info['success']) {
    $msg = getEmailMsg($trans_id);
    echo $msg;
    if (!sendEmail($msg)) {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: chidon@tzivoshashem.org';
        @mail('naftoli@tzivoshashem.org', 'Error Emailing Chidon Confirmation', $msg, implode("\r\n", $headers));
    }
}

echo json_encode($info);