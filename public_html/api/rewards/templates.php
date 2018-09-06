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

    public function create() {
        global $current_user;
        if ( !$current_user->isHQ() )
            return json_error('This Login Cannot Edit Templates');
        
        $login = $current_user->login;

        try {
            $prize = StorePrize::build( $_POST );
            // set template_prize specific things
            $prize->institution_id = 1;
            $prize->parent_prize_id = 0;
            $prize->teacher_edit = 0;
            $prize->prize_type = 'Template';
            $prize->created_by = $current_user->admin_id;

            if ( !$prize->save() )
                return json_error( $prize->errors->full_messages() );

            // return the prize as the response
            return json_response( $prize );
        // send all errors as text
        } catch ( Exception $e ) {
            return json_error( $e->getMessage() );
        }
    }

    public function update( $id ) {
        // validate user
        global $current_user;
        if ( !$current_user->isHQ() )
            return json_error('This Login Cannot Edit Templates');

        try {
            $template = StorePrize::find( $id );

            if ( $template->prize_type !== 'Template' )
                return json_error('This Prize is not a Template');

            // update profile picture
            if( isset( $_FILES['image'] ) ) {
                $template->setImage( $_FILES['image'] );
            }
            // blulk update valid params
            $template->bulkUpdate( $_POST );

            if ( !$template->save() )
                return json_error( $template->errors->full_messages() );

            // return the prize as the response
            return json_response( $template );
        // send all errors as text
        } catch ( Exception $e ) {
            return json_error( $e->getMessage() );
        }
    }
}

rest_router( new TemplatesRouter );
