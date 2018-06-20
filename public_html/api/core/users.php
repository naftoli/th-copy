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
        $this->getUsers( $current_user );
        $response = array_map( function( $user ) {
            return $user->indexSerialize();
        }, $this->users );
        json_response( $response );
    }

    private function getUsers( $admin, $user_id = false ) {
        $options = [ 'include' => ['school', 'platton'] ];
        if ( !$user_id ) {
            global $pdo;

            $sql = "SELECT * FROM users JOIN schools USING ( school_id ) JOIN classes USING ( class_id ) WHERE users.school_id = ?";
            $query = $pdo->prepare( $sql );
            $query->execute( [ 82 ] );

            foreach( $query->fetchAll() as $row ){
                $user = User::build( $row );
                $user->SetRelatedModel( 'school', School::build( $row ) );
                $user->SetRelatedModel( 'platton', Platton::build( $row ) );
                $this->users[] = $user;
            }
            
        } else {
            $this->users = [ User::find( $user_id ), $options ];
        }
        return $this->users;
    }

}

rest_router( new UsersRouter );