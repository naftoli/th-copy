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
            $filters[] = 'u.school_id = ?'; $params[] = $login['id'];
        } else { 
            json_error( 'Access Deinied: CORE-USERS-26' ); 
        }
        $filters[] = 'ur.paid IS NULL';
        // combine the filters
        $filters = implode( ' AND ', $filters );
        // generate the SQL
        $sql = "SELECT u.user_id, u.user_serial, u.first, u.last, s.school_name, c.class_grade, c.class_sub, "
            ."!ISNULL(sr.date_paid) as school_registered, sr.type, s.reg_type, sr.early_bird, ur.paid, "
            ."sr.child_fee FROM users u JOIN schools s USING (school_id) "
            ."LEFT JOIN school_registrations sr ON sr.school_id = s.school_id AND sr.year = 5779 "
            ."LEFT JOIN user_registration ur ON ur.user_id = u.user_id AND ur.year = 5779 "
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
        // global $current_user;
        // $user = User::build( $_POST );
        // if ( $current_user->login['code'] === 'TEACHER' ) {
        //     $user->school_id = Platoon::find( $current_user->login['id'] )->school_id;
        // } else {
        //     $platoon_school_id = Platoon::find( $user->class_id )->school_id;
        //     if ( $platoon_school_id !== $user->school_id )
        //         json_error( 'Please Select a valid Platoon' );
        // }
        // if ( !$user->is_valid() || !$user->save() )
        //     json_error( 'Could not create Soldier. (CODE: CORE-USERS-79)' );
        // json_response( $user );
    }
}

rest_router( new UsersRouter );
