<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$created = 0;

$subject_id = 27;

$sql = "select mission_number from date_tasks_missions where subject_id = $subject_id order by mission_number desc limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$mission_number = $row['mission_number'] + 1;

$id = 21001;
$pic = '6Chazorah-Tanya';

$school_types = [
    1 => [2, 3, 12, 13],
    2 => [2, 3]
];

$ladders = [];
for ($i = 1; $i <= 5; $i++) {
    $ladders[] = $i;
}

$years = [];
for ($i = 6; $i <= 14; $i++) {
    $years[] = $i;
}

// create monthly task
for ($i = 1; $i <= 13; $i++) {
    $jd = jewishtojd($i, 1, 5781);
    if ($jd < 2459251) continue; // only upload tasks after feb 5, 2021

    // figure out which day of week it is in order to find out the quota they have by checking the previous week's tanya mission
    $day_of_week = jddayofweek($jd);
    $start_date = $jd - 7;
    if (in_array($day_of_week, [0,1,2,3,4])) $start_date -= ($day_of_week + 2);
    else if ($day_of_week == 6) $start_date -= 1;

    foreach ($school_types as $lang => $types) {
        foreach ($types as $type) {
            foreach ($ladders as $ladder) {
                foreach ($years as $year) {
                    // find out tanya quota for child
                    $quota_qry = "select quantity from date_tasks dt 
                                join date_tasks_missions dtm using (date_tasks_mission_id) 
                                where subject_id = 27 
                                and school_type_id = " . $type . " 
                                and level = " . $year . " 
                                and track_id = " . $ladder . " 
                                and lang_id = " . $lang . " 
                                and start_date = " . $start_date. " 
                                and grid_id in (21013, 21015)";
//                    echo $quota_qry . "<br />";
                    $quota_res = mysql_query($quota_qry);
                    if (mysql_num_rows($quota_res)) {
                        $quota_row = mysql_fetch_assoc($quota_res);
                        $qty = $quota_row['quantity'];
                    }
                    if ($qty) {
                        // figure out task
                        if ($lang == 1) {
                            $short_name = 'Tanya Testing';
                            $task = "By now, you should know lines 1 - " . $qty . " of תניא בעל פה. Enter the total amount of lines that you have been tested on (by a parent or teacher).";
                        } else if ($lang == 2) {
                            $short_name = 'מבחן תניא';
                            $task = "ביז יעצט, דו דארפסט שוין וויסן פון שורה א' -  " . $qty . " פון תניא בעל פה. שרייב אויף וויפיל שורות ביסטו שוין פארהערט געווארן (דורך אן עלטערן אדער מלמד(ת))";
                        }
                        $mission_qry = "insert into date_tasks_missions
                                        set subject_id = 27,
                                        mission_value = 1.0,
                                        mission_number = " . ($mission_number++) . ",
                                        mission_name = 'תניא בעל פה',
                                        mission_description = 'תניא בעל פה',
                                        school_type_id = " . $type . ",
                                        start_date = " . $jd . ",
                                        end_date = " . $jd . ",
                                        default_on = " . (in_array($type, [2, 3]) ? 1 : 0) . ",
                                        level = " . $year . ",
                                        track_id = " . $ladder . ",
                                        lang_id = " . $lang;
                        echo $mission_qry . "<br />";
                        if (mysql_query($mission_qry)) {
                            $mission_id = mysql_insert_id();
//                            $mission_id = 9999;
                            $task_qry = "insert into date_tasks 
                                        set date_tasks_mission_id = " . $mission_id . ", 
                                        name = '" . $task . "', 
                                        cat_ord_new = $id, 
                                        grid_id = $id, 
                                        short_name = '" . $short_name . "', 
                                        mandatory_qty = 0, 
                                        optional_qty = 1, 
                                        daily_task = 0, 
                                        label_id = 33, 
                                        needed = 1, 
                                        focus_task = 0, 
                                        default_on = " . (in_array($type, [2, 3]) ? 1 : 0) . ", 
                                        label_ord = 4, 
                                        quantity = " . $qty . ",
                                        medium_pic = '" . $pic . "', 
                                        mission_marking = 1, 
                                        grid_marking = 1";
//                            echo $task_qry . "<br /><br />";
                            if (mysql_query($task_qry)) $created++;
                        }
                    }
                }
            }
        }
    }
}
echo "Created: " . $created . " tasks.";