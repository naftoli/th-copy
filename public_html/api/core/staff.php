<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
include_once( __DIR__ . "/../functions/format/parents.php" );

class StaffRouter {

    private $defaultPositions = [
        'school' => 'Base Commander',
        'class' => 'Teacher',
        'staff' => 'Unknown'
    ];

    public function index() {
        global $current_user; global $pdo;

        $filters = [];   $params = [];
        // limit based on admin type
        $login = $current_user->login;
        if ( $login['code'] === 'HQ' ) {
            $filters[] = 'test_school = 0';
        } else if ( $login['code'] === 'CKIDS-ADMIN' ) {
            $filters[] = 'ckids = 1';
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 'school_id = ?';   $params[] = $login['id'];
        } else { json_error( 'Access Denied' ); }
        // combine the filters
        $filters = implode( ' AND ', $filters );

        $sql = "SELECT a.admin_id, aa.auth, aa.id, a.username, a.password, a.title, photo, "
            ." a.first, a.last, a.admin_email AS email, a.admin_phone_work AS work, a.admin_phone_mobile AS cell, "
            ." aa.position, aa.role_id, IFNULL(s.school_name, s2.school_name) as school_name, "
            ." c.class_grade, c.class_sub, IFNULL(s.school_id, s2.school_id) as school_id, "
            ." IFNULL(s.test_school, s2.test_school) as test_school, IFNULL(s.ckids, s2.ckids) as ckids "
            ." FROM admin_auths aa JOIN admins a USING (admin_id) "
            ." LEFT JOIN schools s ON aa.auth IN ('school', 'staff') AND id = school_id "
            ." LEFT JOIN classes c ON aa.auth = 'class' AND id = class_id "
            ." LEFT JOIN schools s2 ON c.school_id = s2.school_id "
            ." WHERE aa.auth IN ('school' , 'class', 'staff') "
            ." HAVING $filters "
            ." ORDER BY school_name, position, first, last;";
        $query = $pdo->prepare( $sql );
        $query->execute( $params );

        $staff = [];
        while( $admin = $query->fetch() ){
            $platoon = (new Platoon([
                'class_grade' => $admin['class_grade'], 'class_sub' => $admin['class_sub']
            ]))->name();
            // set the position if it is blank
            if ( !$admin['position'] ) 
                $admin['position'] = $this->defaultPositions[ $admin['auth'] ];
            // admin has many positions
            $position = [ 
                'auth' => $admin['auth'], 'admin_id' => intval($admin['admin_id']), 
                'id' => intval($admin['id']), 'position' => $admin['position'],
                'base' => $admin['school_name'], 'platoon' => $platoon
            ];
            // if there is no staff, make a new one
            if ( !isset( $staff[$admin['admin_id']] ) ) {
                // remove extra columns
                unset($admin['auth']); unset($admin['id']);
                unset($admin['positions']); unset($admin['school_name']);
                unset($admin['class_grade']); unset($admin['class_sub']);
                
                // create the children array and add this child
                $admin['admin_id'] = intval( $admin['admin_id'] );
                $admin['school_id'] = intval( $admin['school_id'] );
                $admin['positions'] = [ $position ];
                $admin['ckids'] = !!$admin['ckids'];
                $admin['test_school'] = !!$admin['test_school'];
                // $admin['key'] = mashpia\api\auth\Auth::mobileKey( $admin['admin_id'] );
                $staff[ $admin['admin_id'] ] = $admin;
            // add to existing parent
            } else {
                $staff[ $admin['admin_id'] ]['positions'][] = $position;
                $staff[ $admin['admin_id'] ]['position'] = 'Mulitple Positions';
            }
        }
        json_response( array_values( $staff ) );
    }

    public function create() {
        global $current_user; global $pdo;

        // $admin = new Admin([
        //     'username' => $_POST['email'],
        //     'password' => 'p1234',
        //     'first' => formatParentName( $_POST['father'], $_POST['mother'] ),
        //     'father' => $_POST['father'],
        //     'mother' => $_POST['mother'],
        //     'last' => $_POST['last'],
        //     'admin_email'=> $_POST['email'],
        //     'admin_phone_home'  => $_POST['home'],
        //     'admin_phone_mobile'=> $_POST['cell'],
        //     'is_parent' => '1',
        //     'created_by' => $current_user->admin_id,
        // ]);

        // if ( !$admin->is_valid() && $admin->errors->is_invalid('admin_email') )
        //     return json_error( 'There is already an account for this email address.');

        // if ( !$admin->is_valid() )
        //     return json_error( implode(', ', $admin->errors->full_messages() ) );

        // if ( !$admin->save() )
        //     return json_error( 'Server Error CORE-PARENTS-87. Could not create parent account.');

        // $admin->sendParentEmail();

        // $insert_query = $pdo->prepare(
        //     'INSERT INTO admin_auths ( admin_id, auth, id, role_id, position ) '.
        //     'VALUES( ?, "user", ?, 1, "parent");'
        // );
        // $has_parent_query = $pdo->prepare(
        //     'SELECT * FROM admin_auths WHERE admin_id=? AND auth = "user" AND id=?'
        // );
        // foreach( $_POST['children'] as $user_id ) {
        //     $has_parent_query->execute([ $admin->admin_id, $user_id ]);
        //     if ( $has_parent_query->rowCount() == 0 ) {
        //         $insert_query->execute([ $admin->admin_id, $user_id ]);
        //     }
        // }
        
        // json_response( $admin->admin_id );
    }

    public function update( $id ) {
        try { // find the admin
            $admin = Admin::find( $id );
            foreach( $_POST as $key => $value ) {
                $admin->{ $key } = $value;
            }
        } catch ( Exception $e ) { 
            return json_error( 'Could not update staff' );
        }
        // return any errors
        if ( !$admin->is_valid() ) return json_error( $admin->errors->__toString() );
        // make sure we can update him
        if ( !$admin->save() ) return json_error( 'Could not update staff' );
        return json_response( $_POST );
    }
}

rest_router( new StaffRouter );
