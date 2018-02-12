<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page
if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}

$mark_date = "2458076"; // the mark date of the tehillim tasks

echo "<pre>";
// get all the users with duplicate entries
$duplicate_sql = "SELECT date_task_id, subject_id, user_id, mark_date, grid_id, name, MAX(done_qty) as done_qty, quantity, COUNT(*) AS count "
    ."FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) "
    ."WHERE subject_id = 1 AND mark_date=$mark_date AND quantity IS NOT NULL "
    ."GROUP BY subject_id, user_id, mark_date, grid_id HAVING count > 1;";

$duplicate_query = mysql_query($duplicate_sql);
while($duplicate_row = mysql_fetch_assoc($duplicate_query)){
    // get the date_tasks that the user has
    $date_tasks_sql = "SELECT date_tasks_marks.*, dtm.level, dtm.track_id, user_tracks.level as user_level, user_tracks.track_id as user_track "
        ."FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions dtm USING (date_tasks_mission_id) JOIN user_tracks USING (user_id) "
        ."WHERE dtm.subject_id = 1 AND user_tracks.subject_id = 1 AND mark_date=$mark_date AND quantity IS NOT NULL AND user_id = " . $duplicate_row["user_id"] . " "
        ."AND grid_id = ".$duplicate_row["grid_id"]." GROUP BY date_task_id";
        
    $date_tasks_query = mysql_query($date_tasks_sql); // run the query
    
    // if they only have one date_tasks that means that there are genuine duplicate entries
    if(mysql_num_rows($date_tasks_query) == 1){
        //print_r($duplicate_row);
        // delete all the rows
        $delete_sql = "DELETE FROM date_tasks_marks WHERE date_task_id=".$duplicate_row['date_task_id']." AND user_id=".$duplicate_row['user_id']." AND mark_date=$mark_date;";
        // insert a row with the correct values
        $insert_sql = "INSERT INTO date_tasks_marks (date_task_id, user_id, mark_date, done_qty, mark_points) VALUES("
            .implode(", ", [$duplicate_row['date_task_id'], $duplicate_row['user_id'], "$mark_date", $duplicate_row['done_qty'], "0.50"])
            .");";
        //mysql_query($delete_sql);
        //mysql_query($insert_sql);
        echo $delete_sql."\n";
        echo $insert_sql."\n";
    } else if(mysql_num_rows($date_tasks_query) == 2) { // however if it is two rows that means that the levels where likely changed
        while($date_tasks_row = mysql_fetch_assoc($date_tasks_query)){ // get the date_task_id's that the user has
            // pull the date_task_id and user_id from the results
            $date_task_id = $date_tasks_row['date_task_id'];
            $user_id = $date_tasks_row['user_id'];
            // if the row has a different level/track then the current user
            if($date_tasks_row['level'] != $date_tasks_row['user_level'] || $date_tasks_row['track_id'] != $date_tasks_row['user_track']){
                // delete it
                $remove_duplicate_sql = "DELETE FROM date_tasks_marks WHERE date_task_id=$date_task_id AND user_id=$user_id AND mark_date=$mark_date;";
                //mysql_query($remove_duplicate_sql);
                echo "$remove_duplicate_sql\n";
            } else { // if the level and track are what we expect them to be
                if($duplicate_row["done_qty"] != $date_tasks_row['done_qty']){ // if the max quantity is lower then the one that we got (they had a higher mark on the bad row we deleted before)
                    $update_sql = "UPDATE date_tasks_marks SET done_qty=".$duplicate_row["done_qty"]." WHERE date_task_id=$date_task_id AND user_id=$user_id AND mark_date=$mark_date;"; // give them the higher mark
                    //mysql_query($update_sql);
                    echo "$update_sql\n"; // log the sql
                    
                    // TODO: PLEASE NOTE THAT THERE WHERE NO USERS THAT UPDATING THE QUANTITY GAVE THEM A MEDAL. THIS MAY NOT ALWAYS BE TRUE AND THEY MIGHT NEED TO BE AWARDED MISSIONS
                    //      IF THIS IS THE CASE THE REQUIRED AMOUNT IS AVALIABLE AS $duplicate_row['quantity'].
                }
            }
        }
    }
}
