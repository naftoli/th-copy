<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
include_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/classes/recruits.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidon_shipping/class.chidonShipping.php';

use PHPMailer\PHPMailer\PHPMailer as PHPMailer;
use PHPMailer\PHPMailer\SMTP as SMTP;
use PHPMailer\PHPMailer\Exception as Exception;

//require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/Installments.php'; // for subscriptions
//use classes\authorize\Installments as Installments;

class UserRegistrationRouter {
    // parents only
    public function authenticate() {
        global $current_user;
        return count( $current_user->getAuthIds('user') ) > 0;
    }

    // get all the users that the parent has, serialized for the registration pages.
    public function getUsers(){
        global $current_user;
        // global $MASHPIA_DB;
        // load all his user id's
        $user_ids = $current_user->getAuthIds( 'user' );

        // get all the users information
        $users = \Soldier::find( $user_ids,
            ['include' => [ 'school', 'platoon' ] ]
        );
        $users = is_array( $users ) ? $users : [ $users ];

        $available_users = [];
        foreach( $users as $user ){
            if ( !$user->school_id ) continue;
            $available_users[] = $user;
        }

        json_response([
            "users" => $this->serializeUsers( $available_users )
        ]);
    }
    // return shipping price for users submitted
    public function getShipping(){
        global $current_user;

        if( !isset( $_POST[ 'school_ids' ] ) ){
            json_response( false );
        }

        $school_ids = $_POST[ 'school_ids' ];
        $schools_with_shipping = [
            '61', '269', // MyShliach, Anash Kinder
        ];

        $zone = $current_user->shippingZone();
        $child_count = 0;
        $anashKinder = false;
        $myshliach = false;
        foreach ( $school_ids as $school_id ) {
            if ( in_array( $school_id, $schools_with_shipping ) ) {
                $child_count += 1;
                if ( $school_id == '269' ) $anashKinder = true;
                if ( $school_id == '61' ) $myshliach = true;
            }
        }
        
        // added by naftoli 08/30/2018
        if ( $child_count == 0 ) json_response( false );

        // shipping rates
        // Anash Kinder
        if ($anashKinder) {
            // base rate for zone 1 is 57 with an additional 10 for each child
            // base rate for zone 2 is 90 with an additional 15 for each child
            // base rate for zone 3 is 167 with an additional 20 for each child
            switch ($zone) {
                case 1:
                    $base = 57;
                    $increaseBy = 10;
                    break;
                case 2:
                    $base = 90;
                    $increaseBy = 15;
                    break;
                case 3:
                    $base = 167;
                    $increaseBy = 20;
                    break;
            }
            $rate = $base;
            $extra = $child_count - 1;
            $rate += $extra * $increaseBy;
        } else if ($myshliach) {
            // MyShliach
            // usa rate = 35
            // canada rate = 40
            // intl rate = 45
            switch ($zone) {
                case 1:
                    $rate = 35;
                    break;
                case 2:
                    $rate = 40;
                    break;
                case 3:
                    $rate = 45;
                    break;
            }
        } else {
            // we should never get here
            json_response( false );
        }

        json_response( $rate );
    }

