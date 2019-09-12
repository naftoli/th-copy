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
                'school_id' => $row['school_id'], 'class_id' => $row['class_id'] ? $row['class_id'] : false,
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
            $user = \Soldier::find([ $id ]);
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

    public function findBarcode() {
        global $current_user;
        $barcode = substr($_POST['barcode'], 1);
        try {
            $soldier = Soldier::find_by_user_code( $barcode );
            if ( !$soldier->validateAccess( $current_user->login ) )
                json_error( 'Your current login does not have access to this soldier.', 'CORE-USERS-65', 401 );
            json_response( $soldier );
        } catch ( Exception $e ) {
            json_error( 'Soldier does not exist', 'CORE-USERS-82', 404 );
        }
    }

    public function create() {
        global $current_user;
        global $MASHPIA_DB;
        $user = Soldier::build( $_POST );

        $admin_user = Admin::find([ $current_user->admin_id ]);

        // make sure soldier with this first and last name and date of birth doesn't exist in this school
        $school_id = $_POST['school_id'];
        $first = $_POST['first'];
        $last = $_POST['last'];
        $dob = $_POST['dob'];
        $existing_user =  Soldier::find('all',array('conditions' => array('school_id = ? and first = ? and last = ? and dob = ?', $school_id, $first, $last, $dob)));
        if ( $existing_user ) {
            json_error('A child with this name and dob already exists.');
        }

        // if it is a teacher, set the school id to the platoons school id
        if ( $current_user->login->code === 'TEACHER' ) {
            $user->school_id = $current_user->login->model->school_id;
        // make sure the class is in the grade
        } else {
            $platoon_school_id = $user->platoon->school_id;
            if ( $platoon_school_id !== $user->school_id )
                json_error( 'Platoon is not in Base' );
        }
        // save and create the soldier
        if ( !$user->is_valid() || !$user->save() )
            json_error( 'Could not create Soldier. (CODE: CORE-USERS-98)' );
        // parents get auto connected to their kids
       //if ( $current_user->login->code === 'PARENT' ) {
            /*$auth = \AdminAuth::create([
                'admin_id' => $current_user->admin_id,
                'id'       => $user->user_id,     
                'auth'     => 'user',
                'role_id'  => 1
            ]);*/

            /*$userVal = 'user';
            $roldIdVal = 1;

            $data = [
                'admin_id' => $current_user->admin_id,
                'auth'     => $userVal,
                'id'       => $user->user_id,
                'role_id'  => $roldIdVal
            ];

            $sql = "INSERT INTO admin_auths (admin_id, auth, id, role_id) VALUES (:admin_id, :auth, :id, :role_id)";
            $stmt = $MASHPIA_DB->prepare( $sql );
            $stmt->execute($data);*/

        //}

        //var_dump($current_user);
        echo $admin_user;
        // send the full soldier to the client
        //json_response( $user );
    }

    public function update( $id ) {
        global $current_user;

        $user = \Soldier::find([ $id ]);
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
            // if tanya was turned off, remove any tanya marks from bp summary table
            if ( isset( $_POST['yan'] ) && $_POST['yan'] == 0 ) {
                $user->removeFromBpSummary();
            }
        }

        json_response( $user->fullDetailSerialize() );
    }

    public function destroy( $id ) {
        global $current_user; global $MASHPIA_DB;
        try {
            $user = \Soldier::find([ $id ]);
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
        $updated = 0;   $errors = [];
        $soldier = \Soldier::find([ $_POST['user_id'] ]);
        // go through all the updates and update each subject
        foreach( $_POST['subjects'] as $update ) {
            $subject = \Subject::find([ $update[ 'subject_id' ] ]);
            // try to update the missions
            try {
                $subject->setMissions( $soldier->user_id, $update[ 'missions' ] );
                $updated += 1;
            } catch ( Exception $e ) {
                $errors[] = $e->getMessage();
            }
        }
        // update the missions
        json_response([
            'errors' => $errors,
            'updated' => $updated,
            'medalBoard' => $soldier->medalBoard(),
            'rankBoard' => $soldier->rankBoard(),
        ]);
    }

    public function removeFromSchool() {
        global $MASHPIA_DB;

        $soldier = \Soldier::find([ $_POST['user_id'] ]);
        $school_id = 612;

        // find current class grade
        $success = false;
        $stmt = $MASHPIA_DB->prepare("
            SELECT 
                class_grade
            FROM
                classes
            WHERE
                class_id = :id
        ");
        $res = $stmt->execute([
            ':id'   =>  $soldier->class_id
        ]);
        if ( $res ) {
            $row = $stmt->fetch();
            $class_grade = $row['class_grade'];

            // get class id to switch to
            $stmt2 = $MASHPIA_DB->prepare("
                SELECT 
                    class_id
                FROM
                    classes
                WHERE
                    school_id = :school AND class_grade = :grade
            ");
            $res2 = $stmt2->execute([
                ':school'   =>  $school_id, 
                ':grade'    =>  $class_grade
            ]);

            
            if ( $res2 ) {
                $row2 = $stmt2->fetch();
                if ( $row2 ) {
                    $class_id = $row2['class_id'];
                    $moveStmt = $MASHPIA_DB->prepare("
                        UPDATE users 
                        SET school_id = :school, class_id = :grade 
                        WHERE user_id = :user
                    ");
                    $moveStmt->execute([
                        ':school'   =>  $school_id, 
                        ':grade'    =>  $class_id, 
                        ':user'     =>  $soldier->user_id
                    ]);
                    $success = true;
                }
            }
        }
        if ( $success ) {
            json_response("Updated.");
        } else {
            json_error("Error moving student.");
        }
    }
}

rest_router( new UsersRouter );
