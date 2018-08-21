<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class PlatoonTransitionRouter {

    private $find_query;
    private $update_query;
    private $insert_query;

    public function getUsers(){
        global $current_user;   global $pdo;
        $have_class_id = isset( $_POST['class_id'] ) && intval( $_POST['class_id'] );
        if ( !isset( $_POST['school_id'] ) )
            json_error( 'Invalid Request' );

        $params = [ 'school_id' => $_POST['school_id'] ];
        if ( $have_class_id ) $params['class_id'] = $_POST['class_id'];

        $hq_filter = ''; // AND pt.admin_id = 82'
        if ( $current_user->login['code'] !== 'BC' ) {
            $hq_filter = 'AND pt.admin_id = :admin_id';
            $params['admin_id'] = $current_user->admin_id;
        }
        // generate query to fetch the users and the location that they are going to
        $user_query = $pdo->prepare(
            "SELECT u.user_id, u.user_serial, u.first, u.last, u.school_id AS current_school_id, "
            ."pt.school_id, pt.class_id, s.school_name, c.class_grade, c.class_sub, "
            ."!ISNULL(pt.created_at) as being_moved FROM users u "
            ."LEFT JOIN platoon_transitions pt ON u.user_id = pt.user_id AND pt.deployed_at IS NULL $hq_filter "
            ."LEFT JOIN schools s ON pt.school_id = s.school_id "
            ."LEFT JOIN classes c ON pt.class_id = c.class_id "
            ."WHERE u.school_id = :school_id AND u.class_id ".($have_class_id ? "= :class_id ": "IS NULL ") // = null does not work. duh, that would make sense...
            ."ORDER BY u.last, u.first;"
        );
        $user_query->execute( $params );
        $results = [];
        while ( $user = $user_query->fetch() ){
            if ( $user['being_moved']) {
                if ( !$user['school_id'] ) {
                    $user['transition'] = 'Discharged (Removed)';
                } else {
                    $tranistion = ( new Platoon(
                        ['class_grade' => $user['class_grade'], 'class_sub' => $user['class_sub']])
                    )->name();

                    if ( $user['current_school_id'] !== $user['school_id'] ) {
                        $tranistion .= ' ('.$user['school_name'].')';
                    }

                    $user['transition'] = $tranistion;
                }
            } else {
                $user['transition'] = 'Not Transitioning';
            }
            $user['being_moved'] = $user['being_moved'] == '1';
            $results[] = $user;
        }
        json_response( $results );
    }

    function changePlatoon(){
        $user_ids = $_POST['user_ids'];
        $school_id = $_POST['school_id'];
        $class_id = $_POST['class_id'];
        $year = GlobalSettings::getCurrentYear(); // get the current year to log the information.

        if ( !$user_ids || !$school_id || !$class_id || !$year )
            json_error( 'Invalid Request' );
        // prepare the queries
        $this->moveOrCreate( $user_ids, $school_id, $class_id, $year );

        json_response( false );
    }

    function removeFromBase(){
        // get the current year to log the information.
        $year = GlobalSettings::getCurrentYear();

        $user_ids = $_POST['user_ids'];
        if ( !$user_ids )
            json_error('Invalid Request');

        $this->moveOrCreate( $user_ids, null, null, $year );

        json_response( false );
    }

    function transitionPlatoons(){
        global $current_user; global $pdo;

        $filter = '';
        if ( isset($_POST['school_id']) ) {
            $filter = "u.school_id = ".$_POST['school_id']." ";
        }

        if ( $current_user->login['code'] !== 'BC' ) {
            $filter .= "AND pt.admin_id = '$current_user->admin_id' ";
        }    
        
        $transition_query = $pdo->prepare(
            "UPDATE users u JOIN platoon_transitions pt ON u.user_id = pt.user_id AND pt.deployed_at IS NULL " 
            ."SET pt.deployed_at = NOW(), pt.from_school_id = u.school_id, pt.from_class_id = u.class_id, "
            ."u.school_id = pt.school_id, u.class_id = pt.class_id WHERE $filter;"
        );
        $success = $transition_query->execute();
        
        if ( !$success )
            json_error("Unknown Error: Could not deploy platoons. Please email bugs@tzivoshashem.org");
        
        json_response([
            'rowCount' => $transition_query->rowCount()
        ]);
    }

    // move or create entries for an array of user_ids
    private function moveOrCreate( $user_ids, $school_id, $class_id, $year ) {
        global $pdo; global $current_user;
        // setup the queries
        $find_query = $pdo->prepare(
            'SELECT platoon_transitions_id FROM platoon_transitions WHERE user_id = ? AND deployed_at IS NULL'
        );
        $insert_query = $pdo->prepare(
            'INSERT INTO platoon_transitions (user_id, admin_id, school_id, class_id, year) VALUES (?, ?, ?, ?, ?)'
        );
        $update_query = $pdo->prepare(
            'UPDATE platoon_transitions SET user_id = ?, admin_id = ?, school_id = ?, class_id = ?, year = ? WHERE platoon_transitions_id = ?'
        );
        // for each user
        foreach( $user_ids as $user_id ){
            // check if the user exists
            $find_query->execute([ $user_id ]);
            // if yes update that id
            if ( $find_query->rowCount() >= 1 ) {
                $id = $find_query->fetch()['platoon_transitions_id'];
                $update_query->execute([
                    $user_id, $current_user->admin_id, 
                    $school_id, $class_id, $year, $id
                ]);
            // or create a row for him
            } else {
                $insert_query->execute([
                    $user_id, $current_user->admin_id, 
                    $school_id, $class_id, $year
                ]);
            }
        }
    }
}

rest_router( new PlatoonTransitionRouter );