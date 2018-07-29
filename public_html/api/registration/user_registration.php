<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UserRegistrationRouter {
    // parents only
    public function authenticate() {
        global $current_user;
        return count( $current_user->getAuthIds('user') ) > 0;
    }

    // get all the users that the parent has, serialized for the registration pages.
    public function getUsers(){
        global $current_user;   global $pdo;
        // load all his user id's
        $user_ids = $current_user->getAuthIds( 'user' );

        // get all the users information
        $users = User::find( $user_ids );
        $users = is_array( $users ) ? $users : [ $users ];

        $available_users = [];
        foreach( $users as $user ){
            $reg_info = $user->school->getRegInfo();
            if ( $reg_info->default || !$reg_info->date_paid ) continue;
            $available_users[] = $user;
        }

        json_response([
            "users" => $this->serializeUsers( $available_users )
        ]);
    }
    // return shipping price for users submitted
    public function getShipping(){
        global $current_user; global $pdo;

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

        $query = $pdo->prepare(
            "SELECT type, rate FROM shipping_rates WHERE zone=? AND child_count=?;"
        );

        // handle more kids then discounted rates allow for.
        if ( $child_count > 7 ) {
            $query->execute( [ $zone, 7 ] );
            $max_bluk_rates = $query->fetchAll();
            
            $multiplied_rates = array_map( function( $info ) use ( $child_count ) {
                $info['rate'] *= intval( $child_count / 7 );
                return $info;
            }, $max_bluk_rates );

            // get the rate for the remaining kids
            $query->execute( [ $zone, $child_count % 7 ] );
            $rates = $query->fetchAll();

            foreach( $rates as $index => $rate ){
                $rates[$index]['rate'] += $multiplied_rates[$index]['rate'];
            }

            json_response( $rates );
        // return false if no shipping
        } else if( $child_count == 0) {
            json_response( false );
        // return discounted rate for multiple kids if less then max ( 7 )
        } else {
            $query->execute( [ $zone, $child_count ] );
            json_response( $query->fetchAll() );
        } 
    }
    // charge the card and register the users
    public function registerUsers(){
        global $current_user; global $pdo;

        /******************************** SETUP ********************************/
        $payment_info = $_POST['payment'];
        $total = intval( $payment_info['total'] );

        $registrations = $_POST['registrations'];
        $shipping_info = $_POST['shipping'];
        $shipping_charges = intval($shipping_info['shipping_charges']);
        // get all the users that we are registering

        $user_ids = [];
        foreach( $registrations as $info ){
            if( !in_array( $info['user_id'], $user_ids ) ) $user_ids[] = $info['user_id'];
        }
        // get all the user models
        $users = User::find( $user_ids );
        if ( !is_array( $users ) ) $users = [ $users ]; // force an array, even if it is just one user
        
        // setup the variables we will need later
        $user_serials = array_map( function( $user ){ return $user->user_serial; }, $users);
        $year = GlobalSettings::getRegistrationYear( $users[0]->school_id );
        $description = "User Registration for $year: " . implode( ", ", $user_serials );
        
        /******************************** PAYMENT ********************************/
        if ( $total != 0 ) {
            // if we have a payment profile provided
            if ( $payment_info['payment_profile'] ) {
                $customer_profile = $current_user->customerProfile();
                $payment_profile_id = $payment_info['payment_profile'];
            // we need to create the payment profile
            } else {
                $payment_profile  = $current_user->createPaymentProfile( $payment_info );
                $customer_profile = $current_user->customerProfile();

                if ( !($payment_profile instanceof classes\authorize\PaymentProfile) )
                    json_error( $payment_profile ); 
            }
            
            // Let the user know if the transaction fails
            $payment_response = $customer_profile->chargeCard(
                $total, $payment_profile_id, null, null, $description
            );
            if ( !is_array( $payment_response ) ) json_error( $payment_response );
            $transaction_query = $pdo->prepare(
                "INSERT INTO transactions (trans_date, admin_id, description, amount, reg_amount, ship_amount, zip, users_registered, response) "
                ."VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $transaction_query->execute([
                $current_user->admin_id, $description, $payment_info['total'], 
                ( $total - $shipping_charges ), $shipping_charges,
                $current_user->admin_postal, implode( ', ', $user_ids ),
                json_encode( $payment_response )
            ]);
            $trans_id = $pdo->lastInsertId();
        } else {
            $payment_response = 'N/A'; $trans_id = false;
        }

        // register all the users...
        $errors = [];   $registration_table_users = [];
        $registration_info_query = $pdo->prepare(
            "INSERT INTO registration_charges (trans_id, user_id, school_id, type, amount, year) "
            ."VALUES( ?, ?, ?, ?, ?, '$year' )"
        );
        // for each user
        foreach ( $users as $user ) {
            $user_errors = [];
            foreach( $registrations as $registration ){
                if ( !($user->user_id == $registration['user_id']) ) continue;
                // insert a record into the registration_charges table.
                $registration_info_query->execute([
                    ( $trans_id ? $trans_id : null ), 
                    $user->user_id, $user->school_id, $registration['registration_type'],
                    $registration['paid']
                ]);
                // Chayolei Registration
                if ( $registration['registration_type'] == 'chayolei' ) {
                    array_merge( $user_errors, $user->registerChayolei(
                        $current_user->admin_id, $year, $registration['paid']
                    ) );
                    if ( in_array( $user->school_id, [ '269', '61' ] ) )
                        $registration_table_users[ $user->school_id ][] = $user->user_id;
                // Chidon Registration
                } else if ( $registration['registration_type'] == 'chidon' ) {
                    if ( !$user->registerChidon( $year, $registration['size'], $current_user->admin_id ) )
                        $user_errors[] = "Could not register ".$user->user_id." for chidon";
                }
            }
            if ( count( $user_errors ) > 0 ) 
                $errors[$user->user_id] = $user_errors;
        };

        // insert into special myshliach/anash kinder table
        if( count($registration_table_users) > 0 ){
            $registration_table_query = $pdo->prepare(
                "INSERT INTO registration (description, approval, year, school_id, "
                ."admin_id, ship_option, ship_dest, users) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            foreach( $registration_table_users as $school_id => $user_ids ){
                $registration_table_query->execute([
                    $description, json_encode( $payment_response ), $year, $school_id,
                    $current_user->admin_id, $shipping_info['shipping_type'], 
                    $current_user->admin_country, implode( ', ', $user_ids )
                ]);
            }
        }

        if ( count( $errors ) > 0 )
            mail( "bugs@tzivoshashem.org", "Mobile Registration Error(s)", json_encode( $errors ) );
        
        json_response( false );
    }

    // serializer for getUsers()
    private function serializeUsers( $users ) {
        return array_map( function( $user ) {
            return $user->to_array([
                'only'  => [
                    'user_id', 'user_code', 'first', 'last', 'first_he', 'last_he', 'class_id',
                    'lang_id', 'gender', 'dob', 'mobile_pic', 'user_registered', 'user_serial',
                ],
                'methods' => [ 'registrationRates', 'registrationStatus', 'profilePicture' ],
                'include' => [ 
                    'school' => [ 'only' => [ 'school_id', 'school_name' ] ],
                    'platoon' => [ 'only' => [ 'class_id', 'class_grade', 'class_sub' ] ]
                ]
            ]);
        }, $users );
    }
}

rest_router( new UserRegistrationRouter );