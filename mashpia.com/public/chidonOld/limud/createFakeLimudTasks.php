<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

$year = GlobalSettings::getChidonRegYear();

function getMissionNumber() {
    global $subject_id;
    $sql = "SELECT 
                mission_number
            FROM
                date_tasks_missions
            WHERE
                subject_id = $subject_id
            ORDER BY mission_number DESC
            LIMIT 1";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    return intval($row['mission_number']);
}

function getJulianDate($heDate) {
    global $year;
    $params = explode(',', $heDate);
    $yy = $params[1] == 13 ? $year - 1 : $year;
    $jd = jewishtojd($params[1], $params[0], $yy);
    return $jd;
}

$grid_id = 20010;
$subject_id = 21;
$types = [2, 3];
$levels = [10, 11, 12, 13, 14]; // grades 4 - 8
$tracks = [
    1 => 'yesod',
    2 => 'yediah',
    3 => 'havonah',
    4 => 'iyun'
];
$minutes = [
    1 => 10,
    2 => 20,
    3 => 30,
    4 => 45
];

//$info = [];
//if (($handle = fopen('LimmudTasks5784.csv', "r")) !== false) {
//    $j = 0; // counter for rows
//    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
//        if ($j == 0) {
//            $j++;
//            continue;
//        }
//        $i = 0; // counter for columns
//        $info[$j]['start'] = $data[$i];
//        foreach ($levels as $level) {
//            $info[$j]['level_' . $level] = $data[++$i];
//        }
//        $j++;
//    }
//} else {
//    echo "Error opening file.";
//    exit;
//}

$i = 0;
$fakeTasks = [
    [22.1, '1', '46', '100', '157-158', '209'],
    [23.1, '2', '46', '101-102', '159-160', '209-210'],
    [24.1, '3', '47', '102-103', '160', '211-212'],
    [25.1, '4', '48', '103', '161-162', '212-213'],
    [26.1, '5', '49', '103-104', '163', '214-215'],
    [27.1, '6', '50', '105', '163-164', '216'],
    [28.1, '7', '51', '106', '165', '216']
];

$info = [];
foreach ($fakeTasks as $task) {
    $date = explode('.', $task[0]);
    $heDate = $date[0] . ',' . $date[1];
    $details['start'] = getJulianDate($heDate);
    foreach ($levels as $idx => $level) {
        $details['level_' . $level] = $task[$idx + 1];
    }
    $info[] = $details;
}

//echo "<pre>"; print_r($info); echo "</pre>"; exit;

$missionNumber = getMissionNumber() + 1;

mysql_query('set autocommit=0');
mysql_query('begin');

$success = true;
foreach ($info as $details) {
    $start = $details['start'];
    $end = $start;
    foreach ($types as $type) {
        foreach ($levels as $level) {
            if (! $details['level_' . $level]) continue;
            foreach ($tracks as $track => $desc) {
                $tasks = [
                    [
                        'name'  => "<i>Today's unit(s) are: " . $details['level_' . $level] . ".<br />You need to learn " . $minutes[$track] . " minutes per day.</i><br />I learned ___ minutes today.",
                        'qty'   => 120
                    ],
                    [
                        'name'  => "I am Up To Date",
                        'qty'   => 0
                    ]
                ];
                // find out hebrew date of mission
                $heDate = jdtojewish($start, true, CAL_JEWISH_ADD_GERESHAYIM);
                $heDateArr = explode(' ', iconv ('WINDOWS-1255', 'UTF-8', $heDate));
                $heDateInsertable = $heDateArr[0] . ' ' . $heDateArr[1] . ' - Chidon Limmud';
                $mission_name = addslashes($heDateInsertable);
                foreach ([1] as $lang) {
                    $sql = "insert into date_tasks_missions 
                        set school_type_id = $type, 
                        subject_id = $subject_id, 
                        level = $level, 
                        track_id = $track, 
                        mission_name = \"" . $mission_name . "\",   
                        mission_value = 1.0, 
                        mission_number = " . $missionNumber++ . ", 
                        mission_description = '', 
                        start_date = $start, 
                        end_date = $end, 
                        default_on = 1, 
                        lang_id = " . $lang;
                    echo $sql . "<br />";
                    if (! mysql_query($sql)) {
                        $success = false;
                        break 4;
                    } else {
                        $id = mysql_insert_id();
                        foreach ($tasks as $idx => $task) {
                            $grid = $grid_id + $idx;
                            $sql = "insert into date_tasks 
                                set date_tasks_mission_id = $id, 
                                name = \"" . addslashes($task['name']) . "\", 
                                cat = 'chidon limmud', 
                                cat_ord_new = $grid, 
                                points = 0.5, 
                                short_name = '" . ucwords($desc) . " Track',
                                mandatory_qty = 0, 
                                optional_qty = 1, 
                                daily_task = 0, 
                                label_id = 0, 
                                ord = 90, 
                                needed = 1, 
                                focus_task = 0, 
                                default_on = 0, 
                                label_ord = 90, 
                                description = '', 
                                grid_id = $grid, 
                                mission_marking = 1, 
                                grid_marking = 0, 
                                quantity = " . $task['qty'];
                            echo $sql . "<br />";
                            if (!mysql_query($sql)) {
                                $success = false;
                                break 5;
                            }
                        }
                    }
                }
            }
        }
    }
}

if ($success) {
    echo "done.";
    mysql_query('commit');
    mysql_query('set autocommit=1');
} else {
    echo "there were errors.";
    echo mysql_error();
    mysql_query('rollback');
    mysql_query('set autocommit=1');
}