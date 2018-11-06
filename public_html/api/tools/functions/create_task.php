<?php
require_once( __DIR__ . '/../../header/db.php' );
require_once( __DIR__ . '/../../../class.defaults.php' );
require_once( __DIR__ . '/../../../class.globalSettings.php' );

function create_task(
    $subject_id,    $short_name,    $task,
    $lang_id,       $label_id,      $mission_marking,
    $grid_marking,  $grades,        $parsha_ids,
    $school_id,     $school_type_id
){
    global $MASHPIA_DB; // database connection

    // Expect that this function can take 5+ min
    ini_set('max_execution_time', 300);

    // * set some variables.
    $points = 0.5; // miles
    $category = 'Base Task';
    $year = GlobalSettings::getCurrentYear();

    // * get the label
    $label = Label::find( $label_id );
    $school = School::find( $school_id );
    $subject = Subject::find( $subject_id );
    $daily = $label->isDaily();

    // * generate grid_id
    $min_grid_id = 1000000; // 1,000,000+ is the base grid id for custom tasks
    $grid_id_query = 'SELECT max(grid_id) + 1 as grid_id FROM date_tasks WHERE grid_id > ?';
    $grid_id_query = $MASHPIA_DB->prepare( $grid_id_query );
    $grid_id_query->execute([ $min_grid_id ]);

    $grid_id = $grid_id_query->fetch()['grid_id'];
    $grid_id = $grid_id ? $grid_id : $min_grid_id; // set to min if grid_id is not set yet.

    // * generate the category
    $cat_query = 'SELECT DISTINCT dt.cat FROM date_tasks dt '
        .'JOIN date_tasks_missions dtm USING ( date_tasks_mission_id ) '
        .'WHERE dt.cat LIKE \'%'.$category.'%\' '
        .'AND (dtm.created_by_school IS NULL OR dtm.created_by_school = '. $school_id .') '
        .'ORDER BY cat DESC LIMIT 1';
    $cat_query = $MASHPIA_DB->prepare( $cat_query );
    $cat_query->execute();
    
    if ( $cat_query->rowCount() > 0 ) {
        $cat = $cat_query->fetch()['cat'];
        $num = intval( str_replace( $category, '', $cat ) );
        $category .= ' ' . ( $num + 1 );
    } else
        $category .= ' 1'; // add 1 to the end if we do not have any existing tasks

    // * get the school types
    $school_type_id = intval( $school_type_id );
    $school_types = [
        $school_type_id + 2, // girls
        $school_type_id + 3 // boys
    ];

    // * get the selected parshos
    if ( !$parsha_ids )
        $parshos = Parsha::all([ 'conditions' => [
            'year = ? AND start >= ?', $year, unixtojd()
        ]]);
    else
        $parshos = Parsha::all( $parsha_ids );
    // make sure it is an array
    $parshos = is_array( $parshos ) ? $parshos : [ $parshos ];

    // * get the levels for the grades
    $levels_lookup = array(
        'Pre1a' =>  6,  '1'     =>  7,
        '2'     =>  8,  '3'     =>  9,
        '4'     =>  10, '5'     =>  11,
        '6'     =>  12, '7'     =>  13,
        '8'     =>  14
    );
    // make sure that we where passed grades and it is an array
    if ( is_array( $grades ) && count( $grades ) > 0 ) {
        $levels = array_map( function( $grade ) use ( $levels_lookup ) {
            return $levels_lookup[ $grade ];
        }, $grades);
        $levels = array_unique( $levels );
        
        $class_ids = 'SELECT class_id FROM classes WHERE school_id = '.$school_id
            .' AND class_grade IN (\''. implode( '\', \'', $grades ) .'\')';
    } else {
        $levels = array_values( $levels_lookup );
        $class_ids = 'SELECT class_id FROM classes WHERE school_id = '.$school_id;
    }
    // run the query
    $class_ids = $MASHPIA_DB->query( $class_ids );
    $class_ids = $class_ids->fetchAll( PDO::FETCH_COLUMN );
    
    // * Prepare the mission queries
    $find_mission_query = $MASHPIA_DB->prepare(
        'SELECT date_tasks_mission_id AS id FROM date_tasks_missions '
        .'WHERE end_date = :end '
        .'AND level = :level '
        .'AND lang_id = :lang_id '
        .'AND start_date = :start '
        .'AND mission_name = :name '
        .'AND school_type_id = :type '
        .'AND track_id = 1 AND personal = 0 '
        .'AND created_by_school = :school_id;'
    );
    $create_mission_query = $MASHPIA_DB->prepare(
        'INSERT INTO date_tasks_missions SET '
        .'level = :level, '
        .'end_date = :end, '
        .'lang_id = :lang_id, '
        .'start_date = :start, '
        .'mission_name = :name, '
        .'school_type_id = :type, '
        .'subject_id = :subject_id, '
        .'created_by_school = :school_id, '
        .'mission_value = 1.0, track_id = 1, default_on = 0;'
    );
    $find_label_ord_query = $MASHPIA_DB->prepare(
        'SELECT MAX( label_ord ) + 1 as label_ord FROM date_tasks dt '
        .'JOIN date_tasks_missions dtm USING (date_tasks_mission_id) '
        .'WHERE dtm.school_type_id = :school_type_id '
        .'AND dtm.level = :level AND dt.label_id = :label_id;'
    );
    $create_task_query = $MASHPIA_DB->prepare(
        'INSERT INTO date_tasks SET '
        .'cat = :cat, '
        .'name = :task, '
        .'needed = :needed, '
        .'grid_id = :grid_id, '
        .'daily_task = :daily, '
        .'label_id = :label_id, '
        .'label_ord = :label_ord, '
        .'short_name = :short_name, '
        .'grid_marking = :grid_marking, '
        .'mission_marking = :mission_marking, '
        .'date_tasks_mission_id = :mission_id, '
        // all custom tasks are .5 miles, optional and off by default
        .'points = 0.5, default_on = 0, mandatory_qty = 0, optional_qty = 1;'
    );

    // * generate the missions and tasks
    // uses manual transactions as class.defaults.php does NOT use PDO and will be apart from the PDO transaction.
    // Potenially causing database errors.
    $MASHPIA_DB->exec( 'START TRANSACTION;' );

    try {
        foreach( $school_types as $type ) { // each school type has its own mission
            foreach( $levels as $level ) { // as well as every level / grade

                // * get the label_ord for this level
                $find_label_ord_query->execute([
                    ':level' => $level,
                    ':label_id' => $label_id,
                    ':school_type_id' => $type
                ]);
                $row = $find_label_ord_query->fetch();
                $label_ord = $row['label_ord'] ? $row['label_ord'] : 1;

                // * create a task and potenitally a mission for each parsha
                foreach( $parshos as $parsha ) { // as well as each week / parsha
                    // see if we have the mission
                    $success = $find_mission_query->execute([
                        ':type' => $type,           ':level' => $level,
                        ':start' => $parsha->start, ':end' => $parsha->end,
                        ':name' => $parsha->name,   ':lang_id' => $lang_id,
                        ':school_id' => $school_id
                    ]);

                    if ( !$success )
                        throw new Exception( 'Failed to find mission' );
                    // if we do, get the mission_id
                    if ( $find_mission_query->rowCount() > 0 ) {
                        $mission_id = $find_mission_query->fetch()['id'];
                    // * If we do not have a mission, create one
                    } else {
                        $success = $create_mission_query->execute([
                            ':type' => $type,           ':level' => $level,
                            ':start' => $parsha->start, ':end' => $parsha->end,
                            ':name' => $parsha->name,   ':lang_id' => $lang_id,
                            ':school_id' => $school_id, ':subject_id' => $subject_id,
                        ]);
                        if ( !$success )
                            throw new Exception( 'Failed to create mission' );

                        $mission_id = $MASHPIA_DB->lastInsertId();
                    }

                    // enable the mission for the base or the specific platoons
                    if ( empty( $class_ids ) ) {
                        $d = new Defaults( $school_id, 'school' );
                        $d->addOn( $mission_id, 'mission' );
                    } else { // add it to each class
                        foreach( $class_ids as $class_id ) {
                            $d = new Defaults( $class_id, 'class' );
                            $d->addOn( $mission_id, 'mission' );
                        }
                    } // end if class_ids is not empty

                    $success = $create_task_query->execute([
                        ':cat' => $category,
                        ':task' => $task,
                        ':needed' => $daily ? 5 : 1,
                        ':grid_id' => $grid_id,
                        ':daily' => $daily ? 1 : 0,
                        ':label_id' => $label_id,
                        ':label_ord' => $label_ord,
                        ':mission_id' => $mission_id,
                        ':short_name' => $short_name,
                        ':grid_marking' => $grid_marking ? 1 : 0,
                        ':mission_marking' => $mission_marking ? 1 : 0,
                    ]);

                    if ( !$success )
                        throw new Exception( 'Failed to create task' );

                    $task_id = $MASHPIA_DB->lastInsertId();

                    // enable the mission for the base or the specific platoons
                    if ( empty( $class_ids ) ) {
                        $d = new Defaults( $school_id, 'school' );
                        $d->addOn( $task_id, 'task' );
                    } else { // add it to each class
                        foreach( $class_ids as $class_id ) {
                            $d = new Defaults( $class_id, 'class' );
                            $d->addOn( $task_id, 'task' );
                        }
                    } // end if class_ids is not empty

                } // end foreach parsha
            } // end foreach level
        } // end foreach type
    } catch( PDOExecption $e ) {
        $MASHPIA_DB->exec( 'ROLLBACK;' );
        throw $e;
    }

    $MASHPIA_DB->exec( 'COMMIT;' );
    
    return [
        'grid_id' => $grid_id,
        'lang_id' => $lang_id,
        'subject_name' => $subject->subject_name,
        'short_name' => $short_name,
        'name' => $task,
        'points' => 0.5,
        'cat' => $category,
        'mission_marking' => $mission_marking,
        'grid_marking' => $grid_marking,
        'label_name' => $label->label_name,
        'created_by_school' => $school_id,
        'school_name' => $school->school_name
    ];
}
