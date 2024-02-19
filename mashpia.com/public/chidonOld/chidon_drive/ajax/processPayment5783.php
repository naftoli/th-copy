<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

require_once '../../../api/header/db.php';
require_once '../../../api/models/Admin.php';

//***************** LOAD CURRENT YEAR **********************/
require_once '../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once '../../../classes/authorize/CustomerProfile.php';
require_once '../../../classes/authorize/PaymentProfile.php';
use classes\authorize\CustomerProfile as Customer;

//******************* Coupon Codes ************************/
require_once '../../../chidonOld/coupons/class.couponCode.php';
$coupon = new CouponCode($MASHPIA_DB, $year);

//******************* GLOBAL VARIABLES ***********************/
$admin_id = $_POST['admin_id'];
$admin_email = $_POST['admin_email'];
$payment_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
$shipping_charge = isset($_POST['shipping']) ? intval($_POST['shipping']) : 0;
$credit = isset($_POST['credit']) ? intval($_POST['credit']) : 0;
$already_used_credit = isset($_POST['already_used_credit']) ? intval($_POST['already_used_credit']) : 0;
$to_charge = isset($_POST['cart_total']) ? (intval($_POST['cart_total']) - $credit) : 0;
$total_without_credit = isset($_POST['cart_total']) ? intval($_POST['cart_total']) : 0;
$ccInfo = isset($_POST['cc']) ? $_POST['cc'] : [];
$cart = isset($_POST['cart']) ? $_POST['cart'] : [];
$sweaters = isset($_POST['sweaters']) ? $_POST['sweaters'] : [];
$addresses = isset($_POST['addresses']) ? $_POST['addresses'] : [];
$users = [];
$user_info = [];
//$iyun = false;
$celebBoxes = 0;
$celebBoxShipping = 0;
$sweater_info = [];
$emailMsg = '';
if (isset($_POST['coupons'])) {
    $couponsArr = json_decode($_POST['coupons']);
    $coupons = arrayByField($couponsArr, 'user_id', 'coupon');
}
else $coupons = [];

if (isset($_POST['raised'])) {
    $raisedArr = json_decode($_POST['raised']);
    $raised = arrayByField($raisedArr, 'user_id', 'raised');
}
else $raised = [];

if (isset($_POST['tracks'])) {
    $tracksArr = json_decode($_POST['tracks']);
    $tracks = arrayByField($tracksArr, 'user_id', 'track');
}
else $tracks = [];

$trips = isset($_POST['trips']) ? json_decode($_POST['trips']) : [];
$ultimate_trip = isset($_POST['ultimate_trip']) ? json_decode($_POST['ultimate_trip']) : [];
$ultimate_info = isset($_POST['ultimate_info']) ? json_decode($_POST['ultimate_info']) : [];
$country = isset($_POST['country']) ? $_POST['country'] : '';
$creditVal = isset($_POST['creditVal']) ? $_POST['creditVal'] : 0;
$paypal_email = isset($_POST['paypal_email']) ? $_POST['paypal_email'] : '';

define('CELEB_BOX_COST', 20);
define('SWEATER_COST', 25);

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
    global $cart, $users, $celebBoxes, $celebBoxShipping, $user_info;

    if ($cart && count($cart)) {
        foreach ($cart as $item) {
            if (strpos($item['desc'], 'reg') !== false) {
                $regInfo = explode('_', $item['desc']);
                $user_id = $regInfo[1];
                $users[$user_id] = floatval($item['value']);
            } else if ($item['desc'] == 'num_celeb_boxes') {
                $celebBoxes = intval($item['value']);
            } else if ($item['desc'] == 'celeb_box_ship') {
                $celebBoxShipping = intval($item['value']);
            } else if ($item['desc'] == 'names' && $item['value']) {
                $user_info = $item['value'];
            }
        }
    }
}

