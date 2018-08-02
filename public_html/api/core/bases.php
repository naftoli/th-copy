<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class BaseRouter {

    public function index() {
        global $current_user; global $pdo;
        // filters and params for the filters
        $filters = [];   $params = [];
        // limit based on admin type
        $login = $current_user->login;
        if ( $login['code'] === 'HQ' ) {
            $filters[] = 'schools.test_school = 0';
        } else if ( $login['code'] === 'CKIDS-ADMIN' ) {
            $filters[] = 'schools.ckids = 1';
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 'schools.school_id = ?';   $params[] = $login['id'];
        } else if ( $login['code'] === 'TEACHER' ) {
            $filters[] = 'class_id = ?';   $params[] = $login['id'];
        } else { json_error( 'Access Deinied'); }
        // combine the filters
        $filters = 'WHERE ' . implode( ' AND ', $filters );
        // generate the SQL
        $query = $pdo->prepare( 
            "SELECT schools.* FROM classes JOIN schools USING (school_id) $filters "
            ." GROUP BY school_id ORDER BY school_name;"
        );
        $query->execute( $params );

        $bases = [];
        // fetch all results and parse them as models
        while( $base = $query->fetch() ){
            $base['school_id'] = intval( $base['school_id'] );
            $bases[] = $base;
        }
        json_response( $bases );
    }
}

rest_router( new BaseRouter );
