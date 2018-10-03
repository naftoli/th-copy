<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../../header/header.php" );
include_once( __DIR__ . "/../../../calendar.php" );

class BirthdayRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;

        // define $filters and $params;
        $filter = $current_user->login->getFilter();
        if ( !$filter )
            json_error( 'Access Deinied: HOME-BIRTHDAY-26' );

        $start_date = unixtojd();
        $end_date = $start_date + 3; // 3 days of birthdays

        $query = $MASHPIA_DB->prepare(
             " SELECT user_id, class_id, first, last, mobile_pic, user_photo_id, school_name, class_grade, class_sub, start_date, end_date "
            ." FROM birthdays JOIN users u USING (user_id) JOIN schools s USING (school_id) "
            ." JOIN classes c USING (class_id) JOIN date_tasks_missions USING (date_tasks_mission_id) "
            ." WHERE start_date >= $start_date AND end_date < $end_date AND $filter "
            ." GROUP BY user_id ORDER BY start_date, first, last;"
        );
        $query->execute();
        $birthdays = [];
        while( $row = $query->fetch() ) {
            $soldier = [
                'user_id' => $row['user_id'], 'class_id' => $row['class_id'],
                'name' => $row['first'] . ' ' . $row['last'],
                'platoon' => ( new Platoon(['class_grade' => $row['class_grade'], 'class_sub' => $row['class_sub']]) )->name(),
                'base' => $row['school_name'],
                'profilePicture' => ( new User(['mobile_pic' => $row['mobile_pic'], 'user_photo_id' => $row['user_photo_id']]) )->profilePicture()
            ];
            $birthdays[ dateToHebrew( $row['start_date'] ) ][] = $soldier;
        };

        // year, status, soldiers, total, reg_open
        json_response( $birthdays, true, true );
    }
}

rest_router( new BirthdayRouter );