function setSweaterInfo() {
    global $cart, $sweaters, $sweater_info;

    // find out size and shipping info for sweaters purchased
    if ($sweaters && count($sweaters)) {
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
                    }
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
    global $admin_id, $to_charge, $payment_id;

    // create description for authorize
    $desc = getAuthDesc();

    if ($payment_id) {
        $admin = \Admin::find('first', ['admin_id' => $admin_id]);
        $cp = new Customer($admin->authorize_customer_profile_id);
        $response = $cp->chargeCard($to_charge, $payment_id, null, null, $desc);
        return $response;
    } else {
        $payment = addNewCard();
        if (is_object($payment)) {
            $payment_id = $payment->customerPaymentProfileId;
            $customer_profile_id = $payment->customerProfileId;
            if ($payment_id && $customer_profile_id) {
                $cp = new Customer($customer_profile_id);
                $response = $cp->chargeCard($to_charge, $payment_id, null, null, $desc);
                return $response;
            } else return false;
        } else {
            return $payment;
        }
    }
}

function getAuthDesc() {
    // code format (either for child or family):
    // C<user_serial>:<code>-<amount>,
    // F<admin_id>:<code>-<amount>,
    $desc = [];
    $descriptions = getDescriptions();
    foreach ($descriptions as $item) {
        $desc[] = $item['prefix'] . $item['id'] . ':' . $item['code'] . '-' . $item['amount'];
    }

    return implode(',', $desc);
}

