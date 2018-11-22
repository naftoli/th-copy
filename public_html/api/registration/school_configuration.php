<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class SchoolRegistrationRouter implements RestRouter {
    // limit controller to HQ
    function authenticate(){
        global $current_user;
        return $current_user->isHQ();
    }

    // return all schools with their registration info
    function index(){
        $year = isset( $_GET['year'] ) && $_GET['year'] ? $_GET['year'] : '5779';
        $schools = School::find( 'all', [
            'include' => 'school_registrations', 'order' => 'school_name',
            'conditions' => "test_school = 0"
        ] );
        json_response( array_map( function( $school ) use ( $year ) {
            $array = $school->to_array([
                'only' => [ 'school_id', 'school_name', 'school_era' ],
                // 'include' => [ 'school_registrations' ],
            ]);
            $array['reg_info'] = $school->registrationSettings( $year );
            return $array;
        }, $schools ) );
    }

    function show( $id ){
        $reg_info = $this->getInstance( $id );
        json_response( $reg_info );
    }

    function create() {
        $year = isset( $_POST['year'] ) ? $_POST['year'] : GlobalSettings::getRegistrationYear();
        $reg_info = new SchoolRegistration([
            'school_id' => $_POST['school_id'], 'year' => $year,
            'type'  => $_POST['type'],  'fee' => $_POST['fee'],
            'balance' => $_POST['balance'], 'early_bird' => $_POST['early_bird']
        ]);           

        if ( $reg_info->type == 2 && isset( $_POST['reg_deadline'] ) ) {
            $reg_info->reg_deadline = $_POST['reg_deadline'];
        }

        if ( isset( $_POST['child_fee'] ) ) {
            $reg_info->child_fee = $_POST['child_fee'];
        }

        if ( !$reg_info->is_valid() )
            json_error( "Invalid Registration Info", $reg_info->errors->full_messages() );
        
        try {
            $reg_info->save();
            json_response( $reg_info );
        } catch ( Exception $e ) {
            json_error("Could not save school registration information for the selected year.");
        }
    }

    function update( $id ) {
        $reg_info = $this->getInstance( $id );

        foreach( SchoolRegistration::table()->columns as $column ){
            if ( !isset( $_POST[ $column->name ] ) ) continue;
            $reg_info->{ $column->name } = $_POST[ $column->name ];
        }

        if ( !$reg_info->is_valid() )
            json_error( "Invalid Registration Info", $reg_info->errors->full_messages() );
        
        try {
            $reg_info->save();
            json_response( $reg_info );
        } catch ( Exception $e ) {
            json_error("Could not update school registration information for the current year.");
        }
    }

    public function destroy( $id ){
        try {
            $reg_info = $this->getInstance( $id );
        } catch ( ActiveRecord\RecordNotFound $e ){
            http_response_code( 404 ); die();
        }
        
        json_response( null, $reg_info->delete() );
    }

    // set the school_era to last year
    public function disableSchool() {
        $school = School::find( $_POST[ 'school_id' ] );
        $school->school_era = intval( GlobalSettings::getCurrentYear() ) - 1;
        json_response( false, $school->save() );
    }

    private function getInstance( $id ){
        try {
            return SchoolRegistration::find( $id );
        } catch ( ActiveRecord\RecordNotFound $e ){
            json_error( "School Registration Info Not Found", null, 404 );
        }
    }
}

rest_router( new SchoolRegistrationRouter );
