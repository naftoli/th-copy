<?php
$admin_auth = array('school');
require('header.php'); 

$d = unixtojd();
$day = date("N");
$end = $d;

switch ($day) 
{
    case 1:
        $end -= 2;
        break;
        
    case 2:
        $end -= 3;
        break;
    
    case 3:
        $end -= 4;
        break;
    
    case 4:
        $end -= 5;
        break;
    
    case 5:
        $end += 1;
        break;
    
    case 6:
        break;
    
    case 7:
        $end -= 1;
        break;
}
$start = ($end - 6);

require_once 'class.parshos.php';
$p = new Parshos;
$parshos = $p->getParshos();

$grades = array();
$sql = "select * from classes where class_era = 0 and school_id = " . $admin_user['auths']['school'][0];
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $grades[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
}

$action = '';
if (isset($_POST['submit'])) {
    $action = 'makeReport';
}
?>
<!doctype html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mark Students</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            td:not(:first-child) {
                width: 50px;
            }
            th, td {
                padding: 5px;
                font-size: 12px;
            }
            th {
                text-align: center;
            }
            fieldset {
                float: left;
                width: 50%;
            }
        </style>
    </head>
		
    <body>
        <? include('admin_header.php'); ?>
        <h1>Mark Students</h1>
        
        <? if ($action == '') : ?>
            <form name="date_tasks_report" id="date_tasks_report" action="mark_missions.php" method="post" accept-charset="UTF-8">
                
                <fieldset>
                    <legend>Options</legend>
                
                    <!-- ***** WEEKLY PERIOD ***** -->
                    <div class="date_list select_box">                  
                        <select name="date_list" class="sSelect">
                            <? for ($rno = 0; $rno < count($parshos); $rno++) : ?>
                                <? $report = $parshos[$rno]; ?>
                                <? if ($start == $report['start'] && $end == $report['end']) :  ?>
                                <? $report_name = $report['name']; ?>
                                <option selected value="<?=$report['start'];?>:<?=$report['end'];?>"><?=$report['name'];?> - <?=jdtogregorian($report['start']);?></option>
                                <? else : ?>
                                <option value="<?=$report['start'];?>:<?=$report['end'];?>"><?=$report['name'];?> - <?=jdtogregorian($report['start']);?></option>                                
                                <? endif; ?>
                            <? endfor; ?>
                        </select>
                    </div>
                    <!-- ***** WEEKLY PERIOD ***** -->
                    <br /><br /><br />
                    
                    <div class="date_list select_box">                  
                        <select name="grade[]" class="grade" multiple>
                            <option value='0' selected>All Classes</option>
                            <?php
                            foreach ($grades as $id => $grade) {
                                echo "<option value='" . $id . "'>" . $grade . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <br />
                    
                    <div class="date_list select_box"> 
                        <input name="submit" class="submit" type="submit" value="GO">
                    </div>
                </fieldset>
                
            </form>
        <? else : ?>
            <?php
            $selectedGrades = $_POST['grade'];
            if ($selectedGrades[0] == 0) $allClasses = true;
            else $allClasses = false;
            
            $userIDs = array();
            $sql = "select user_id from users
                    join classes using (class_id) 
                    where heb_year = 5777";
            if (!$allClasses) $sql .= " and class_id in (" . implode(',', $selectedGrades) . ")";
            $sql .= " order by class_grade, class_sub, last, first";
            $result = mysql_query($sql);
            while ($row = mysql_fetch_assoc($result)) {
                $userIDs[] = $row['user_id'];
            }
            
            $dates = explode(':', $_POST['date_list']);
            $start = $dates[0];
            $end = $dates[1];
            include("classes/user.php");
            include("classes/user_track.php");
            include("classes/school_class.php");
            include("class.taskExceptions.php");
            include("classes/date_tasks_mission.php");
            include("classes/daily_task.php");
            include("classes/weekly_task.php");
            include("classes/shabbos_task.php");
            include("classes/no_label_task.php");
            include("classes/task.php");
            include("classes/date_tasks_mark.php");
        
            $users = array();
            foreach ($userIDs as $user_id) {
                $sql = "SELECT * FROM users WHERE user_id=" . $user_id;
                $query = mysql_query($sql);
                $row = mysql_fetch_assoc($query);
                $user = new user($row);
                $user->get_user_tracks(-1, $start, $end); 
                $users[] = $user;
            }
            //echo "<pre>"; print_r($users); echo "</pre>"; exit;
            ?>
            <table id="marking">
                <tr>
                    <th>Grade</th>
                    <th>Student</th>
                    <th>Sun</th>
                    <th>Mon</th>
                    <th>Tues</th>
                    <th>Wed</th>
                    <th>Thu</th>
                    <th>Fri</th>
                    <th>Shabbos</th>
                </tr>
                <?php
                foreach ($users as $user) {
                    echo "<tr id='" . $user->user_id . "'><td>" . $grades[$user->class_id] . "</td><td>" . $user->first . ' ' . $user->last . "</td>";
                    $daily_task = $user->daily_tasks[0];
                    for ($j = 0; $j < 7; $j++) {
                        $date_task_mark = $daily_task->date_task_marks[$j];                        
                        echo "<td contenteditable='true' id='" . $user->user_id . ":" . $date_task_mark->date_task_id . ":" . $date_task_mark->mark_date . "'>" . $date_task_mark->done_qty . "</td>"; 
                    }
                    echo "</tr>";
                }
                ?>
            </table>
            <br />
            <input name="submit" class="submit" type="submit" id="save" value="Save All">
        <? endif; ?>
    </body>
    <script type="text/javascript" src="scripts/functions.js"></script>
    <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
    <script>
        $( function() {
            $(".sSelect").sSelect();
            
            $("#save").click( function() {
                var updated = 0;
                var errors = [];
                var tosend = 0;
                
                $("#marking tr").each( function(i) {
                    if (i) {
                        var id = $(this).attr('id');
                        if (id) {
                            for (var j = 2; j < 9; j++) {
                                // loop through days of week
                                var td = $(this).find('td').eq(j);
                                var val = parseInt($(td).text());
                                if (val) {
                                    tosend++;
                                }
                            }
                        }
                    }
                });
                
                $("#marking tr").each( function(i) {
                    if (i) {
                        var id = $(this).attr('id');
                        if (id) {
                            for (var j = 2; j < 9; j++) {
                                // loop through days of week
                                var td = $(this).find('td').eq(j);
                                var val = parseInt($(td).text());
                                if (val) {
                                    // get user id and task id and mark date
                                    var info = $(td).attr('id');
                                    var pos1 = info.indexOf(':');
                                    var user = info.substring(0, pos1);
                                    var more = info.substring(pos1+1);
                                    var pos2 = more.indexOf(':');
                                    var task = more.substring(0, pos2);
                                    var date = more.substring(pos2+1);
                                    var parameters = [user, task, date, val];
                                    
                                    url = "add_functions.php?function_name=add_mark&parameters=" + parameters;
                                    $.getJSON(url, function(success) {
                                        if (success == false) {
                                            errors.push('Error updating ' + user + ".\n");
                                        } else {
                                            updated++;
                                        }
                                        if (--tosend == 0) {
                                            if (errors.length) {
                                                alert(errors);
                                            } else {
                                                alert("Updated.");
                                                location.href = "mark_missions.php";
                                            }
                                        }
                                    });
                                }
                            }
                        }
                    }
                });
            })
        });
    </script>
</html>
