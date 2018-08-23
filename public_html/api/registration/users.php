<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UsersRouter {

    public function index() {
        global $current_user; global $pdo;
        // filters and params for the filters
        $filters = [];   $params = [];
        // limit based on admin type
        $login = $current_user->login;
        if ( $login['code'] === 'BC' ) {
            $filters[] = 'u.school_id = :school_id'; $params['school_id'] = $login['id'];
        } else { 
            json_error( 'Access Deinied: CORE-USERS-26' ); 
        }
        $params['year'] = GlobalSettings::getRegistrationYear( $login['id'] );
        $filters[] = 'ur.paid IS NULL';
        $filters[] = 'u.chayolei = 1';
        // combine the filters
        $filters = implode( ' AND ', $filters );
        // generate the SQL
        $sql = "SELECT u.user_id, u.user_serial, u.first, u.last, s.school_name, c.class_grade, c.class_sub, "
            ."!ISNULL(sr.date_paid) as school_registered, sr.type, s.reg_type, sr.early_bird, ur.paid, "
            ."sr.child_fee FROM users u JOIN schools s USING (school_id) "
            ."LEFT JOIN school_registrations sr ON sr.school_id = s.school_id AND sr.year = :year "
            ."LEFT JOIN user_registration ur ON ur.user_id = u.user_id AND ur.year = :year "
            ."LEFT JOIN classes c USING (class_id) WHERE $filters "
            ."ORDER BY first, last, class_grade, class_sub;";
        $query = $pdo->prepare( $sql );
        $query->execute( $params );

        $users = [];
        // fetch all results and parse them as models
        while( $row = $query->fetch() ){
            $platoon = ( new Platoon(['class_grade' => $row['class_grade'], 'class_sub' => $row['class_sub']]) )->name();
            $early_bird = $row['early_bird'] ? new DateTime( $row['early_bird'] ) : SchoolRegistration::getDefaultEarlyBird();
            $type = intval( $row['type'] ? $row['type'] : $row['reg_type'] );
            $fee = GlobalSettings::calculateChildFee( $type, 0, true, $early_bird > new DateTime() );
            $fee = intval( $row['child_fee'] > 0 ? $row['child_fee'] : $fee );
            // format and return just the data we want...
            $users[] = [
                'user_id' => intval($row['user_id']), 'first' => $row['first'], 'last' => $row['last'],
                'platoon' => $platoon, 'fee' => $fee, 'paid' => $row['paid'] ? intval($row['paid']) : false, 
                'school_name' => $row['school_name'], 'user_serial' => intval($row['user_serial']), 'type' => $type
            ];
        }
        json_response( $users );
    }

    public function create() {
        global $current_user; global $pdo;

        if ( !$current_user->login['code'] === 'BC' )
            json_error( 'Only Base Commanders can authorize registration.');
        $school = School::find( $current_user->login['id'] );
        // get all the users we are registering\
        if ( !isset($_POST['user_ids']) || count($_POST['user_ids']) < 1 ) 
            json_error('Please select some soldiers to register.');
        try {
            $users = User::find( $_POST['user_ids'] );
            $users = is_array( $users ) ? $users : [ $users ];
        } catch ( Exception $e ) {
            json_error( 'Invalid Request', $e );
        }
        // get the payment information
        if ( !isset( $_POST[ 'payment' ] ) ) {
            json_error( 'Please select a Credit Card' );
        } else if ( isset($_POST['payment']['payment_profile_id']) ){
            $customer_profile = $school->customerProfile();
            $payment_profile_id = $_POST['payment']['payment_profile_id'];
        } else {
            $payment_profile  = $school->createPaymentProfile( $_POST['payment'], $current_user->admin_email );
            $customer_profile = $school->customerProfile();
            if ( !($payment_profile instanceof classes\authorize\PaymentProfile) )
                json_error( $payment_profile );
            $payment_profile_id = $payment_profile->customerPaymentProfileId; 
        }

        // setup the variables we will need later
        $user_serials = array_map( function( $user ){ return $user->user_serial; }, $users);
        $year = GlobalSettings::getRegistrationYear( $school->school_id );
        $description = "Base - Soldier Registration for $year: " . implode( ", ", $user_serials );
        $total = intval( $_POST['total'] );

        // create Transaction
        $create_transaction_query = $pdo->prepare(
            "INSERT INTO transactions (school_id, trans_date, description, amount, admin_id, zip, users_registered) VALUES (?, NOW(), ?, ?, ?, ?, ?)"
        );
        $delete_transaction_query = $pdo->prepare( 'DELETE FROM transactions WHERE transaction_id = ?' );
        $finish_transaction_query = $pdo->prepare( 'UPDATE transactions SET response = ? WHERE trans_id = ?' );
        $create_transaction_query->execute([
            $school->school_id, $description, $total, $current_user->admin_id, 
            $school->shipping_postal, implode( ', ', $_POST['user_ids'] )
        ]);
        $trans_id = $pdo->lastInsertId();

        // Run the transaction
        $payment_response = $customer_profile->chargeCard(
            $total, $payment_profile_id, null, $trans_id, $description
        );
        if ( !is_array( $payment_response ) ) {
            $delete_transaction_query->execute([ $trans_id ]);
            json_error( $payment_response );
        }
        $updated = $finish_transaction_query->execute([json_encode($payment_response), $trans_id]);

        $errors = [];
        $fee = $school->getRegInfo( $year )->getChildFee( true );
        foreach( $users as $user ) {
            $user_errors = $user->registerChayolei(
                $current_user->admin_id, $year, $fee
            );
            if ( count( $user_errors ) > 0 ) $errors[$user->user_id] = $user_errors;
        }
        // email any errors
        if ( count( $errors ) > 0 ) {
            mail( "bugs@tzivoshashem.org", "BC Registration Error(s)", json_encode( $errors ) );
        }

        json_response([
            'updated' => $updated,
            'response' => $payment_response,
            'transaction_id' => $trans_id
        ]);
    }
}

rest_router( new UsersRouter );
