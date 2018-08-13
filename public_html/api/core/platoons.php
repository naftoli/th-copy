<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class PlatoonRouter {

    public function index() {
        global $current_user; global $pdo;
        // filters and params for the filters
        $filters = [];   $params = [];
        // limit based on admin type
        $login = $current_user->login;
        if ( $login['code'] === 'HQ' && !isset( $_GET['school_id'] ) ) {
            $filters[] = 's.test_school = 0';
        } else if ( $login['code'] === 'CKIDS-ADMIN' && !isset( $_GET['school_id'] ) ) {
            $filters[] = 's.ckids = 1';
        } else if ( $login['code'] === 'BC' && isset( $_GET['all'] ) ) { // get all bases on the account
            $school_ids = $current_user->getAuthIds( 'school' );
            $filters[] = 'c.school_id IN ('. implode(', ', $school_ids ) .')';
        } else if ( $login['code'] === 'BC' && !isset( $_GET['school_id'] ) ) {
            $filters[] = 'c.school_id = ?';   $params[] = $login['id'];
        } else if ( $login['code'] === 'TEACHER' ) {
            $filters[] = 'c.class_id = ?';   $params[] = $login['id'];
        } else if ( isset( $_GET['school_id'] ) ) {
            $filters[] = 'c.school_id = ?';   $params[] = $_GET['school_id'];
        } else { json_error( 'Invalid request. Please select a Base.'); }
        // combine the filters
        $filters = 'WHERE ' . implode( ' AND ', $filters );
        // generate the SQL
        $sql = "SELECT class_id, class_grade, class_sub, s.school_id, school_name, "
            ."class_teacher as teacher, c.cell, c.email, COUNT(user_id) as soldier_count, staff_count "
            ."FROM classes c JOIN schools s USING (school_id) LEFT JOIN users u USING ( class_id ) "
            ."LEFT JOIN ( SELECT count(*) as staff_count, id FROM admin_auths WHERE auth='class' GROUP BY id ) s ON s.id = c.class_id "
            ." $filters GROUP BY class_id ORDER BY school_name, class_grade, class_sub ";
        $query = $pdo->prepare( $sql );
        $query->execute( $params );

        $platoons = [];
        // fetch all results and parse them as models
        while( $platoon = $query->fetch() ){
            $platoon['name'] = ( new Platoon([
                'class_grade' => $platoon['class_grade'], 'class_sub' => $platoon['class_sub']
            ]) )->name();
            $platoon['class_id']  = intval( $platoon['class_id' ] );
            $platoon['school_id'] = intval( $platoon['school_id'] );
            $platoon['soldier_count'] = intval( $platoon['soldier_count'] );
            $platoon['staff_count'] = intval( $platoon['staff_count'] );
            $platoons[] = $platoon;
        }
        json_response( $platoons );
    }

    public function show( $id ) {
        global $current_user;
        try {
            $platoon = Platoon::find( $id );
            if ( !$platoon->validateAccess( $current_user->login ) )
                return json_error( 'Your current login does not have access to this platoon.', 'CORE-PLATOONS-54', 401 );
        } catch ( ActiveRecord\RecordNotFound $e ) {
            return json_error( 'Platoon does not exist', 'CORE-PLATOONS-56', 404 );
        }
        json_response( $platoon );
    }

    public function create() {
        global $current_user; global $pdo;

        if ( !in_array( $current_user->login['code'], ['HQ', 'CKIDS-ADMIN', 'BC'] ) )
            return json_error( 'Access Deined' );
        if ( $current_user->login['code'] == 'BC' )
            $_POST['school_id'] = $current_user->login['id'];

        $platoon = Platoon::build( $_POST );
        if ( !$platoon->is_valid() || !$platoon->save() )
            json_error( 'Could not create Platoon. (CODE: CORE-PLATOONS-65)' );

        json_response( $platoon );
    }

    public function update( $id ) {
        global $current_user;

        $platoon = Platoon::find( $id );
        if ( !$platoon->validateAccess( $current_user->login ) )
            json_error( 'Your current login does not have access to this soldier.', 'CORE-USERS-75', 401 );
        
        $columns = array_keys( Platoon::table()->columns );
        foreach( $_POST as $key => $value ) {
            if ( in_array( $key, $columns ) ) $platoon->{ $key } = $_POST[ $key ];
        }
        if ( !$platoon->is_valid() || !$platoon->save() )
            json_error('Could not update soldier. Please check to make sure that the data is valid', 'CORE-USERS-90');
        
        json_response( $platoon );
    }
}

rest_router( new PlatoonRouter );
