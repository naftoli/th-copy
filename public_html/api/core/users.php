<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UsersRouter {

    public function index() {
        global $current_user; global $pdo;
        // limit based on admin type
        $filters_and_params = $this->getFilters( $current_user->login );
        // combine the filters
        $filters = 'WHERE ' . implode( ' AND ', $filters_and_params['filters'] );
        // generate the SQL
        $sql = "SELECT u.*, s.*, c.class_grade, c.class_sub FROM users u "
            ."JOIN schools s USING ( school_id ) "
            ."LEFT JOIN classes c USING ( class_id ) $filters "
            ."ORDER BY school_name, class_grade, class_sub, last, first";
        $query = $pdo->prepare( $sql );
        $query->execute( $filters_and_params['params'] );

        $users = [];
        // fetch all results and parse them as models
        while( $row = $query->fetch() ){
            $profilePicture = ( new User(['mobile_pic' => $row['mobile_pic'], 'user_photo_id' => $row['user_photo_id']]) )->profilePicture();
            $platoon = ( new Platoon(['class_grade' => $row['class_grade'], 'class_sub' => $row['class_sub']]) )->name();
            // format dates
            $dob = $row['dob'] ? ( new DateTime( $row['dob'] ) )->format(DateTime::ATOM) : $row['dob'];
            $user_registered = $row['user_registered'] ? ( new DateTime( $row['user_registered'] ) )->format(DateTime::ATOM) : $row['user_registered'];
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
            $user = User::find( $id );
            if ( !$user->validateAccess( $current_user->login ) )
                json_error( 'Your current login does not have access to this soldier.', 'CORE-USERS-65', 401 );
            json_response( $user );
        } catch ( Exception $e ) {
            json_error( 'Soldier does not exist', 'CORE-USERS-68', 404 );
        }
    }

    public function create() {
        global $current_user;
        $user = User::build( $_POST );
        if ( $current_user->login['code'] === 'TEACHER' ) {
            $user->school_id = Platoon::find( $current_user->login['id'] )->school_id;
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

        $user = User::find( $id );
        if ( !$user->validateAccess( $current_user->login ) )
            json_error( 'Your current login does not have access to this soldier.', 'CORE-USERS-75', 401 );
        
        // update the profile picture
        if ( isset( $_FILES['profile'] ) ) {
            $result = $user->setProfilePicture( $_FILES['profile'] );
            if ( is_string( $result ) ) json_error( $result );
            json_response([
                'mobile_pic' => $user->mobile_pic,
                'profilePicture' => $user->profilePicture()
            ]);
        // update other properties
        } else {
            foreach( User::table()->columns as $column ){
                if ( !isset( $_POST[ $column->name ] ) ) continue;
                $user->{ $column->name } = $_POST[ $column->name ];
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
            json_response( $user );
        }
    }

    public function destroy( $id ) {
        global $current_user;
        try {
            $user = User::find( $id );
            if ( !$user->validateAccess( $current_user->login ) )
                json_error( 'Your current login does not have access to this soldier.', 'CORE-USERS-126', 401 );
            if ( !in_array( $current_user->login['code'], ['BC', 'HQ', 'CKIDS-ADMIN'] ) ) {
                json_error( 'Your current login does not have the ability to remove users' );
            }
            if ( $user->canDestroy() ) {
                json_response( 'deleted', $user->delete() );
            } else {
                $user->school_id = null;
                $user->class_id = null;
                json_response( 'removed-from-school', $user->save() );
            }
        } catch ( Exception $e ) {
            json_error( 'Soldier does not exist', 'CORE-USERS-137', 401 );
        }
    }

    public function uploadProfile() {
        global $current_user;
        if ( isset( $_FILES['profile'] ) ) {
            $result = User::uploadProfilePicture( $current_user->admin_id, $_FILES['profile'] );
            if ( is_string( $result ) ) json_error( $result );
            json_response( $result );
        }
        json_error('Server did not get the profile picture :-(.');
    }

    private function getFilters( $login ){
        // filters and params for the filters
        $filters = [];   $params = [];
        if ( $login['code'] === 'HQ' ) {
            $filters[] = 's.test_school = 0';
        } else if ( $login['code'] === 'CKIDS-ADMIN' ) {
            $filters[] = 's.ckids = 1';
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 'u.school_id = ?'; $params[] = $login['id'];
        } else if ( $login['code'] === 'TEACHER' ) {
            $filters[] = 'u.class_id = ?'; $params[] = $login['id'];
        } else { json_error( 'Access Deinied: CORE-USERS-26' ); }
        
        return [ 'filters' => $filters, 'params' => $params ];
    }
}

rest_router( new UsersRouter );
