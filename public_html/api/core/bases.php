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
            $filters[] = 's.test_school = 0';
        } else if ( $login['code'] === 'CKIDS-ADMIN' ) {
            $filters[] = 's.ckids = 1';
        } else if ( isset( $_GET['all'] ) ) { // get all bases on the account
            $school_ids = $current_user->getAuthIds( 'school' );
            $filters[] = 's.school_id IN ('. implode(', ', $school_ids ) .')';
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 's.school_id = ?';   $params[] = $login['id'];
        } else if ( $login['code'] === 'TEACHER' ) {
            $filters[] = 'c.class_id = ?';   $params[] = $login['id'];
        } else { json_error( 'Access Deinied'); }
        // combine the filters
        $filters = 'WHERE ' . implode( ' AND ', $filters );
        // generate the SQL
        $query = $pdo->prepare( 
            "SELECT s.school_id, s.school_name FROM classes c JOIN schools s USING (school_id) $filters "
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
