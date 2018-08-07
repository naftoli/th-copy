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
            $filters[] = 'schools.test_school = 0';
        } else if ( $login['code'] === 'CKIDS-ADMIN' && !isset( $_GET['school_id'] ) ) {
            $filters[] = 'schools.ckids = 1';
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 'classes.school_id = ?';   $params[] = $login['id'];
        } else if ( $login['code'] === 'TEACHER' ) {
            $filters[] = 'classes.class_id = ?';   $params[] = $login['id'];
        } else if ( isset( $_GET['school_id'] ) ) {
            $filters[] = 'classes.school_id = ?';   $params[] = $_GET['school_id'];
        } else { json_error( 'Invalid request. Please select a Base.'); }
        // combine the filters
        $filters = 'WHERE ' . implode( ' AND ', $filters );
        // generate the SQL
        $sql = "SELECT class_id, class_grade, class_sub, school_id, school_name FROM classes JOIN schools USING (school_id) "
            ." $filters ORDER BY school_name, class_grade, class_sub ";
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
            $platoons[] = $platoon;
        }
        json_response( $platoons );
    }
}

rest_router( new PlatoonRouter );
