<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class GridRouter {

    // Only teachers can use this feature.
    public function authenticate( $current_user ) {
        return $current_user->login->code === 'TEACHER';
    }

    public function get() {
        global $current_user; global $MASHPIA_DB;
        extract( $this->getDates( $_POST['type'], $_POST['date'] ) );

        // * Load the tasks
        $missions = $this->getTasks( $_POST['type'], $_POST['date'] );
        $grid_ids = array_map( function($mission) { return $mission['grid_id']; }, $missions );
        
        // * Load all registered soldiers in this platoon
        $soldiers = Soldier::all([ 
            'conditions' => 'class_id = '. $current_user->login->class_id . ' AND user_registered > 0 ',
            'order' => 'first, last'
        ]);
        if ( !is_array( $soldiers ) ) $soldiers = [ $soldiers ]; // cast to array
        
        // * Load soldier marks from the DBS in one query
        $marks_sql = 'SELECT u.user_id, dtm.mark_date, dtm.done_qty, dt.grid_id '
            .' FROM date_tasks_marks dtm JOIN users u USING (user_id) JOIN date_tasks dt USING (date_task_id) '
            .' WHERE u.class_id = :class_id ' // * :class_id
            .' AND dtm.mark_date >= :start_date ' // * :start_date ( mark date should be that or later )
            .' AND dtm.mark_date <= :end_date ' // * :end_date ( mark date should be that or sooner )
            .' AND dt.grid_id IN ('.implode( ', ', $grid_ids ).') '
            .' ORDER BY user_id , mark_date , grid_id;';
        $marks_query = $MASHPIA_DB->prepare( $marks_sql );
        $marks_query->execute([
            ':class_id'  => $current_user->login->class_id,
            ':start_date' => $start_date,   ':end_date' => $end_date,
        ]);
        $marks = [];
        while( $mark = $marks_query->fetch() ){
            $user_id = $mark['user_id'];    $grid_id = $mark['grid_id'];
            unset( $mark['user_id'] );  unset( $mark['grid_id'] );
            $marks[ $user_id ][ $grid_id ] = $mark;
        };

        // * Format the soldiers in a way that the client expects
        $formatted_soldiers = [];
        foreach ( $soldiers as $soldier ) {
            $formatted_soldiers[] = [
                'rank' => $soldier->rank(),
                'name' => $soldier->name(),
                'user_id' => $soldier->user_id,
                'user_serial' => $soldier->user_serial,
                'marks' => isset( $marks[ $soldier->user_id ] ) ? $marks[ $soldier->user_id ] : new stdClass
            ];
        }

        json_response([
            'missions' => $missions,
            'soldiers' => $formatted_soldiers
        ], true, true);
    }

    public function customize() {
        global $current_user; global $MASHPIA_DB;
        $class_id = $current_user->login->class_id;

        $mission_sort = implode( ',', $_POST['mission_sort'] );

        $enabled_ids  = array_map( function( $id ) use ( $MASHPIA_DB ) {
            return $MASHPIA_DB->quote( $id );
        }, $_POST['mission_status']['enabled'] );
        $enabled_ids = implode( ', ', $enabled_ids );

        $disabled_marks = rtrim(
            str_repeat( '( '.$class_id.', ? ),', count( $_POST['mission_status']['disabled'] ) ), 
            ','
        );

        $sorting_query = $MASHPIA_DB->prepare(
             ' INSERT INTO class_date_task_sorting ( class_id, grid_type, sorted_grid_ids ) '
            .' VALUES ( '.$class_id.', :type, :mission_sort ) '
            .' ON DUPLICATE KEY UPDATE sorted_grid_ids = :mission_sort;'
        );
        $enabled_query = $MASHPIA_DB->prepare(
            'DELETE FROM class_date_tasks_disabled WHERE class_id = '.$class_id.' AND grid_id IN ('. $enabled_ids .')'
        );
        $disabled_query = $MASHPIA_DB->prepare(
             ' INSERT INTO class_date_tasks_disabled ( class_id, grid_id ) VALUES '.$disabled_marks.' '
            .' ON DUPLICATE KEY UPDATE grid_id=grid_id;'
        );

        $sorted = $sorting_query->execute([
            ':type' => $_POST['type'], ':mission_sort' => $mission_sort
        ]);
        $enabled = $enabled_query->execute();
        $disabled = $disabled_query->execute( $_POST['mission_status']['disabled'] );
        
        json_response([
            'sorted' => $sorted,
            'enabled' => $enabled,
            'disabled' => $disabled
        ]);
    }

    private function getTasks( $type, $date = false, $load_disabled = false ){
        global $current_user; global $MASHPIA_DB;

        // * start_date and end_date filters
        // by default we expect a date that is between the start date and end date
        $dates_filter = ' dtm.start_date <= :start_date '.'AND dtm.end_date >= :end_date ';
        // weekly missions may be in middle of the week, but they are within the start and end date
        if ( $type == 'weekly' || !$date ) // if no date we use the start and end dates of the year and need to use this option.
            $dates_filter = ' dtm.start_date >= :start_date '.'AND dtm.end_date <= :end_date ';
        // * subject_id filters
        // by default we load all subjects where at least one soldier is enrolled and it is not a tehillim campaign
        $campaigns_filter = 'SELECT DISTINCT subject_id FROM user_tracks JOIN users USING (user_id) '
        .'WHERE class_id = :class_id AND user_registered > 0 AND subject_type != \'WWTC\'';
        // tehillim only shows WWTC campaigns
        if ( $type == 'tehillim' )
            $campaigns_filter = 'SELECT subject_id FROM subjects WHERE subject_type = \'WWTC\'';

        // get the $start_date, $end_date and $mark_date variables
        if ( $date )
            extract( $this->getDates( $type, $date ) );
        else {
            $mark_date = 'start_date';
            $start_date = GlobalSettings::getCurYearDates()['start'];
            $end_date = GlobalSettings::getCurYearDates()['end'];
        }

        // * Final Query
        // Query used to fetch tasks for grid.
        $missions_sql = 'SELECT dt.short_name as cat, dt.points AS points, dt.grid_id, dt.quantity, '
            .$mark_date.' AS mark_date, dtm.subject_id, dtm.start_date, dtm.end_date, '
            .' s.subject_id, s.subject_name, dt.mandatory_qty, IFNULL( disabled, 0 ) as disabled, '
            .' s.subject_id, s.subject_name, dt.mandatory_qty '
            .' FROM mashpiadb.date_tasks dt '
            .' JOIN mashpiadb.date_tasks_missions dtm USING (date_tasks_mission_id) '
            .' JOIN mashpiadb.subjects s USING (subject_id) '
            .' LEFT JOIN ( '
                .'SELECT grid_id, 1 as disabled FROM class_date_tasks_disabled where class_id = :class_id '
            .') AS cdtd USING (grid_id) '
            // * Misc limits used for keeping the data clean
            .' WHERE dtm.personal = 0 '
            .' AND dt.cat != \'\' AND dt.short_name != \'\' '
//            .' AND dt.grid_id IS NOT NULL AND dt.grid_marking = 1 '
            // * Limits based on :start_date, :end_date and :daily_task
            .' AND dt.daily_task = :daily_task '
            .' AND ' . $dates_filter // add date filter if we are limiting by dates
            .' AND dtm.subject_id IN (' . $campaigns_filter .') '
            // * Limits based on :school_id
            .' AND ( dtm.created_by_school IS NULL OR dtm.created_by_school = :school_id )'
//            .' AND dtm.lang_id = ( SELECT lang_id FROM schools WHERE school_id = :school_id ) '
            // * Limit the level, subjects and school_type_id based on the :class_id
            .' AND dtm.school_type_id IN ('
                .' SELECT DISTINCT school_type_id FROM users '
                .' WHERE class_id = :class_id AND user_registered > 0 '
            .' ) '
            .' AND dtm.level IN ('
                .' SELECT DISTINCT level FROM user_tracks JOIN users USING (user_id) '
                .' WHERE class_id = :class_id AND user_registered > 0 '
            .' ) '
            .' GROUP BY dtm.subject_id, dt.grid_id';
        // params for query
        $params = [
            ':class_id'  => $current_user->login->class_id,
            ':school_id'  => $current_user->login->school_id,
            ':daily_task' => $type == 'daily' ? '1' : '0',
            ':start_date' => $start_date, ':end_date' => $end_date
        ];

        $missions_query = $MASHPIA_DB->prepare( $missions_sql );
        $missions_query->execute( $params );
//        $missions_query->debugDumpParams();
        $missions = $missions_query->fetchAll();

        $missions = $this->sortMissions( $missions, $current_user->login->class_id, $type );
        
        return $missions;
    }
    // * get start_date, end_date and value based on the date (parsha_id for weekly)
    private function getDates( $type, $value ) {
        if ( $type === 'weekly' ) {
            $parsha = \Parsha::find([ $value ]);
            return [ 'start_date' => $parsha->start, 'mark_date' => 'start_date', 'end_date' => $parsha->end ];
        }
        return [ 'start_date' => $value, 'mark_date' => $value, 'end_date' => $value ];
    }

    private function sortMissions( $missions, $class_id, $type ){
        global $MASHPIA_DB;
        // get the saved sorting settings
        $sorting_query = $MASHPIA_DB->prepare(
            'SELECT sorted_grid_ids FROM class_date_task_sorting WHERE class_id = :class_id AND grid_type=:type'
        );
        $sorting_query->execute([':class_id' => $class_id, ':type' => $type ]);
        $sorted_grid_ids = explode( ',', $sorting_query->fetch( PDO::FETCH_COLUMN ) );

        if( count( $sorted_grid_ids ) == 0 )
            return $missions;
        // separate the ones we can sort from the ones we cannot
        $missions = array_reduce( $missions,
            function( $result, $mission ) use ( $sorted_grid_ids ) {
                $key = in_array( $mission['grid_id'], $sorted_grid_ids ) ? 'sortable' : 'unknown';
                $result[$key][] = $mission;
                return $result;
            },
            [ 'sortable' => [], 'unknown' => [] ]
        );
        // sort the ones we can based on the users settings
        usort( $missions['sortable'], function ( $a, $b ) use ( $sorted_grid_ids ) {
            $a = $a['grid_id']; $b = $b['grid_id'];
            if ( $a == $b ) { echo "0\n"; return 0; }
            // return based on the index of the item in the sorted array where it goes
            return array_search( $a, $sorted_grid_ids ) > array_search( $b, $sorted_grid_ids ) ? 1 : -1;
        });
        // put all missions we cannot merge behined the ones we can.
        $missions = array_merge( $missions['sortable'], $missions['unknown'] );

        return $missions;
    }
}

rest_router( new GridRouter );

die();