<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UsersRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;

        $filters = $current_user->login->getFilter( 's.', 'u.' );
        // generate the SQL
        $sql = "SELECT u.*, s.school_name, s.shipping_city, s.school_era, c.class_grade, c.class_sub FROM users u "
            ."JOIN schools s USING ( school_id ) "
            ."LEFT JOIN classes c USING ( class_id ) WHERE $filters "
            ."ORDER BY school_name, class_grade, class_sub, last, first";
        $query = $MASHPIA_DB->prepare( $sql );
        $query->execute();

        $users = [];
        // fetch all results and parse them as models
        while( $row = $query->fetch() ){
            $profilePicture = ( new Soldier(['mobile_pic' => $row['mobile_pic'], 'user_photo_id' => $row['user_photo_id']]) )->profilePicture();
            $platoon = ( new Platoon(['class_grade' => $row['class_grade'], 'class_sub' => $row['class_sub']]) )->name();
            // format dates
            $dob = $row['dob']; $user_registered = $row['user_registered'];
            // $dob = $row['dob'] ? ( new DateTime( $row['dob'] ) )->format('n/j/Y') : $row['dob'];
            // $user_registered = $row['user_registered'] ? ( new DateTime( $row['user_registered'] ) )->format('n/j/Y g:i A') : $row['user_registered'];
            // format and return just the data we want...
            $users[] = [
                'user_id' => intval($row['user_id']), 'user_serial' => intval($row['user_serial']), 
                'first' => $row['first'], 'last' => $row['last'], 'dob' => $dob, 'gender' => $row['gender'], 
                'user_registered' => $user_registered,  'mobile_pic' => $row['mobile_pic'], 'profilePicture' => $profilePicture,
                'chayolei' => intval($row['chayolei']), 'yan' => intval($row['yan']), 'chidon' => intval($row['chidon']), 
                'school_id' => intval( $row['school_id'] ), 'class_id' => $row['class_id'] ? intval( $row['class_id'] ) : false, 
                'school' => [ 'school_id' => $row['school_id'], 'school_name' => $row['school_name'], 
                    'shipping_city' => $row['shipping_city'], 'school_era' => $row['school_era'] ],
                'barcode' => '3'.$row['user_code'],
                'platoon' => ( $platoon ? [ 'name' => $platoon ] : null )
            ];
        }
        json_response( $users );
    }

    public function show( $id ) {
        global $current_user;
        try {
            $user = Soldier::find( $id );
            if ( !$user->validateAccess( $current_user->login ) )
                json_error( 'Your current login does not have access to this soldier.', 'CORE-USERS-65', 401 );
            // ! do not add true true here as PHP cannot handle the barcode as a number
            json_response( $user->fullDetailSerialize() );
        } catch ( Exception $e ) {
            json_error( 'Soldier does not exist', 'CORE-USERS-69', 404 );
        }
    }

    public function findSerial() {
        global $current_user;
        $serial = $_REQUEST['serial'];
        try {
            $soldier = Soldier::find_by_user_serial( $serial );
            if ( !$soldier->validateAccess( $current_user->login ) )
                json_error( 'Your current login does not have access to this soldier.', 'CORE-USERS-65', 401 );
            json_response( $soldier );
        } catch ( Exception $e ) {
            json_error( 'Soldier does not exist', 'CORE-USERS-82', 404 );
        }
    }

    public function create() {
        global $current_user;
        $user = Soldier::build( $_POST );
        if ( $current_user->login->code === 'TEACHER' ) {
            $user->school_id = $current_user->login->model->school_id;
        } else {
            $platoon_school_id = Platoon::find( $user->class_id )->school_id;
            if ( $platoon_school_id !== $user->school_id )
                json_error( 'Please Select a valid Platoon' );
        }
        if ( !$user->is_valid() || !$user->save() )
            json_error( 'Could not create Soldier. (CODE: CORE-USERS-79)' );
        json_response( $user );
    }

    public function update( $id ) {
        global $current_user;

        $user = Soldier::find( $id );
        if ( !$user->validateAccess( $current_user->login ) )
            json_error( 'Your current login does not have access to this soldier.', 'CORE-USERS-77', 401 );
        
        // update the profile picture
        if ( isset( $_FILES['profile'] ) ) {
            $result = $user->setProfilePicture( $_FILES['profile'] );
            if ( is_string( $result ) )
                json_error( $result );
        // update other properties
        } else {
            $columns = array_keys( Soldier::table()->columns );
            foreach( $_POST as $key => $value ) {
                if ( in_array( $key, $columns ) ) $user->{ $key } = $_POST[ $key ];
            }
            if ( !$user->is_valid() || !$user->save() )
                json_error('Could not update soldier. Please check to make sure that the data is valid', 'CORE-USERS-90');
            // update the birthday missions if dob was changed
            if ( isset( $_POST['dob'] ) ){
                $user->setupBirthdayMissions();
            }
            if ( isset( $_POST['school_type_id'] ) ){
                $user->enrollInCampaigns();
            }
        }

        json_response( $user->fullDetailSerialize() );
    }

    public function destroy( $id ) {
        global $current_user; global $MASHPIA_DB;
        try {
            $user = Soldier::find( $id );
            if ( !$user->validateAccess( $current_user->login ) )
                json_error( 'Your current login does not have access to this soldier.', 'CORE-USERS-126', 401 );
            if ( !in_array( $current_user->login->code, ['BC', 'HQ', 'INST'] ) ) {
                json_error( 'Your current login does not have the ability to remove users' );
            }
            if ( $user->canDestroy() && $user->delete() ) {
                // remove from parent account
                $MASHPIA_DB->query('DELETE FROM admin_auths WHERE id='.$user->user_id.' AND auth = "user"');
                return json_response( 'Soldier has been deleted.' );
            } else if ( !$user->canDestroy() ) {
                $user->school_id = null;
                $user->class_id = null;
                if ( $user->save() ) 
                    return json_response( 'Soldier has been removed from Base.' );
            }
            return json_error( 'Could not delete soldier.' );
        } catch ( Exception $e ) {
            json_error( 'Soldier does not exist', 'CORE-USERS-137', 401 );
        }
    }

    public function uploadProfile() {
        global $current_user;
        if ( isset( $_FILES['profile'] ) ) {
            $result = Soldier::uploadProfilePicture( $current_user->admin_id, $_FILES['profile'] );
            if ( is_string( $result ) ) json_error( $result );
            json_response( $result );
        }
        json_error('Server did not get the profile picture :-(.');
    }

    public function updateMissions() {
        json_response( $_POST );
    }
}

rest_router( new UsersRouter );
