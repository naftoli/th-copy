<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class TemplatesRouter {

    public function index() {
        global $current_user; global $POINTS_DB;
        $login = $current_user->login;
        $IMG_PATH = StorePrize::IMG_PATH;

        $prizes = StorePrize::find('all', [
            'conditions' => "prize_name != '' AND prize_type = 'Template' AND parent_prize_id = 0",
            'order' => 'prize_name ASC'
        ]);

        $response = [];
        foreach( $prizes as $prize )
            $response[] = $prize->templateSerialize();

        json_response( $response, true, true );
    }

    // public function create() {
    //     global $current_user;
    //     $login = $current_user->login;

    //     try {
    //         $prize = StorePrize::build( $_POST );

    //         if ( $login['code'] == 'BC' ) {
    //             $prize->institution_id = $login['id'];
    //         } else if ( $login['code'] == 'TEACHER' ) {
    //             $prize->teacher_edit = 1;
    //             $prize->institution_id = $login['school_id'];
    //             $prize->teacher_id = $current_user->admin_id;
    //         }

    //         $prize->prize_type = '';
    //         $prize->created_by = $current_user->admin_id;

    //         if ( !$prize->save() )
    //             return json_error( $prize->errors->full_messages() );

    //         // update prize_classes table
    //         if ( isset( $_POST['platoons'] )
    //             && !$prize->setPlatoons( $_POST['platoons'] ) 
    //         ) return json_error( 'Could limit to Platoons');

    //         if ( $login['code'] == 'TEACHER'
    //             && !$prize->setPlatoons([ $login['id'] ])
    //         ) return json_error( 'Could connect to Platoon, Please contact Base Commander');

    //         // return the prize as the response
    //         return json_response( $prize );
    //     // send all errors as text
    //     } catch ( Exception $e ) {
    //         return json_error( $e->getMessage() );
    //     }
    // }

    // public function update( $id ) {
    //     try {
    //         $prize = StorePrize::find( $id );
    //         // update profile picture
    //         if( isset( $_FILES['image'] ) ) {
    //             $prize->setImage( $_FILES['image'] );
    //         }
    //         // blulk update valid params
    //         $prize->bulkUpdate( $_POST );

    //         if ( !$prize->save() )
    //             return json_error( $prize->errors->full_messages() );

    //         // update prize_classes table
    //         if (
    //             isset( $_POST['platoons'] ) && 
    //             !$prize->setPlatoons( $_POST['platoons'] ) 
    //         ) return json_error( 'Could update Platoons');

    //         // return the prize as the response
    //         return json_response( $prize );
    //     // send all errors as text
    //     } catch ( Exception $e ) {
    //         return json_error( $e->getMessage() );
    //     }
    // }
}

rest_router( new TemplatesRouter );
