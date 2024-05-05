<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UsersRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;

        $ranks = [];
        $sql = "SELECT * FROM ranks";
        $stmt = $MASHPIA_DB->query( $sql );
        while( $row = $stmt->fetch() ) {
            $ranks[$row['rank_ord']] = $row['rank_name'];
        }

        $filters = $current_user->login->getFilter( 's.', 'u.' );
        // generate the SQL
        // if we are a parent
        if ( $current_user->login->code === 'PARENT' ) {
            $sql = "
                SELECT u.*, aa.admin_id, s.school_name, s.shipping_city, s.school_era, c.class_grade, c.class_sub, 
                       MAX(rank_ord) as `rank` 
                FROM users u 
                JOIN schools s USING ( school_id ) 
                JOIN rank_marks using ( user_id ) 
                LEFT JOIN admin_auths aa ON ( aa.id = u.user_id AND aa.auth = 'user' )
                LEFT JOIN classes c USING ( class_id ) WHERE $filters 
                GROUP BY user_id 
                ORDER BY school_name, class_grade, class_sub, last, first
            ";
        } else {
            // can't get admin id here b/c it creates bugs
            $sql = "
                SELECT u.*, s.school_name, s.shipping_city, s.school_era, c.class_grade, c.class_sub, 
                       MAX(rank_ord) as `rank` 
                FROM users u 
                JOIN schools s USING ( school_id ) 
                JOIN rank_marks using ( user_id ) 
                LEFT JOIN classes c USING ( class_id ) WHERE $filters 
                GROUP BY user_id 
                ORDER BY school_name, class_grade, class_sub, last, first
            ";
        }
