<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$subject_id = 27;

$sql = "select mission_number from date_tasks_missions where subject_id = $subject_id order by mission_number desc limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$mission_number = $row['mission_number'] + 1;

$ids = [
    'Learn line'    => 21013,
    'Review'        => 21015
];

$pics = [
    'Learn line'    => '14New-lines-Tanya',
    'Review'        => '6Chazorah-Tanya'
];

$ladders = [];
$date_types = [];
if (($handle = fopen("ladders.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        $i = 0; // index into data array
        $type = $data[$i++]; // if it's new lines or reviewing existing lines
        $start_date = $data[$i++];
        if (!is_numeric($start_date)) continue;
        $date_types[$start_date] = $type;
        while (isset($data[$i])) {
            for ($j = 0; $j < 3; $j++) {
                switch ($j) {
                    case 0:
                        $ladders[$start_date]['age'][] = $data[$i++];
                        break;
                    case 1:
                        $ladders[$start_date]['ladder'][] = $data[$i++];
                        break;
                    case 2:
                        $ladders[$start_date]['quota'][] = $data[$i++];
                        break;
                }
            }
        }
    }
}
echo "<pre>";
//print_r($date_types);
//print_r($ladders);
echo "</pre>";

$school_types = [2,3,12,13];
foreach ($ladders as $start_date => $missions) {
    for ($i = 0; $i < 40; $i++) {
        foreach ($school_types as $type) {
            $mission_qry = "select date_tasks_mission_id from date_tasks_missions 
                            where subject_id = " . $subject_id . "  
                            and school_type_id = " . $type . " 
                            and start_date = " . $start_date . " 
                            and level = " . $missions['age'][$i] . " 
                            and track_id = " . $missions['ladder'][$i] . " 
                            and lang_id = 1";
            echo $mission_qry . "<br />";
            $mission_res = mysql_query($mission_qry);
            if (mysql_num_rows($mission_res) > 0) {
                $mission_id = mysql_fetch_assoc($mission_res)['date_tasks_mission_id'];
            } else {
                $mission_qry = "insert into date_tasks_missions
                                set subject_id = 27,
                                mission_value = 1.0,
                                mission_number = " . ($mission_number++) . ",
                                mission_name = 'תניא בעל פה',
                                mission_description = 'תניא בעל פה',
                                school_type_id = " . $type . ",
                                start_date = " . $start_date . ",
                                end_date = " . ($start_date + 6) . ",
                                default_on = " . (in_array($type, [2,3]) ? 1 : 0) . ",
                                level = " . $missions['age'][$i] . ",
                                track_id = " . $missions['ladder'][$i] . ",
                                lang_id = 1";
                if (mysql_query($mission_qry)) {
                    $mission_id = mysql_insert_id();
                }
            }
//            $mission_id = 1111;
            if ($mission_id) {
                $id = $ids[$date_types[$start_date]];
                $pic = $pics[$date_types[$start_date]];
                if ($date_types[$start_date] == 'Learn line') {
                    $name = "My weekly quota is to learn lines " . $missions['quota'][$i] . " of תניא בעל פה. Enter the highest line number you learned by heart this week.";
                } else if ($date_types[$start_date] == 'Review') {
                    $name = "My weekly quota is to review lines " . $missions['quota'][$i] . " of תניא בעל פה. Enter the number of lines you reviewed.";
                }
                $qty = $missions['quota'][$i];
                if (strpos($qty, '-') !== false) {
                    $quota = explode('-', $qty);
                    $qty = $quota[1];
                }
                $task_qry = "insert into date_tasks 
                            set date_tasks_mission_id = " . $mission_id . ", 
                            name = '" . $name . "', 
                            cat_ord_new = $id, 
                            grid_id = $id, 
                            short_name = 'Weekly Tanya Quota', 
                            mandatory_qty = 0, 
                            optional_qty = 1, 
                            daily_task = 0, 
                            label_id = 33, 
                            needed = 1, 
                            focus_task = 0, 
                            default_on = " . (in_array($type, [2, 3]) ? 1 : 0) . ", 
                            label_ord = 4, 
                            quantity = " . $qty . ",
                            description = '" . $missions['quota'][$i] . "', 
                            medium_pic = '" . $pic . "', 
                            mission_marking = 1, 
                            grid_marking = 1";
                echo $task_qry . "<br /><br />";
            }
        }
    }
}

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
    foreach ($school_types as $type) {
        foreach ($ladders as $ladder) {
            foreach ($years as $year) {

            }
        }
    }
}