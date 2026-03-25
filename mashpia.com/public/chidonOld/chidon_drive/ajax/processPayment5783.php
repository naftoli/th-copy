<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('max_execution_time', 300);

require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../../../api/models/Admin.php';

//***************** LOAD CURRENT YEAR **********************/
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

//*************** LOAD AUTHORIZE FUNCTIONS *********************/
require_once __DIR__ . '/../../../classes/authorize/CustomerProfile.php';
require_once __DIR__ . '/../../../classes/authorize/PaymentProfile.php';
require_once __DIR__ . '/../../../classes/authorize/Card.php';
use classes\authorize\CustomerProfile as Customer;
use classes\authorize\Card as Card;

//******************* Coupon Codes ************************/
require_once __DIR__ . '/../../../chidonOld/coupons/class.couponCode.php';
$coupon = new CouponCode($MASHPIA_DB, $year);

require_once __DIR__ . '/../../../chidonTests/class.chidonTests.php';
$ct = new ChidonTests($year);

require_once __DIR__ . '/../../../mobile/reg/ajax/encrypt.php';

//******************* GLOBAL VARIABLES ***********************/
$admin = $_POST['admin_id'];
$admin_id = encrypt_decrypt('decrypt', $admin);
$admin_email = $_POST['admin_email'];
$payment_id = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
$last_four = isset($_POST['last_four']) ? $_POST['last_four'] : 0;
$shipping_charges = isset($_POST['shipping']) ? $_POST['shipping'] : [];
$credit = isset($_POST['credit']) ? intval($_POST['credit']) : 0;
//$already_used_credit = isset($_POST['already_used_credit']) ? intval($_POST['already_used_credit']) : 0;
$to_charge = isset($_POST['cart_total']) ? (intval($_POST['cart_total']) - $credit) : 0; // we want it to be negative if there's a refund needed
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
$credits = [];
$user_tracks = [];

// Check if there's already a purchase in progress
$stmtP = $MASHPIA_DB->prepare("SELECT COUNT(*) as total FROM purchase_processing WHERE admin_id = :admin_id");
$stmtP->execute(['admin_id' => $admin_id]);
$rowP = $stmtP->fetch(PDO::FETCH_ASSOC);

if ($rowP['total'] > 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Your purchase is already being processed. Please wait for it to complete.'
    ]);
    exit;
}

// Insert a record to indicate a purchase is in progress
$stmtPP = $MASHPIA_DB->prepare("INSERT INTO purchase_processing (admin_id) VALUES (:admin_id)");
$stmtPP->execute(['admin_id' => $admin_id]);

if (isset($_POST['tracks'])) {
    $tracksArr = json_decode($_POST['tracks']);
    $tracks = arrayByField($tracksArr, 'user_id', 'track');
}
else $tracks = [];

$trips = isset($_POST['trips']) ? json_decode($_POST['trips']) : [];
$ultimate_trip = isset($_POST['ultimate_trip']) ? json_decode($_POST['ultimate_trip']) : [];
$ultimate_info = isset($_POST['ultimate_info']) ? json_decode($_POST['ultimate_info']) : [];
$country = isset($_POST['country']) ? $_POST['country'] : '';
$creditVal = isset($_POST['creditVal']) ? $_POST['creditVal'] : 0; // refund amount
$paypal_email = isset($_POST['paypal_email']) ? $_POST['paypal_email'] : '';

define('CELEB_BOX_COST', 20);
define('SWEATER_COST', 25);

//echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
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
    global $cart, $users, $celebBoxes, $celebBoxShipping, $user_info, $credits;

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
            } else if (strpos($item['desc'], '_credit_') !== false) {
                $credit_info = explode('_', $item['desc']);
                $desc = $credit_info[0];
                $user_id = $credit_info[2];
                $credits[$user_id][$desc] = floatval($item['value']);
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
            'x_card_code' => $ccInfo['cvv'], 
            'zip' => $ccInfo['zip']
        ];
        $newCard = $admin->createPaymentProfile($props);
        return $newCard;
    }
    return false;
}

