<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
include_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/classes/recruits.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/Installments.php'; // for subscriptions
use classes\authorize\Installments as Installments;

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

    // check if any of the users getting registered is already registered
    function checkRegistration($users) {
        // check that none of the users are already registered
        $errors = [];
        foreach ($users as $user) {
            $status = $user->registrationStatus();
            if ($status['chayolei']) { // child is already registered
                $errors[] = $user->first . " " . $user->last . " is already registered for Chayolei Tzivos Hashem.";
            }
        }
        if ($errors) json_error(implode("\n", $errors) . "\nPlease remove them and try again.");
    }

    // charge the card and register the users
    public function registerUsers() {
        global $current_user; global $MASHPIA_DB;
        $admin = $current_user; // $current_user global gets overwritten by wp
        $year = GlobalSettings::getRegistrationYear();

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

        $this->checkRegistration($users); // find out if any users are already registered

        // description for authorize and db
        // based off "code" variable in registration array
        $desc = [];
        foreach ($cart as $item) {
            $desc[] = $item['code'];
        }
        
        /******************************** PAYMENT ********************************/
        if ( $total != 0 ) {
            // if we have a payment profile provided
            if ( isset($payment_info['payment_profile']) && $payment_info['payment_profile'] ) {
                $customer_profile = $admin->customerProfile();
                $payment_profile_id = $payment_info['payment_profile'];
            // we need to create the payment profile
            } else {
                $payment_profile  = $admin->createPaymentProfile( $payment_info );
                $customer_profile = $admin->customerProfile();
                if ( !$payment_profile instanceof classes\authorize\PaymentProfile )
                    json_error( $payment_profile );
                $payment_profile_id = $payment_profile->customerPaymentProfileId;
            }

            $installmentError = false;
            if ($installments) {
                // figure out amount for installments
                $amount = 0;
                foreach ($cart as $reg) {
                    if ($reg['type'] == 'advance registration') $amount += $reg['paid'];
                }
                if ($amount) {
                    // create subscription
                    try {
                        $subscription = new Installments($customer_profile, $payment_profile_id);
                        $installmentError = $subscription->createSubscription($amount, $installments);
                        if ($installmentError) json_error($installmentError);
                        else $total -= $amount; // subtract amount from total
                    } catch (Exception $e) {
                        json_error($e);
                    }
                }
            }

            // total getting charged is dependent on the installment plan
            $payment_response = $customer_profile->chargeCard(
                $total, $payment_profile_id, null, null, implode(',', $desc)
            );
            if ( !is_array( $payment_response ) ) json_error( $payment_response );
            $transaction_query = $MASHPIA_DB->prepare(
                "INSERT INTO transactions (trans_date, admin_id, description, amount, zip, users_registered, response) "
                ."VALUES (NOW(), ?, ?, ?, ?, ?, ?)"
            );
            $transaction_query->execute([
                $admin->admin_id, implode(',', $desc), $total,
                $admin->admin_postal, implode( ', ', $user_ids ),
                json_encode( $payment_response )
            ]);
            $trans_id = $MASHPIA_DB->lastInsertId() ?? 0;
        } else {
            $trans_id = 0;
        }

        $errors = [];
        try {
            // process each item in the cart
            foreach ( $cart as $registration ) {
                $user_id = $registration['user_id'];
                // get user modal;
                foreach ($users as $user) {
                    if ($user->user_id == $user_id) break;
                }

                if ($registration['codeOnly']) {
                    $code = $registration['codeOnly'];
                    $year = GlobalSettings::getChidonRegYear();
                    if (! in_array($code, ['LDE', 'THE'])) $errors[$user_id][] = $user->registrationCharge($code, $registration['paid'], $trans_id, $year);
                    switch ($code) {
                        case 'THE':
                            $amount = $registration['paid'];
                            $discount = $registration['discount'] ?? 0;
                            if ( $user->school->reg_type == 1 ) $amount = $amount > 0 ? $amount : null;
                            $errors[$user_id][] = $user->registerChayolei($admin->admin_id, $year, $amount, $trans_id, 0, 0, $discount);
                            if (! $errors[$user_id] || empty($errors[$user_id])) $this->sendEmail($user, $year);
                            break;
                        case 'LDE':
                            $recruited = intval( $registration['recruited'] ) == 1 ? true : false;
                            $recruited_by = intval( $registration['recruitedBy'] );
                            if (!
                                $user->registerChidon(
                                    $year, $registration['size'], $registration['book'], intval($registration['yarmulka']), ucwords($registration['name_pref']),
                                    $admin->admin_id, $amount, $trans_id, $recruited, $recruited_by, implode(',', $registration['poll']),
                                    $registration['comments'], $registration['track'] )
                            )
                                $errors[$user_id][] = "Could not register ".$user->user_id." for chidon";
                            else $errors[$user_id][] = $user->registrationCharge($code, $registration['paid'], $trans_id, $year);

                            // add book purchased info to db
                            if ( intval( $registration['purchased'] ) == 1 ) {
                                $location = $registration['purchasedWhere'];
                                $store_name = $registration['store']['store_name'];
                                $store_city = $registration['store']['store_city'];
                                $version = $registration['bookVersion'];
                                $user->addBookPurchase( $year, $user->user_id, $location, 0, $store_name, $store_city, $version );
                            }

                            // send email to recruited by child
                            if ($recruited_by) {
                                $recruitedChild = $user->first . ' ' . $user->last;
                                $r = new Recruits($recruited_by);
                                $r->sendEmail($recruitedChild);
                            }

                            // add chidon prizes
                            $user->addChidonPrizes($registration['chidon_prizes'], $year);

                            // send email to parents
                            $this->sendEmailToParents($user, $year, $admin, $installmentError);
                            break;
                        case 'KHKE':
                            $user->addKhkReg($year, $user_id);
                            break;
                        case 'YB1':
                        case 'YB2':
                        case 'YB3':
                        case 'YB4':
                        case 'YB5':
                            $user->addBookPurchase($year, $user_id, 'parent_account', $trans_id);
                            break;
                    }
                } else {
                    // add the registration charge
                    $errors[$user_id][] = $user->registrationCharge(
                        $registration['registration_type'],
                        $registration['paid'],
                        $trans_id, $year
                    );
                }
            }
        } catch( Exception $e ) {
            $errors[0] = $e;
        }

        if ( count( $errors ) > 0 ) {
//            echo "<pre>"; print_r( $errors ); echo "</pre>";
            @mail("support@tzivoshashem.org", "Mobile Registration Error(s)", json_encode($errors));
            json_error( 'There were errors.', $errors );
        }

        json_response( "Successfully Registered." );
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

    private function sendEmail($user, $year) {
        // send email to anash kinder about registration
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: HQ Office <admin@tzivoshashem.org>';
        $subject = "New Chayolei Registration";
        $to = 'anash@tzivoshashem.org';
        $msg = $user->first . ' ' . $user->last . '(User ID: ' . $user->user_id . ') just registered for chayolei tzivos hashem for the year of ' . $year;
        @mail($to, $subject, $msg, implode("\r\n", $headers));
    }

    private function sendEmailToParents($user, $year, $admin, $installmentError = false) {
        // send email to parents
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: Chidon Office <chidon@tzivoshashem.org>';
        if ($user->school_id == 61) $headers[] = "Cc: chidon@myshliach.com";
        if ($user->school_id == 269) $headers[] = 'CC: chidonanash@gmail.com';

        $subject = "Chidon Limmud Enrollment Confirmation";

        $message = "Mazal Tov! Your child(ren) is / are enrolled in the Chidon Limmud program for $year.
                <br /><br />
                If you'd like to make any changes in your enrollment, you can log into your account now and do so.
                <br /><br />
                Please reach out to your school's Chidon coordinator with any questions.
                <br /><br />
                Hatzlocha Rabba in your learning!";
        if (isset($installmentError)) {
            if ($installmentError) $message .= "<br /><br /><b>Unfortunately, we were unable to process your installment plan. Please contact HQ.</b>";
            else $message .= "<br /><br /><b>Your installment plan was successfully processed.</b>";
        }

        $to = $admin->admin_email;
        if ( $to ) {
            if ( !mail( $to, $subject, $message, implode("\r\n", $headers) ) ) {
                $to = "naftoli@tzivoshashem.org";
                $subject = "Error in chidon email";
                $message .= "<br /><b>Sent to " . $admin->admin_email . "</b>";
                @mail( $to, $subject, $message, implode("\r\n", $headers) );
            }
        }
    }
}