    // charge the card and register the users
    public function registerUsers() {
        global $current_user, $MASHPIA_DB;
        $admin = $current_user; // $current_user global gets overwritten by wp
        $chidonYr = GlobalSettings::getRegistrationYear();

        /******************************** SETUP ********************************/
        // * get the post data
        $payment_info = $_POST['payment'];
        $cart = $_POST['cart'];
        $total = intval( $payment_info['total'] );
        $installments = intval($payment_info['installments']) ?? 0;

        // make sure info is correct and create array of user ids
        $user_ids = [];
        foreach ($cart as $reg) {
            if (! is_numeric($reg['paid'])) {
                // we have an error and need to stop registration
                json_error("There is an error in the amount being paid. please try again.");
            }
            if (! in_array($reg['user_id'], $user_ids)) $user_ids[] = $reg['user_id'];
        }

        // * get all the user models
        $users = \Soldier::find( $user_ids, [ 'include' => 'school' ] );
        if ( !is_array( $users ) ) $users = [ $users ]; // force an array, even if it is just one user

        // start transaction
        $MASHPIA_DB->beginTransaction();

        /******************************** PAYMENT ********************************/
        $installmentsCreated = false;
        $trans_id = 0;
        if (isset($_COOKIE['naftoli'])) {}
        else if ( $total != 0 ) {
            $customer_profile = $admin->customerProfile();
            // if we have a payment profile provided
            if ( isset($payment_info['payment_profile']) && $payment_info['payment_profile'] )
                $payment_profile_id = $payment_info['payment_profile'];
            // we need to create the payment profile
            else {
                $payment_profile  = $admin->createPaymentProfile( $payment_info );
                if ( !$payment_profile instanceof classes\authorize\PaymentProfile ) {
                    $MASHPIA_DB->rollBack();
                    json_error( $payment_profile );
                }
                $payment_profile_id = $payment_profile->customerPaymentProfileId;
            }

            if ($installments) {
                // figure out amount for installments
                $amount = 0;
                foreach ($cart as $reg) {
                    if ($reg['type'] == 'advance registration') $amount += intval($reg['paid']);
                }
                if ($amount) {
                    // create installments (called subscriptions in authorize)
                    try {
                        $subscription = new Installments($customer_profile, $payment_profile_id, true, isset($payment_info['payment_profile']));
                        $result = $subscription->createSubscription($amount, $installments);
                        if (strpos($result, "Error") !== false) {
                            $MASHPIA_DB->rollBack();
                            json_error($result);
                        } else {
                            $total -= $amount; // subtract amount from total
                            $installmentsCreated = true;
                            $saved = $subscription->saveToDb($MASHPIA_DB, $admin->admin_id);
                            if (!$saved) {
                                $subscription->cancelSubscription();
                                $MASHPIA_DB->rollBack();
                                json_error("Error saving installments to db");
                            }
                        }
                    } catch (Exception $e) {
                        $MASHPIA_DB->rollBack();
                        json_error($e->getMessage());
                    }
                }
            }

            // description for authorize and db
            // based off "code" variable in registration array
            $desc = [];
            foreach ($cart as $item) {
                $codeOnly = $item['codeOnly'];
                // find out if we need to change the amount in the code
                // change amount to 0 for the advance registration if there's installments
                if ($installmentsCreated) {
                    if (in_array($codeOnly, ['RRYSD', 'RRYDA', 'RRHVN'])) {
                        $item['code'] = $codeOnly . '-0';
                    } else if (in_array($codeOnly, ['RRSUSA', 'RRSCAN', 'RRSINT']) !== false) {
                        $item['code'] = $codeOnly . '-0';
                    }
                }
                // don't add LDE to desc if editing only
                if ($codeOnly == 'LDE' && intval($item['editingOnly']) == 1 && parseInt($item['paid']) == 0) continue;
                $desc[] = $item['code'];
            }

            // total getting charged is dependent on the installment plan
            // if there's only installments and nothing to charge, then don't charge anything
            if ($total) {
                $payment_response = $customer_profile->chargeCard(
                    $total, $payment_profile_id, null, null, implode(',', $desc)
                );
                if (!is_array($payment_response)) {
                    // undo the subscription
                    if ($installmentsCreated) {
                        $subscription->cancelSubscription();
                        $subscription->removeFromDb($MASHPIA_DB);
                    }
                    $MASHPIA_DB->rollBack();
                    json_error($payment_response);
                }
                $transaction_query = $MASHPIA_DB->prepare(
                    "INSERT INTO transactions (trans_date, admin_id, description, amount, zip, users_registered, response) "
                    . "VALUES (NOW(), ?, ?, ?, ?, ?, ?)"
                );
                $transaction_query->execute([
                    $admin->admin_id, implode(',', $desc), $total,
                    $admin->admin_postal, implode(', ', $user_ids),
                    json_encode($payment_response)
                ]);
                $trans_id = $MASHPIA_DB->lastInsertId() ?? 0;
            }
        }

        // prepare variables for confirmation email
        $itemsForEmail = [];

        /******************************** REGISTRATION ********************************/
        try {
            // process each item in the cart
            foreach ( $cart as $registration ) {
                $user_id = $registration['user_id'];
                $amount = $registration['paid'];

                // get user modal;
                $user = array_filter( $users, function( $user ) use ( $user_id ) {
                    return $user->user_id == $user_id;
                });
                $user = $user[0];

                if ($registration['codeOnly']) {
                    $code = $registration['codeOnly'];
                    $chayoleiReg = strpos($code, 'THE') !== false;
                    $chidonReg = strpos($code, 'LDE') !== false;
                    $chidonYr = GlobalSettings::getChidonRegYear();
                    $cthYr = GlobalSettings::getRegistrationYear($user->school_id);

                    if ($chayoleiReg || $chidonReg) {
                        if ($chayoleiReg) {
                            // check if user is already registered
                            $status = $user->registrationStatus($cthYr, $chidonYr);
                            if (! $status['chayolei']) { // user not yet registered, so register him/her
                                $discount = $registration['discount'] ?? 0;
                                $error = $user->registerChayolei($admin->admin_id, $cthYr, $amount, $trans_id, 0, 0, $discount);
                                if (! empty($error)) {
                                    $MASHPIA_DB->rollBack();
                                    json_error(implode(',', $error));
                                } else {
                                    $itemsForEmail[$user_id][] = [
                                        'first'     => $user->first,
                                        'last'      => $user->last,
                                        'school'    => $user->school_id,
                                        'code'      => $code,
                                        'amount'    => $amount,
                                        'discount'  => $discount,
                                        'trans_id'  => $trans_id,
                                        'year'      => $cthYr,
                                    ];
//                                    if ($user->school_id == 269) {
//                                        // send email to anash kinder about child's registration
//                                        if (! $this->sendRegEmail($user, $cthYr)) {
//                                            $MASHPIA_DB->rollBack();
//                                            json_error("Error sending email to anash kinder");
//                                        }
//                                    }
                                }
                            }
                        }
                        if ($chidonReg) {
                            $recruited = intval($registration['recruited']) == 1 ? true : false;
                            $recruited_by = intval($registration['recruitedBy']);

                            if (isset($registration['editingOnly']) && $registration['editingOnly']) {
                                if (!
                                    $user->registerChidon(
                                        $chidonYr, $registration['size'], $registration['book'], intval($registration['yarmulka']), ucwords($registration['name_pref']),
                                        $admin->admin_id, $amount, $trans_id, $recruited, $recruited_by, implode(',', $registration['poll']),
                                        $registration['comments'], $registration['track'])
                                ) {
                                    $MASHPIA_DB->rollBack();
                                    json_error("Could not update " . $user_id . " for chidon");
                                } else {
                                    $itemsForEmail[$user_id][] = [
                                        'first'     => $user->first,
                                        'last'      => $user->last,
                                        'school'    => $user->school_id,
                                        'code'      => $code,
                                        'amount'    => $amount,
                                        'reg_info'  => $registration,
                                        'recruited_by' => $recruited_by,
                                        'trans_id'  => $trans_id,
                                        'year'      => $chidonYr,
                                    ];
                                }

                                // if recruited by changed, send new email
                                if (isset($user->newRecruit) && $user->newRecruit) {
                                    $recruitedChild = $user->first . ' ' . $user->last;
                                    $r = new Recruits($recruited_by);
                                    $r->sendEmail($recruitedChild);
                                }
                            } else {
                                if (!
                                    $user->registerChidon(
                                        $chidonYr, $registration['size'], $registration['book'], intval($registration['yarmulka']), ucwords($registration['name_pref']),
                                        $admin->admin_id, $amount, $trans_id, $recruited, $recruited_by, implode(',', $registration['poll']),
                                        $registration['comments'], $registration['track'])
                                ) {
                                    $MASHPIA_DB->rollBack();
                                    json_error("Could not register " . $user_id . " for chidon");
                                } else {
                                    $itemsForEmail[$user_id][] = [
                                        'first'     => $user->first,
                                        'last'      => $user->last,
                                        'school'    => $user->school_id,
                                        'code'      => $code,
                                        'amount'    => $amount,
                                        'reg_info'  => $registration,
                                        'recruited' => $recruited,
                                        'recruited_by' => $recruited_by,
                                        'trans_id'  => $trans_id,
                                        'year'      => $chidonYr
                                    ];
                                }

                                // if there's ms/ak extra charges, we need to break it up and add it separately
                                if (strpos($code, ':') !== false) {
                                    $codes = explode(':', $code);
                                    foreach ($codes as $code) {
                                        switch ($code) {
                                            case 'MYSLDS-10':
                                            case 'AKLDS-10':
                                                $amount = 10;
                                                break;
                                            case 'AKLDBC-20':
                                                $amount = 20;
                                                break;
                                        }
                                        $user->registrationCharge($code, $amount, $trans_id, $chidonYr);
                                        $itemsForEmail[$user_id][] = [
                                            'first'     => $user->first,
                                            'last'      => $user->last,
                                            'school'    => $user->school_id,
                                            'code'      => $code,
                                            'amount'    => $amount,
                                            'trans_id'  => $trans_id,
                                            'year'      => $chidonYr
                                        ];
                                    }
                                }

                                // add book purchased info to db
                                if (intval($registration['purchased']) == 1) {
                                    $location = $registration['purchasedWhere'];
                                    $store_name = $registration['store']['store_name'];
                                    $store_city = $registration['store']['store_city'];
                                    $version = $registration['bookVersion'];
                                    $user->addBookPurchase($chidonYr, $user_id, $location, 0, $store_name, $store_city, $version);
                                }

                                // send email to recruited by child
                                if ($recruited_by) {
                                    $recruitedChild = $user->first . ' ' . $user->last;
                                    $r = new Recruits($recruited_by);
                                    $r->sendEmail($recruitedChild);
                                }
                            }

                            // add chidon prizes
                            $user->addChidonPrizes($registration['chidon_prizes'], $chidonYr);
                        }
                    } else {
                        $yr = $chidonYr;
                        if (in_array($code, ['shipping', 'HACH', 'THAKUSA', 'THAKCAN', 'THAKINT', 'THMSUSA', 'THMSCAN', 'THMSINT'])) $yr = $cthYr;
                        $user->registrationCharge($code, $amount, $trans_id, $chidonYr);
                        switch ($code) {
                            case 'KHKE':
                                $user->addKhkReg($chidonYr, $user_id);
                                break;
                            case 'YB1':
                            case 'YB2':
                            case 'YB3':
                            case 'YB4':
                            case 'YB5':
                                $user->addBookPurchase($chidonYr, $user_id, 'parent_account', $trans_id);
                                break;
                            case 'RRYSD':
                            case 'RRYDA':
                            case 'RRHVN':
                                // early registration
                                if (! $installmentsCreated) $user->earlyReg($chidonYr, $user_id, $amount);
                                break;
                        }
                        $itemsForEmail[$user_id][] = [
                            'first'     => $user->first,
                            'last'      => $user->last,
                            'school'    => $user->school_id,
                            'code'      => $code,
                            'amount'    => $amount,
                            'trans_id'  => $trans_id,
                            'year'      => $yr
                        ];
                    }
                } else {
                    // add the registration charge
                    $user->registrationCharge($registration['registration_type'], $amount, $trans_id, $chidonYr);
                }
            }
            // if we get here then all is good
            $MASHPIA_DB->commit();

            // send email to parents
            if (! empty($itemsForEmail)) {
                $sent = $this->sendEmailToParents($itemsForEmail);
                if ($sent) json_response("Successfully Registered.");
                // otherwise the error message comes from the email function
            } else {
                json_response("Successfully Processed.");
            }
        } catch ( Exception $e ) {
            $MASHPIA_DB->rollBack();
            json_error( 'Error: ' . $e->getMessage() );
        }
    }

