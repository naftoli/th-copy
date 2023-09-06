<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
include_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/classes/recruits.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/authorize/Installments.php'; // for subscriptions

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
//            echo $user->user_id . ' = ' . $user->school_id . "<br />";
            if ( !$user->school_id ) continue;
            //$reg_info = $user->school->registration();
            // make sure they paid for this year
            //if ( $reg_info && $reg_info->date_paid ) {
                $available_users[] = $user;
            //}
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
    public function registerUsers(){
        global $current_user; global $MASHPIA_DB;
        $admin = $current_user; // $current_user global gets overwritten by wp

        /******************************** SETUP ********************************/
        // * get the post data
        $payment_info = $_POST['payment'];
        $total = intval( $payment_info['total'] );
        $registrations = $_POST['registrations'];
        $hachayols = isset($_POST['hachayols']) ? $_POST['hachayols'] : [];
        $shipping_info = $_POST['shipping'];
        $shipping_charges = intval($shipping_info['shipping_charges']);
        $year = GlobalSettings::getRegistrationYear();
        $installments = intval($payment_info['installments']) ?? 0;

        // make sure info is correct and create array of user ids
        $user_ids = [];
        foreach ($registrations as $reg) {
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

        // keep user id -> serial number:school_id associations for description
        $user_serials = [];
        foreach ($users as $user) {
            $user_serials[$user->user_id] = $user->user_serial . ":" . $user->school_id;
        }

        // description for authorize and db
        $desc = [];
        foreach ($registrations as $reg) {
            if (intval($reg['paid']) > 0) {
                $desc[$reg['user_id']][$reg['registration_type']] = $reg['paid'];
            }
        }
        foreach ($hachayols as $hachayol) {
            $desc[$hachayol['user_id']]['hachayol'] = $hachayol['paid'];
        }

        $description = '';
        $firstChild = true;
        foreach ($desc as $user_id => $details) {
            $serial = $user_serials[$user_id];
            if ($firstChild) $firstChild = false;
            else $description .= ", ";
            $description .= $serial;
            foreach ($details as $type => $paid) {
                $description .= " #" . $type . " " . $paid;
            }
            $description .= " ";
        }
        if ($shipping_charges) $description .= "#shipping " . $shipping_charges;
        
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

                if ( !($payment_profile instanceof classes\authorize\PaymentProfile) )
                    json_error( $payment_profile );
                $payment_profile_id = $payment_profile->customerPaymentProfileId;
            }

            $installmentError = false;
            if ($installments) {
                // figure out amount for installments
                foreach ($registrations as $reg) {
                    if ($reg['registration_type'] == 'chidon' && $reg['type'] == 'advance registration') {
                        $amount += $reg['paid'];
                    }
                }
                if ($amount) {
                    // create subscription
                    $subscription = new classes\authorize\Installments();
                    $installmentError = $subscription->createSubscription($amount, $installments, $customer_profile->customerProfileId);
                    if (! $installmentError) {
                        // remove this amount from the total to be charged today
                        $total -= $amount;
                    }
                }
            }

            // total getting charged is dependent on the installment plan
            $payment_response = $customer_profile->chargeCard(
                $total, $payment_profile_id, null, null, $description
            );
            if ( !is_array( $payment_response ) ) json_error( $payment_response );
            $transaction_query = $MASHPIA_DB->prepare(
                "INSERT INTO transactions (trans_date, admin_id, description, amount, reg_amount, ship_amount, zip, users_registered, response) "
                ."VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $transaction_query->execute([
                $admin->admin_id, $description, $total,
                ( $total - $shipping_charges ), $shipping_charges,
                $admin->admin_postal, implode( ', ', $user_ids ),
                json_encode( $payment_response )
            ]);
            $trans_id = $MASHPIA_DB->lastInsertId();
        } else {
            $trans_id = 0;
        }

        try {
            // register all the users...
            $errors = [];   $registration_table_users = [];
            
            // add shipping to the registration_charges table
            if ( $shipping_charges > 0 ) {
                $shipping_charge_query = $MASHPIA_DB->prepare(
                    "INSERT INTO registration_charges (trans_id, user_id, school_id, type, amount, year) "
                    ."VALUES( :trans_id, :user_id, :school_id, :type, :amount, :year )"
                );
                $shipping_charge_query->execute([
                    'trans_id' => $trans_id, 'school_id' => 269, // only school with shipping at the moment. TODO update later.. 
                    'user_id' => ( count( $users ) == 1 ? $users[0]->user_id : 0 ), 
                    'type' => 'shipping',   'amount' => $shipping_charges, 
                    'year' => GlobalSettings::getRegistrationYear()
                ]);
            }

            foreach ($users as $user) {
                $users[$user->user_id] = $user;
            }

            // for each user registration
            $user_errors = [];
            foreach( $registrations as $registration ) {
                // get user modal
                $user = $users[$registration['user_id']];

                // set trans_id to empty string if false
                if ( !$trans_id ) $trans_id = '';

                // set the year based on the school id for chayolei only
                $year = $registration['registration_type'] == 'chayolei' ?
                    GlobalSettings::getRegistrationYear( $user->school_id ) :
                    GlobalSettings::getRegistrationYear();
                // if they do not pay, the value is null
                $amount = $registration['paid'];
                $discount = $registration['discount'] ?? 0;
                if ( $user->school->reg_type == 1 )
                    $amount = $amount > 0 ? $amount : null;
                // Chayolei Registration
                if ( $registration['registration_type'] == 'chayolei' ) {
                    $lite = isset( $registration['lite_version'] ) ? $registration['lite_version'] : 0;
                    $ckids = isset( $registration['ckids'] ) ? $registration['ckids'] : 0;
                    $chayolei_errors = $user->registerChayolei(
                        $admin->admin_id, $year, $amount, $trans_id, $lite, $ckids, $discount
                    );
                    if ( is_array( $chayolei_errors ) ) array_merge( $user_errors, $chayolei_errors );
                    if ( in_array( $user->school_id, [ '269', '61' ] ) )
                        $registration_table_users[ $user->school_id ][] = $user->user_id;

                    if ($user->school_id == '269') {
                        // send email to anash kinder about registration
                        $headers[] = 'MIME-Version: 1.0';
                        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
                        $headers[] = 'From: HQ Office <admin@tzivoshashem.org>';
                        $subject = "New Chayolei Registration";
                        $to = 'anash@tzivoshashem.org';
                        $msg = $user->first . ' ' . $user->last . '(User ID: ' . $user->user_id . ') just registered for chayolei tzivos hashem for the year of ' . $year;
                        @mail($to, $subject, $msg, implode("\r\n", $headers));
                    }
                // Chidon Registration
                } else if ( $registration['registration_type'] == 'chidon' ) {
                    $year = GlobalSettings::getChidonRegYear();
                    $recruited = intval( $registration['recruited'] ) == 1 ? true : false;
                    $recruited_by = intval( $registration['recruitedBy'] );
//                        echo "<pre>"; print_r($registration); echo "</pre>"; return [];
                    if ( !$user->registerChidon(
                        $year, $registration['size'], $registration['book'], intval($registration['yarmulka']), ucwords($registration['name_pref']),
                        $admin->admin_id, $amount, $trans_id, $recruited, $recruited_by, implode(',', $registration['poll']),
                        $registration['comments'], $registration['track'] ) )
                            $user_errors[] = "Could not register ".$user->user_id." for chidon";
                    else {
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
                // Yahadus purchase
                } else if ( $registration['registration_type'] == 'yahadus' ) {
                    $year = GlobalSettings::getChidonRegYear();
                    // add the registration charge
                    $user->registrationCharge(
                        $registration['registration_type'],
                        floatval($amount),
                        $trans_id, $year
                    );
                    // add book purchase to db
                    $user->addBookPurchase($year, $user->user_id, 'parent_account', $trans_id);
                } else if ( $registration['registration_type'] == 'khk' ) {
                    // update khk_reg in db
                    $year = GlobalSettings::getChidonRegYear();
                    $user->addKhkReg($year, $user->user_id);
                // other registrations
                } else {
                    // add the registration charge
                    $user->registrationCharge(
                        $registration['registration_type'],
                        floatval( $amount ),
                        $trans_id, $year
                    );
                }

                if ( count( $user_errors ) > 0 ) 
                    $errors[$user->user_id] = $user_errors;
            }
        } catch( Exception $e ) {
            $errors['other'] = $e;
        };

        if ( count( $errors ) > 0 ) {
//            echo "<pre>"; print_r( $errors ); echo "</pre>";
            @mail("support@tzivoshashem.org", "Mobile Registration Error(s)", json_encode($errors));
            json_error( 'There were errors.', $errors );
        }

        foreach ($hachayols as $hachayol) {
            $user = \Soldier::find_by_pk($hachayol['user_id']);
            $user->registrationCharge('hachayol', $hachayol['paid'], $trans_id, $year);
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
                    'platoon' => [ 'only' => [ 'class_id', 'class_grade', 'class_sub' ] ]
                ]
            ]);
        }, $users );
    }
}

rest_router( new UserRegistrationRouter );
