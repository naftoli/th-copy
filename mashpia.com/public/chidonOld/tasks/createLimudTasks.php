<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

function getMissionNumber() {
    global $subject_id;
    $sql = "select mission_number as number from date_tasks_missions where subject_id = " . $subject_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return intval($row['mission_number']);
}

function getJulianDate($heDate) {
    $params = explode(',', $heDate);
    $jd = jewishtojd($params[0], $params[1], 5782);
    return $jd;
}

$grid_id = 20010;
$subject_id = 21;
$types = [2, 3];
$levels = [4,5,6,7,8];

$tasks = [
    1   =>  [
        'task'      => 'I learned my quota for the chidon limmud program',
        'short'     => 'chidon limmud',
        'mission'   => 'chidon limmud',
        'desc'      => 'chidon limmud',
    ],
    2   =>  [
        'task'      => '',
        'short'     => '',
        'mission'   => '',
        'desc'      => '',
    ]
];

$info = [];
if (($handle = fopen('limmudTasks.csv', "r")) !== false) {
    $j = 0; // counter for rows
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        if ($j == 0) {
            $j++;
            continue;
        }
        $i = 0; // counter for columns
        $info[$j]['start'] = $data[$i];
        foreach ($levels as $level) {
            $info[$j]['level_' . $level] = $data[++$i];
        }
        $j++;
    }
} else {
    echo "Error opening file.";
}

$missionNumber = getMissionNumber() + 1;

$qrys = [];
foreach ($info as $details) {
    $start = getJulianDate($details['start']);
    $end = $start;
    foreach ($types as $type) {
        foreach ($levels as $level) {
            $sql = "insert into date_tasks_missions 
                    set school_type_id = $type, 
                    subject_id = $subject_id, 
                    level = $level, 
                    track_id = 1, 
                    mission_name = '',   
                    mission_value = 1.0, 
                    mission_number = " . $missionNumber++ . ", 
                    mission_description = '', 
                    start_date = $start, 
                    end_date = $end, 
                    default_on = 1, 
                    lang_id = 1";
            $qrys[] = $sql;
        }
    }
}


/*
$sql = "insert into date_tasks
        set date_tasks_mission_id = $id, 
        name = '" . $taskInfo['name'] . "', 
        cat = '" . $taskInfo['cat'] . "',
        cat_ord_new = " . $taskInfo['cat_ord'] . ", 
        points = 0.5, 
        short_name = '" . $taskInfo['short'] . "', 
        mandatory_qty = $mand, 
        optional_qty = $opt, 
        daily_task = 0, 
        label_id = 37, 
        ord = " . $taskInfo['ord'] . ", 
        needed = 1,
        focus_task = 0,
        default_on = 1, 
        label_ord = 2,  
        description = '" . $desc . "',
        grid_id = " . $taskInfo['grid_id'] . ",
        mission_marking = " . $taskInfo['mission_marking'] . ",
        grid_marking = " . $taskInfo['grid_marking'];
if ($qty) {
    $sql .= ", quantity = $qty";
}
*/