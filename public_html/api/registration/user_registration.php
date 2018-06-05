<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UserRegistrationRouter {
    // parents only
    public function authenticate() {
        global $current_user;
        return count( $current_user->getAuthIds('user') ) > 0;
    }

    public function getUsers(){
        global $current_user;   global $pdo;
        // load all his user id's
        $user_ids = $current_user->getAuthIds( 'user' );
        $year = GlobalSettings::getRegistrationYear();
        // limit to the ones we care about
        $register_ids_query = $pdo->prepare(
            "SELECT u.user_id, user_reg_id, th_chidon_id FROM users u "
            ."LEFT JOIN user_registration ur ON ur.user_id = u.user_id AND ur.year = :year "
            ."LEFT JOIN th_chidon tc ON tc.user_id = u.user_id AND tc.year = :year "
            ."WHERE ( user_reg_id IS NULL OR th_chidon_id IS NULL ) "
            ."AND u.user_id IN ( " . implode( ", ", $user_ids ) . " )"
        );
        $register_ids_query->execute([ ':year' => $year ]);
        
        // sort that data into useable chunks
        $registration_info = [];
        $user_ids = [];
        foreach( $register_ids_query->fetchAll() as $row ){
            $registration_info[ $row['user_id'] ] = $row;
            $user_ids[] = $row['user_id'];
        };
        
        $users = User::find( $user_ids );

        json_response([
            "registration_info" => $registration_info, 
            "users" => $users 
        ]);
    }

    public function getShipping(){

    }

    public function registerUsers(){

    }
}

rest_router( new UserRegistrationRouter );