<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
include_once( __DIR__ . "/../../calendar.php" );

class UsersRouter {
    // index, but we are using a POST request for it
    public function create(){
        global $current_user; global $pdo;
        // limit based on admin type
        extract($this->getFilters( $current_user->login ));
        // add filters from post request
        if ( isset($_POST['school_id']) && $_POST['school_id'] ) {
            $filters[] = 'u.school_id = ?'; $params[] = $_POST['school_id'];
        } if ( isset($_POST['class_id']) && $_POST['class_id'] ) {
            $filters[] = 'u.class_id = ?'; $params[] = $_POST['class_id'];
        } if ( isset($_POST['rank']) && $_POST['rank'] ) {
            $filters[] = 'rm.rank_ord = ?'; $params[] = $_POST['rank'];
        } if ( isset($_POST['user_serial']) && $_POST['user_serial'] ) {
            $filters[] = 'u.user_serial = ?'; $params[] = $_POST['user_serial'];
        } if ( isset($_POST['hide_printed']) && $_POST['hide_printed'] ) {
            $filters[] = 'rm.date_printed IS NULL';
        } if ( isset($_POST['earned_before']) ) { // expect julian date from client
            $filters[] = 'rm.date_promoted < ?'; $params[] = $_POST['earned_before'];
        }
        // combine the filters
        $filters[] = 'u.user_registered IS NOT NULL';
        $filters = implode( ' AND ', $filters );

        $rank_marks = "(SELECT MAX(rank_ord) max_rank, user_id FROM rank_marks GROUP BY user_id) cr USING (user_id) "
            ." JOIN rank_marks rm ON (rm.rank_ord = cr.max_rank AND rm.user_id = u.user_id) ";
        if ( isset($_POST['current']) && !$_POST['current'] ) {
            $rank_marks = 'rank_marks rm USING (user_id) ';
        }

        $sql = "SELECT rank_name AS rank, rank_ord, u.user_id, user_serial, first, last, first_he, last_he, "
            ." school_name, school_number, logo, user_photo_id, mobile_pic, rm.date_printed as printed, "
            ." rm.date_promoted, user_code AS barcode, class_grade, class_sub, user_start_date, dob, dob_he_offset "
            ." FROM users u LEFT JOIN $rank_marks "
            ." LEFT JOIN ranks r USING (rank_ord) JOIN schools s USING (school_id) "
            ." LEFT JOIN classes c USING (class_id) WHERE $filters "
            ." ORDER BY s.school_name, c.class_grade, c.class_sub, u.first, u.last, rm.rank_ord;";
        $query = $pdo->prepare( $sql );
        $query->execute( $params );
        
        $response = [];
        while( $row = $query->fetch() ){
            // set the picture, and profile name using the models
            $row['profilePicture'] = ( new User(['mobile_pic' => $row['mobile_pic'], 'user_photo_id' => $row['user_photo_id']]) )->profilePicture();
            $row['platoon'] = ( new Platoon(['class_grade' => $row['class_grade'], 'class_sub' => $row['class_sub']]) )->name();
            // functions from public_html/calendar.php
            $row['member_since'] = $row['user_start_date'] ? dateToHebrewShortYear($row['user_start_date']) : false;
            // copied from admin_card_print.php, TODO, validate and move to User model
            $dob = dateToJD( $row['dob'] );
            $cal = cal_from_jd( $dob, CAL_JEWISH );
            $row['valid_utill'] = $dob ? dateToHebrewShortYear( cal_to_jd( CAL_JEWISH, 13, cal_days_in_month( CAL_JEWISH, 13, $cal['year'] + 13 ), $cal['year'] + 13 ) ) : false;
            // set the logo to show for the school
            $row['school_logo'] = "/schoolLogos/".$row['logo'];
            // set the correct types
            $row['rank_ord'] = intval( $row['rank_ord'] );
            $row['barcode'] = '3'. $row['barcode'];
            $response[] = $row;
        }
        json_response( $response );
    }

    public function markPrinted() {
        global $pdo;
        $date = date("Y-m-d H:i:s");
        $printed_query = $pdo->prepare(
            "UPDATE rank_marks SET date_printed='$date' WHERE user_id=? AND rank_ord=?"
        );
        $not_printed_query = $pdo->prepare(
            'UPDATE rank_marks SET date_printed=null WHERE user_id=? AND rank_ord=?'
        );
        $status = [];
        // expects a post like so { updates: [ { user_id, rank_ord, printed }, ... ] }
        foreach( $_POST['updates'] as $update ) {
            $query = $printed_query;
            $printed = !(!$update['printed'] || $update['printed'] === 'false');
            if ( !$printed ) { $query = $not_printed_query; }
            $success = $query->execute([ $update['user_id'], $update['rank_ord'] ]);
            $status[$update['user_id']] = ( $success ? $printed : !$printed );
        }
        json_response( $status );
    }

    private function getFilters( $login ){
        // filters and params for the filters
        $filters = [];   $params = [];
        if ( $login['code'] === 'HQ' ) {
            $filters[] = 's.test_school = 0';
        } else if ( $login['code'] === 'INST' ) {
            $filters[] = 's.inst_id = ?'; $params[] = $login['id'];
        } else if ( $login['code'] === 'BC' ) {
            $filters[] = 'u.school_id = ?'; $params[] = $login['id'];
        } else if ( $login['code'] === 'TEACHER' ) {
            $filters[] = 'u.class_id = ?'; $params[] = $login['id'];
        } else { json_error( 'Access Deinied: CORE-USERS-26' ); }
        
        return [ 'filters' => $filters, 'params' => $params ];
    }
}

rest_router( new UsersRouter );
