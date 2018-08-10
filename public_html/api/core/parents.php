<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class ParentsRouter {

    public function index() {
        global $current_user; global $pdo;

        $filters = [];   $params = [];
        // limit based on admin type
        $login = $current_user->login;
        if ( $login['code'] === 'HQ' ) {
            $filters[] = 's.test_school = 0';
        } else if ( $login['code'] === 'CKIDS-ADMIN' ) {
            $filters[] = 's.ckids = 1';
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 's.school_id = ?';   $params[] = $login['id'];
        } else { json_error( 'Access Denied' ); }
        // combine the filters
        $filters = implode( ' AND ', $filters );

        $sql = "SELECT a.admin_id, a.username, a.title, a.first, a.last, "
            ." admin_address1, admin_city, admin_state, admin_postal, admin_country, "
            ." admin_phone_mobile, admin_email, father_pic, mother_pic, "
            ." u.first as child_first, u.last as child_last, u.user_id, u.user_serial FROM "
            ." admins a JOIN admin_auths aa ON aa.auth = 'user' AND a.admin_id = aa.admin_id "
            ." JOIN users u ON aa.id = u.user_id JOIN schools s USING (school_id) WHERE $filters;";
        $query = $pdo->prepare( $sql );
        $query->execute( $params );

        $parents = [];
        // fetch all results and parse them as models
        while( $parent = $query->fetch() ){
            $child = [ 
                'first' => $parent['child_first'], 'last' => $parent['child_last'], 
                'user_id' => $parent['user_id'], 'user_serial' => $parent['user_serial'] 
            ];
            if ( !isset( $parents[$parent['admin_id']] ) ) {
                // remove extra columns
                unset($parent['child_first']); unset($parent['child_last']);
                unset($parent['user_serial']); unset($parent['user_id']);
                // create the children array and add this child
                $parent['children'] = [ $child ];
                $parents[$parent['admin_id']] = $parent;
            } else {
                // add to existing parent
                $parents[$parent['admin_id']]['children'][] = $child;
            }
        }
        json_response( array_values( $parents ) );
    }

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
