<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../../header/header.php" );
include_once( __DIR__ . "/../../../calendar.php" );

class BirthdayRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;

        // define $filters and $params;
        $filter = $current_user->login->getFilter( 's.', 'u.' );
        if ( !$filter )
            json_error( 'Access Deinied: HOME-BIRTHDAY-14' );

        $start = isset($_GET['start']) ? $_GET['start'] : null;
        $end = isset($_GET['end']) ? $_GET['end'] : null;

        if ($end) {
            $dateInfo = explode( '-', $end );
            $end_date = gregoriantojd( $dateInfo[1], $dateInfo[2], $dateInfo[0] );
        } else {
            $end_date = intval(unixtojd());
        }

        if ($start) {
            $dateInfo = explode( '-', $start );
            $start_date = gregoriantojd( $dateInfo[1], $dateInfo[2], $dateInfo[0] );
        } else {
            $start_date = $end_date - 7; // x days of promotions
        }

        $extra = '';
        if (isset($_GET['gender'])) {
            $extra .= "AND u.gender = '" . $_GET['gender'] . "'";
        }
        if (isset($_GET['school'])) {
            $extra .= "AND u.school_id = '" . $_GET['school'] . "'";
        }

        // add rank_ord > 1 to hide privates
        $query = $MASHPIA_DB->prepare(
             " SELECT user_id, class_id, first, last, mobile_pic, user_photo_id, school_name, "
            ." class_grade, class_sub, date_promoted, rank_ord, rank_name "
            ." FROM rank_marks JOIN ranks USING (rank_ord) JOIN users u USING (user_id) "
            ." JOIN schools s USING (school_id) JOIN classes c USING (class_id) "
            ." WHERE date_promoted >= $start_date AND date_promoted <= $end_date "
            ." AND ($filter) AND u.user_registered IS NOT NULL $extra"
            ." GROUP BY user_id ORDER BY date_promoted DESC, rank_ord DESC, last, first;"
        );
//        $query->debugDumpParams();
//        json_response([], true, true);
        $query->execute();
        $promotions = [];
        while( $row = $query->fetch() ) {
            $soldier = [
                'user_id' => $row['user_id'], 'class_id' => $row['class_id'], 'rank_ord' => $row['rank_ord'],
                'name' => $row['rank_name']. ' ' .$row['first'] . ' ' . $row['last'], 
                'full_name' => $row['first'] . ' ' . $row['last'], 
                'rank_name' => $row['rank_name'], 
                'platoon' => ( new Platoon(['class_grade' => $row['class_grade'], 'class_sub' => $row['class_sub']]) )->name(),
                'profilePicture' => ( new Soldier(['mobile_pic' => $row['mobile_pic'], 'user_photo_id' => $row['user_photo_id']]) )->profilePicture()
            ];
            $promotions[ dateToHebrew( $row['date_promoted'] ) ][] = $soldier;
        };

        // year, status, soldiers, total, reg_open
        json_response( $promotions, true, true );
    }
}

rest_router( new BirthdayRouter );
