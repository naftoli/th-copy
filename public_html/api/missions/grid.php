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
        // basic params for all the queries
        $params = [
            ':class_id'  => $current_user->login->class_id,
            ':start_date' => $start_date,   ':end_date' => $end_date,
        ];
        // * modified filters
        // by default we expect
        $dates_filter = ' dtm.start_date <= :start_date '.'AND dtm.end_date >= :end_date ';
        // by default we load all subjects where at least one soldier is enrolled and it is not a tehillim campaign
        $campaigns_filter = 'SELECT DISTINCT subject_id FROM user_tracks JOIN users USING (user_id) '
                            .'WHERE class_id = :class_id AND user_registered > 0 AND subject_type != \'WWTC\'';
        // weekly missions may be in middle of the week, but they are within the start and end date
        if ( $_POST['type'] == 'weekly' )
            $dates_filter = ' dtm.start_date >= :start_date '.'AND dtm.end_date <= :end_date ';
        // tehillim only shows WWTC campaigns
        if ( $_POST['type'] == 'tehillim' )
            $campaigns_filter = 'SELECT subject_id FROM subjects WHERE subject_type = \'WWTC\'';

        // * Final Query
        // Query used to fetch tasks for grid.
        $missions_sql = 'SELECT dt.cat, dt.points AS points, dt.grid_id, dt.quantity, '.$mark_date.' AS mark_date, '
            .' dtm.subject_id, dtm.start_date, dtm.end_date, s.subject_id, s.subject_name, dt.mandatory_qty '
            .' FROM mashpiadb.date_tasks dt '
            .' JOIN mashpiadb.date_tasks_missions dtm USING (date_tasks_mission_id) '
            .' JOIN mashpiadb.subjects s USING (subject_id) '
            // * Misc limits used for keeping the data clean
            .' WHERE dtm.personal = 0 '
            .' AND dt.cat != \'\' AND dt.grid_id IS NOT NULL AND dt.grid_marking = 1 '
            // * Limits based on :start_date, :end_date and :daily_task
            .' AND dt.daily_task = :daily_task '
            .' AND ' . $dates_filter
            .' AND dtm.subject_id IN (' . $campaigns_filter .') '
            // * Limits based on :school_id
            .' AND ( dtm.created_by_school IS NULL OR dtm.created_by_school = :school_id )'
            .' AND dtm.lang_id = ( SELECT lang_id FROM schools WHERE school_id = :school_id ) '
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
        $mission_params = array_merge( $params, [
            ':school_id'  => $current_user->login->school_id,
            ':daily_task' => $_POST['type'] == 'daily' ? '1' : '0',
        ]);

        // get the missions and grid_ids
        $missions_query = $MASHPIA_DB->prepare( $missions_sql );
        $missions_query->execute( $mission_params );
        $missions = $missions_query->fetchAll();
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
        $marks_query->execute( $params );
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
                'profilePicture' => $soldier->profilePicture(),
                'marks' => isset( $marks[ $soldier->user_id ] ) ? $marks[ $soldier->user_id ] : new stdClass
            ];
        }

        json_response([
            'missions' => $missions,
            'soldiers' => $formatted_soldiers
        ]);
    }

    private function getDates( $type, $value ) {
        if ( $type === 'daily' )
            return [ 'start_date' => $value, 'mark_date' => $value, 'end_date' => $value ];
        if ( $type === 'weekly' ) {
            $parsha = Parsha::find( $value );
            return [ 'start_date' => $parsha->start, 'mark_date' => 'start_date', 'end_date' => $parsha->end ];
        }
        if ( $type === 'tehillim' ) {
            return [ 'start_date' => $value, 'mark_date' => $value, 'end_date' => $value ];
        }
    }
}

rest_router( new GridRouter );

die();