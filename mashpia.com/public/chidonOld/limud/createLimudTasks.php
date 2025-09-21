<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

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

// Get limud task information from database instead of CSV
$info = [];

// Query to get all limud book units for the current year, grouped by date
$sql = "SELECT 
            date,
            day,
            book,
            GROUP_CONCAT(unit ORDER BY unit SEPARATOR ',') as units
        FROM limud_book_units 
        WHERE year = $year 
        GROUP BY date, day, book 
        ORDER BY day, book";

$result = mysql_query($sql);
if (!$result) {
    echo "Error querying database: " . mysql_error();
    exit;
}

// Process the database results to match the expected structure
$dateData = [];
while ($row = mysql_fetch_assoc($result)) {
    $date = $row['date'];
    $book = $row['book'];
    $units = $row['units'];
    
    // Convert date to Julian date for consistency with existing code
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if ($dateObj) {
        $julianDate = gregoriantojd($dateObj->format('m'), $dateObj->format('d'), $dateObj->format('Y'));
        
        if (!isset($dateData[$date])) {
            $dateData[$date] = [
                'start' => $julianDate,
                'day' => $row['day']
            ];
            // Initialize all levels to empty
            foreach ($levels as $level) {
                $dateData[$date]['level_' . $level] = '';
            }
        }
        
        // Map book numbers to grade levels (assuming book 1-5 maps to levels 10-14)
        $levelIndex = $book + 9; // book 1 -> level 10, book 2 -> level 11, etc.
        if (in_array($levelIndex, $levels)) {
            $dateData[$date]['level_' . $levelIndex] = $units;
        }
    }
}

// Convert associative array to indexed array to match original structure
$j = 1;
foreach ($dateData as $date => $data) {
    $info[$j] = $data;
    $j++;
}

if (empty($info)) {
    echo "No limud task data found in database for year $year.";
    exit;
}

//echo "<pre>"; print_r($info); echo "</pre>"; exit;

$missionNumber = getMissionNumber() + 1;

mysql_query('set autocommit=0');
mysql_query('begin');

$success = true;
foreach ($info as $details) {
    $start = $details['start']; // Already converted to Julian date in the database query processing
    $end = $start;
    foreach ($types as $type) {
        foreach ($levels as $level) {
            if (! $details['level_' . $level]) continue;
            foreach ($tracks as $track => $desc) {
                $units = $details['level_' . $level];
                // audio links 
                $individualUnits = explode(',', $units);
                $audioLinks = [];
                foreach ($individualUnits as $unit) {
                    $audioLinks[] = "<a href=# data-audio=" . $unit . " onclick=showAudioPlayer(this)>Unit " . $unit . "</a>";
                }
                $tasks = [
                    [
                        'name'  => "Today's unit(s) are: <b>" . $units . "</b>. <div class='audioLinks'>Audio Links: " . implode(', ', $audioLinks) . "</div><br /><i>You need to learn " . $minutes[$track] . " minutes per day.</i><br />How many minutes did you learn today?",
                        'qty'   => 120
                    ],
                    [
                        'name'  => "I am Up To Date"
                    ]
                ];
                // find out hebrew date of mission
                $heDate = jdtojewish($start, true, CAL_JEWISH_ADD_GERESHAYIM);
                $heDateArr = explode(' ', iconv ('WINDOWS-1255', 'UTF-8', $heDate));
                $heDateInsertable = $heDateArr[0] . ' ' . $heDateArr[1] . ' - Chidon Limmud';
                $mission_name = addslashes($heDateInsertable);
                foreach ([1, 2] as $lang) {
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
                                default_on = 1, 
                                label_ord = 90, 
                                description = '', 
                                grid_id = $grid, 
                                mission_marking = 1, 
                                grid_marking = 0";
                            if ($task['qty'] > 0) {
                                $sql .= ", quantity = " . $task['qty'];
                            }
                            // echo $sql; continue;
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
// $success = false;

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