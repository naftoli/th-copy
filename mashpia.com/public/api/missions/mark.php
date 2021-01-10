<?php
// define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

// ! LEGACY CODE, USES mysql_query
require_once( API_ROOT . '/../mission_report/classes/missions.php' );
require_once( API_ROOT . '/../yearly_prize/classes/TotalWeeklyTasks.php' );
require_once( API_ROOT . '/../classes/medal_updater.php' );
require_once( API_ROOT . '/../classes/rank_updater.php' );
require_once( API_ROOT . '/../class.globalSettings.php' );

class MarkRouter {

    public function get() {

        $soldier = \Soldier::find([ $_POST['user_id'] ]);
        $parsha = \Parsha::find([ $_POST['parsha_id'] ]);

        // * Generate the missions using the legacy code
        json_response( $soldier->missions( $parsha ), true, true );
    }

    public function create() {
        global $MASHPIA_DB;

        $medal_updater = new medal_updater();
        $rank_updater = new rank_updater();
        $soldiers_updated = [];

        // * Get the post paramaters.
        $user_id_string = [];
        $mark = $_POST['mark'];
        $dates = $_POST['dates'];
        $grid_id = $_POST['grid_id'];
        // escape user_ids
        foreach( $_POST['user_ids'] as $user_id )
            $user_ids[] = $MASHPIA_DB->quote( $user_id );
        $user_id_string = implode( ', ', $user_ids );

        // * Prepare All Queries
        // query to get the date_task_id from the grid id, mark date, and user_id
        $date_task_query = ' SELECT u.user_id, dt.date_task_id, dt.grid_id, '
            .' dt.points, dt.daily_task, dt.mandatory_qty, dt.needed, '
            .' dtm.date_tasks_mission_id, dtm.start_date, dtm.end_date, '
            .' dtm.subject_id, dtm.mission_value, dtm.mission_name '
            .' FROM date_tasks dt JOIN date_tasks_missions dtm USING (date_tasks_mission_id) '
            .' JOIN user_tracks ut ON ut.level = dtm.level AND ut.subject_id = dtm.subject_id '
            .' JOIN users u ON ut.user_id = u.user_id AND dtm.school_type_id = u.school_type_id AND dtm.lang_id = u.lang_id '
            .' WHERE grid_id = :grid_id AND ut.user_id IN ('.$user_id_string.') '
            .' AND start_date <= :mark_date AND end_date >= :mark_date ; ';
        $date_task_query = $MASHPIA_DB->prepare( $date_task_query );

        // TODO: design queries to prevent O(n) performance on dates.
        foreach( $dates as $mark_date ) {
            $query_array = [ 'grid_id' => $grid_id, 'mark_date' => $mark_date ];
            // get the date_task_id for each soldier
            $date_task_ids = [];    $date_task_query->execute( $query_array );
            while( $row = $date_task_query->fetch() )
                $date_task_ids[ $row['user_id'] ] = $row;
            
            foreach( $date_task_ids as $user_id => $user_task ) {
                // update the task
                $this->updateTask( $user_task, $mark, $mark_date );
                // update the mission
                $this->updateMission(
                    $user_task['user_id'],
                    $user_task['date_tasks_mission_id'],
                    $mark_date
                );
                // update the cache as each mission is marked
                TotalWeeklyTasks::updateUser( $user_task['user_id'], $mark_date, false );

                // if it's a tanya mark or mishna mark update the yud alef nissan tables
                $gridIds = [21001,21002,21003,21004,21005,21006,21007,21008,21013,21014];
                if (in_array($grid_id, $gridIds)) {
                    $year = GlobalSettings::getChidonYear();

                    // get campaigns for current year
                    $sql = "SELECT * FROM line_campaigns WHERE year = " . $year;
                    $result = mysql_query( $sql );
                    while ($row = mysql_fetch_assoc( $result )) {
                        if (strtolower($row['type']) == 'tanya') $tanyaCampaign = $row['id'];
                        else if (strtolower($row['type']) == 'mishna') $mishnaCampaign = $row['id'];
                    }

                    $campaign = $grid_id % 2 == 0 ? $mishnaCampaign : $tanyaCampaign; // mishna campaigns are even numbers
                    $campaign_qry = "SELECT mission_sheet_amount AS t FROM lines_learned WHERE campaign_id = " . $campaign . " AND user_id = " . $user_id;
                    $exists_query = mysql_query($campaign_qry);
                    if (mysql_num_rows($exists_query) > 0) {
                        if ( $mark > 0 ) {
                            $update_sql = "UPDATE lines_learned"
                                ." SET mission_sheet_amount = " . $mark
                                ." WHERE campaign_id = " . $campaign
                                ." AND user_id = " . $user_id;
                        } else {
                            $update_sql = "DELETE FROM lines_learned"
                                ." WHERE campaign_id = " . $campaign
                                ." AND user_id = " . $user_id;
                        }
                        mysql_query($update_sql);
                    } else {
                        $user_info_query = mysql_query("SELECT school_id, class_id FROM users WHERE user_id = " . $user_id);
                        $user_info = mysql_fetch_assoc($user_info_query);
                        $insert_sql = "INSERT INTO lines_learned SET "
                            ."campaign_id = " . $campaign . ", "
                            ."user_id = " . $user_id . ", "
                            ."mission_sheet_amount = " . $mark . ", "
                            ."school_id = " . $user_info['school_id'] . ", "
                            ."class_id = " . $user_info['class_id'];
                        mysql_query($insert_sql);
                    }
                }
            }
        }

        // * Update each soldiers medal and rank
        foreach( $_POST['user_ids'] as $user_id ) {
            $medal = $medal_updater->update_medal_two( $user_id );
            $rank = $rank_updater->update_rank_two( $user_id );
            
            $soldiers_updated[ $user_id ]['medal'] = $medal ? $medal : false;
            $soldiers_updated[ $user_id ]['rank'] = $rank ? $rank : false;
        }

        return json_response( $soldiers_updated, true, true );
    }

