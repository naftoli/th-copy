<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class ParentsRouter {

    function removeChild() {
        global $pdo;
        if ( !isset($_POST['user_id']) ) json_error('Need a child to remove');
        if ( !isset($_POST['admin_id']) ) json_error('Need a parent to remove from');

        $query = $pdo->prepare(
            'DELETE FROM admin_auths WHERE admin_id = ? AND auth="user" AND id = ?'
        );
        $success = $query->execute([$_POST['admin_id'], $_POST['user_id']]);
        json_response( false, $success );
    }

    function addChild() {
        global $pdo;
        if ( !isset($_POST['user_id']) ) json_error('Need a child to add');
        if ( !isset($_POST['username']) ) json_error('Need a parent to add from');
        
        $user = User::find( $_POST['user_id'] );
        if ( !$user ) json_error( 'Invalid User ID' );

        $admin = Admin::find_by_username( $_POST['username'] );        
        if ( !$admin ) json_error( 'Cannot add Soldier to parent account. Username does not exist.' );

        $query = $pdo->prepare(
            'INSERT INTO admin_auths ( admin_id, auth, id, role_id, position ) '.
            'VALUES( ?, "user", ?, 1, "parent");'
        );
        if ( $user->parentAccount() ) json_error( 'Child already has a parent account.' );
        $success = $query->execute([ $admin->admin_id, $user->user_id ]);

        json_response( false, $success );
    }
}

rest_router( new ParentsRouter );
