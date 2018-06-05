<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class SchoolRegistrationRouter {
    // limit controller to HQ
    function authenticate(){
        global $current_user;
        return $current_user->isHQ();
    }

    // return all schools with their registration info
    function index(){
        $schools = School::find( 'all', [
            'include' => 'school_reg_infos', 'order' => 'school_name',
            'conditions' => "test_school = '0'"
        ] );
        json_response( array_map( function( $school ) {
            return $school->to_array([
                'only' => [ 'school_id', 'school_name' ],
                'include' => [ 'school_reg_infos' ]
            ]);
        }, $schools ) );
    }

    function show( $id ){
        $schoolRegInfo = $this->getInstance( $id );
        json_response( $schoolRegInfo );
    }

    function create() {
        $schoolRegInfo = new SchoolRegInfo([
            'school_id' => $_POST['school_id'], 'year' => GlobalSettings::getRegistrationYear(),
            'type'  => $_POST['type'],  'fee' => $_POST['fee'],
            'balance' => $_POST['balance'], 'early_bird' => $_POST['early_bird']
        ]);

        if ( $schoolRegInfo->type == 2 && isset( $_POST['reg_deadline'] ) ) {
            $schoolRegInfo->reg_deadline = $_POST['reg_deadline'];
        }

        if ( !$schoolRegInfo->is_valid() )
            json_error( "Invalid Registration Info", $schoolRegInfo->errors->full_messages() );
        
        try {
            $schoolRegInfo->save();
            json_response( $schoolRegInfo );
        } catch ( Exception $e ) {
            json_error("Could not save school registration information for the current year.");
        }
    }

    function update( $id ) {
        $schoolRegInfo = $this->getInstance( $id );

        foreach( SchoolRegInfo::table()->columns as $column ){
            if ( !isset( $_POST[ $column->name ] ) ) continue;
            $schoolRegInfo->{ $column->name } = $_POST[ $column->name ];
        }

        if ( !$schoolRegInfo->is_valid() )
            json_error( "Invalid Registration Info", $schoolRegInfo->errors->full_messages() );
        
        try {
            $schoolRegInfo->save();
            json_response( $schoolRegInfo );
        } catch ( Exception $e ) {
            json_error("Could not update school registration information for the current year.");
        }
    }

    public function destroy( $id ){
        try {
            $schoolRegInfo = $this->getInstance( $id );
        } catch ( ActiveRecord\RecordNotFound $e ){
            http_response_code( 404 ); die();
        }
        
        json_response( null, $schoolRegInfo->delete() );
    }

    private function getInstance( $id ){
        try {
            return $schoolRegInfo = SchoolRegInfo::find( $id );
        } catch ( ActiveRecord\RecordNotFound $e ){
            json_error( "School Registration Info Not Found", null, 404 );
        }
    }
}

rest_router( new SchoolRegistrationRouter );