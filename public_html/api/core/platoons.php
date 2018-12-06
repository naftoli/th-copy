<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class PlatoonRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;
        // filters and params for the filters
        $filters = [];   $params = [];
        // limit based on admin type
        $login = $current_user->login;

        if ( $login->code === 'BC' && isset( $_GET['all'] ) ) {
            $school_ids = $current_user->getAuthIds( 'school' );
            $filters[] = 'c.school_id IN ('. implode(', ', $school_ids ) .')';
        } else if ( isset( $_GET['school_id'] ) ) {
            $filters[] = 'c.school_id = ?';   $params[] = $_GET['school_id'];
        } else {
            $filters[] = $login->getFilter( 's.', 'c.' );
        }
        // combine the filters
        $filters = 'WHERE ' . implode( ' AND ', $filters );
        // generate the SQL
        $sql = "SELECT class_id, class_grade, class_sub, s.school_id, school_name, miles_balance, "
            ."class_teacher as teacher, c.cell, c.email, COUNT(user_id) as soldier_count, staff_count "
            ."FROM classes c JOIN schools s USING (school_id) LEFT JOIN users u USING ( class_id ) "
            ."LEFT JOIN ( SELECT count(*) as staff_count, id FROM admin_auths WHERE auth='class' GROUP BY id ) s ON s.id = c.class_id "
            ." $filters GROUP BY class_id ORDER BY school_name, class_grade, class_sub;";
        $query = $MASHPIA_DB->prepare( $sql );
        $query->execute( $params );

        $platoons = [];
        // fetch all results and parse them as models
        while( $platoon = $query->fetch() ){
            $platoon['name'] = $this->getName($platoon);
            $platoon['class_id']  = intval( $platoon['class_id' ] );
            $platoon['school_id'] = intval( $platoon['school_id'] );
            $platoon['soldier_count'] = intval( $platoon['soldier_count'] );
            $platoon['staff_count'] = intval( $platoon['staff_count'] );
            $platoons[] = $platoon;
        }
        json_response( $platoons, true, true );
    }

    public function small(){
        global $MASHPIA_DB;
        // get the school_id
        $school_id = isset( $_POST['school_id'] ) ? $_POST['school_id'] : false;
        if ( !$school_id ) return json_response([]);
        // get the platoons
        $query = $MASHPIA_DB->prepare( 'SELECT school_id, class_id, class_grade, class_sub FROM classes WHERE school_id=:school_id;' );
        $query->execute(['school_id' => $school_id]);
        $platoons = $query->fetchAll();
        // serialize the platoons
        $platoons = array_map(function ( $platoon ){
            $platoon['name'] = $this->getName($platoon); return $platoon;
        }, $platoons );
        return json_response( $platoons );
    }

    public function show( $id ) {
        global $current_user;
        try {
            $platoon = \Platoon::find([ $id ]);
            if ( !$platoon->validateAccess( $current_user->login ) )
                return json_error( 'Your current login does not have access to this platoon.', 'CORE-PLATOONS-54', 401 );
        } catch ( ActiveRecord\RecordNotFound $e ) {
            return json_error( 'Platoon does not exist', 'CORE-PLATOONS-56', 404 );
        }
        json_response( $platoon );
    }

    public function create() {
        global $current_user; global $MASHPIA_DB;

        if ( !in_array( $current_user->login->code, ['HQ', 'INST', 'BC'] ) )
            return json_error( 'Access Deined' );
        if ( $current_user->login->code == 'BC' )
            $_POST['school_id'] = $current_user->login->id;

        $platoon = Platoon::build( $_POST );
        if ( !$platoon->is_valid() || !$platoon->save() )
            json_error( 'Could not create Platoon. (CODE: CORE-PLATOONS-65)' );

        json_response( $platoon );
    }

    public function update( $id ) {
        global $current_user;

        $platoon = \Platoon::find([ $id ]);
        if ( !$platoon->validateAccess( $current_user->login ) )
            json_error( 'Your current login does not have access to this platoon.', 'CORE-PLATOONS-99', 401 );
        
        $columns = array_keys( Platoon::table()->columns );
        foreach( $_POST as $key => $value ) {
            if ( in_array( $key, $columns ) ) $platoon->{ $key } = $_POST[ $key ];
        }
        if ( !$platoon->is_valid() || !$platoon->save() )
            json_error('Could not update platoon. Please check to make sure that the data is valid', 'CORE-PLATOONS-106');
        
        json_response( $platoon );
    }

    public function destroy( $id ) {
        global $current_user;

        $platoon = \Platoon::find([ $id ]);
        if ( !$platoon->delete() )
            return json_error( 'Cannot Delete Platoon', 'CORE-PLATOONS-116' );
        
        json_response( $platoon );
    }

    private function getName( $platoon ){
        return ( new Platoon([
            'class_grade' => $platoon['class_grade'], 'class_sub' => $platoon['class_sub']
        ]) )->name();
    }
}

rest_router( new PlatoonRouter );
