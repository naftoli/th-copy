<?php
require 'db.php';
$data = array();
$users = array();
$sql = "SELECT u.user_id, dt.done_qty AS total
                FROM date_tasks_marks dt
                JOIN date_tasks
                USING ( date_task_id )
                JOIN date_tasks_missions dtm
                USING ( date_tasks_mission_id )
                JOIN users u 
                USING ( user_id ) 
                JOIN user_tracks ut
                USING ( user_id, subject_id, track_id, level ) 
                JOIN schools s 
                USING (school_id) 
                JOIN classes c 
                USING (class_id) 
                WHERE dtm.subject_id =1
                AND dtm.start_date = 2457775 
                AND dtm.end_date = 2457775  
                AND date_tasks.grid_id = 64  
                AND dtm.school_type_id = u.school_type_id
                AND u.user_registered >0 
                AND ut.enrolled =1 
                AND s.school_era is null 
                AND c.class_era = 0
				AND u.user_id = ut.user_id
				AND u.lang_id = dtm.lang_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $data[] = $row;
    if (array_key_exists($row['user_id'], $users)) {
        $users[$row['user_id']]++;
    } else {
        $users[$row['user_id']] = 1;
    }
}

echo "<pre>"; print_r($users); echo "</pre>";