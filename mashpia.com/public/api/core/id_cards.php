<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );
include_once( __DIR__ . "/../../calendar.php" );

class IdCardsRouter {
    // index, but we are using a POST request for it
    public function create(){
        global $current_user; global $MASHPIA_DB;
        // limit based on admin type
        $params = [];
        $filters = [ $current_user->login->getFilter( 's.', 'u.' ) ];
        if ( !$filters[0] )
            return json_error( 'Access Deinied: CORE-ID_CARDS-14' );
        // add filters from post request
        if ( isset($_POST['school_id']) && $_POST['school_id'] ) {
            $filters[] = 'u.school_id = ?'; $params[] = $_POST['school_id'];
        }
        if ( isset($_POST['class_id']) && $_POST['class_id'] ) {
            $filters[] = 'u.class_id = ?'; $params[] = $_POST['class_id'];
        }
        if ( isset($_POST['rank']) && $_POST['rank'] ) {
            $filters[] = 'rm.rank_ord = ?'; $params[] = $_POST['rank'];
        }
        if ( isset($_POST['user_serial']) && $_POST['user_serial'] ) {
            $filters[] = 'u.user_serial = ?'; $params[] = $_POST['user_serial'];
        }
        if ( isset($_POST['hide_printed']) && $_POST['hide_printed'] ) {
            $filters[] = 'rm.date_printed IS NULL';
        }
//        if ( isset($_POST['earned_before']) ) { // expect julian date from client
//            $filters[] = 'rm.date_promoted <= ?'; $params[] = $_POST['earned_before'];
//        }
        if ( isset($_POST['earned_start']) ) {
            $filters[] = 'rm.date_promoted >= ?'; $params[] = $_POST['earned_start'];
        }
        if ( isset($_POST['earned_end']) ) {
            $filters[] = 'rm.date_promoted <= ?'; $params[] = $_POST['earned_end'];
        }
        // combine the filters
        $filters[] = 'u.user_registered > 0';
        $filters[] = 'u.medals_ranks = 1';
        // add schools exceptions
        $filters[] = 'u.school_id not in (180, 585, 588, 612, 709)';
        $filters = implode( ' AND ', $filters );
//        $fix = " OR ((rm.date_book_shipped is null OR rm.date_card_shipped is null) AND rm.date_printed is not null";
//        if ( isset($_POST['rank']) && $_POST['rank'] ) $fix .= " AND rank_ord = " . $_POST['rank'];
//        $fix .= ")";
//        $filters .= $fix;
//        echo $filters; exit;

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
        $query = $MASHPIA_DB->prepare( $sql );
        $query->execute( $params );

        $response = [];
        while( $row = $query->fetch() ){
            // set the picture, and profile name using the models
            $row['profilePicture'] = ( new Soldier(['mobile_pic' => $row['mobile_pic'], 'user_photo_id' => $row['user_photo_id']]) )->profilePicture();
            $row['platoon'] = ( new Platoon(['class_grade' => $row['class_grade'], 'class_sub' => $row['class_sub']]) )->name();
            // functions from public_html/calendar.php
            $row['member_since'] = $row['user_start_date'] ? dateToHebrewShortYear($row['user_start_date']) : false;
            // copied from admin_card_print.php, TODO, validate and move to Soldier model
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
        global $MASHPIA_DB;
        $date = date("Y-m-d H:i:s");
        $printed_query = $MASHPIA_DB->prepare(
            "UPDATE rank_marks SET date_printed='$date', date_book_shipped = '$date', date_card_shipped = '$date' WHERE user_id=? AND rank_ord=?"
        );
        $not_printed_query = $MASHPIA_DB->prepare(
            'UPDATE rank_marks SET date_printed=null, date_book_shipped = null, date_card_shipped = null WHERE user_id=? AND rank_ord=?'
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
}

rest_router( new IdCardsRouter );
