<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
include_once( __DIR__ . "/../tools/functions/format/parents.php" );

class StaffRouter {

    private $defaultPositions = [
        'school' => 'Base Commander',
        'class' => 'Teacher',
        'staff' => 'Unknown'
    ];

    public function index() {
        global $current_user; global $MASHPIA_DB;

        $filters = [];   $params = [];
        // limit based on admin type
        $filters[] = $current_user->login->getFilter( '', '' );
        if ( !$filters[0] )
            return json_error( 'Access Denied: CORE-STAFF-21' );
        // combine the filters
        $filters = implode( ' AND ', $filters );

        $sql = "SELECT a.admin_id, aa.auth, aa.id, a.username, a.password, a.title, photo, "
            ." a.first, a.last, a.admin_email AS email, a.admin_phone_work AS work, a.admin_phone_mobile AS cell, "
            ." aa.position, r.role_name as role, IFNULL(s.school_name, s2.school_name) as school_name, "
            ." c.class_grade, c.class_sub, IFNULL(s.school_id, s2.school_id) as school_id, "
            ." IFNULL(s.test_school, s2.test_school) as test_school, IFNULL(s.inst_id, s2.inst_id) AS inst_id "
            ." FROM admin_auths aa JOIN roles r USING (role_id) JOIN admins a USING (admin_id) "
            ." LEFT JOIN schools s ON aa.auth IN ('school', 'staff') AND id = school_id "
            ." LEFT JOIN classes c ON aa.auth = 'class' AND id = class_id "
            ." LEFT JOIN schools s2 ON c.school_id = s2.school_id "
            ." WHERE aa.auth IN ('school' , 'class', 'staff') AND a.auth !='super' "
            ." HAVING $filters "
            ." ORDER BY school_name, aa.auth, role, position, first, last;";
        // echo $sql; die();
        $query = $MASHPIA_DB->prepare( $sql );
        $query->execute( $params );

        $staff = [];
        while( $admin = $query->fetch() ){
            $platoon = trim( (new Platoon([
                'class_grade' => $admin['class_grade'], 'class_sub' => $admin['class_sub']
            ]))->name() );

            if ( $platoon )
                $admin['platoon'] = $platoon;
            else
                $admin['platoon'] = false;
            
            // set the position if it is blank
            if ( !$admin['position'] ) 
                $admin['position'] = $this->defaultPositions[ $admin['auth'] ];
            // admin has many positions
            $position = [ 
                'auth' => $admin['auth'], 'admin_id' => intval($admin['admin_id']), 
                'id' => intval($admin['id']), 'base' => $admin['school_name'],
                'position' => $admin['position'], 'role' => $admin['role'],
                'platoon' => $platoon ? $platoon : 'All Platoons',
            ];
            // if there is no staff, make a new one
            if ( !isset( $staff[$admin['admin_id']] ) ) {
                // remove extra columns
                unset($admin['auth']); unset($admin['id']);
                unset($admin['positions']); unset($admin['class_grade']);
                unset($admin['class_sub']);
                
                // create the admin/staff instance
                $admin['admin_id'] = intval( $admin['admin_id'] );
                $admin['school_id'] = intval( $admin['school_id'] );
                $admin['positions'] = [ $position ];
                $admin['email'] = $admin['email'] ? $admin['email'] : '';
                $admin['test_school'] = !!$admin['test_school'];
                // $admin['key'] = mashpia\api\auth\Auth::mobileKey( $admin['admin_id'] );
                $staff[ $admin['admin_id'] ] = $admin;
            // add to existing parent
            } else {
                $staff[ $admin['admin_id'] ]['positions'][] = $position;
                if ( $admin['position'] !== $staff[ $admin['admin_id'] ]['position'] )
                    $staff[ $admin['admin_id'] ]['position'] = 'Mulitple Positions';
                
                if ( $admin['role'] !== $staff[ $admin['admin_id'] ]['role'] )
                    $staff[ $admin['admin_id'] ]['role'] = 'Mulitple Roles';
                
                if ( $admin['school_name'] !== $staff[ $admin['admin_id'] ]['school_name'] )
                    $staff[ $admin['admin_id'] ]['school_name'] = 'Mulitple Bases';

                if ( $platoon && $staff[ $admin['admin_id'] ]['platoon'] )
                    $staff[ $admin['admin_id'] ]['platoon'] = 'Multiple Platoons';
                else if ( $platoon )
                    $staff[ $admin['admin_id'] ]['platoon'] = $platoon;
            }
        }
        json_response( array_values( $staff ) );
    }

    public function create() {
        global $current_user; global $MASHPIA_DB;

        $admin = new Admin([
            'username' => $_POST['username'],   'password' => $_POST['password'],
            'title' => $_POST['title'], 'first' => $_POST['first'], 'last' => $_POST['last'],
            'admin_email'=> $_POST['email'],    'admin_phone_work'  => $_POST['work'],
            'admin_phone_mobile'=> $_POST['cell'],  'created_by' => $current_user->admin_id,
        ]);

        if ( !$admin->is_valid() && $admin->errors->is_invalid('admin_email') )
            return json_error( 'There is already an account for this email address.');

        if ( !$admin->is_valid() )
            return json_error( implode(', ', $admin->errors->full_messages() ) );

        $auth = new AdminAuth( $_POST['auth'] );
        // send a sign-up email
        // if ( $auth->auth === 'school' ) {
        //     $base = School::find( $auth->id )->school_name;
        //     $admin->sendNewBCEmail( $auth->auth, $base );
        // }
        // save the admin info
        if ( !$admin->save() )
            return json_error( 'Server Error CORE-STAFF-115. Could not create staff account.');
        // connect the auth
        $auth->admin_id = $admin->admin_id;
        // save the user
        if ( !$auth->save() ) {
            $admin->delete();
            return json_error( 'Server Error CORE-STAFF-121. Could not create staff account.');
        }
        json_response( false );
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
