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