    // serializer for getUsers()
    private function serializeUsers( $users ) {
        return array_map( function( $user ) {
            return $user->to_array([
                'only'  => [
                    'user_id', 'user_code', 'first', 'last', 'first_he', 'last_he', 'class_id', 'lang_id', 'gender', 'dob',
                    'mobile_pic', 'user_registered', 'user_serial', 'non_th_school', 'non_th_school_id', 'hachayol'
                ],
                'methods' => [ 'registrationRates', 'registrationStatus', 'profilePicture', 'parentAccount', 'newPic', 'getDiscount', 'getChidonInfo', 'regYears' ],
                'include' => [
                    'school' => [ 'only' => [ 'school_id', 'school_name', 'shipping_method', 'inst_id', 'school_country', 'shipping_country' ] ],
                    'platoon' => [ 'only' => [ 'class_id','class_grade', 'class_sub' ] ]
                ]
            ]);
        }, $users );
    }

    private function sendRegEmail($user, $chidonYr) {
        // send email to anash kinder about registration
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: HQ Office <admin@tzivoshashem.org>';
        $subject = "New Chayolei Registration";
        $to = 'anash@tzivoshashem.org';
        $msg = $user->first . ' ' . $user->last . '(User ID: ' . $user->user_id . ') just registered for chayolei tzivos hashem for the year of ' . $chidonYr;
        return mail($to, $subject, $msg, implode("\r\n", $headers));
    }

