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

        $prizes = StorePrize::find('all', [
            'conditions' => $filter,
            'order' => 'is_active DESC, prize_count ASC, prize_name ASC', 
            'include' => [ 'school' ]
        ]);

        json_response( $prizes, true, true );
    }

    public function show( $id ){
        $prize = StorePrize::find( $id );
        json_response( $prize->jsonSerialize([
            'methods' => [ 'platoons' ]
        ]));
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

            // TODO update prize_classes table

            if ( !$prize->save() )
                return json_error( $prize->errors->full_messages() );
            // return the prize as the response
            return json_response( $prize );
        // send all errors as text
        } catch ( Exception $e ) {
            return json_error( $e->getMessage() );
        }
    }
}

rest_router( new PrizesRouter );
