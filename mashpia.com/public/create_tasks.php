<?php
/*
 *      create_tasks.php
 *
 *  Excepts Excel sheet and inserts it into the `date_task_missions` and `date_task` tables
 *
 *  For details on data input please see excel sheet at ./SystemTasks/TasksTemplate.xlsx
 *  
 */

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = array('school');
require_once 'header.php';

if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}

$sql1 = "select subject_id, subject_name from subjects s 
        join school_type_subjects sts using (subject_id) 
        where s.subject_type in ('', 'Tanya') 
        and sts.school_type_id in (2,3,12,13) 
        and s.subject_id not in (1, 91)
        group by s.subject_id 
        order by s.subject_id";
$result1 = mysql_query($sql1);
$campaigns = array();
while ($row1 = mysql_fetch_assoc($result1)) {
    $campaigns[$row1['subject_id']] = $row1['subject_name'];
}

function getStartEnd($arr)
{
    $temp = array();
    for ($i = 0; $i < count($arr); $i++) {
        if ($i % 2 == 0) {
            $temp['start'][] = $arr[$i];
        } else {
            $temp['end'][] = $arr[$i];
        }
    }
    return $temp;
}

function createStartArray($val, $subject_id)
{
    // these tasks are a colon separated list of dates that are both the start date and end date of that task
    global $missionYear, $arrStart, $arrEnd;
    $arrValues = explode(':', $val);
    foreach ($arrValues as $value) {
        $arrTemp = explode(',', $value);
        $year = $arrTemp[0] == 13 ? ($missionYear - 1) : $missionYear;
        //$year = $missionYear;
        // for some reason this function is returning a jd date that is 1 day greater then the actual date
        $jd = (jewishtojd($arrTemp[0], $arrTemp[1], $year) - 1);
        echo $jd; exit;
        $arrStart[] = $jd;
        if (in_array($subject_id, [12, 27, 41])) { // end date is 6 days later (Mivtzoim / Tanya / Avos Ubanim)
            $arrEnd[] = $jd + 6;
        } else { // end date is same as start date
            $arrEnd[] = $jd;
        }
    }
}

?>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <link href="admin_styles.css" rel="stylesheet" type="text/css">
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script type='text/javascript'>
    $(function () {
      $("#submit").click(function () {
        if ($("#subject").val() == 0) {
          alert("You have not chosen a campaign!");
          return false;
        }
        if ($("#file").val() == '') {
          alert("You have not uploaded a file!");
          return false;
        }
      });
    });
  </script>
  <style>
    td {
      font-size: 12px;
      font-family: Arial, Helvetica, sans-serif;
      border: 1px solid black;
    }

    #task_form, #files {
      font-size: 14px;
    }

    #files {
      line-height: 1.6;
    }

    #instructions {
      font-size: 16px;
      line-height: 1.4;
    }
  </style>
</head>

<body>
<? require 'admin_header.php'; ?>
<h1>Upload Tasks</h1>
<?
if (isset($_POST['submit'])) {
    require_once 'PHPExcel/IOFactory.php';

    $subject_id = $_POST['subject'];
    $subjects = array(
        4 => "Tefillah",
        12 => "Mivtzoim",
        13 => "Niggunim",
        15 => "Hakhel",
        16 => "Hiskashrus",
        21 => "SeferHamitzvos",
        27 => "TanyaBalPeh",
        40 => "YomaDepagra",
        41 => "AvosUbanim",
        42 => "Viholachto",
        45 => "Cheshbon",
        90 => "Chitas",
        92 => "JewishSongs",
        93 => "AssistingOtherJews",
        94 => "YomTov",
        100 => "BriasHaguf",
        101 => "MishnaBalPeh"
    );

    // there is null data in the database under mission_number
    $sql = "select mission_number from date_tasks_missions where subject_id = $subject_id order by mission_number desc limit 1";
    //echo $sql;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $missionNumber = ceil($row['mission_number'] + 1);
    } else {
        $missionNumber = 1;
    }
    //echo $missionNumber;

    // get start and end from db
    require_once 'class.globalSettings.php';
    $missionYear = GlobalSettings::getRegistrationYear();

    $defaultStart = 2460203;
    $defaultEnd = 2460580;

    $weeks = array();
    $sql2 = 'select * from parshos where year in(' . ($missionYear - 1) . ',' . $missionYear . ')';
    $result2 = mysql_query($sql2);
    while ($row2 = mysql_fetch_assoc($result2)) {
        $weeks[$row2['start']][$row2['end']] = $row2['name'];
    }
