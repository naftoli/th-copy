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

        $response = [];
        foreach( $prizes as $prize ) { // cache the prizes for large stores to prevent return runs to the DBS
            $prize_platoons = isset( $platoons[ $prize->prize_id ] ) ? $platoons[ $prize->prize_id ] : [];
            $prize->cachePlatoons( $prize_platoons );
            
            if ( $login['code'] == 'TEACHER' // teachers
                && count( $prize_platoons ) > 0 // if the prize has 1 or more platoons
                && !in_array( $login['id'], $prize_platoons ) // and they are not in the list
            ) continue; // they do not see the prize.

            $response[] = $prize; 
        }

        json_response( $response, true, true );
    }

    public function show( $id ){
        $prize = StorePrize::find( $id );
        json_response( $prize );
    }

    public function create() {
        global $current_user;
        $login = $current_user->login;

        try {
            $prize = StorePrize::build( $_POST );

            if ( $login['code'] == 'BC' ) {
                $prize->institution_id = $login['id'];
            } else if ( $login['code'] == 'TEACHER' ) {
                $prize->teacher_edit = 1;
                $prize->institution_id = $login['school_id'];
                $prize->teacher_id = $current_user->admin_id;
            }

            $prize->prize_type = '';
            $prize->created_by = $current_user->admin_id;

            if ( !$prize->save() )
                return json_error( $prize->errors->full_messages() );

            // update prize_classes table
            if ( isset( $_POST['platoons'] )
                && !$prize->setPlatoons( $_POST['platoons'] ) 
            ) return json_error( 'Error limiting to Platoons');

            if ( $login['code'] == 'TEACHER'
                && !$prize->setPlatoons([ $login['id'] ])
            ) return json_error( 'Error connecting to Platoon, Please contact Base Commander');

            // return the prize as the response
            return json_response( $prize );
        // send all errors as text
        } catch ( Exception $e ) {
            return json_error( $e->getMessage() );
        }
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
                !$prize->setPlatoons( $_POST['platoons'] ) 
            ) return json_error( 'Could connect Platoons');

            // return the prize as the response
            return json_response( $prize );
        // send all errors as text
        } catch ( Exception $e ) {
            return json_error( $e->getMessage() );
        }
    }

    public function uploadImage() {
        global $current_user;
        if ( isset( $_FILES['image'] ) ) {
            try {
                $result = StorePrize::uploadImage( $current_user->admin_id, $_FILES['image'] );
                json_response([
                    'image' => StorePrize::IMG_PATH . $result,
                    'image_id' => $result
                ]);
            } catch ( Exception $e ) {
                return json_error( $e->getMessage() );
            }
        }
        json_error('Server did not get the prize image.');
    }
}

rest_router( new PrizesRouter );
