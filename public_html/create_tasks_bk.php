<?
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 300);
ini_set('display_errors', TRUE);

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
        27	=> 	"TanyaBaalPeh",  
        40  =>  "YomaDepagra", 
        41  =>  "AvosUbanim", 
        42  =>  "Viholachto", 
        45  =>  "Cheshbon", 
        90  =>  "Chitas", 
        92  =>  "JewishSongs", 
        93  =>  "AssistingOtherJews", 
        94  =>  "YomTov", 
        100 => 	"BriasHaguf"
    );
    
    $sql = "select mission_number from date_tasks_missions where subject_id = $subject_id order by mission_number desc limit 1";
    //echo $sql;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $missionNumber = ceil($row['mission_number'] + 1);
	//echo $missionNumber;
    
    $sql2 = "select * from parshos where year = 5777";
    $result2 = mysql_query( $sql2 );
    while ( $row2 = mysql_fetch_assoc( $result2 ) ) {
        $weeks[$row2['start']][$row2['end']] = $row2['name'];
    }
    echo "<pre>";
    //print_r($weeks);
    echo "</pre>";
	
	$defaultStart = 2457634; // Sept 2, 2016
	$defaultEnd = 2458005; // Sept 8, 2017
	
	$lang = $_POST['lang'];
	if ($lang == 1) {
  		$file = "SystemTasks/" . $subjects[$subject_id] . "5777.xlsx";
	} else if ($lang == 2) {
		$file = "SystemTasks/Yi" . $subjects[$subject_id] . "5777.xlsx";
	}
    
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
                "action", "missionName", "missionDescription", "missionValue", "startDate", "endDate", 
                "firstLevel", "lastLevel", "types", "catOrd", "cat", "qty", "mandatory", "ord", "daily", "needed", 
                "focus", "taskID", "shortName", "task", "points", "default", "labelOrd", "labelID", "lang", "pic" 
            );
            
            foreach ( $objWorksheet->getRowIterator() as $row ) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $i = 0;
				
                if ( $firstRow ) {
					/*
                    $headers = array(
                        "Action", "Mission Name", "Mission Description", "Mission Value", "Start Date", "End Date", 
                        "From Age", "Till Age", "School Types (2,3,12,13)", "Task Category", "Qty", "Mandatory Start end", 
                        "Task Ord", "Daily", "Needed", "Focus / Charge It start", "Task Name", "Points", "Default On", "TaskLabel Ord", "Label ID", "Lang"                    
                    );
                    foreach( $cellIterator as $cell ) { 
                        $actual[] = trim($cell->getValue());
                    }
                    $diff = array_diff($headers, $actual);
                    if (!empty($diff)) {
                        echo "<pre>";
                        print_r($headers);
                        print_r($actual);
                        echo "</pre>";
                        echo "You have a corrupted file, please redownload template file and try again!.";
                        exit;
                    }
					 * 
					 */
                    $firstRow = false;
                    continue;
                }
                
                foreach( $cellIterator as $cell ) { 
                    $val = trim($cell->getValue());
					//echo $val . "<br />"; continue;
                    switch ($i) {
                        case 0:
                            ${$fieldNames[$i]} = 'add';
                            break;
                        case 4:
							if (strpos($val, ':') !== false) {
								$arrValues = explode(':', $val);
								foreach ($arrValues as $value) {
									$arrTemp = explode(',', $value);
									$year = $arrTemp[0] == 13 ? 5775 : 5776; 
									$jd = jewishtojd($arrTemp[0], $arrTemp[1], $year);
                    				$arrStart[] = $jd;
									$arrStart[] = $jd;
									$startDate = $arrStart[0];
								}
							} else {
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
									if (empty($val)) {
										$startDate = $defaultStart;
										$endDate = $defaultEnd;
									} else {
										$startDate = $val;
									}
	                            }
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
							if (strpos($val, ',')) {
                            	$types = explode(',', $val);
							} else {
								$types = array($val);
							}
                            break;
						case 9:
							$catOrd = (double)$val;
							break;
                        case 12:
                            if (strpos($val, ',')) {
                                $arrMandatory = explode(',', $val);
                            } else {
                                $mandatory = $val;
                            }
                            break;
                        case 16:
                            if (strpos($val, ',')) {
                                $arrFocus = explode(',', $val);
                            } else {
                                $focus = $val;
                            }
                            break;
						case 17:
							$taskID = (double)$val;
							break;
                        case 19:
                            $task = $val;
                            if (!strpos($task, '.')) 
                                $task .= ".";
                            break;
						case 24:
							$lang = 1;
							if (is_numeric($val) && $val > 1) {
								$lang = $val;
							}
							break;
                        default:
                            ${$fieldNames[$i]} = $val;
                            break;
                    }
                    if (++$i == count($fieldNames)) 
                        break;
                }
                
                if (isset($arrStart) && !empty($arrStart) && empty($startDate)) {
                    $year = $arrStart[0] == 13 ? 5775 : 5776; 
                    $startDate = jewishtojd($arrStart[0], $arrStart[1], $year);
                    $endDate = jewishtojd($arrEnd[0], $arrEnd[1], $year);
                }
                //echo $startDate . "<br />";
                //echo $endDate . "<br />";
				//continue;
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
                //echo "<pre>"; print_r( $arrStart ); echo "</pre>";                
				$num = isset($arrStart) && !empty($arrStart) ? count($arrStart) : 2;
				//echo $num; exit;
				$loops = $num / 2;
				$k = 0;
				while ($loops--) { //index into $arrStart array
					if ($num > 2) {
						$startDate = $arrStart[$k++];
						$endDate = $arrStart[$k++];
					} 
					$start = $startDate;
	                if ($endDate > ($start + 6)) {
	                    $end = $start + 6;
	                } else {
	                    $end = $endDate;
	                }
					//echo $start . '-' . $end; exit;
	                while ($start <= $end) {
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
                            if (!empty($arrFocus)) {
                                $focus = 0;
                                $f = getStartEnd($arrFocus);
                                for ($c = 0; $c < count($f['start']); $c++) {
                                    if ($start >= $f['start'][$c] && $end <= $f['end'][$c]) {
                                        $focus = 1;
                                    }
                                }
                            }
	                        for ($level = $firstLevel; $level <= $lastLevel; $level++) {
	                        	//echo $task . "<br />" . $start . ' - ' . $end . ' T: ' . $type . ' L: ' . $level . "<br />"; 
	                            $missions[$action][$mission][$missionValue][$start][$end][$type][$level][$default][$lang][] = array(
	                                'task'  =>  $task, 
	                                'catOrd'=>	$catOrd, 
	                                'cat'   =>  $cat, 
	                                'qty'   =>  $qty, 
	                                'points'=>  $points, 
	                                'mand'  =>  $mandatory, 
	                                'focus' =>  $focus, 
	                                'label' =>  $labelID, 
	                                'labelOrd'	=> $labelOrd, 
	                                'daily' =>  $daily, 
	                                'needed'=>  $needed, 
	                                'ord'   =>  $ord, 
	                                'def'   =>  $default, 
									'task_id'	=> $taskID, 
									'short_name'=> $shortName, 
									'pic'		=> $pic
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
						
	                    //for start date we need to find the next friday
	                    while (jddayofweek(++$start) != 5) {}
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
				$arrStart = array();
                $missionName = "";
            }
			
			echo "<pre>";
			//print_r($missions);
			echo "</pre>";
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
                                        	foreach ($arr as $lang => $info) {
	                                            //get missions  
	                                            $sql1 = "select date_tasks_mission_id from date_tasks_missions 
	                                                    where school_type_id = $type  
	                                                    and subject_id = $subject_id  
	                                                    and level = $level 
	                                                    and track_id = $track 
	                                                    and mission_name = \"" . mysql_real_escape_string( $mission ) . "\"  
	                                                    and mission_value = $value 
	                                                    and start_date = $start  
	                                                    and end_date = $end 
	                                                    and lang_id = $lang";
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
			$line = 1;
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
                                        	foreach ($arr as $lang => $info) { 
	                                            $sql = "insert into date_tasks_missions 
	                                                    set school_type_id = $type, 
	                                                    subject_id = $subject_id, 
	                                                    level = $level, 
	                                                    track_id = $track, 
	                                                    mission_name = \"" . mysql_real_escape_string( $mission ) . "\",  
	                                                    mission_value = $value, 
	                                                    mission_number = " . $missionNumber++ . ", 
	                                                    start_date = $start, 
	                                                    end_date = $end, 
	                                                    lang_id = $lang";
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
	                                                foreach( $info as $task ) {
	                                                    $sql = "insert into date_tasks 
	                                                            set date_tasks_mission_id = $id, 
	                                                            name = \"" . mysql_real_escape_string( $task['task'] ) . "\", 
	                                                            cat_ord = " . mysql_real_escape_string( $task['catOrd'] ) . ", 
	                                                            cat = \"" . mysql_real_escape_string( $task['cat'] ) . "\", 
	                                                            points = " . $task['points'] . ", 
																task_id = " . $task['task_id'] . ", 
																short_name = \"" . mysql_real_escape_string($task['short_name']) . "\""; 
	                                                    if ($task['mand']) { 
	                                                        $sql .= ", mandatory_qty = 1, optional_qty = 0";
	                                                    } else {
	                                                        $sql .= ", mandatory_qty = 0, optional_qty = 1";
	                                                    } 
	                                                    if ($task['daily']) {
	                                                        $sql .= ", daily_task = " . $task['daily'];
	                                                    }
	                                                    if ($task['label']) {
	                                                        $sql .= ", label_id = " . $task['label']; 
														}
														if ($task['labelOrd']) {
															$sql .= ", label_ord = " . $task['labelOrd'];
														} 
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
														if ($task['pic']) {
															$sql .= ", medium_pic = \"" . mysql_real_escape_string($task['pic']) . "\"";
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
            <form enctype="multipart/form-data" action="create_tasks.php" method="post">
                Choose Campaign: <br />
                <select name="subject" id='subject'>
                    <option value='0'>Choose One</option>
                    <?
                    foreach ($campaigns as $id => $campaign) {
                        echo "<option value='$id'>" . $campaign . "</option>";
                    }
                    ?>
                </select><br /><br />
                
                Choose language:<br />
                <input type="radio" name="lang" value="1" checked /> English<br />
                <input type="radio" name="lang" value="2" /> Yiddish
                <br /><br />
                 
                Choose file to upload:<br />
                <input name="tasks" type="file" id='file' /><br /><br />
                <input type="submit" name="submit" value="Upload" id='submit' />
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