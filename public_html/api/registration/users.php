<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UsersRouter {

    public function authenticate(){
        global $current_user;
        return $current_user->login->code === 'BC';
    }

    public function index() {
        // TODO, test and fix
        global $current_user; global $MASHPIA_DB;
        // filters and params for the filters
        $filters = [];   $params = [];
        // limit based on admin type
        $login = $current_user->login;
        $school = $current_user->login->model;

        $params['school_id'] = $login->id;
        $params['year'] = GlobalSettings::getRegistrationYear( $login->id );
        $filters[] = 'u.school_id = :school_id';
        $filters[] = 'ur.paid IS NULL';
        $filters[] = 'u.chayolei = 1';
        // combine the filters
        $filters = implode( ' AND ', $filters );
        // generate the SQL
        $sql = "SELECT u.user_id, u.user_serial, u.first, u.last, s.school_name, "
            ."c.class_grade, c.class_sub, ur.paid "
            ."FROM users u JOIN schools s USING (school_id) "
            ."LEFT JOIN user_registration ur ON ur.user_id = u.user_id AND ur.year = :year "
            ."LEFT JOIN classes c USING (class_id) WHERE $filters "
            ."ORDER BY first, last, class_grade, class_sub;";

        $query = $MASHPIA_DB->prepare( $sql );
        $query->execute( $params );

        $users = [];
        // fetch all results and parse them as models
        while( $row = $query->fetch() ){
            // generate the platoon name
            $platoon = Platoon::generateName( $row['class_grade'], $row['class_sub'] );

            $early_bird = $school->earlyBird();
            $fee = $school->soldierFee();
            $type = $school->reg_type;
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
        global $current_user; global $MASHPIA_DB;

        if ( !$current_user->login->code === 'BC' )
            json_error( 'Only Base Commanders can authorize registration.');
        $school = $current_user->login->model;
        // get all the users we are registering
        if ( !isset($_POST['user_ids']) || count($_POST['user_ids']) < 1 ) 
            json_error('Please select some soldiers to register.');
        try {
            $users = \Soldier::find( $_POST['user_ids'] );
            $users = is_array( $users ) ? $users : [ $users ];
        } catch ( Exception $e ) {
            json_error( 'Invalid Request', $e );
        }

        // setup the variables we will need later
        $year = GlobalSettings::getRegistrationYear( $school->school_id );
        $total = intval( $_POST['total'] );
        $trans_id = false;
        $payment_response = false;
        // if we need to charge them
        if ( $total > 0 ) {
            if ( !isset( $_POST[ 'payment' ] ) )
                json_error( 'No payment information provided' );
            // attempt to get the payment profile
            try {
                $payment_profile_id = $school->getPaymentProfileId( $_POST['payment'] );
                $customer_profile = $school->customerProfile();
            } catch ( Exception $e ) {
                json_error( $e );
            }
            
            // * setup the queries
            $description = "Soldier Registration ( Base ) for $year: " . count( $users ) . " Soldiers. ";

            // create Transaction
            $create_transaction_query = $MASHPIA_DB->prepare(
                "INSERT INTO transactions (school_id, trans_date, description, amount, admin_id, zip, response, users_registered) "
                ."VALUES (?, NOW(), ?, ?, ?, ?, ?, ?)"
            );
            // * submit the transaction
            $payment_response = $customer_profile->chargeCard(
                $total, $payment_profile_id, null, null, $description
            );
            // * make sure we get a valid response
            if ( !is_array( $payment_response ) ) {
                json_error( $payment_response );
            }
            // * save the transaction to our dbs
            $create_transaction_query->execute([
                $school->school_id,             $description,   $total,
                $current_user->admin_id,        $school->shipping_postal,
                json_encode($payment_response), implode( ', ', $_POST['user_ids'] )
            ]);

            $trans_id = $MASHPIA_DB->lastInsertId();
        }

        $errors = [];
        $fee = $school->soldierFee();

        foreach( $users as $user ) {
            $user_errors = $user->registerChayolei( $current_user->admin_id, $year, $fee );
            if ( count( $user_errors ) > 0 )
                $errors[ $user->user_id ] = $user_errors;
        }
        // email any errors
        if ( count( $errors ) > 0 ) {
            @mail( "bugs@tzivoshashem.org", "BC Registration Error(s)", json_encode( $errors ) );
        }

        json_response([
            'response' => $payment_response,
            'transaction_id' => $trans_id,
            'errors' => $errors
        ]);
    }
}

rest_router( new UsersRouter );
