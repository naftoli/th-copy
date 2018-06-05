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

        json_response([
            "users" => $this->serializeUsers( $users )
        ]);
    }

    public function getShipping(){

    }

    public function registerUsers(){

    }

    private function serializeUsers( $users ) {
        return array_map( function( $user ) {
            return $user->to_array([
                'only'  => [
                    'user_id', 'user_code', 'first', 'last', 'first_he', 'last_he',
                    'lang_id', 'gender', 'dob', 'mobile_pic', 'user_registered', 'user_serial',
                ],
                'methods' => [ 'registrationRates', 'registrationStatus', 'profilePicture' ],
                'include' => [ 
                    'school' => [ 'only' => [ 'school_id', 'school_name' ] ],
                    'platton' => [ 'only' => [ 'class_id', 'class_grade', 'class_sub' ] ]
                ]
            ]);
        }, $users );
    }
}

rest_router( new UserRegistrationRouter );