    private function getInfoForEmail($items) {
        global $MASHPIA_DB;

        // get chidon prize name
        $stmt = $MASHPIA_DB->prepare("select prize_name from chidon_prizes where prize_id = ?");
        // get recruited by name
        $stmtRecruit = $MASHPIA_DB->prepare("select first, last from users where user_id = ? or user_serial = ?");

        $tracks = [
            'maven'     => 'Yesod',
            'pro'       => 'Yediah',
            'expert'    => 'Havonah',
            'genius'    => 'Iyun'
        ];

        $cth_names = [];
        $chidon_names = [];

        foreach ($items as $details) {
            foreach ($details as $detail) {
                if ($detail['code'] == 'THE') {
                    $cth_names[] = $detail['first'];
                }
                if (strpos($detail['code'], 'LDE') !== false) {
                    $chidon_names[] = $detail['first'];
                }
            }
        }

        $trans_id = 0;
        $message = "<html><body>";
        $message .= "<h2>Enrollment Confirmation</h2>";
        if ($cth_names) {
            $message .= "<p>Mazal Tov! " . implode(',', $cth_names) . (count($cth_names) > 1 ? ' are ' : ' is ') . " now enrolled in the Chayolei program.</p>";
        }
        if ($chidon_names) {
            $message .= "<p>Mazal Tov! " . implode(',', $chidon_names) . (count($chidon_names) > 1 ? ' are ' : ' is ') . " now enrolled in the Chidon program.</p>";
        }
        $message .= "<p>Below is a summary of your enrollment(s):</p>";

        $total_amount = 0;
        $pre_reg_amount = 0;
        foreach ($items as $details) {
            $name = $details[0]['first'] . ' ' . $details[0]['last'];
            $chidonReg = false;
            $chayoleiReg = false;

            $message .= "<h3>" . $name . "</h3>";
            $message .= "<blockquote>";
            foreach ($details as $detail) {
                $total_amount += floatval($detail['amount']);
                if ($detail['code'] == 'THE' || strpos($detail['code'], 'LDE') !== false)
                    $message .= "<p><b>" . ChidonShipping::getDescription($detail['code']) . '</b>: $' . $detail['amount'];
                else
                    $message .= "<p>" . ChidonShipping::getDescription($detail['code']) . ': $' . $detail['amount'];
                switch ($detail['code']) {
                    case 'THE':
                        $chayoleiReg = true;
                        break;
                    case 'LDE':
                    case 'LDE:MYSLDS-10':
                    case 'LDE:AKLDS-10:AKLDBC-20':
                        $chidonReg = true;
                        $message .= "<br />This includes:<br /><ul><li>3 tests</li><li>Study Guide</li><li>Chidon Kop Card Game</li></ul></p>";
                        $message .= "<p><b>Limmud Shipping</b><br />";
                        if (in_array($detail['school'], [61, 269]))
                            $message .= "The Study Guide & Chidon Kop will be shipped to your house during Cheshvan.</p>";
                        else
                            $message .= "The Study Guide & Chidon Kop will be shipped to your school.</p>";
                        break;
                    case 'KHKE':
                        $message .= "<br />This includes:<br /><ul><li>KHK tests</li><li>KHK Study Guide</li></ul></p>";
                        break;
                    case 'YB1':
                    case 'YB2':
                    case 'YB3':
                    case 'YB4':
                    case 'YB5':
                        $message .= "<br />The book will be shipped to your school.</p>";
                        break;
                    case 'RRYSD':
                    case 'RRYDA':
                    case 'RRHVN':
                    case 'RRKHK':
                        $pre_reg_amount += $detail['amount'];
                        break;
                    default:
                        $message .= "</p>";
                        break;
                }

                if (empty($trans_id)) {
                    $trans_id = $detail['trans_id'];
                }

                if (isset($detail['discount']) && $detail['discount']) {
                    $message .= "<p>Discount: $" . $detail['discount'] . "</p>";
                }

                if (isset($detail['recruited_by']) && $detail['recruited_by']) {
                    $names = [];
                    if (strpos($detail['recruited_by'], ',') !== false) {
                        $recruits = explode(',', $detail['recruited_by']);
                    } else {
                        $recruits = [$detail['recruited_by']];
                    }
                    foreach ($recruits as $recruit) {
                        $stmtRecruit->execute([$recruit, $recruit]);
                        $name = $stmtRecruit->fetch();
                        $names[] = $name['first'] . ' ' . $name['last'];
                    }
                    $message .= "<p><b>Recruitment</b><br />You recruited " . implode(',', $names) . " to this year's Chidon.</p>";
                }

                if (isset($detail['reg_info'])) {
                    $message .= "<p><b>Registration Information</b></p>";
                    $message .= "<p>Sweater Size: " . $detail['reg_info']['size'] . "</p>";
                    $message .= "<p>Book Number: " . $detail['reg_info']['book'] . "</p>";
                    $message .= "<p>Yarmulka Size: " . $detail['reg_info']['yarmulka'] . "</p>";
                    $message .= "<p>Track: <b>" . $tracks[ $detail['reg_info']['track'] ] . "</b></p>";
                    if ($detail['reg_info']['purchased']) {
                        $location = $detail['purchasedWhere'];
                        $store_name = $detail['store']['store_name'];
                        $store_city = $detail['store']['store_city'];
                        $message .= "<p>Book Purchased: Yes</p>";
                        $message .= "<p>Book Version: " . $detail['reg_info']['bookVersion'] . "</p>";
                        $message .= "<p>Purchased Where: " . $detail['reg_info']['purchasedWhere'] . "</p>";
                        if ($location == 'store') {
                            $message .= "<p>Store Name: " . $store_name . "</p>";
                            $message .= "<p>Store City: " . $store_city . "</p>";
                        }
                    }
                    $message .= "<p>Comments: " . $detail['reg_info']['comments'] . "</p>";
                    $message .= "<p>Chidon Prizes:</p><ol>";
                    foreach ($detail['reg_info']['chidon_prizes'] as $prize) {
                        $stmt->execute([$prize['id']]);
                        $prize_name = $stmt->fetch()['prize_name'];
                        $message .= "<li>" . $prize_name . "</li>";
                    }
                    $message .= "</ol>";
                }
            }
            $message .= "<p><b>Chidon Experience</b><br />Registration for the Experience at the end of this year's Limmud program costs $36-$350";
            if (in_array($details[0]['school'], [61, 269])) {
                $message .= " plus shipping";
            }
            $message .= ", depending on the track passed on the tests.</p>";

            if ($pre_reg_amount) {
                $message .= "<p><b>Pre Registration Payment</b>";
                $message .= "<br />You have already paid $" . $pre_reg_amount . " towards your registration.";
                $message .= " If you end up needing to pay less, you will be able to get a refund. If you need to pay more, 
                    you will be able to pay the difference during Reward Registration at the end.</p>";
            }
            $message .= "</blockquote>";
        }
        $message .= "<p>Total Charged: $" . $total_amount . "</p>";
        $message .= "<p>Transaction ID: " . $trans_id . "</p>";

        // footer
        $message .= "<p><b>Customer Service</b><br />
For any questions throughout the duration of the Chidon, please be in touch with your schools Chidon Coordinator.
<br /><br />
If you have any questions regarding your credit card charges please email <a href='mailto:accounting@tzivoshashem.org'>accounting@tzivoshashem.org</a>.
<br /><br />
Wishing you Much Hatzlocho!<br />
Tzivos Hashem HQ</body></html>";

        $subject = "Enrollment Confirmation";
        if ($chayoleiReg && $chidonReg) {
            $subject = "Chayolei and Chidon Enrollment Confirmation";
        } else if ($chayoleiReg) {
            $subject = "Chayolei Enrollment Confirmation";
        } else if ($chidonReg) {
            $subject = "Chidon Enrollment Confirmation";
        }
        return [$subject, $message];
    }