function insertIntoRegCharges($trans_id = 0) {
    global $MASHPIA_DB, $users, $year;

    $user_ids = array_keys($users);
    $stmtSchoolIDs = $MASHPIA_DB->query("
        SELECT user_id, school_id 
        FROM users 
        WHERE user_id in (" . implode(',', $user_ids) . ")
    ");
    $rows = $stmtSchoolIDs->fetchAll();
    foreach ($rows as $row) {
        $school_ids[$row['user_id']] = $row['school_id'];
    }

    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO registration_charges 
        SET 
            trans_id = :trans_id,
            user_id = :user, 
            school_id = :school, 
            admin_id = :admin,
            type = :type, 
            amount = :amount, 
            year = :year
    ");

    $success = true;
    $descriptions = getDescriptions();

    $MASHPIA_DB->beginTransaction();
    foreach ($descriptions as $item) {
        if ($item['prefix'] == 'C') {
            if (! $stmt->execute([
                'trans_id' => $trans_id,
                'user' => $item['id'],
                'school' => isset($item['school_id']) ? $item['school_id'] : $school_ids[$item['id']],
                'admin' => 0,
                'type' => $item['code'],
                'amount' => $item['amount'],
                'year' => $year
            ])) {
                $success = false;
                break;
            }
        } else if ($item['prefix'] == 'F') {
            if (! $stmt->execute([
                'trans_id' => $trans_id,
                'user' => 0,
                'school' => 0,
                'admin' => $item['id'],
                'type' => $item['code'],
                'amount' => $item['amount'],
                'year' => $year
            ])) {
                $success = false;
                break;
            }
        }
    }

    if ($success) {
        $MASHPIA_DB->commit();
        return true;
    } else {
//        $stmt->debugDumpParams();
        $MASHPIA_DB->rollBack();
        return false;
    }
}

function getDescriptions() {
    global $users, $admin_id, $celebBoxes, $sweaters, $celebBoxShipping, $sweater_info, $tracks, $ultimate_trip,
           $shipping_charge, $country, $credit, $already_used_credit;

    $desc = [];

    if ($already_used_credit == 0) {
        $existing_codes = getExistingCodes();
        if (count($existing_codes)) {
            // if there's credit first zero out the amounts in the registration_charges table
            $desc = [];
            if ($credit > 0) {
                foreach ($existing_codes as $code) {
                    $desc[] = [
                        'prefix' => 'C',
                        'id' => $code['user_id'],
                        'code' => $code['type'],
                        'amount' => '-' . $code['amount'],
                        'school_id' => $code['school_id']
                    ];
                }
            }
        }
    }

    if (count($users)) {
        foreach ($users as $user_id => $amount) {
            $user_track = $tracks[$user_id];
            switch ($user_track) {
                case 'Yesod':
                    $code = 'RRYSD';
                    break;
                case 'Yediah':
                    $code = 'RRYDA';
                    break;
                case 'Havonah':
                case 'Iyun':
                    if (in_array($user_id, $ultimate_trip)) $code = 'RRKHK';
                    else $code = 'RRHVN';
                    break;
                default:
                    continue;
            }
            $desc[] = [
                'prefix'    => 'C',
                'id'        => $user_id,
                'code'      => $code,
                'amount'    => $amount
            ];
        }
    }

    if ($shipping_charge) {
        // figure out code for shipping charge
        // check country of family
        switch ($country) {
            case 'USA':
                $code = 'RRSUSA';
                break;
            case 'Canada':
                $code = 'RRSCAN';
                break;
            default:
                $code = 'RRSINT';
                break;
        }
        $desc[] = [
            'prefix'    => 'F',
            'id'        => $admin_id,
            'code'      => $code,
            'amount'    => $shipping_charge
        ];
    }

    if ($celebBoxes) {
        $desc[] = [
            'prefix'    => 'F',
            'id'        => $admin_id,
            'code'      => 'RRCB',
            'amount'    => $celebBoxes * CELEB_BOX_COST
        ];
        if ($celebBoxShipping) {
            $desc[] = [
                'prefix'    => 'F',
                'id'        => $admin_id,
                'code'      => 'RRCBS',
                'amount'    => $celebBoxShipping
            ];
        }
    }

    if ($sweaters) {
        $shipping_cost = 0;
        $num_sweaters = 0;
        foreach ($sweater_info as $other)  {
            foreach ($other as $sweater) {
                $num_sweaters++;
                $shipping_cost += intval($sweater['ship']);
            }
        }
        if ($num_sweaters) {
            $desc[] = [
                'prefix'    => 'F',
                'id'        => $admin_id,
                'code'      => 'RRSW',
                'amount'    => $num_sweaters * SWEATER_COST
            ];
            if ($shipping_cost) {
                $desc[] = [
                    'prefix'    => 'F',
                    'id'        => $admin_id,
                    'code'      => 'RRSWS',
                    'amount'    => $shipping_cost
                ];
            }
        }
    }

    return $desc;
}

function getExistingCodes() {
    global $admin_id, $year, $MASHPIA_DB;

    // get children for admin
    $stmt = $MASHPIA_DB->prepare("
        SELECT id 
        FROM admin_auths 
        WHERE admin_id = :admin AND auth = 'user'
    ");
    $stmt->execute([':admin' => $admin_id]);
    $children = $stmt->fetchAll();
    $user_ids = array_map(function($child) {
        return $child['id'];
    }, $children);

    // get codes
    $stmt2 = $MASHPIA_DB->prepare("
        SELECT user_id, type, amount, school_id 
        FROM registration_charges
        WHERE user_id in (" . implode(',', $user_ids) . ") AND year = :year 
        AND type in ('RRYSD', 'RRYDA', 'RRHVN', 'RRSUSA', 'RRSCAN', 'RRSINT', 'PRRSUSA', 'PRRSCAN', 'PRRSINT') 
        AND amount > 0 
    ");
    $stmt2->execute([':year' => $year]);
    $codes = $stmt2->fetchAll();

    return $codes;
}

function updateFamilyBalance() {
    global $MASHPIA_DB, $admin_id, $year, $to_charge, $credit, $creditVal, $paypal_email, $total_without_credit, $already_used_credit;

    if ($credit > 0) {
        $desc = getDescriptions();

        // create string for accounting_code
        // format is <prefix><id>:<code>-<amount>,<prefix><id>:<code>-<amount>,...
        $code = '';
        foreach ($desc as $item) {
            $code .= $item['prefix'] . $item['id'] . ':' . $item['code'] . '-' . $item['amount'] . ',';
        }
        // remove trailing comma
        $code = rtrim($code, ',');

        $stmt = $MASHPIA_DB->prepare("
                UPDATE family_prepaid_balances 
                SET used = :amount, 
                    refund_amount = :refund,
                    refund_type = :type, 
                    paypal = :paypal, 
                    accounting_code = :code 
                WHERE admin_id = :admin AND year = :year
        ");

        if ($already_used_credit) {
            $stmt = $MASHPIA_DB->prepare("
                UPDATE family_prepaid_balances 
                SET used_2 = :amount, 
                    refund_amount = :refund,
                    refund_type = :type, 
                    paypal = :paypal, 
                    accounting_code = :code 
                WHERE admin_id = :admin AND year = :year
            ");
        }

        // find out if we are using up all prepaid amount
        if ($to_charge > 0) {
            $amount = $credit;
            $refund = 0;
            $type = '';
            $code = '';
        } else {
            $amount = $total_without_credit;
            $refund = $credit - $total_without_credit;
            switch (intval($creditVal)) {
                case 1:
                    $type = 'donation';
                    break;
                case 2:
                    $type = 'refund';
                    break;
                case 3:
                    $type = 'paypal';
                    break;
            }
        }

        if (! $stmt->execute([
            ':amount'   => $amount,
            ':refund'   => $refund,
            ':type'     => $type,
            ':paypal'   => $paypal_email,
            ':admin'    => $admin_id,
            ':year'     => $year,
            ':code'     => $code
        ])) {
//            $stmt->debugDumpParams();
            return false;
        }
        return true;
    }
    return true;
}

function processReg() {
    global $admin_id, $year, $sqlReg, $users;

    // register users
    $success = true;
    if ($users && count($users)) {
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
    }
    return $success;
}

function updateShipping() {
    global $MASHPIA_DB, $year, $admin_id, $shipping_charge;

    $sqlInsert = "UPDATE chidon_parent_shipping 
                  SET amount_paid = :amount, date_paid = now()
                  WHERE parent_id = :admin AND year = :year";
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
    if ($sweaters && count($sweaters)) {
        foreach ($sweaters as $sweater) {
            $type = $sweater['type'];
            $typeInfo = explode('_', $type);
            foreach ($sweater_info[$type] as $idx => $details) {
                $res = $sqlSweater->execute([
                    ':year' => $year,
                    ':size' => $details['size'],
                    ':type' => $typeInfo[0],
                    ':admin' => $admin_id,
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
                            ':purchase_id' => $purchase_id,
                            ':address' => $addressInfo['address'],
                            ':city' => $addressInfo['city'],
                            ':state' => $addressInfo['state'],
                            ':zip' => $addressInfo['zip'],
                            ':country' => 'USA'
                        ]);
                        if (!$res2) {
                            $success = false;
                            break 2;
                        }
                    }
                }
            }
        }
    }
    return $success;
}

function getSerials(array $ids) {
    global $MASHPIA_DB;

    $serials = [];
    $sql = "select user_id, user_serial from users where user_id in (" . implode(',', $ids) . ")";
    $stmt = $MASHPIA_DB->query($sql);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $serials[$row['user_id']] = $row['user_serial'];
    }

    return $serials;
}

function redeemCoupons() {
    global $coupon, $users;

    if ($users && count($users)) {
        $serials = getSerials(array_keys($users));
        // redeem coupons
        if ($serials && count($serials)) {
            foreach ($serials as $user_serial) {
                if ($coupon->checkForUserCode($user_serial)) $coupon->useUserCode($user_serial);
            }
        }
    }
}

function saveTripInfo() {
    global $sqlTrip, $trips;

    $success = true;
    if ($trips && count($trips)) {
        foreach ($trips as $trip) {
            if (!$sqlTrip->execute([
                'trip' => $trip->trip,
                'user' => $trip->user_id
            ])) {
                $success = false;
                break;
            }
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
            between_streets1 = :between1, 
            between_streets2 = :between2, 
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
    if ($ultimate_trip && count($ultimate_trip)) {
        foreach ($ultimate_trip as $user_id) {
            if (isset($ultimate_info[$user_id])) {
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
                    'user' => $user_id,
                    'year' => $year
                ]);
                if (!$res) {
//                    $stmt->debugDumpParams();
                    $success = false;
                    break;
                }
            }
        }
    }
    return $success;
}

function extractAddress($info) {
    return $info['address'] . " " . $info['city'] . ", " . $info['state'] . " " . $info['zip'];
}

function getEmailMsg($trans_id, $last_four) {
    global $users, $user_info, $celebBoxes, $sweaters, $celebBoxShipping, $addresses, $sweater_info, $to_charge, $coupons,
           $raised, $tracks, $credit, $creditVal, $paypal_email;

    $msg = "Below is a summary of your Chidon registration and extra purchase(s) where applicable.<br /><br />";

    if ($users) {
        $msg .= "REGISTRATION<br /><br /><blockquote>";
        foreach ($users as $user_id => $amount) {
            $msg .= "Registered " . $user_info[$user_id]['first'] . " for: $" . $amount . "<br />";
            $msg .= "Track: " . $tracks[$user_id] . "<br />";
            if (isset($coupons[$user_id]) || isset($raised[$user_id])) {
                $msg .= "Discounts applied:<br /><ul>";
                if (isset($coupons[$user_id])) $msg .= "<li>Voucher: $" . $coupons[$user_id] . "</li>";
                if (isset($raised[$user_id])) $msg .= "<li>Chidon Drive: " . $raised[$user_id] . "</li>";
                $msg .= "</ul>";
            }
        }
        $msg .= "</blockquote><br /><br />";
    }

    if ($celebBoxes || $sweaters) {
        $msg .= "EXTRA PURCHASES<br /><br /><blockquote>";
        $msg .= "You purchased:<br /><ol>";
        if ($celebBoxes) {
            $msg .= "<li>" . $celebBoxes . " Celebration boxe(s) for: $" . ($celebBoxes * CELEB_BOX_COST) . ". ";
            if ($celebBoxShipping) $msg .= "It will be shipped to: " . extractAddress($addresses['celeb_box']);
            else $msg .= "It will be sent to your child's school";
            $msg .= "</li>";
        }
        if ($sweaters) {
            foreach ($sweater_info as $type => $other)  {
                foreach ($other as $num => $sweater) {
                    $size = $sweater['size'];
                    $shipping = intval($sweater['ship']);
                    $typeStr = str_replace('_', ' ', $type);
                    $msg .= "<li>" . $num . " " . ucwords($size) . " " . ucwords($typeStr) . " Sweater for: $" . ($num * SWEATER_COST) . ". ";
                    if ($shipping) {
                        $key = $type . "_" . $num;
                        $msg .= "It will be shipped to: " . extractAddress($addresses[$key]);
                    }
                    else $msg .= "It will be sent to you child's school.";
                    $msg .= "</li>";
                }
            }
        }
        $msg .= "</ol></blockquote><br /><br />";
    }

    $msg . "SUMMARY<br /><br /><blockquote>";
    if ($credit > 0) $msg .= "Amount Credited From Your Pre Registration: $" . $credit . ".<br />";
    if ($to_charge > 0) {
        $msg .= "Total Charged Today: $" . $to_charge . ".<br />";
        if ($trans_id) $msg .= "Transaction ID: " . $trans_id . ".<br />";
        if ($last_four) $msg .= "Last 4 digits of card: <" . $last_four . ">.<br />";
    } else if ($to_charge < 0) {
        $refund = abs($to_charge);
        switch (parseInt($creditVal)) {
            case 1:
                $msg .= "Thank you for choosing to donate the remaining $" . $refund . " from your pre registration to our scholarship fund!<br />";
                break;
            case 2:
                $msg .= "You have chosen to have the remaining $" . $refund . " from your pre registration credited to your original payment method.<br />";
                break;
            case 3:
                $msg .= "You have chosen to have the remaining $" . $refund . " from your pre registration credited to your PayPal address " . $paypal_email . ".<br />";
                break;
        }
    }
    $msg .= "<br />All purchases are non-refundable.<br /><br />";
    $msg .= "Please continue to review for the Chidon Final.<br /><br />";
    $msg .= "If you have any questions, please be in touch with your school's Chidon co-ordinator.<br /><br />";
    $msg .= "Wishing you much continued Nachas,<br />Chidon HQ</blockquote>";

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

function sendMyselfEmail($error, $desc) {
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: chidon@tzivoshashem.org';
    if (is_array($desc)) $desc = implode('<br />', $desc);
    @mail('naftoli@tzivoshashem.org', 'Error with Chidon Registration', ($error . "<br /><br />" . $desc), implode("\r\n", $headers));
}

//******************* PROGRAM STARTS HERE ***********************/
processCart();
setSweaterInfo();

// if we are only refunding credit, then we don't need to do anything else
//if ($credit && $_POST['cart_total'] == 0) {
//    $registered = processReg(); // there could be registration even with no charge
//    $success = updateFamilyBalance();
//    $info['success'] = $success;
//    $info['msg'] = 'Your credit has been refunded. Thank you!';
//    $info['error'] = 'There was a problem refunding your credit.';
//    echo json_encode($info);
//    exit;
//}

// process everything
$MASHPIA_DB->beginTransaction();
// first do all the db stuff and only save if payment goes through
$registered = processReg();
$shippingUpdated = updateShipping();
$celebBoxesProcessed = processCelebBoxes();
$sweatersProcessed = processSweaters();
$tripsSaved = saveTripInfo();
$ultimate = saveUltimateTripInfo();

$info = [];
$trans_id = 0;
$last_four = 0;
if ($registered && $shippingUpdated && $celebBoxesProcessed && $sweatersProcessed && $tripsSaved && $ultimate) {
    if ($to_charge > 0) {
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
            $last_four = $payment['transactionResponse']['accountNumber'];
            // payment went through so commit to db
            $MASHPIA_DB->commit();
            // update registration_charges table
            $inserted = insertIntoRegCharges($trans_id);
            $updated = updateFamilyBalance();
            if (!$inserted || !$updated) {
                // send email to myself
                if (!$inserted && !$updated) {
                    $error = 'There was an error inserting into the registration_charges table and updating the family balance. Please check the database.';
                    $desc = getDescriptions();
                    $desc[] = 'Admin ID: ' . $admin_id . ' Credit Used: ' . $credit;
                } else if (!$inserted) {
                    $error = 'There was an error inserting into the registration_charges table. Please check the database.';
                    $desc = getDescriptions();
                } else if (!$updated) {
                    $error = 'There was an error updating the family balance. Please check the database.';
                    $desc = 'Admin ID: ' . $admin_id . ' Credit Used: ' . $credit;
                }
                sendMyselfEmail($error, $desc);
            }
            // redeem coupons and update family balance
            redeemCoupons();
            $info['success'] = true;
            $msg = 'Congratulations! You have successfully registered your child(ren) and / or ordered your additional purchase(s).' . "\r\n" .
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
        // update family balance
        if (! updateFamilyBalance()) {
            $error = 'There was an error updating the family balance. Please check the database.';
            $desc = 'Admin ID: ' . $admin_id . ' Credit Used: ' . $credit;
            sendMyselfEmail($error, $desc);
        }
        $info['success'] = true;
        $msg = 'Congratulations! ';
        if (count($users)) $msg .= 'You have successfully registered your child(ren) for the Chidon. ';
        if ($celebBoxes || $sweaters) $msg .= 'You have successfully ordered your additional purchase(s). ';
        $info['msg'] = $msg;
    }
} else {
    $MASHPIA_DB->rollBack();
    $info['success'] = false;
    $info['error'] = 'There was an error saving your registration(s) and / or your extra purchase(s). Please try again. If this continues, please send an email to chidon@tzivoshashem.org';
}

echo json_encode($info);

// send email confirmation
if ($info['success']) {
    $msg = getEmailMsg($trans_id, $last_four);
    if (! sendEmail($msg)) {
        sendMyselfEmail('There was an error sending the confirmation email.', $msg);
    }
}