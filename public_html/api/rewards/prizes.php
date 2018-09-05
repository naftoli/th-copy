<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class PrizesRouter {

    public function index() {
        global $current_user; global $POINTS_DB;
        $login = $current_user->login;
        $IMG_PATH = StorePrize::IMG_PATH;

        $filter = 'institution_id IN ( SELECT school_id FROM mashpiadb.schools WHERE test_school = 0 ) ';
        if ( $login['code'] == 'INST' ) {
            $filter = 'institution_id IN ( SELECT school_id FROM mashpiadb.schools WHERE inst_id = '. $login['id'].' ) ';
        } else if ( $login['code'] == 'BC' ) {
            $filter = 'institution_id = '. $login['id'].' ';
        } else if ( $login['code'] == 'TEACHER' ) {
            $filter = 'institution_id = '. $login['school_id'];
        }

        $prizes = StorePrize::find('all', [
            'conditions' => $filter,
            'order' => 'is_active DESC, prize_count ASC, prize_name ASC', 
            'include' => [ 'school' ]
        ]);

        // optimize platoons queries for large stores
        $platoons = [];
        $query = $POINTS_DB->query( 'SELECT prize_id, class_id FROM prize_classes' );
        while ( $platoon = $query->fetch() ) {
            $platoons[ $platoon['prize_id'] ][] = intval( $platoon['class_id'] );
        }

        foreach( $prizes as $prize )
            $prize->cachePlatoons( isset( $platoons[ $prize->prize_id ] ) ? $platoons[ $prize->prize_id ] : [] );

        json_response( $prizes, true, true );
    }

    public function show( $id ){
        $prize = StorePrize::find( $id );
        json_response( $prize );
    }

    public function update( $id ) {
        try {
            $prize = StorePrize::find( $id );
            // update profile picture
            if( isset( $_FILES['image'] ) ) {
                $prize->setImage( $_FILES['image'] );
            }
            // blulk update valid params
            $prize->bulkUpdate( $_POST );

            if ( !$prize->save() )
                return json_error( $prize->errors->full_messages() );

            // update prize_classes table
            if (
                isset( $_POST['platoons'] ) && 
                is_array( $_POST['platoons'] ) &&
                !$prize->setPlatoons( $_POST['platoons'] ) 
            ) return json_error( 'Could update Platoons');

            // return the prize as the response
            return json_response( $prize );
        // send all errors as text
        } catch ( Exception $e ) {
            return json_error( $e->getMessage() );
        }
    }
}

rest_router( new PrizesRouter );