    private function sendEmailToParents($items) {
        global $current_user, $MASHPIA_DB;
        $admin = $current_user; // $current_user global gets overwritten by wp
        $to = $admin->admin_email;
        $success = true;

        if ( $to ) {
            [$subject, $message] = $this->getInfoForEmail($items);
            $bcc = "enrollment@mashpia.com";
//                $headers[] = "Bcc: " . $bcc;
            if (in_array($_SERVER['HTTP_HOST'], ['tzivos.local', 'localhost'])) {
                $mailer = new PHPMailer();
                try {
                    // server settings
                    $mailer->SMTPDebug = 0;
                    $mailer->isSMTP();
                    $mailer->SMTPAuth = true;
                    $mailer->AuthType = 'LOGIN';
//                    if ($_SERVER['HTTP_HOST'] == 'tzivos.local') {
                    $mailer->Host = 'smtp.gmail.com';
                    $mailer->Username = 'naftolir@gmail.com';
                    $mailer->Password = 'rnkkcgdkmfytaodo';
                    $mailer->SMTPSecure = 'tls';
                    $mailer->Port = 587;
//                    } else {
//                        $mailer->Host = 'host2.tzivoshashem.com';
//                        $mailer->Username = 'enrollment@mashpia.com';
//                        $mailer->Password = 'Naftoli@8770!';
//                        $mailer->SMTPSecure = 'ssl';
//                        $mailer->Port = 456;
//                    }
                    // recipients
                    $mailer->isHTML();
                    $mailer->Subject = $subject;
                    $mailer->Body = $message;
                    $mailer->AltBody = strip_tags($message);
                    $mailer->addAddress($to);
                    $mailer->setFrom('dev@tzivoshashem.org', 'Tzivos Hashem HQ');
                    if (! $mailer->send()) {
                        $success = false;
                        json_error('Your information has been saved but there was an error sending the confirmation email.\nError: ' . $mailer->ErrorInfo);
                        $mailer->clearAddresses();
                        $mailer->addAddress($bcc);
                        $mailer->send();
                    }
                } catch (Exception $e) {
                    $success = false;
                    json_error('Your information has been saved but there was an error sending the confirmation email.\nError: ' . $e->getMessage() . "\n" . $mailer->ErrorInfo);
                }
            } else {
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=iso-8859-1';
                $headers[] = 'From: Tzivos Hashem HQ <dev@tzivoshashem.org>';
                $headers[] = "Bcc: " . $bcc;
                if (! @mail($to, $subject, $message, implode("\r\n", $headers))) {
                    $success = false;
                    $msg = "Your information has been saved but there was an error sending the confirmation email.\n
                        Please contact HQ (718-907-8884) to check that your information was saved correctly.";
                    json_error($msg);
                    @mail($bcc, $subject, $message, implode("\r\n", $headers));
                }
            }
        } else {
            $success = false;
            json_error('Your information has been saved but there was an error sending the confirmation email.\nNo email address found for this account.');
        }
        return $success;
    }
}

rest_router( new UserRegistrationRouter );