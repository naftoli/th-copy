<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class RegistrationRouter {

    public function index() {
        global $current_user; global $pdo;
        
        if ( in_array( $current_user->login['code'], ['HQ', 'INST'] ) ) {
            // return registration info for institution
        }
    }

    private function getFilters( $login ){
        // filters and params for the filters
        $filters = [];   $params = [];
        if ( $login['code'] === 'HQ' ) {
            $filters[] = 's.test_school = 0';
        } else if ( $login['code'] === 'INST' ) {
            $filters[] = 's.inst_id = ?'; $params[] = $login['id'];
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 'u.school_id = ?'; $params[] = $login['id'];
        } else if ( $login['code'] === 'TEACHER' ) {
            $filters[] = 'u.class_id = ?'; $params[] = $login['id'];
        } else { json_error( 'Access Deinied: HOME-REG-26' ); }
        
        return [ 'filters' => $filters, 'params' => $params ];
    }
}

rest_router( new RegistrationRouter );
