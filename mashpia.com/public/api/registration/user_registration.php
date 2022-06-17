<?php
ini_set('display_errors',1);
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
include_once( __DIR__ . "/../../class.globalSettings.php" );

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
            '269', // Anash Kinder
        ];

        $zone = $current_user->shippingZone();
        $child_count = 0;
        foreach( $school_ids as $school_id ){
            if ( in_array( $school_id, $schools_with_shipping ) )
                $child_count += 1;
        }
        
        // added by naftoli 08/30/2018
        if ( $child_count == 0 ) json_response( false );
        // we don't need to check the database 
        // base rate for zone 1 is 57 with an additional 10 for each child
        // base rate for zone 2 is 90 with an additional 15 for each child
        // base rate for zone 3 is 167 with an additional 20 for each child
        switch ( $zone ) {
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
        json_response( $rate );
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
        $shipping_info = $_POST['shipping'];
        $shipping_charges = intval($shipping_info['shipping_charges']);
        
        // * get all the users that we are registering
        $totals = [ 'chayolei' => 0, 'chidon' => 0, 'yahadus' => 0, 'shipping' => $shipping_charges ];
        $user_ids = [];
        
        // * get each registration
        foreach( $registrations as $info ){
            if (isset($info['user_id'])) {
                if (!in_array($info['user_id'], $user_ids)) $user_ids[] = $info['user_id'];
            }
            if (!is_numeric($info['paid'])) {
                // we have an error and need to stop registration
                json_error("There is an error in the amount being paid. please try again.");
            }
            $totals[$info['registration_type']] += intval($info['paid']) - intval($info['discount']);
        }

        // * get all the user models
        $users = \Soldier::find( $user_ids, [ 'include' => 'school' ] );
        if ( !is_array( $users ) ) $users = [ $users ]; // force an array, even if it is just one user
        
        // * get the transaction description
        $totals_string = '';
        foreach( $totals as $k => $v ) {
            if ( $v > 0 ) $totals_string .= "$k: $v ";
        }
        $totals_string = trim( $totals_string );
        // get the description four our database
        $user_serials = array_map( function( $user ){ return $user->user_serial . ':' . $user->school_id; }, $users);
        $year = GlobalSettings::getRegistrationYear( $users[0]->school_id );
        $description = "Parent Registration ($totals_string) $year: " . implode( ", ", $user_serials );
        
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

            // Let the user know if the transaction fails
            $payment_response = $customer_profile->chargeCard(
                $total, $payment_profile_id, null, null, $description
            );
            if ( !is_array( $payment_response ) ) json_error( $payment_response );
            $transaction_query = $MASHPIA_DB->prepare(
                "INSERT INTO transactions (trans_date, admin_id, description, amount, reg_amount, ship_amount, zip, users_registered, response) "
                ."VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $transaction_query->execute([
                $admin->admin_id, $description, $payment_info['total'],
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
            // for each user
            foreach ( $users as $user ) {
                $user_errors = [];
                foreach( $registrations as $registration ){

                    if ( !($user->user_id == $registration['user_id']) )
                        continue;

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

                            // add chidon prizes
//                            $user->addChidonPrizes($registration['chidon_prizes'], $year);

                            // send email to parents
                            $headers[] = 'MIME-Version: 1.0';
                            $headers[] = 'Content-type: text/html; charset=iso-8859-1';
                            $headers[] = 'From: Chidon Office <chidon@tzivoshashem.org>';
                            if ($user->school_id == '269') $headers[] = 'CC: chidonanash@gmail.com';

                            $subject = "Chidon Limmud Registration Confirmation";
                            $message = "Mazal Tov! Your child(ren) is / are enrolled in the Chidon Limmud program for $year.
                                        <br /><br/>
                                        We hope you will take full advantage from the resources available for this phenomenal journey, and utilize the opportunities to study and bond with your child.
                                        <br /><br />
                                        In order to begin learning, your child will need the Yahadus book corresponding to their grade (Grade 4 - Book 1; Grade 5 - Book 2; Grade 6- Book 3; Grade 7 - Book 4; Grade 8 - Book 5)
                                        along with the accompanying study guide, that will help them optimize their study with information needed from each unit, corrections and study aids.
                                        <br /><br />
                                        Please speak to your school's Chidon coordinator to order these items. (The study guide is also available online.)
                                        <br /><br />
                                        To download a copy of the study guide and to view important dates for Chidon tests and the Shabbaton, visit <a href='www.thechidon.com'>www.thechidon.com</a>.
                                        <br /><br />
                                        If you have any questions regarding the Limmud, please contact your school's Base Commander.
                                        <br /><br /> 
                                        If you have any questions regarding your credit card charges please contact <a href='mailto:accounting@tzivoshashem.org'>Accounting@TzivosHashem.org</a>";
                            if ( $user->school_id == 61 ) {
                                $message = "
                                Mazal Tov! Your child(ren) are enrolled in the Chidon Limmud program for $year.
                                <br /><br />
                                We hope you will take advantage from the resources available for this phenomenal journey, and utilize the opportunities to study and bond with your child.
                                <br /><br />
                                This is also an opportunity to have a Bubby or Zaidy learn with your child weekly. 
                                <br /><br />
                                MyShliach's online classes is very popular and will help keep your child/ren on a schedule as well as connect with like minded friends throughout this journey. Click <a href='https://www.thechidon.com/resources/online-classes/'>Here</a> to sign up for the classes.
                                In order to begin learning, your child/ren will need the Yahadus book corresponding to their grade (Grade 4 - Book 1; Grade 5 - Book 2; Grade 6- Book 3; Grade 7 - Book 4; Grade 8 - Book 5) along with the accompanying study guide, that will help them optimize their study with information needed for each unit, as well as corrections and study aids. 
                                <br /><br />
                                Study guides will be shipped to your home. To download a copy of the study guide and to view important dates for the Chidon tests and Shabbaton, visit <a href='http://www.thechidon.com'>www.thechidon.com</a>.
                                <br /><br /><br />
                                Wishing you Much Hatzlocho!
                                <br /><br />
                                For any questions throughout the duration of the Chidon Zman please be in touch with your Chidon Coordinator at MyShliach.";
                                $headers[] = "Cc: chidon@myshliach.com";
                            }

                            $to = $admin->admin_email;
                            if ( $to ) {
//                                if ( !mail( $to, $subject, $message, implode("\r\n", $headers) ) ) {
//                                    $to = "naftoli@tzivoshashem.org";
//                                    $subject = "Error in chidon email";
//                                    $message .= "<br /><b>Sent to " . $admin->admin_email . "</b>";
//                                    @mail( $to, $subject, $message, implode("\r\n", $headers) );
//                                }
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
        
        json_response( "Successfully Registered." );
    }

    // serializer for getUsers()
    private function serializeUsers( $users ) {
        return array_map( function( $user ) {
            return $user->to_array([
                'only'  => [
                    'user_id', 'user_code', 'first', 'last', 'first_he', 'last_he', 'class_id', 'lang_id', 'gender', 'dob',
                    'mobile_pic', 'user_registered', 'user_serial', 'non_th_school', 'non_th_school_id'
                ],
                'methods' => [ 'registrationRates', 'registrationStatus', 'profilePicture', 'parentAccount', 'newPic', 'getDiscount', 'getChidonInfo', 'regYears' ],
                'include' => [
                    'school' => [ 'only' => [ 'school_id', 'school_name', 'shipping_method', 'inst_id', 'school_country' ] ],
                    'platoon' => [ 'only' => [ 'class_id', 'class_grade', 'class_sub' ] ]
                ]
            ]);
        }, $users );
    }
}

rest_router( new UserRegistrationRouter );