//        if (isset($_COOKIE['naftoli'])) {
//            echo $sql;
//            exit;
//        }
        $query = $MASHPIA_DB->prepare( $sql );
        $query->execute();
        $info = $query->fetchAll();
        if ($query->errorCode() !== "00000") {
            json_error("SQL Error: ".implode(', ', $query->errorInfo()), false, 500);
        }

        $users = [];
        // fetch all results and parse them as models
        $stmt = $MASHPIA_DB->prepare("SELECT admin_id FROM admin_auths WHERE id = :id AND auth = 'user'");
        foreach ($info as $row) {
            if ( !$row['admin_id'] && !$current_user->isHQ() ) {
                $stmt->execute([':id' => $row['user_id']]);
                $row['admin_id'] = $stmt->fetch(PDO::FETCH_ASSOC)['admin_id'];
            }
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
                'first_he' => $row['first_he'], 'last_he' => $row['last_he'], 
                'user_registered' => $user_registered,  'mobile_pic' => $row['mobile_pic'], 'profilePicture' => $profilePicture,
                'chayolei' => intval($row['chayolei']), 'yan' => intval($row['yan']), 'chidon' => intval($row['chidon']), 
                'school_id' => $row['school_id'], 'class_id' => $row['class_id'] ? $row['class_id'] : false,
                'school' => [ 'school_id' => $row['school_id'], 'school_name' => $row['school_name'], 
                    'shipping_city' => $row['shipping_city'], 'school_era' => $row['school_era'] ],
                'barcode' => '3'.$row['user_code'],
                'platoon' => ( $platoon ? [ 'name' => $platoon ] : null ),
                'rank'  => $ranks[$row['rank']],
                'admin_id'  => $row['admin_id'],
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
        $cur_user = $current_user; // apparently there's a duplicate $current_user variable created by WP at some point in this function which is causing bugs

        $user = Soldier::build( $_POST );
        // if it's day school, change hachayol and medals/ranks modules to be turned off
        if ($cur_user->login->inst_id == 4) {
            $user->hachayols = 0;
            $user->medals_ranks = 0;
        }
 
        // make sure soldier with this first and last name and date of birth doesn't exist in this school
        $school_id = $_POST['school_id'];
        $first = ucwords($_POST['first']);
        $last = ucwords($_POST['last']);
        $dob = $_POST['dob'];
        $existing_user =  Soldier::find('all',array('conditions' => array('school_id = ? and first = ? and last = ? and dob = ?', $school_id, $first, $last, $dob)));
        if ( $existing_user ) {
            json_error('A child with this name and dob already exists.');
        }

        // if it is a teacher, set the school id to the platoons school id
        if ( $cur_user->login->code === 'TEACHER' ) {
            $user->school_id = $cur_user->login->model->school_id;
        // make sure the class is in the grade
        } else {
            $platoon_school_id = $user->platoon->school_id;
            if ( $platoon_school_id !== $user->school_id )
                json_error( 'Platoon is not in Base' );
        }

        // set chayolei / chidon eligibility for new soldier
        $user->chayolei_eligible = 1;
        if ($user->platoon->class_grade > 3 && $user->platoon->class_grade != 8) $user->chidon_eligible = 1;

        // save and create the soldier
        if ( !$user->is_valid() || !$user->save() )
            json_error( 'Could not create Soldier. (CODE: CORE-USERS-98)' );
        // parents get auto connected to their kids

        if ( $cur_user->login->code === 'PARENT' ) {
            $auth = \AdminAuth::create([
                'admin_id' => $cur_user->admin_id,
                'id'       => $user->user_id,     
                'auth'     => 'user',
                'role_id'  => 1
            ]);

            /*$userVal = 'user';
            $roldIdVal = 1;

            $data = [
                'admin_id' => $admin_user->admin_id,
                'auth'     => $userVal,
                'id'       => $user->user_id,
                'role_id'  => $roldIdVal
            ];

            $sql = "INSERT INTO admin_auths (admin_id, auth, id, role_id) VALUES (:admin_id, :auth, :id, :role_id)";
            $stmt = $MASHPIA_DB->prepare( $sql );
            $stmt->execute($data);*/
       }

        // send the full soldier to the client
        json_response( $user );
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
            $toCapitalize = ['first', 'last', 'non_th_school'];
            foreach( $_POST as $key => $value ) {
                if ( in_array( $key, $columns ) ) {
                    if ( in_array( $key, $toCapitalize ) ) $value = ucwords( $value );
                    $user->{ $key } = $value;
                }
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

        if (is_array($_POST['user_id'])) {
            $users = $_POST['user_id'];
        } else {
            $users = [$_POST['user_id']];
        }

        $school_id = 612;

        $stmt = $MASHPIA_DB->prepare("
            SELECT 
                class_grade
            FROM
                classes
            WHERE
                class_id = :id
        ");

        $stmt2 = $MASHPIA_DB->prepare("
            SELECT 
                class_id
            FROM
                classes
            WHERE
                school_id = :school AND class_grade = :grade
        ");

        $moveStmt = $MASHPIA_DB->prepare("
            UPDATE users 
            SET school_id = :school, class_id = :grade 
            WHERE user_id = :user
        ");

        $errors = [];
        foreach ($users as $user) {
            $soldier = \Soldier::find($user);
            // delete soldier if has no points
            if ($soldier->canDestroy()) {
                $soldier->delete();
                // remove from parent account
                $MASHPIA_DB->query('DELETE FROM admin_auths WHERE id=' . $user->user_id . ' AND auth = "user"');
//                return json_response('Soldier has been deleted.');
            }

            // make sure we have a class connected to student
            if (!$soldier->class_id) {
//                json_error("Student needs to be assigned to a grade before he/she can be removed.");
//                return;
                $errors[] = $user . " is not assigned to a grade and therefore can not be removed.";
                continue;
            }

            // find current class grade
            $res = $stmt->execute([
                ':id' => $soldier->class_id
            ]);
            if ($res) {
                $row = $stmt->fetch();
                $class_grade = $row['class_grade'];

                // get class id to switch to

                $res2 = $stmt2->execute([
                    ':school' => $school_id,
                    ':grade' => $class_grade
                ]);

                if ($res2) {
                    $row2 = $stmt2->fetch();
                    if ($row2) {
                        $class_id = $row2['class_id'];

                        $moveStmt->execute([
                            ':school' => $school_id,
                            ':grade' => $class_id,
                            ':user' => $soldier->user_id
                        ]);
                    }
                }
            }
            if (!( $res && $res2 )) {
                $errors[] = "There was an error moving " . $user;
            }
        }
        if ( empty($errors) ) {
            json_response("Updated.");
        } else {
            json_error(implode('\n', $errors));
        }
    }

    public function updateBirthdayMissions( $id ) {
        global $current_user;

        $user = \Soldier::find([ $_POST['user_id'] ]);
        if ( !$user->validateAccess( $current_user->login ) )
            json_error( 'Your current login does not have access to this soldier.', 'CORE-USERS-77', 401 );

        $user->setupBirthdayMissions();
        json_response("Updated.");
    }

}

rest_router( new UsersRouter );
