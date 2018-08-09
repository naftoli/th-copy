<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class AdminAuthRouter {

    function addTeacher() {
        global $pdo;
        if ( !isset($_POST['class_id']) ) json_error('Platoon missing.');
        if ( !isset($_POST['email']) ) json_error('Email missing');
        
        $platoon = Platoon::find( $_POST['class_id'] );
        if ( !$platoon ) json_error( 'Invalid Platoon' );

        $admin = Admin::find_by_admin_email( $_POST['email'] );        
        if ( !$admin ) json_error( 'E-mail does not exist' );
        // prevent duplicates
        foreach( $platoon->staff() as $staff ) {
            if ( $staff['admin_id'] == $admin->admin_id ) 
                return json_error( 'Account already has access' );
        }
        // generate the query
        $query = $pdo->prepare(
            'INSERT INTO admin_auths ( admin_id, auth, id, role_id, position ) '.
            'VALUES( ?, "class", ?, 13, "Teacher");'
        );
        // and return the results
        $success = $query->execute([ $admin->admin_id, $platoon->class_id ]);
        json_response( false, $success );
    }

    public function index() {
        $auth = AdminAuth::findAuth( 2, 'school', 58 );
        json_response( $auth->admin );
    }
}

rest_router( new AdminAuthRouter );