rest_router( new UserRegistrationRouter );

// lookup description for registration charges table by codeOnly property
//        $descriptions = [
//            'THE'   =>  'CTH enrollment',
//            'HACH'  =>  'Hachayol subscription',
//
//            'THAKUSA'   =>  'CTH AK shipping USA',
//            'THAKCAN'   =>  'CTH AK shipping CAN',
//            'THAKINT'   =>  'CTH AK shipping INT',
//
//            'THMSUSA'   =>  'CTH MS shipping USA',
//            'THMSCAN'   =>  'CTH MS shipping CAN',
//            'THMSINT'   =>  'CTH MS shipping INT',
//
//            'LDE'       =>  'Chidon enrollment',
//            'KHKE'      =>  'Khk enrollment',
//            'LDE:MYSLDS'    =>  'MyShliach chidon enrollment shipping',
//            'LDE:AKLDS'     =>  'Anash Kinder chidon enrollment shipping',
//            'LDE:AKLDS:AKLDBC'  =>  'Anash Kinder chidon enrollment bc fee',
//
//            'RRYSD'     =>  'Chidon Reg Yesod',
//            'RRYDA'     =>  'Chidon Reg Yediah',
//            'RRHVN'     =>  'Chidon Reg Havona / Iyun',
//            'RRKHK'     =>  'Chidon Reg Khk',
//
//            'RRSUSA'    =>  'Chidon Reg shipping USA',
//            'RRSCAN'    =>  'Chidon Reg shipping CAN',
//            'RRSINT'    =>  'Chidon Reg shipping INT',
//
//            'YB1'   =>  'Yahadus Book 1',
//            'YB2'   =>  'Yahadus Book 2',
//            'YB3'   =>  'Yahadus Book 3',
//            'YB4'   =>  'Yahadus Book 4',
//            'YB5'   =>  'Yahadus Book 5',
//        ];