function processFee() {
    global $admin_id, $to_charge, $payment_id, $cc_info;

    // create description for authorize
    $desc = getAuthDesc();

    // find out if authorize description is too long
    if (strlen($desc) > 250) {
        $desc = getShortDesc($desc); 
    }

    $error = '';
    if ($payment_id) {
        $admin = \Admin::find('first', ['admin_id' => $admin_id]);
        $cp = new Customer($admin->authorize_customer_profile_id);
        $response = $cp->chargeCard($to_charge, $payment_id, null, null, $desc);
        if (is_array($response)) return $response;
        else $error = $response;
    } else {
        $payment = addNewCard();
        if (is_object($payment)) {
            $payment_id = $payment->customerPaymentProfileId;
            $customer_profile_id = $payment->customerProfileId;
            if ($payment_id && $customer_profile_id) {
                $cp = new Customer($customer_profile_id);
                $response = $cp->chargeCard($to_charge, $payment_id, null, null, $desc);
                if (is_array($response)) return $response;
                else $error = $response;
            }
        } 
        // if we get here we had errors so try to charge card without the user ID
        if (isset($_COOKIE['test_card']) && intval($_COOKIE['test_card']) == 1) {
            try {
                $c = new Card();
                $result = $c->charge($cc_info, $to_charge, $desc);
                if (is_array($result)) return $result;
                else $error = $result;
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    }
    return $error ? $error : false;
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

function getShortDesc($desc) {
    $new_desc = 'desc too long - ' . $desc;
    return substr($new_desc, 0, 250);
}

function insertIntoRegCharges($trans_id = 0) {
    global $MASHPIA_DB, $users, $year;

    $user_ids = array_keys($users);
    $school_ids = [];

    $stmtSchoolIDs = $MASHPIA_DB->prepare("
        SELECT user_id, school_id 
        FROM users 
        WHERE user_id in (" . implode(',', $user_ids) . ")
    ");
    $stmtSchoolIDs->execute();
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
        if (!isset($item['authorize_only'])) {
            if ($item['prefix'] == 'C') {
                $user_id = $item['id'];
                $school_id = $school_ids[$user_id];
                $admin_id = isset($item['admin_id']) ? $item['admin_id'] : 0;
            } else if ($item['prefix'] == 'F') {
                $user_id = 0;
                $school_id = 0;
                $admin_id = $item['id'];
            }
            if (!$stmt->execute([
                'trans_id' => $trans_id,
                'user' => $user_id,
                'school' => $school_id,
                'admin' => $admin_id,
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
        $MASHPIA_DB->rollBack();
        return false;
    }
}

function getDescriptions() {
    global $users, $admin_id, $celebBoxes, $sweaters, $celebBoxShipping, $sweater_info, $tracks, $ultimate_trip,
           $shipping_charges, $country, $credits, $credit, $to_charge, $creditVal, $user_tracks;

    $desc = [];

    // if there's credit first zero out the amounts in the registration_charges table
    if ($credit > 0) {
        $desc[] = [
            'prefix' => 'F',
            'id' => $admin_id,
            'code' => 'RRFAM',
            'amount' => -abs($credit),
        ];
        // check if we are refunding anything and add to desc
        if ($to_charge < 0) {
            $refund = abs($to_charge);
            if ($creditVal > 1) {
                $desc[] = [
                    'prefix' => 'F',
                    'id' => $admin_id,
                    'code' => 'R',
                    'amount' => $refund,
                ];
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
                    $code = '';
                    continue;
            }
            $user_tracks[$user_id] = $code;
            $desc[] = [
                'prefix'    => 'C',
                'id'        => $user_id,
                'code'      => $code,
                'amount'    => $amount
            ];
        }
    }

    if (!empty($shipping_charges)) {
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
        foreach ($shipping_charges as $user_id => $amount) {
            // only save for users actually registering
            if (isset($users[$user_id])) {
                $desc[] = [
                    'prefix'    => 'C',
                    'id'        => $user_id,
                    'code'      => $code,
                    'amount'    => $amount,
                    'admin_id'  => $admin_id
                ];
            }
        }
    }

    if (!empty($credits)) {
        $credit_codes = [
            'drive'     => 'CD',
            'voucher'   => 'CV',
            'trip'      => 'CT'
        ];
        foreach ($credits as $user_id => $details) {
            foreach ($details as $type_of_credit => $amount) {
                if ($type_of_credit == 'personal') {
                    // add and subtract credit only in authorize desc to understand the registration charge amount
                    $desc[] = [
                        'prefix' => 'C',
                        'id' => $user_id,
                        'code' => $user_tracks[$user_id],
                        'amount' => $amount,
                        'authorize_only' => 1
                    ];
                    // subtract amount for authorize only
                    $desc[] = [
                        'prefix' => 'C',
                        'id' => $user_id,
                        'code' => $user_tracks[$user_id],
                        'amount' => -abs($amount),
                        'authorize_only' => 1
                    ];
                    // check if we need to change the code in accounting from original track to new track
                    $codes = checkPersonalCredit($user_id, $amount);
                    if ($codes && isset($codes['new'])) {
                        // debit from original code
                        $desc[] = [
                            'prefix'    => 'C',
                            'id'        => $user_id,
                            'code'      => $codes['original'],
                            'amount'    => -abs($amount)
                        ];
                        // add to new code
                        $desc[] = [
                            'prefix'    => 'C',
                            'id'        => $user_id,
                            'code'      => $codes['new'],
                            'amount'    => $amount
                        ];
                    } 
                } else {
                    // add to credit amount to registration charge
                    $desc[] = [
                        'prefix' => 'C',
                        'id' => $user_id,
                        'code' => $user_tracks[$user_id],
                        'amount' => $amount,
                    ];
                    // debit credit amount
                    $desc[] = [
                        'prefix' => 'C',
                        'id' => $user_id,
                        'code' => $credit_codes[$type_of_credit],
                        'amount' => -abs($amount),
                    ];
                }
            }
        }
    }

    if ($celebBoxes) {
        $desc[] = [
            'prefix'    => 'F',
            'id'        => $admin_id,
            'code'      => 'CB',
            'amount'    => $celebBoxes * CELEB_BOX_COST
        ];
        if ($celebBoxShipping) {
            $desc[] = [
                'prefix'    => 'F',
                'id'        => $admin_id,
                'code'      => 'CBS',
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
                'code'      => 'SW',
                'amount'    => $num_sweaters * SWEATER_COST
            ];
            if ($shipping_cost) {
                $desc[] = [
                    'prefix'    => 'F',
                    'id'        => $admin_id,
                    'code'      => 'SWS',
                    'amount'    => $shipping_cost
                ];
            }
        }
    }

    return $desc;
}

function checkPersonalCredit($user_id, $amount) {
    global $MASHPIA_DB, $year, $ct;

    // get child's school_id, class_id and th_chidon_id
    $stmtUser = $MASHPIA_DB->prepare("
        SELECT u.school_id, u.class_id, th_chidon_id 
        FROM users u 
        JOIN th_chidon tc using (user_id) 
        WHERE user_id = :user AND tc.year = :year
    ");
    $stmtUser->execute([
        ':user' => $user_id,
        ':year' => $year
    ]);
    $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $child = [
        'user_id' => $user_id,
        'school_id' => $rowUser['school_id'],
        'class_id' => $rowUser['class_id'],
        'th_chidon_id' => $rowUser['th_chidon_id']
    ];
    // find out what track child achieved
    $track = $ct->getHighestTrackPassed($child)['highest_track'];
    if ($track == 'iyun') $track = 'genius';

    // if there's a track then compare with what was entered in registration_charges and update if needed
    if ($track != '') {
        $stmt = $MASHPIA_DB->prepare("
            SELECT type FROM registration_charges 
            WHERE year = :year AND user_id = :user 
                AND type in ('RRYSD', 'RRYDA', 'RRHVN') 
                AND amount = :amount
        ");
        $stmt->execute([
            'year' => $year,
            'user' => $user_id,
            'amount' => $amount
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $type = $row['type'];
            $types = [
                'maven' => 'RRYSD',
                'pro' => 'RRYDA',
                'expert' => 'RRHVN',
                'genius' => 'RRHVN'
            ];
            $key = array_search($type, $types);
            if ($key != $track) {
                $codes = [
                    'original' => $type,
                    'new' => $types[$track]
                ];
                return $codes;
            }
        }
    }
    return false;
}

function processRefund($amount) {
    global $MASHPIA_DB, $admin_id, $year, $creditVal, $paypal_email;
    
    $refund_type = '';
    switch ($creditVal) {
        case 1:
            $refund_type = 'donation';
            break;
        case 2:
            $refund_type = 'refund';
            break;
        case 3:
            $refund_type = 'paypal';
            break;
    }

    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO family_prepaid_balances (admin_id, year, refund_amount, refund_type, paypal, accounting_code) 
        VALUES (:admin_id, :year, :refund, :type, :paypal, :code)
    ");
    
    $res = $stmt->execute([
        'admin_id'  => $admin_id,
        'year'      => $year,
        'refund'    => $amount,
        'type'      => $refund_type,
        'paypal'    => $paypal_email,
        'code'      => getAuthDesc()
    ]);

    return $res;
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

// function updateShipping() {
//     // was used in 5784 to update how much was paid for shipping amounts that were PREDETERMINED IN THIS TABLE
//     global $MASHPIA_DB, $year, $admin_id, $shipping_charges;

//     $total_charge = 0;
//     foreach ($shipping_charges as $shipping_charge) {
//         $total_charge += intval($shipping_charge);
//     }

//     $sqlInsert = "
//         INSERT IGNORE INTO chidon_parent_shipping 
//         SET 
//             parent_id = :admin,
//             year = :year, 
//             amount_paid = :amount, 
//             date_paid = now()
//         ON DUPLICATE KEY UPDATE amount_paid = (amount_paid + :amount), date_paid = now()";
//     $stmtInsert = $MASHPIA_DB->prepare($sqlInsert);

//     $updated = $stmtInsert->execute([
//         'admin'     => $admin_id,
//         'year'      => $year,
//         'amount'    => $total_charge
//     ]);

//     return $updated;
// }

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
        } else {
            if ($res) return true;
        }
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

function saveAuthDesc() {
    global $MASHPIA_DB, $year, $admin_id;

    $stmt = $MASHPIA_DB->prepare("
        INSERT INTO authorize_transactions 
        SET 
            description = :desc, 
            long_desc = :long_desc, 
            year = :year, 
            admin_id = :admin
    ");
    $new_desc = '';
    $desc = getAuthDesc();
    if (strlen($desc) > 250) $new_desc = getShortDesc($desc);
    $stmt->execute([
        ':desc' => empty($new_desc) ? $desc : $new_desc,
        ':long_desc' => $desc,
        ':year' => $year, 
        ':admin' => $admin_id
    ]);
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
            thurs_walking = :thurs,
            ms_walking = :ms,
            poll = :chidon_answer, 
            height = :height,
            weight = :weight,
            ski = :ski,
            skill = :skill,
            outerwear = :outerwear, 
            trip_option = :trip_option
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
                    'thurs' => $info->thurs_walking,
                    'ms' => $info->ms_walking,
                    'chidon_answer' => $info->chidon_answer,
                    'user' => $user_id,
                    'year' => $year,
                    'outerwear' => $info->outerwear,
                    'height' => $info->height,
                    'weight' => $info->weight,
                    'ski' => $info->ski,
                    'skill' => $info->skill,
                    'trip_option' => $info->trip_option
                ]);
                if (!$res) {
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

function getEmailMsg($trans_id) {
    global $users, $user_info, $celebBoxes, $sweaters, $celebBoxShipping, $addresses, $sweater_info, $to_charge,
           $tracks, $credit, $creditVal, $paypal_email, $credits, $shipping_charges, $last_four;

    $msg = "Below is a summary of your Chidon registration and extra purchase(s) where applicable.<br /><br />";

    if ($users) {
        $msg .= "REGISTRATION<br /><blockquote>";
        foreach ($users as $user_id => $amount) {
            $msg .= "Registered " . $user_info[$user_id]['first'] . " for: $" . $amount . "<br />";
            $msg .= "Track: " . $tracks[$user_id] . "<br />";
            // check if there's a shipping charge
            if (isset($shipping_charges[$user_id])) {
                $msg .= "Shipping Charge: $" . $shipping_charges[$user_id] . "<br />";
            }
            if (!empty($credits[$user_id])) {
                $msg .= "Discounts applied:<br /><ul>";
                foreach ($credits[$user_id] as $credit_type => $credit_value) {
                    $msg .= "<li>$credit_type: $$credit_value</li>";
                }
                $msg .= "</ul>";
            }
        }
        $msg .= "</blockquote><br />";
    }

    if ($celebBoxes || $sweaters) {
        $msg .= "EXTRA PURCHASES<br /><blockquote>";
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
        $msg .= "</ol></blockquote><br />";
    }

    $msg . "SUMMARY<br /><blockquote>";
    if ($credit > 0) $msg .= "Amount Credited From Your Pre Registration Credit: $" . $credit . ".<br />";
    if ($to_charge > 0) {
        $msg .= "Total Charged Today: $" . $to_charge . ".<br />";
        if ($trans_id) $msg .= "Transaction ID: $trans_id.<br />";
        if ($last_four > 0) $msg .= "Last 4 digits of card: $last_four.<br />";
    } else if ($to_charge < 0) {
        $refund = abs($to_charge);
        switch (intval($creditVal)) {
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

    $msg .= "<br /><footer style='font-size: 9px; color: grey;'>Our Address: <address>792 Eastern Parkway Brooklyn, NY 11213</address><br /><br />
            To Unsubscribe please click <a href='http://mashpia.com/unsubscribe.php'>here</a></footer>";

    return $msg;
}

function sendEmail($msg) {
    global $admin_email;

    if ($admin_email) {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: Chidon Headquarters <chidon@mashpia.com>';
        $headers[] = 'Reply-to: Chidon Headquarters <chidon@tzivoshashem.org>';
        $headers[] = 'Bcc: dedications@tzivoshashem.org';
        if (isset($_COOKIE['myshliach']) && intval($_COOKIE['myshliach'])) $headers[] = 'Cc: chidon@myshliach.com';
        if (@mail($admin_email, 'Chidon Confirmation', $msg, implode("\r\n", $headers))) return true;
        else return false;
    }
    return false;
}

function sendMyselfEmail($error, $desc) {
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: chidon@mashpia.com';
    $description = '';
    if (is_array($desc)) {
        foreach ($desc as $item) {
            $description .= implode('<br />', $item);
        }
        $description .= '<br />';
    }
    @mail('naftoli@tzivoshashem.org', 'Error with Chidon Registration', ($error . "<br /><br />" . $description), implode("\r\n", $headers));
}

//******************* PROGRAM STARTS HERE ***********************/
processCart();
setSweaterInfo();

// Start the transaction
$MASHPIA_DB->beginTransaction();

// Perform all database operations first
$registered = processReg();
//$shippingUpdated = updateShipping(); // not needed anymore as the amounts paid for shipping are part of the cart and go straight into the registration_charges table
$celebBoxesProcessed = processCelebBoxes();
$sweatersProcessed = processSweaters();
$tripsSaved = saveTripInfo();
$ultimate = saveUltimateTripInfo();

$info = [];
$trans_id = 0;

// Check if all database operations were successful
if ($registered && $celebBoxesProcessed && $sweatersProcessed && $tripsSaved && $ultimate) {
    // Now process the credit card
    if ($to_charge > 0) {
        $payment = processFee(); // Process the credit card payment
        if (!$payment) {
            // Roll back the transaction if payment fails
            $MASHPIA_DB->rollBack();
            $info['success'] = false;
            $info['error'] = 'There seems to have been an issue with your new card.';
            echo json_encode($info);
            exit; // Exit after sending the error response
        } else {
            // Payment was successful
            if (is_array($payment)) {
                // echo "<pre>"; print_r($payment); echo "</pre>";
                $trans_id = $payment['transactionResponse']['transId'];
                // Commit the transaction since payment was successful
                $MASHPIA_DB->commit();
                saveAuthDesc();
                // Redeem coupons
                redeemCoupons();
                // Update registration_charges table
                $inserted = insertIntoRegCharges($trans_id);
                if (!$inserted) {
                    // Handle errors in inserting
                    $error = 'There was an error inserting into the registration_charges table. Please check the database.';
                    $desc = getDescriptions();
                    sendMyselfEmail($error, $desc);
                }
                $info['success'] = true;
                $msg = 'Congratulations! You have successfully registered your child(ren) and / or ordered your additional purchase(s).' . "\r\n" .
                    'Your card has been charged $' . $to_charge . '. Your transaction ID for your record is: ' . $trans_id . '.' . "\r\n" .
                    'You should receive an email confirmation shortly with all the details.' . "\r\n" .
                    'If you do not receive an email, please check your SPAM folder'. "\r\n" . 'Thank You!';
                $info['msg'] = $msg;
            } else {
                // Roll back if payment processing returns an unexpected result
                $MASHPIA_DB->rollBack();
                $info['success'] = false;
                $info['error'] = $payment; // Handle payment error
            }
        }
    } else {
        // If no charge, just commit the transaction
        $MASHPIA_DB->commit();
        saveAuthDesc();
        // Redeem coupons 
        redeemCoupons();
        // Update registration_charges table
        $inserted = insertIntoRegCharges($trans_id);
        if (!$inserted) {
            // Handle errors in inserting
            $error = 'There was an error inserting into the registration_charges table. Please check the database.';
            $desc = getDescriptions();
            sendMyselfEmail($error, $desc);
        }
        if ($to_charge < 0) {
            // process refund
            processRefund(abs($to_charge));
        }
        $info['success'] = true;
        $msg = 'Congratulations! ';
        if (count($users)) $msg .= 'You have successfully registered your child(ren) for the Chidon. ';
        if ($celebBoxes || $sweaters) $msg .= 'You have successfully ordered your additional purchase(s). ';
        $info['msg'] = $msg;
    }
} else {
    // Roll back if any of the database operations failed
    $MASHPIA_DB->rollBack();
    $info['success'] = false;
    $info['error'] = 'There was an error saving your registration(s) and/or your extra purchase(s). Please try again. If this continues, please send an email to chidon@tzivoshashem.org';
}

// After processing, remove the record
$stmtD = $MASHPIA_DB->prepare("DELETE FROM purchase_processing WHERE admin_id = :admin_id");
$stmtD->execute(['admin_id' => $admin_id]);

// Return the response
echo json_encode($info);

// Send email confirmation if successful
if ($info['success']) {
    $msg = getEmailMsg($trans_id);
    if (!sendEmail($msg)) {
        sendMyselfEmail('There was an error sending the confirmation email.', $msg);
    }
}