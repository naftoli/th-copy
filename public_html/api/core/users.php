<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UsersRouter {
    private $users = [];

    public function authenticate() {
        global $current_user;
        return in_array( $current_user->authCode(), [ 'HQ', 'BC' ] );
    }

    public function index() {
        global $current_user;
        json_response( $this->getUsers( $current_user ) );
    }

    private function getUsers( $admin, $user_id = false ) {
        $options = [ 'include' => ['school', 'platton'] ];
        if ( !$user_id ) {
            if ( $admin->authCode() == 'HQ' ) {
                $this->users = User::all( $options );
            } else if ( $admin->authCode() == 'BC' ) {
                $this->users = User::find_all_by_school_id( $admin->getAuthIds( 'school' ), $options );
            }
        } else {
            $this->users = [ User::find( $user_id ), $options ];
        }
        return $this->users;
    }
}

rest_router( new UsersRouter );