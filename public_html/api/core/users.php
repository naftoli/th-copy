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
            global $pdo;

            $sql = "SELECT * FROM users JOIN schools USING ( school_id ) JOIN classes USING ( class_id ) WHERE users.school_id = ?";
            $query = $pdo->prepare( $sql );
            $query->execute( [ 82 ] );

            // $rows = [];
            // foreach( $query->fetchAll() as $row ){
            //     $rows[] = new User( $row, true, false, false );
            // }
            $user = new User;
            print_r( $user->attributes() ); die();
            // if ( $admin->authCode() == 'HQ' ) {
            //     $this->users = User::all( $options );
            // } else if ( $admin->authCode() == 'BC' ) {
            //     $this->users = User::find_all_by_school_id( $admin->getAuthIds( 'school' ), $options );
            // }
        } else {
            $this->users = [ User::find( $user_id ), $options ];
        }
        return $this->users;
    }
}

rest_router( new UsersRouter );