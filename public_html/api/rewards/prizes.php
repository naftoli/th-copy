<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class PrizesRouter {

    public function index() {
        global $current_user; global $POINTS_DB;
        $login = $current_user->login;
        $IMG_PATH = StorePrize::IMG_PATH;

        $filter = ' institution_id IN ( SELECT school_id FROM mashpiadb.schools WHERE test_school = 0 ) ';
        if ( $login['code'] == 'INST' ) {
            $filter = ' institution_id IN ( SELECT school_id FROM mashpiadb.schools WHERE inst_id = '. $login['id'].' ) ';
        } else if ( $login['code'] == 'BC' ) {
            $filter = ' institution_id = '. $login['id'].' ';
        // Does not work
        } else if ( $login['code'] == 'TEACHER' ) {
            $filter = ' institution_id = '. $login['school_id'].' AND teacher_id = ' . $login['id'];
        }

        $prizes = StorePrize::find('all', [ 'conditions' => $filter ]);

        json_response( $prizes, true, true );
    }

    public function show( $id ){
        json_response( StorePrize::find( $id ) );
    }

    public function update( $id ) {
        $prize = StorePrize::find( $id );
        $prize->bulkUpdate( $_POST );

        if ( !$prize->save() )
            return json_error( $prize->errors->full_messages() );
        
        return json_response( $prize );
    }
}

rest_router( new PrizesRouter );
