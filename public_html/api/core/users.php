<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UsersRouter {

    public function authenticate() {
        global $current_user;
        return in_array( $current_user->authCode(), [ 'HQ', 'BC' ] );
    }

    public function index() {
        global $current_user; global $pdo;
        // filters and params for the filters
        $filters = [];   $params = [];
        // limit based on admin type
        if ( $current_user->isHQ() ) {
            $filters[] = 'schools.test_school = 0';
        } else if ( $current_user->authCode() === 'CKIDS-ADMIN' ) {
            $filters[] = 'schools.ckids = 1';
        } else if ( $current_user->authCode() === 'BC' ) {
            $filters[] = 'users.school_id = ?';
            $params[] = $current_user->getAuthIds('school')[0];
        }
        // combine the filters
        $filters = count( $filters ) > 0 ? 'WHERE ' . implode( ' AND ', $filters ) : '';
        // generate the SQL
        $sql = "SELECT * FROM users JOIN schools USING ( school_id ) JOIN classes USING ( class_id ) $filters ORDER BY school_name, class_grade, class_sub";
        $query = $pdo->prepare( $sql );
        $query->execute( $params );

        $users = []; $user = null;
        // fetch all results and parse them as models
        while( $row = $query->fetch() ){
            $profilePicture = ( new User(['mobile_pic' => $row['mobile_pic'], 'user_photo_id' => $row['user_photo_id']]) )->profilePicture();
            $platton = ( new Platton(['class_grade' => $row['class_grade'], 'class_sub' => $row['class_sub']]) )->name();
            // use the BuildModel trait to create instances from 
            $users[] = [
                'user_id' => $row['user_id'], 'user_serial' => $row['user_serial'], 'first' => $row['first'], 
                'last' => $row['last'], 'dob' => $row['dob'], 'gender' => $row['gender'], 'user_registered' => $row['user_registered'],
                'chayolei' => $row['chayolei'], 'yan' => $row['yan'], 'chidon' => $row['chidon'], 'mobile_pic' => $row['mobile_pic'],
                'school' => [ 'school_id' => $row['school_id'], 'school_name' => $row['school_name'], 
                    'shipping_city' => $row['shipping_city'], 'school_era' => $row['school_era'] ],
                'profilePicture' => $profilePicture, 'platton' => [ 'name' => $platton ]
            ];
            $user = null;
        }

        json_response( $users );
    }

    public function update( $id ) {
        $user = User::find( $id );
        // update the profile picture
        if ( isset( $_FILES['profile'] ) ) {
            $result = $user->setProfilePicture( $_FILES['profile'] );
            if ( is_string( $result ) ) json_error( $result );
            json_response([
                'mobile_pic' => $user->mobile_pic,
                'profilePicture' => $user->profilePicture()
            ]);
        }
        // update everything else
    }
}

rest_router( new UsersRouter );