    private function updateTask( $user_task, $mark, $mark_date ) {
        global $MASHPIA_DB;

        //delete any existing marks for this grid id on this day
        $unmark_task_query = $MASHPIA_DB->prepare(
             ' DELETE dtm FROM date_tasks_marks dtm '
            .' JOIN date_tasks dt USING (date_task_id) '
            .' WHERE dt.grid_id = :grid_id '
            .' AND user_id = :user_id AND mark_date = :mark_date;'
        );
        // add the mark to the dbs if we need to
        $mark_task_query = $MASHPIA_DB->prepare(
            ' INSERT INTO date_tasks_marks '
           .' (date_task_id, user_id, mark_date, mark_points, done_qty) '
           .' VALUES ( :date_task_id, :user_id, :mark_date, :mark_points, :done_qty )'
           .' ON DUPLICATE KEY UPDATE done_qty = :done_qty;'
        );
        // validate the data type of done_qty
        $done_qty = $mark;
        if ( $user_task['daily_task'] )
            $done_qty = $done_qty ? '1' : '0';

        // delete any existing marks
        $unmark_task_query->execute([
            ':grid_id' => $user_task['grid_id'], 
            ':user_id' => $user_task['user_id'], 
            ':mark_date' => $mark_date,
        ]);

        // save the mark to the dbs if we have a mark to mark
        if ( $mark ) {
            $mark_task_query->execute([
                ':date_task_id' => $user_task['date_task_id'], 
                ':user_id' => $user_task['user_id'], 
                ':mark_date' => $mark_date,
                ':mark_points' => $user_task['points'],
                ':done_qty' => $done_qty
            ]);
        }
    }

    // * Check if the mission was compleated and update it accordingly
    private function updateMission( $user_id, $date_tasks_mission_id, $mark_date ) {
        global $MASHPIA_DB;
        // arguments for all queries
        $args = [ ':user_id' => $user_id, ':date_tasks_mission_id' => $date_tasks_mission_id ];
        // SQL to check if tasks where compleated
        $non_daily_check = $MASHPIA_DB->prepare(
             ' SELECT dt.* FROM date_tasks AS dt '
            .' LEFT JOIN date_tasks_marks AS dtm ON ( '
                .' dt.date_task_id = dtm.date_task_id '
                .' AND dtm.user_id = :user_id '
            .') WHERE dt.date_tasks_mission_id = :date_tasks_mission_id '
            .' AND dt.mandatory_qty = 1 AND dt.daily_task = 0 '
            .' AND (dtm.date_task_id IS NULL OR dtm.done_qty < dt.quantity)'
        );
        // check for any unfinished daily tasks
        $daily_check = $MASHPIA_DB->prepare(
             ' SELECT dt.needed, IFNULL(total, 0) as total '
            .'FROM date_tasks AS dt LEFT JOIN ( '
                .' SELECT date_task_id, COUNT(*) AS total '
                .' FROM date_tasks_marks WHERE user_id = :user_id '
                .' GROUP BY date_task_id'
            .' ) marks USING (date_task_id) '
            .' WHERE dt.date_tasks_mission_id = :date_tasks_mission_id '
            .' AND daily_task = 1 AND mandatory_qty = 1 '
            .' HAVING dt.needed > total;'
        );
        // query to mark single missions
        $mark_mission_query = $MASHPIA_DB->prepare(
             ' INSERT IGNORE INTO date_tasks_mission_marks '
            .' (user_id, date_tasks_mission_id, subject_id, mission_value, mission_name, mark_date)'
            .' SELECT :user_id as user_id, date_tasks_mission_id, subject_id, mission_value, mission_name, '
            .' :mark_date as mark_date FROM date_tasks_missions '
            .' WHERE date_tasks_mission_id = :date_tasks_mission_id;'
        );
        // unmark a single mission
        $unmark_mission_query = $MASHPIA_DB->prepare(
            ' DELETE FROM date_tasks_mission_marks '
            .' WHERE date_tasks_mission_id = :date_tasks_mission_id '
            .' AND user_id = :user_id;'
        );
        // * Logic for checking missions, see SQL above for details
        $daily_check->execute( $args );
        $non_daily_check->execute( $args );
        
        if ( $non_daily_check->rowCount() > 0 || $daily_check->rowCount() > 0 )
            $unmark_mission_query->execute( $args );
        else 
            $mark_mission_query->execute( array_merge( 
                $args, [ ':mark_date' => $mark_date ] 
            ));
    }
}

rest_router( new MarkRouter );

die();