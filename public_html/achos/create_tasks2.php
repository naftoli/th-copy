<?
$admin_auth = array('school');
require_once 'header.php';

if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}
 
$sql1 = "select subject_id, subject_name from subjects s 
        join school_type_subjects sts using (subject_id) 
        where s.subject_type in ('', 'WWTC') 
        and sts.school_type_id in (2,3,12,13) 
        and s.subject_id not in (1, 27) 
        order by s.subject_id";
$result1 = mysql_query($sql1);
$campaigns = array();
while ($row1 = mysql_fetch_assoc($result1)) {
    $campaigns[$row1['subject_id']] = $row1['subject_name'];
}

function getStartEnd($arr) {
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
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type='text/javascript'>
            $(function() {
                $("#submit").click(function() {
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
        1   =>  "Tehillim", 
        4   =>  "Tefillah", 
        12  =>  "Mivtzoim", 
        13  =>  "Nigunnim", 
        15  =>  "Hakhel", 
        16  =>  "Hiskashrus", 
        21  =>  "SeferHamitzvos", 
        40  =>  "YomaDepagra", 
        41  =>  "AvosUbanim", 
        42  =>  "Viholachto", 
        45  =>  "Cheshbon", 
        90  =>  "Chitas", 
        91  =>  "TanyaBaalPeh", 
        92  =>  "JewishSongs", 
        93  =>  "AssistingOtherJews", 
        94  =>  "YomTov"
    );
    
    $sql = "select mission_number from date_tasks_missions where subject_id = $subject_id order by mission_number desc limit 1";
    //echo $sql;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $missionNumber = $row['mission_number'] + 1;
    
    $sql2 = "select * from parshos where start >= 2456530 and end <= 2456921";
    $result2 = mysql_query( $sql2 );
    while ( $row2 = mysql_fetch_assoc( $result2 ) ) {
        $weeks[$row2['start']][$row2['end']] = $row2['name'];
    }
    echo "<pre>";
    //print_r($weeks);
    echo "</pre>";
  
    $file = "SystemTasks/" . $subjects[$subject_id] . "5774.xlsx";
    if (file_exists($_FILES['tasks']['tmp_name'])) {
        if (move_uploaded_file($_FILES['tasks']['tmp_name'], $file)) {
            $track = 1;

            //load spreadsheet
            $objPHPExcel = PHPExcel_IOFactory::load( $file );
            $objWorksheet = $objPHPExcel->getActiveSheet();
            
            $missions = array();
            $firstRow = true;
            $missionNames = array();
            $fieldNames = array(
                "action", "missionName", "missionDescription", "missionValue", "startDate", "endDate", "firstLevel", "lastLevel", "types",  
                "cat", "qty", "mandatory", "ord", "daily", "needed", "focus", "task", "points", "default", "labelOrd", "labelID"
            );
            
            foreach ( $objWorksheet->getRowIterator() as $row ) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $i = 0;
                if ( $firstRow ) {
                    $headers = array(
                        "Action", "Mission Name", "Mission Description", "Mission Value", "Start Date", "End Date", 
                        "From Age", "Till Age", "School Types (2,3,12,13)", "Task Category", "Qty", "Mandatory Start end", 
                        "Task Ord", "Daily", "Needed", "Focus / Charge It start", "Task Name", "Points", "Default On", "TaskLabel Ord", "Label ID"                     
                    );
                    foreach( $cellIterator as $cell ) { 
                        $actual[] = trim($cell->getValue());
                    }
                    $diff = array_diff($headers, $actual);
                    //if (!empty($diff)) {
                        /*
                        echo "<pre>";
                        print_r($headers);
                        print_r($actual);
                        echo "</pre>";
                         * 
                         */
                    //    echo "You have a corrupted file, please redownload template file and try again!.";
                    //    exit;
                    //}
                    $firstRow = false;
                    continue;
                }
                
                foreach( $cellIterator as $cell ) { 
                    $val = trim($cell->getValue());
                    switch ($i) {
                        case 0:
                            ${$fieldNames[$i]} = 'add';
                            break;
                        case 4:
                            if (strpos($val, ',')) {
                                $arrStart = explode(',', $val);
                                if (strlen($arrStart[0]) > 3) {
                                    $startDate = $arrStart[0];
                                    $endDate = $arrStart[1];
                                } else {
                                    $startDate = "";
                                    $endDate = "";
                                }
                            } else {
                                $startDate = $val;
                            }
                            break;
                        case 5:
                            if (!empty($val)) {
                                if (!is_numeric($val)) {
                                    $arrEnd = explode(',', $val);
                                } else {
                                    $endDate = $val;
                                }
                            }
                            break;
                        case 8: 
                            $types = explode(',', $val);
                            break;
                        case 11:
                            if (strpos($val, ',')) {
                                $arrMandatory = explode(',', $val);
                            } else {
                                $mandatory = $val;
                            }
                            break;
                        case 15:
                            if (strpos($val, ',')) {
                                $arrFocus = explode(',', $val);
                            } else {
                                $focus = $val;
                            }
                            break;
                        case 16:
                            $task = $val;
                            if (!strpos($task, '.')) 
                                $task .= ".";
                            break;
                        default:
                            ${$fieldNames[$i]} = $val;
                            break;
                    }
                    if (++$i == count($fieldNames)) 
                        break;
                }
                
                if (isset($arrStart) && empty($startDate)) {
                    $year = $arrStart[0] == 13 ? 5773 : 5774; 
                    $startDate = jewishtojd($arrStart[0], $arrStart[1], $year);
                    $endDate = jewishtojd($arrEnd[0], $arrEnd[1], $year);
                }
                
                //get array of start and end dates
                if (isset($arrStart)) {
                    $arrDates = getStartEnd($arrStart);
                } else {
                    $arrDates['start'][] = $startDate;
                    $arrDates['end'][] = $endDate;
                }
                /*
                //make sure start and end date is greater than or equal to today
                $today = unixtojd();
                if ($startDate < $today) {
                    $startDate = $today;
                }
                if ($endDate < $today) {
                    $endDate = $today;
                }
                */  

                $num = count($arrDates['start']);
                for ($k = 0; $k < $num; $k++) {
                    $startDate = trim($arrDates['start'][$k]);
                    $endDate = trim($arrDates['end'][$k]);                         
                                
                    $start = $startDate;
                    if ($endDate > ($start + 6)) {
                        $end = $start + 6;
                    } else {
                        $end = $endDate;
                    }
                                                    
                    while ($start <= $end) {
                        $mission = $missionName;
                        if (empty($missionName)) {
                            $mission = (array_key_exists($end, $weeks[$start]) ? $weeks[$start][$end] : end($weeks[$start]));
                        }  
                        foreach ($types as $type) {
                            for ($level = $firstLevel; $level <= $lastLevel; $level++) {
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
                                if (!empty($arrFocus)) {
                                    $focus = 0;
                                    $f = getStartEnd($arrFocus);
                                    for ($c = 0; $c < count($f['start']); $c++) {
                                        if ($start >= $f['start'][$c] && $end <= $f['end'][$c]) {
                                            $focus = 1;
                                        }
                                    }
                                }
                                $missions[$action][$mission][$missionValue][$start][$end][$type][$level][$default][] = array(
                                    'task'  =>  $task, 
                                    'cat'   =>  $cat, 
                                    'qty'   =>  $qty, 
                                    'points'    =>  $points, 
                                    'mand'  =>  $mandatory, 
                                    'focus' =>  $focus, 
                                    'label' =>  $labelID, 
                                    'daily' =>  $daily, 
                                    'needed'    =>  $needed, 
                                    'ord'   =>  $ord, 
                                    'def'   =>  $default
                                );
                                /*
                                echo "Mission - " . $mission . "<br />";
                                echo "Start - " . $start . "<br />";
                                echo "End - " . $end . "<br />";
                                echo "Type - " . $type . "<br />";
                                echo "Level - " . $level . "<br />";
                                echo "Category - " . $cat . "<br /><br />"; 
                                */
                            }
                        }
                        //for start date we need to find the next sunday
                        while (jddayofweek(++$start)) {}
                        if ($endDate >= $start) {
                            if ($endDate > ($start + 6)) {
                                $end = $start + 6;
                            } else {
                                $end = $endDate;
                            }
                        }
                    }
                }
                $arrMandatory = array();
                $arrFocus = array();
                $missionName = "";
            }
            //exit;
            /*
            $num = 0;
            foreach( $missions['add'] as $mission => $info ) {
                foreach( $info as $value => $otherInfo ) { 
                    foreach ($otherInfo as $number => $info) {
                        foreach( $info as $start => $arr ) {
                            foreach( $arr as $end => $info ) {
                                foreach( $info as $type => $arr ) {
                                    foreach( $arr as $level => $info ) {
                                        foreach( $info as $default => $arr ) {
                                            //echo count($arr) . "<br />";
                                            $num += count($arr);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            echo $num;
            
            echo "<pre>";
            print_r($missions);
            echo "</pre>";
            exit;
            */
            if (isset($missions['delete'])) {
                $dtm = array();
                foreach( $missions['delete'] as $mission => $info ) {
                    foreach( $info as $value => $otherInfo ) { 
                        foreach( $otherInfo as $start => $arr ) {
                            foreach( $arr as $end => $info ) {
                                foreach( $info as $type => $arr ) {
                                    foreach( $arr as $level => $info ) {
                                        foreach( $info as $default => $arr ) {
                                            //get missions  
                                            $sql1 = "select date_tasks_mission_id from date_tasks_missions 
                                                    where school_type_id = $type  
                                                    and subject_id = $subject_id  
                                                    and level = $level 
                                                    and track_id = $track 
                                                    and mission_name = \"" . mysql_real_escape_string( $mission ) . "\"  
                                                    and mission_value = $value 
                                                    and start_date = $start  
                                                    and end_date = $end";
                                            if ( !$default ) {
                                                $sql1 .= " and default_on = 0";
                                            } else {
                                                $sql1 .= " and default_on = 1";
                                            }
                                            //echo $sql1 . "<br />";
                                            $result1 = mysql_query($sql1);
                                            $row1 = mysql_fetch_assoc($result1);
                                            $dtm[] = $row1['date_tasks_mission_id']; 
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                //delete from missions, tasks
                $sql2 = "delete from date_tasks_missions where date_tasks_mission_id in (" . implode(',', $dtm) . ")";
                $sql3 = "delete from date_tasks where date_tasks_mission_id in (" . implode(',', $dtm) . ")";
                echo $sql2 . "<br />";
                echo $sql3 . "<br />";
                /*        
                mysql_query("SET AUTOCOMMIT=0");
                mysql_query("BEGIN");
                if (mysql_query($sql2) && mysql_query($sql3)) {
                    mysql_query("COMMIT");
                    mysql_query("SET AUTOCOMMIT=1");
                } else {
                    mysql_query("ROLLBACK");
                    mysql_query("SET AUTOCOMMIT=1");
                    echo "Could not delete missions / tasks. Please speak to your systems administrator.";
                    exit;
                }
                 * 
                 */
            }
            
            mysql_query("SET AUTOCOMMIT=0");
            mysql_query("BEGIN"); 
            if (isset($missions['add'])) {
                $missionsCreated = 0;
                $tasksCreated = 0;
                foreach( $missions['add'] as $mission => $info ) {
                    foreach( $info as $value => $otherInfo ) { 
                        foreach( $otherInfo as $start => $arr ) {
                            foreach( $arr as $end => $info ) {
                                foreach( $info as $type => $arr ) {
                                    foreach( $arr as $level => $info ) {
                                        foreach( $info as $default => $arr ) {
                                            $sql = "insert into date_tasks_missions 
                                                    set school_type_id = $type, 
                                                    subject_id = $subject_id, 
                                                    level = $level, 
                                                    track_id = $track, 
                                                    mission_name = \"" . mysql_real_escape_string( $mission ) . "\",  
                                                    mission_value = $value, 
                                                    mission_number = " . $missionNumber++ . ", 
                                                    start_date = $start, 
                                                    end_date = $end";
                                            if ( !$default ) {
                                                $sql .= ", default_on = 0";
                                            }
                                            //echo $sql . "<br />";
                                            //$missionsCreated++;
                                            
                                            if ( mysql_query( $sql ) ) {
                                                $missionsCreated++; 
                                                $id = mysql_insert_id();
                                                //$id = 1111;
                                                $success = true;
                                                foreach( $arr as $task ) {
                                                    $sql = "insert into date_tasks 
                                                            set date_tasks_mission_id = $id, 
                                                            name = \"" . mysql_real_escape_string( $task['task'] ) . "\", 
                                                            cat = \"" . mysql_real_escape_string( $task['cat'] ) . "\", 
                                                            points = $task[points]"; 
                                                    if ($task['mand']) { 
                                                        $sql .= ", mandatory_qty = 1, optional_qty = 0";
                                                    } else {
                                                        $sql .= ", mandatory_qty = 0, optional_qty = 1";
                                                    } 
                                                    if ($task['daily']) {
                                                        $sql .= ", daily_task = $task[daily]";
                                                    }
                                                    if ($task['label'])
                                                        $sql .= ", label_id = $task[label]"; 
                                                        
                                                    if ($task['ord']) {
                                                        $sql .= ", ord = " . $task['ord'];
                                                    }
                                                    if ( $task['qty'] ) {
                                                        $sql .= ", quantity = " . $task['qty'];
                                                    }
                                                    if ( $task['needed'] ) {
                                                        $sql .= ", needed = " . $task['needed'];
                                                    }
                                                    if ($task['focus']) {
                                                        $sql .= ", focus_task = " . $task['focus'];
                                                    }
                                                    if ( !$task['def'] ) {
                                                        $sql .= ", default_on = 0";
                                                    }
                                                    //echo $sql . "<br />";
                                                    //$tasksCreated++;
                                                    
                                                    if ( mysql_query( $sql ) ) {
                                                        $tasksCreated++; 
                                                    } else {
                                                        echo $sql . "<br />" . mysql_error() . "<br />"; 
                                                        mysql_query("ROLLBACK");
                                                        mysql_query("SET AUTOCOMMIT=1");
                                                        exit;
                                                    }
                                                    
                                                }
                                               
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
                Please download template file by clicking <a href='SystemTasks/TasksTemplate.xlsx'>here</a>.<br />
                Fill in all information according to the format of the template file.<br />
                Once completed, use the following form to upload the file.<br /><br />
            </p>
        </div>
        
        <div id="task_form">
            <h2>Upload File</h2>
            <form enctype="multipart/form-data" action="create_tasks2.php" method="post">
                Choose Campaign: <br />
                <select name="subject" id='subject'>
                    <option value='0'>Choose One</option>
                    <?
                    foreach ($campaigns as $id => $campaign) {
                        echo "<option value='$id'>" . $campaign . "</option>";
                    }
                    ?>
                </select><br /><br />
                Choose file to upload:<br />
                <input name="tasks" type="file" id='file' /><br /><br />
                <input type="submit" name="submit" value="Upload" id='submit' />
            </form>
        </div>
         
        <div id="files">
            <h2>Download existing files</h2>
            <?
            $d = dir("SystemTasks/");
            while (($file = $d->read()) !== false) {
                if (strstr($file, ".xlsx")) {
                    echo "<a href='SystemTasks/{$file}'>$file</a><br />";
                }
            }
            ?>
        </div>
<? } ?>
    </body>
</html>    