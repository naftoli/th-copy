<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../../header/header.php" );
include_once( __DIR__ . "/../../../calendar.php" );

class BirthdayRouter {

    public function index() {
        global $current_user; global $MASHPIA_DB;

        // define $filters and $params;
        extract( $this->getFilters( $current_user->login ) );
        $start_date = unixtojd();
        $end_date = $start_date + 3; // 3 days of birthdays

        $query = $MASHPIA_DB->prepare(
             " SELECT user_id, class_id, first, last, mobile_pic, user_photo_id, school_name, class_grade, class_sub, start_date, end_date "
            ." FROM birthdays JOIN users u USING (user_id) JOIN schools s USING (school_id) "
            ." JOIN classes c USING (class_id) JOIN date_tasks_missions USING (date_tasks_mission_id) "
            ." WHERE start_date >= $start_date AND end_date < $end_date "
            ." AND " . implode( ' AND ', $filters ) . ' '
            ." GROUP BY user_id ORDER BY start_date, first, last;"
        );
        $query->execute( $params );
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
        } else { json_error( 'Access Deinied: HOME-BIRTHDAY-26' ); }
        
        return [ 'filters' => $filters, 'params' => $params ];
    }
}

rest_router( new BirthdayRouter );
