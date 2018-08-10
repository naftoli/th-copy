<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class AdminAuthRouter {

    public function create() {
        // make sure we have an auth and id
        if ( !isset($_POST['id']) ) json_error('id missing.');
        if ( !isset($_POST['auth']) ) json_error('auth missing.');
        // make sure we have an admin ( first by Id then email )
        try {
            if ( isset($_POST['admin_id']) ) {
                $admin = Admin::find( intval( $_POST['admin_id'] ) ); 
            } else if ( isset($_POST['email']) ) {
                $admin = Admin::find_by_admin_email( $_POST['email'] ); 
            } else if ( isset($_POST['username']) ) {
                $admin = Admin::find_by_username( $_POST['username'] ); 
            } else {
                return json_error('Admin account not found.');
            }
            if ( !$admin ) return json_error('Admin account not found.');
        } catch ( ActiveRecord\RecordNotFound $e ) {
            return json_error('Admin account not found.');
        }
        
        $attrs = [ 'admin_id' => $admin->admin_id, 'auth' => $_POST['auth'], 'id' => $_POST['id'] ];
        if ( isset( $_POST['role_id'] ) ) $attrs['role_id'] = $_POST['role_id'];
        if ( isset( $_POST['position'] ) ) $attrs['position'] = $_POST['position'];

        try {
            $auth = AdminAuth::create( $attrs );
        } catch ( ActiveRecord\DatabaseException $e ) {
            return json_error( 'Login already exists. Please refresh page.' );
        }
        return json_response( $auth );
    }

    public function delete() {
        if ( !isset($_POST['id']) || !isset($_POST['admin_id']) || !isset($_POST['auth']) )
            return json_error('Cannot delete invalid login. Please try again.');
        try {
            $auth = AdminAuth::findAuth( $_POST['admin_id'], $_POST['auth'], $_POST['id'] );
            if ( $auth ) return json_response( false, $auth->delete() );
        } catch ( Exception $e ) {
            return json_error('Cannot delete invalid login. Please try again.');
        }
       return json_error('You cannot delete login if it does not exist');
    }

    // public function index() {
    //     $auth = AdminAuth::findAuth( 2, 'school', 58 );
    //     json_response( $auth->admin );
    // }
}

rest_router( new AdminAuthRouter );