//    echo "<pre>"; print_r( $weeks ); echo "</pre>";
//    exit;

    $langSheet = $_POST['lang'];
    if ($langSheet == 1) {
        $file = "SystemTasks/" . $subjects[$subject_id] . $missionYear . ".xlsx";
    } else if ($langSheet == 2) {
        $file = "SystemTasks/Yi" . $subjects[$subject_id] . $missionYear . ".xlsx";
    }

    $arrMandatory = array();
    $arrFocus = array();
    $arrStart = array();
    $arrEnd = array();
    $missionName = "";

    // load the file and save it to the database
    if (file_exists($_FILES['tasks']['tmp_name'])) {
        if (move_uploaded_file($_FILES['tasks']['tmp_name'], $file)) {
            $track = 1;

            //load spreadsheet
            $objPHPExcel = PHPExcel_IOFactory::load($file);
            $objWorksheet = $objPHPExcel->getActiveSheet();

            $missions = array();
            $firstRow = true;
            $fieldNames = array(
                'action',
                'missionValue',
                'start',
                'end',
                'firstLevel',
                'lastLevel',
                'catOrd',
                'mission_marking',
                'grid_marking',
                'qty',
                'mandatory',
                'daily',
                'needed',
                'focus',
                'points',
                'default',
                'labelOrd',
                'pic_boys',
                'pic_girls',
                'types',
                'lang',
                'missionName',
                'shortName',
                'task',
                'cat',
                'labelID'
            );

            foreach ($objWorksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $i = 0;

                if ($firstRow) {
                    $firstRow = false;
                    continue;
                }

                foreach ($cellIterator as $cell) {
                    $val = trim($cell->getValue());
                    //echo $val . "<br />"; continue;
                    switch ($i) { //$i represents the column in the row
                        case 0:
                            // set the action to add no matter what
                            ${$fieldNames[$i]} = 'add';
                            break;
                        case 1:
                            // skip row if it's empty
                            if (empty($val)) {
                                continue 2;
                            }
                            ${$fieldNames[$i]} = $val;
                            break;
                        // Start Date
                        case 2:
                            if (strpos($val, ':') !== false) {
                                createStartArray($val, $subject_id);
                                $startDate = $arrStart[0];
                            } else {
                                if (strpos($val, ',') !== false) {
                                    $arrTemp = explode(',', $val);
                                    $year = in_array($arrTemp[0], array(12, 13)) ? ($missionYear - 1) : $missionYear;
                                    $startDate = jewishtojd($arrTemp[0], $arrTemp[1], $year);
                                    //echo "Start : " . $val . " = " . $startDate . "<br />";
                                    $arrStart[] = $startDate;
                                } else {
                                    if (empty($val)) {
                                        $startDate = $defaultStart;
                                        $endDate = $defaultEnd;
                                    } else {
                                        $startDate = $val;
                                    }
                                }
                            }
                            break;
                        // End Date
                        case 3:
                            if (strpos($val, ',') !== false) {
                                $arrTemp = explode(',', $val);
                                $year = in_array($arrTemp[0], array(12, 13)) ? ($missionYear - 1) : $missionYear;
                                $endDate = jewishtojd($arrTemp[0], $arrTemp[1], $year);
                                //echo "End : " . $val . " = " . $endDate  ."<br />";

                                // check that end date is in same week as start date
                                if ($endDate > $startDate) {
                                    $temp = $startDate;
                                    // if start date is thursday and end date is after thurs
                                    if (jddayofweek($temp) == 4) {
                                        $arrEnd[] = $temp;
                                        $arrStart[] = ++$temp;
                                    } else {
                                        while (jddayofweek(++$temp) != 4) {
                                        }
                                    }
                                    while ($temp < $endDate) {
                                        // create new mission
                                        $arrEnd[] = $temp;
                                        $arrStart[] = ++$temp;
                                        $temp += 6;
                                    }
                                }
                                $arrEnd[] = $endDate;
                            } else {
                                if (!empty($val)) {
                                    $endDate = $val;
                                }
                            }
                            break;
                        // Task Category order
                        case 6:
                            $catOrd = (int)$val;
                            break;
                        // Mission marking, Grid Marking, Quantity, Daily, Needed all default to 0 if left blank
                        case 7:
                        case 8:
                        case 9:
                        case 11:
                        case 12:
                            if ($val == '') {
                                $val = 0;
                            }
                            ${$fieldNames[$i]} = $val;
                            break;
                        // mandatory: set $arrMandatory if contains ",". otherwise set it to $mandatory
                        case 10:
                            if (strpos($val, ',')) {
                                $arrMandatory = explode(',', $val);
                            } else {
                                $mandatory = (int)$val;
                            }
                            break;
                        // focus: set $arrFocus if contains ",". otherwise set it to $focus
                        case 13:
                            if (strpos($val, ',')) {
                                $arrFocus = explode(',', $val);
                            } else {
                                $focus = (int)$val;
                            }
                            break;
                        // school types
                        case 19:
                            $types = explode(',', $val);
                            break;
                        // set the language to one by default or the input if it was a valid number greater then 1
                        case 20:
                            $lang = 1;
                            if (is_numeric($val) && $val > 1) {
                                $lang = intval($val);
                            }
                            break;
                        // Add a period to the task name
                        case 23:
                            $task = $val;
                            if (!strpos($task, '.'))
                                $task .= ".";
                            break;
                        // turn the label id into an int for the forgin key relationship
                        case 25:
                            $labelID = empty($val) ? 0 : intval($val);
                            break;
                        // For rows 1, 4, 5, 14, 15, 16, 17, 18, 21, 22, and 24
                        // create a variable from the feildnames and set it to the value (some nice meta programming here)
                        default:
                            ${$fieldNames[$i]} = $val; // Metaprogram the variable name to the value without hard coding it. I like :-)
                            break;
                    }
                    // stop if we hit the last line
                    // incrament i before the check since count(x) starts at 1.
                    if (++$i == count($fieldNames)) {
                        break;
                    }
                }
                //continue;
                // make sure we don't have incorrect yiddish label for english task or vice versa
                if ($lang == 1) {
                    if ($labelID > 49) $labelID -= 20;
                } else if ($lang == 2) {
                    if ($labelID > 29 && $labelID < 50) $labelID += 20;
                }
                /*
                // set the start date from the array if it is full and $startDate is empty
                if (!empty($arrStart) && empty($startDate)) {
                    $year = $arrStart[0] == 13 ? 5777 : 5778;
                    $startDate = jewishtojd($arrStart[0], $arrStart[1], $year);
                    $year = $arrEnd[0] == 13 ? 5777 : 5778;
                    $endDate = jewishtojd($arrEnd[0], $arrEnd[1], $year);
                }
                */
//                echo $startDate . "<br />";
//                echo $endDate . "<br />";
//				continue;

                //make sure start and end date is greater than or equal to today
                // also need to make sure start and end date is from friday to thursday if changing see commented out below at end of loop
//                $today = unixtojd() + 1;
//                $day = date("w");
//                if ($day != 5) {
//                    if ($day < 5) {
//                       $today -= (2 + $day);
//                    } else if ($day == 6) {
//                       $today -= 1;
//                    }
//                }
//                if ($startDate < $today) {
//                    $startDate = $today;
//                }
//                if ($endDate < $today) {
//                    $endDate = $today;
//                }

                // if no dates were entered create array based on default start and end dates
                if (empty($arrStart)) {
                    $startTemp = $startDate;
                    $endTemp = $endDate;
                    while ($startTemp < $endTemp) {
                        $arrStart[] = $startTemp;
                        $arrEnd[] = $startTemp + 6;
                        $startTemp += 7;
                    }
                }
//                echo "<pre>"; print_r( $arrStart ); echo "</pre>";

                // This takes the array of start dates and creates new missions based on that
                $num = count($arrStart);
                for ($k = 0; $k < $num; $k++) { //index into $arrStart array
                    $startDate = $arrStart[$k];
                    $endDate = $arrEnd[$k];
                    $start = $startDate;
                    $end = $endDate;
//					        echo $start . '-' . $end . "<br /><br />"; continue;
//                  echo 'Start: ' . $start . ' Today: ' . unixtojd() . "<br />";
//                    if ($start <= unixtojd()) continue;

                    //while ($start <= $end) {
                    foreach ($types as $type) {
                        $mission = $missionName;
                        if (empty($mission)) {
                            //echo $start . "<br />";
                            //echo $end . "<br />";
                            $mission = (array_key_exists($end, $weeks[$start]) ? $weeks[$start][$end] : end($weeks[$start]));
                        }
                        //check if there's an array of dates for mandatory or focus
                        if (!empty($arrMandatory)) {
                            $mandatory = 0;
                            $mand = getStartEnd($arrMandatory);
                            for ($c = 0; $c < count($mand['start']); $c++) {
                                if ($start >= $mand['start'][$c] && $end <= $mand['end'][$c]) {
                                    $mandatory = 1;
                                }
                            }
                        }
//                            if (!empty($arrFocus)) {
//                                $focus = 0;
//                                $f = getStartEnd($arrFocus);
//                                for ($c = 0; $c < count($f['start']); $c++) {
//                                    if ($start >= $f['start'][$c] && $end <= $f['end'][$c]) {
//                                        $focus = 1;
//                                    }
//                                }
//                            }
                        $focus = 0;
                        if (is_numeric($type) && $type % 2 == 0) {
                            $pic = $pic_boys;
                        } else {
                            $pic = $pic_girls;
                        }

                        for ($level = $firstLevel; $level <= $lastLevel; $level++) {
                            //echo $task . "<br />" . $start . ' - ' . $end . ' T: ' . $type . ' L: ' . $level . "<br />";
                            $missions[$action][$type][$level][$missionName][$missionValue][$start][$end][$lang][] = array(
                                'task' => $task,
                                'catOrd' => $catOrd,
                                'cat' => $cat,
                                'qty' => $qty,
                                'points' => $points,
                                'mand' => $mandatory,
                                'focus' => $focus,
                                'label' => $labelID,
                                'labelOrd' => $labelOrd,
                                'daily' => $daily,
                                'needed' => $needed,
                                'def' => $default,
                                'short_name' => $shortName,
                                'pic' => $pic,
                                'grid_id' => $catOrd,
                                'mission_marking' => $mission_marking,
                                'grid_marking' => $grid_marking
                            );
                            /*
                            echo "Mission - " . $mission . "<br />";
                            echo "Start - " . $start . "<br />";
                            echo "End - " . $end . "<br />";
                            echo "Type - " . $type . "<br />";
                            echo "Level - " . $level . "<br /><br />";
                            */
                        }
                    }
                    /*
                                //for start date we need to find the next friday
                                while (jddayofweek(++$start) != 5) {}
                                if ($endDate >= $start) {
                                    if ($endDate > ($start + 6)) {
                                        $end = $start + 6;
                                    } else {
                                        $end = $endDate;
                                    }
                                }
                                */
                }
                //}
                $arrMandatory = array();
                $arrFocus = array();
                $arrStart = array();
                $arrEnd = array();
                $missionName = "";
            }
            //exit;
            echo "<pre>";
            print_r($missions);
            echo "</pre>";
            exit;

            mysql_query("SET AUTOCOMMIT=0");
            mysql_query("BEGIN");
            $line = 1;
            if (isset($missions['add'])) {
                $missionsCreated = 0;
                $tasksCreated = 0;
                foreach ($missions['add'] as $type => $info) {
                    foreach ($info as $level => $otherInfo) {
                        foreach ($otherInfo as $mName => $arr) {
                            foreach ($arr as $mVal => $info) {
                                foreach ($info as $start => $arr) {
                                    foreach ($arr as $end => $info) {
                                        foreach ($info as $lang => $other) {
                                            $sql = "insert into date_tasks_missions 
                                                    set school_type_id = " . mysql_real_escape_string($type) . ", 
                                                    subject_id = " . mysql_real_escape_string($subject_id) . ", 
                                                    level = " . mysql_real_escape_string($level) . ", 
                                                    track_id = " . mysql_real_escape_string($track) . ", 
                                                    mission_name = \"" . mysql_real_escape_string($mName) . "\",  
                                                    mission_value = " . mysql_real_escape_string($mVal) . ", 
                                                    mission_number = " . $missionNumber++ . ", 
                                                    start_date = " . mysql_real_escape_string($start) . ", 
                                                    end_date = " . mysql_real_escape_string($end) . ", 
                                                    lang_id = " . mysql_real_escape_string($lang) . ",
                                                    default_on = 1";
                                            //echo $sql . "<br />";
                                            //continue;
                                            if (mysql_query($sql)) {
                                                $missionsCreated++;
                                                $id = mysql_insert_id();
                                                //$id = 1111;
                                                foreach ($other as $task) {
                                                    $sql = "insert into date_tasks 
                                                            set date_tasks_mission_id = $id, 
                                                            name = \"" . mysql_real_escape_string($task['task']) . "\", 
                                                            cat_ord_new = " . mysql_real_escape_string($task['catOrd']) . ", 
                                                            cat = \"" . mysql_real_escape_string($task['cat']) . "\", 
                                                            points = " . $task['points'] . ", 
                                                            short_name = \"" . mysql_real_escape_string($task['short_name']) . "\",
                                                            grid_marking = " . mysql_real_escape_string($task['grid_marking']) . ",
                                                            mission_marking = " . mysql_real_escape_string($task['mission_marking']) . ",
                                                            daily_task = " . mysql_real_escape_string($task['daily']) . ",
                                                            label_id = " . mysql_real_escape_string($task['label']) . ",
                                                            grid_id = " . mysql_real_escape_string($task['grid_id']);
                                                    if ($task['mand']) {
                                                        $sql .= ", mandatory_qty = 1, optional_qty = 0";
                                                    } else {
                                                        $sql .= ", mandatory_qty = 0, optional_qty = 1";
                                                    }
                                                    if ($task['labelOrd']) {
                                                        $sql .= ", label_ord = " . mysql_real_escape_string($task['labelOrd']);
                                                    }
                                                    if ($task['qty']) {
                                                        $sql .= ", quantity = " . mysql_real_escape_string($task['qty']);
                                                    }
                                                    if ($task['needed']) {
                                                        $sql .= ", needed = " . mysql_real_escape_string($task['needed']);
                                                    }
                                                    if ($task['focus']) {
                                                        $sql .= ", focus_task = " . mysql_real_escape_string($task['focus']);
                                                    }
                                                    if (!$task['def']) {
                                                        $sql .= ", default_on = 0";
                                                    }
                                                    if ($task['pic']) {
                                                        $sql .= ", medium_pic = \"" . mysql_real_escape_string($task['pic']) . "\"";
                                                    }
                                                    //echo $sql . "<br />";
                                                    //$tasksCreated++;

                                                    if (mysql_query($sql)) {
                                                        $tasksCreated++;
                                                    } else {
                                                        echo $sql . "<br />" . mysql_error() . "<br />";
                                                        mysql_query("ROLLBACK");
                                                        mysql_query("SET AUTOCOMMIT=1");
                                                        exit;
                                                    }

                                                }
                                                //echo "<br />";

                                            } else {
                                                echo $sql . "<br />" . mysql_error() . "<br />";
                                                mysql_query("ROLLBACK");
                                                mysql_query("SET AUTOCOMMIT=1");
                                                exit;
                                            }

                                            //echo "<br />";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                echo "Missions Created: " . $missionsCreated;
                echo "<br />Tasks Created: " . $tasksCreated;
            }
            //if we get here then there were no errors
            mysql_query("COMMIT");
            mysql_query("SET AUTOCOMMIT=1");
        } else {
            echo "There is a problem with the file, please contact your systems admin.";
        }
    } else {
        echo "You have not uploaded any file, please try again.";
    }
} else {
    ?>
  <div id='instructions'>
    <h2>Instructions</h2>
    <p>
      Please download template file by clicking <a href='SystemTasks/TasksTemplate.xlsx'>here</a>.<br/>
      Fill in all information according to the format of the template file.<br/>
      Once completed, use the following form to upload the file.<br/><br/>
    </p>
  </div>

  <div id="task_form">
    <h2>Upload File</h2>
    <form enctype="multipart/form-data" action="create_tasks.php" method="post">
      Choose Campaign: <br/>
      <select name="subject" id='subject'>
        <option value='0'>Choose One</option>
          <?
          foreach ($campaigns as $id => $campaign) {
              echo "<option value='$id'>" . $campaign . "</option>";
          }
          ?>
      </select><br/><br/>

      Choose language:<br/>
      <input type="radio" name="lang" value="1" checked/> English<br/>
      <input type="radio" name="lang" value="2"/> Yiddish
      <br/><br/>

      Choose file to upload:<br/>
      <input name="tasks" type="file" id='file'/><br/><br/>
      <input type="submit" name="submit" value="Upload" id='submit'/>
    </form>
  </div>

  <div id="files">
    <h2>Download existing files</h2>
      <?
      $d = dir("SystemTasks/");
      $files = array();
      while (($file = $d->read()) !== false) {
          if (strstr($file, ".xlsx")) {
              $files[] = $file;
          }
      }
      sort($files);
      foreach ($files as $file) {
          echo "<a href='SystemTasks/{$file}'>$file</a><br />";
      }
      ?>
  </div>
<? } ?>
</body>
</html>    