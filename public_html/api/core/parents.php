<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
include_once( __DIR__ . "/../functions/format/parents.php" );

class ParentsRouter {

    public function index() {
        global $current_user; global $pdo;

        $filters = [];   $params = [];
        // limit based on admin type
        $login = $current_user->login;
        if ( $login['code'] === 'HQ' ) {
            $filters[] = 's.test_school = 0';
            $filters[] = 'u.user_registered IS NOT NULL';
        } else if ( $login['code'] === 'INST' ) {
            $filters[] = 's.inst_id = ?'; $params[] = $login['id'];
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 's.school_id = ?';   $params[] = $login['id'];
        } else { json_error( 'Access Denied' ); }
        // combine the filters
        $filters = implode( ' AND ', $filters );

        $sql = "SELECT a.admin_id, a.username, a.title, a.first, a.father, a.mother, a.last, "
            ." CONCAT( admin_address1, ' ', admin_address2) as address, admin_city as city, "
            ." admin_state as state, admin_postal as zip, admin_country as country, "
            ." admin_phone_mobile as cell, admin_email as email, father_pic, mother_pic, "
            ." u.first as child_first, u.last as child_last, u.user_id, u.user_serial FROM "
            ." users u JOIN schools s USING (school_id) LEFT JOIN admin_auths aa ON aa.auth = 'user' AND aa.id = u.user_id "
            ." LEFT JOIN admins a USING (admin_id) WHERE $filters;";
        $query = $pdo->prepare( $sql );
        $query->execute( $params );

        $parents = []; $children = [];
        // fetch all results and parse them as models
        while( $parent = $query->fetch() ){
            $child = [ 
                'first' => $parent['child_first'], 'last' => $parent['child_last'], 
                'user_id' => intval($parent['user_id']), 'user_serial' => intval($parent['user_serial'])
            ];
            // if there is no parent, save them as a child
            if ( !$parent['admin_id'] ) {
                $children[] = $child;
            // create new parents
            } else if ( !isset( $parents[$parent['admin_id']] ) ) {
                // remove extra columns
                unset($parent['child_first']); unset($parent['child_last']);
                unset($parent['user_serial']); unset($parent['user_id']);
                // create the children array and add this child
                $parent['admin_id'] = intval( $parent['admin_id'] );
                $parent['first'] = formatParentName( $parent['father'], $parent['mother'], $parent['first'] );
                $parent['children'] = [ $child ];
                $parent['father_pic'] = $parent['father_pic'] ? '/mobile/reg/' . $parent['father_pic'] : false;
                $parent['mother_pic'] = $parent['mother_pic'] ? '/mobile/reg/' . $parent['mother_pic'] : false;
                $parent['key'] = mashpia\api\auth\Auth::mobileKey( $parent['admin_id'] );
                if ( $parent['first'] ) $parents[$parent['admin_id']] = $parent;
            // add to existing parent
            } else {
                $parents[$parent['admin_id']]['children'][] = $child;
            }
        }
        json_response([
            'parents' => array_values( $parents ),
            'children' => array_values( $children ) 
        ]);
    }

    public function create() {
        global $current_user; global $pdo;

        $admin = new Admin([
            'username' => $_POST['email'],
            'password' => 'p1234',
            'first' => formatParentName( $_POST['father'], $_POST['mother'] ),
            'father' => $_POST['father'],
            'mother' => $_POST['mother'],
            'last' => $_POST['last'],
            'admin_email'=> $_POST['email'],
            'admin_phone_home'  => $_POST['home'],
            'admin_phone_mobile'=> $_POST['cell'],
            'is_parent' => '1',
            'created_by' => $current_user->admin_id,
        ]);

        if ( !$admin->is_valid() && $admin->errors->is_invalid('admin_email') )
            return json_error( 'There is already an account for this email address.');

        if ( !$admin->is_valid() )
            return json_error( implode(', ', $admin->errors->full_messages() ) );

        if ( !$admin->save() )
            return json_error( 'Server Error CORE-PARENTS-87. Could not create parent account.');

        $admin->sendParentEmail();

        $insert_query = $pdo->prepare(
            'INSERT INTO admin_auths ( admin_id, auth, id, role_id, position ) '.
            'VALUES( ?, "user", ?, 1, "parent");'
        );
        $has_parent_query = $pdo->prepare(
            'SELECT * FROM admin_auths WHERE admin_id=? AND auth = "user" AND id=?'
        );
        foreach( $_POST['children'] as $user_id ) {
            $has_parent_query->execute([ $admin->admin_id, $user_id ]);
            if ( $has_parent_query->rowCount() == 0 ) {
                $insert_query->execute([ $admin->admin_id, $user_id ]);
            }
        }
        
        json_response( $admin->admin_id );
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

        json_response( ['admin_id' => $admin->admin_id ], $success );
    }
}

rest_router( new ParentsRouter );
