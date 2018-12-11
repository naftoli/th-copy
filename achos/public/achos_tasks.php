<?
$admin_auth = array('school');
require_once 'header.php';

require_once 'class.achosCustomization.php'; 
$ac = new AchosCustomization;

if (isset($_GET['start'])) {
    $start = $_GET['start'];
    $end = $_GET['end'];
    $ac->setDates($start, $end);
}

require_once 'class.parshos.php';
$p = new Parshos;
$parshos = $p->getParshos();

if (isset($_POST['submit'])) {
    $newTasks = array();
    $mandatory = array();
    $points = array();
    $dates = array();
    foreach ($_POST as $k => $v) {
        if ($k == 'newTask') {
            foreach ($v as $label => $task) {
                if ($task != "")
                    $newTasks[$label] = $task;
            }
        } else if ($k == 'mandatory') {
            foreach ($v as $label => $mand) {
                $mandatory[$label] = $mand;
            }
        } else if ($k == 'points') {
            foreach ($v as $label => $point) {
                $points[$label] = $point;
            }
        } else if ($k == 'dates') {
            if ($v != -1) {
                $info = explode(":", $v);
                $dates['start'] = $info[0];
                $dates['end'] = $info[1];
            }
        }
    }
    /* 
    echo "<pre>";
    print_r($newTasks);
    print_r($mandatory);
    print_r($points);
    print_r($dates);
    echo "</pre>";
    exit;
    */ 
    $errors = array();
    foreach ($newTasks as $label => $task) {
        if ($task != "") {
            $data['label'] = $label;
            $data['name'] = $task;
            $data['mandatory'] = $mandatory[$label];
            $data['points'] = $points[$label];
            $data['dates'] = $dates;
            $ac->createNewTask($data);
            if ($e = $ac->getErrors()) {
                $errors[] = $e;
            }
        }
    }
    if (count($errors) > 0) {
        print_r($errors);
    }
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link href="styles/achosCustomization.css" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type="text/javascript">
            $( function() {                
                $("#dates select").change( function() {
                    var dates = $(this).val();
                    if (dates != -1) {
                        var pos = dates.indexOf(":");
                        var start = dates.substring(0, pos);
                        var end = dates.substring(pos+1);
                        window.location = "achos_tasks.php?start=" + start + "&end=" + end;
                    } else {
                        window.location = "achos_tasks.php";
                    }
                });
            });
        </script>
    </head>
    
    <body>
        <? require 'admin_header.php'; ?>
        <h1>Choose your tasks</h1>
        <? 
        $ac->setTasks();
        $masterTasks = $ac->getTasks();
		$tasks = $masterTasks[0];
		$subtasks = $masterTasks[1];
        $taskPoints = $ac->getTaskPoints();
        $mandatoryTasks = $ac->getMandatoryTasks();
        $userTasks = $ac->getUserTasks();
		$labels = $ac->getLabels();
				        
		//echo "<pre>"; print_r($tasks); echo "</pre>";
		
        function newTaskHtml($label) {
            ?>
            <h2></h2>
            New Task: <input type="text" name="newTask[<?=$label?>]" class="newTask" size="36" /><br />
            <?
            if (!in_array($label, array(1,4))) {
            ?>
            <input type="radio" name="mandatory[<?=$label?>]" value="1" /> Mandatory<br />
            <input type="radio" name="mandatory[<?=$label?>]" value="0" /> Optional<br />
            <?
            } else {
                echo "<input type='hidden' name='mandatory[$label]' value='1' />";                
            }
            ?>
            Points 
            <select name="points[<?=$label?>]">
                <?
                for ($i = 1; $i < 11; $i++) {
                    echo "<option value=" . $i . ">" . $i . "</option>";
                }
				echo "<option value='25'>25</option>";
				echo "<option value='50'>50</option>";
				echo "<option value='100'>100</option>";
				echo "<option value='150'>150</option>";
                ?>
            </select>
            <?
        }
        ?>
        
        <form method="post" action="achos_tasks.php">
            
            <div class="infobox2" id="tasks" style="padding: 10px">
                
                <div id="dates">
                    Week: 
                    <select name="dates">
                        <option value="-1">All</option>
                        <?
                        for ($i = 0; $i < count($parshos); $i++) {
                            if ($parshos[$i]['start'] < 2456579) continue; //tasks only show from Vayeiro
                            if (isset($start) && $start == $parshos[$i]['start']) 
                                echo "<option value=" . $parshos[$i]['start'] . ":" . $parshos[$i]['end'] . " selected='selected'>" . $parshos[$i]['name'] . "</option>"; 
                            else 
                                echo "<option value=" . $parshos[$i]['start'] . ":" . $parshos[$i]['end'] . ">" . $parshos[$i]['name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>               
                
            <div style="clear: both"></div>
            
            <div class="infobox2" id="tasks" style="padding: 10px">
                
	            <? $j = 1; ?>
	            <? foreach ($labels as $id => $label) : ?>
	            
	            	<div id="basics">
	                    <!--<div class="subHeader"><b><?=$label?></b></div>-->
	                    <fieldset>
	                        <legend><?=$label?></legend>
	                        <table>
		                        <? 
	                        	foreach ($tasks[$id] as $taskID => $task) {
	                        		echo "<tr><td>" . $task['name'];
									$i = 0;
									foreach ($subtasks[$task['date_task_id']] as $subtask) {
										if ($i++ == 0) {
											echo "</td><td>" . $subtask['name'] . "</td></tr>";
										} else {
											echo "<tr><td></td><td>" . $subtask['name'] . "</td></tr>";
										}
	                        			//if ($mandatoryTasks[$id][$taskID]) echo "<span class='star'>* </span>";
	                        		}
		                        }
		                        //newTaskHtml($id);
		                        ?>
	                        </table>
	                    </fieldset> 
	                </div>
	                
	                <? if (($j++ % 2) == 0) : ?>
	                <div style="clear: both"></div>
	                <? endif; ?>
	            
	            <? endforeach; ?>
	            
	        </div>
               	<!-- 
                <div id="basics">
                    <div class="subHeader"><b>Achos Basics</b> - Mandatory tasks that every achos member must do.</div>
                    <fieldset>
                        <legend>Daily</legend>
                        <? 
                        foreach ($tasks[1] as $task) {
                            if ($mandatoryTasks[1][$task]) echo "<span class='star'>* </span>";
                            echo $task . " [" . $taskPoints[1][$task] . " point(s)]<br />";
                        }
                        newTaskHtml(1);
                        ?>
                    </fieldset> 
                    <fieldset>
                        <legend>Weekly</legend>
                        <? 
                        foreach ($tasks[2] as $task) {
                            if ($mandatoryTasks[2][$task]) echo "<span class='star'>* </span>";
                            echo $task . " [" . $taskPoints[2][$task] . " point(s)]<br />";
                        } 
                        newTaskHtml(2);
                        ?>
                    </fieldset>
                </div>
                
                <div style="clear: both"></div>
                
                <div id="hundred">
                    <div class="subHeader"><b>Players 100</b></div>
                    <fieldset class="daily">
                        <legend>Daily</legend>
                        <? 
                        foreach ($tasks[3] as $task) {
                            //echo "<input type='checkbox' name='2:" . rawurlencode(substr($task, 0, count($task)-2)) . "'";
                            //if ($enrolledTasks[2][$task]) echo " checked='checked' value='enrolled' ";
                            //echo " />";
                            if ($mandatoryTasks[3][$task]) echo "<span class='star'> * </span>";
                            echo $task . " [" . $taskPoints[3][$task] . " point(s)]";
                            if (isset($userTasks[3][$task])) 
                                echo "<span> - (" . $userTasks[3][$task] . ")</span>"; 
                             echo "<br />";
                        }
                        newTaskHtml(3);
                        ?>
                    </fieldset> 
                    <fieldset class="weekly">
                        <legend>Weekly</legend>
                        <? 
                        foreach ($tasks[4] as $task) {
                            //echo "<input type='checkbox' name='5:" . rawurlencode(substr($task, 0, count($task)-2)) . "'";
                            //if ($enrolledTasks[5][$task]) echo " checked='checked' value='enrolled' ";
                            //echo " />";
                            if ($mandatoryTasks[4][$task]) echo "<span class='star'> * </span>";
                            echo $task . " [" . $taskPoints[4][$task] . " point(s)]";
                            if (isset($userTasks[4][$task])) 
                                echo "<span> - (" . $userTasks[4][$task] . ")</span>"; 
                             echo "<br />";
                        } 
                        newTaskHtml(4);
                        ?>
                    </fieldset>
                </div>
                
                <div style="clear: both"></div>
                
                <div id="hundredOne">
                    <div class="subHeader"><b>Players 101</b></div>
                    <fieldset class="daily">
                        <legend>Daily</legend>
                        <? 
                        foreach ($tasks[5] as $task) {
                            //echo "<input type='checkbox' name='3:" . rawurlencode(substr($task, 0, count($task)-2)) . "'";
                            //if ($enrolledTasks[3][$task]) echo " checked='checked' value='enrolled' ";
                            //echo " />"; 
                            if ($mandatoryTasks[5][$task]) echo "<span class='star'> * </span>";
                            echo $task . " [" . $taskPoints[5][$task] . " point(s)]";
                            if (isset($userTasks[5][$task])) 
                                echo "<span> - (" . $userTasks[5][$task] . ")</span>"; 
                             echo "<br />";
                        } 
                        newTaskHtml(5);
                        ?>
                    </fieldset> 
                    <fieldset class="weekly">
                        <legend>Weekly</legend>
                        <? 
                        foreach ($tasks[6] as $task) {
                            //echo "<input type='checkbox' name='6:" . rawurlencode(substr($task, 0, count($task)-2)) . "'";
                            //if ($enrolledTasks[6][$task]) echo " checked='checked' value='enrolled' ";
                            //echo " />"; 
                            if ($mandatoryTasks[6][$task]) echo "<span class='star'> * </span>";
                            echo $task . " [" . $taskPoints[6][$task] . " point(s)]";
                            if (isset($userTasks[6][$task])) 
                                echo "<span> - (" . $userTasks[6][$task] . ")</span>"; 
                             echo "<br />";
                        } 
                        newTaskHtml(6);
                        ?>
                    </fieldset>
                    
                    <div style="clear: both"></div>
                    
                    <div align="center">
                        <input type="submit" name="submit" value="submit" id="submit" />
                    </div>
                </div>
                -->
            
        </form>
    </body>
</html>