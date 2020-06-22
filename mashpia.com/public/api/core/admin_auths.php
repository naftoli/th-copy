<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class AdminAuthRouter {

    public function create() {
        global $current_user;
        // make sure we have an auth and id
        if ( !isset($_POST['id']) ) json_error('id missing.');
        if ( !isset($_POST['auth']) ) json_error('auth missing.');
        // make sure we have an admin ( first by Id then email )
        try {
            if ( isset($_POST['admin_id']) ) {
                $admin = \Admin::find([ intval( $_POST['admin_id'] ) ]);
            } else if ( isset($_POST['email']) ) {
                $admin = \Admin::find_by_admin_email( $_POST['email'] ); 
            } else if ( isset($_POST['username']) ) {
                $admin = \Admin::find_by_username( $_POST['username'] ); 
            } else {
                return json_error('Admin account not found.');
            }
            if ( !$admin ) return json_error('Admin account not found.');
        } catch ( ActiveRecord\RecordNotFound $e ) {
            return json_error('Admin account not found.');
        }
        
        $attrs = [ 'admin_id' => $admin->admin_id, 'auth' => $_POST['auth'] ];
        if ( isset( $_POST['role_id'] ) ) $attrs['role_id'] = $_POST['role_id'];
        if ( isset( $_POST['position'] ) ) $attrs['position'] = $_POST['position'];

        if ( $current_user->login->code == 'BC' && !isset( $_POST['id'] ) ) {
            $attrs['id'] = $current_user->login->id;
        } else {
            $attrs['id'] = $_POST['id'];
        }

        try {
            $auth = AdminAuth::create( $attrs );
        } catch ( ActiveRecord\DatabaseException $e ) {
            return json_error( 'Login already exists. Please refresh page.' );
        }

        return json_response( $auth );
    }

    public function update( $id ) {
        // find the auth
        $auth = AdminAuth::findAuth( $_POST['admin_id'], $_POST['auth'], $_POST['id'] );
        // update the position
        $auth->position = $_POST['position'];
        $auth->save();
        // and return it
        json_response( $auth->position );
    }

    public function delete() {
        if ( !isset($_POST['id']) || !isset($_POST['admin_id']) || !isset($_POST['auth']) )
            return json_error('Cannot delete invalid login. Please try again.');
        try {
            $auth = AdminAuth::findAuth( $_POST['admin_id'], $_POST['auth'], $_POST['id'] );
            if ( $auth ) return json_response( $auth, $auth->delete() );
        } catch ( Exception $e ) {
            return json_error('Cannot delete invalid login. Please try again.');
        }
       return json_error('You cannot delete login if it does not exist');
    }

    /**
     * delete admin auth connections and then add new one
     */
    public function removeTeacher() {
        global $MASHPIA_DB;

        // delete old position
        try {
            $auth = AdminAuth::findAuth( $_POST['admin_id'], $_POST['auth'], $_POST['id'] );
            $auth->delete();
        } catch ( Exception $e ) {
            return json_error('Cannot delete invalid login. Please try again.');
        } 

        // get school id
        $stmt = $MASHPIA_DB->prepare("SELECT school_id FROM classes WHERE class_id = :id");
        $stmt->execute([':id' => $_POST['id']]);
        $school_id = $stmt->fetch()['school_id'];

        // create new position
        $new_auth = AdminAuth::create([
            'admin_id'  =>  $_POST['admin_id'], 
            'id'    =>  $school_id, 
            'auth'  =>  'staff', 
            'role_id'   =>  40,
            'position'  =>  ''
        ]);
        if (!$new_auth) return json_error('Error creating new position.');
        return json_response( $new_auth );
    }

    public function index() {
        $auth = AdminAuth::findAuth( 2, 'school', 58 );
        json_response( $auth );
    }
}

rest_router( new AdminAuthRouter );
