<?
$admin_auth = array('user');
require_once 'header.php';

require_once 'class.achosStudent.php';
$as = new AchosStudent($admin_user['admin_id']);
$user_id = $as->getStudentID();

require_once 'class.achosCustomization.php'; 
$ac = new AchosCustomization();
$ac->setStudent($user_id); 

if (isset($_POST['submit'])) {
    //echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
	
	$ids = array();
	foreach ($_POST as $k => $v) {
		if (is_int($k)) {
			$ids[] = mysql_real_escape_string($k);
		}
	}
	
	//we need to find all task ids for each tasks/subtasks and add it to user
	$taskIDs = $ac->getTaskIDs($ids);
	//echo "<pre>"; print_r($taskIDs); echo "</pre>"; exit;
	
	//delete existing taskIDs from user_tasks
	//find this year's starting task id
	$sql = "select date_task_id from date_tasks where date_tasks_mission_id > 48 order by date_task_id limit 1";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$taskID = $row['date_task_id'];	
	$sql = "delete from user_tasks where user_id = " . $user_id . " and task_id >= " . $taskID;
	//echo $sql;
	mysql_query($sql);
	
    require_once 'class.defaults.php';
	$d = new Defaults($user_id);
	foreach ($taskIDs as $id) {
		$d->addOn($id, 'task');
	}
}

$ac->setTasks();
$allTasks = $ac->getTasks();
$tasks = $allTasks[0];
$subtasks = $allTasks[1];
$taskPoints = $ac->getTaskPoints();
$ac->setEnrolledTasks();
$enrolledTasks = $ac->getEnrolledTasks();
$mandatoryTasks = $ac->getMandatoryTasks();
$labels = $ac->getLabels();
/*
echo "<pre>";
print_r($tasks);
print_r($subtasks);
print_r($mandatoryTasks);
echo "</pre>";
exit;
 * 
 */
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link href="styles/achosCustomization.css" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type="text/javascript">
            $( function() {
            	$(".task").click( function() {
            		var checked = $(this).attr('checked');
            		var list = $(this).parent().next('div').find('.subtask');
            		$.each(list, function() {
            			$(this).attr('checked', checked);
            		});
            	});
            	
            	//if subtask is clicked make sure task is also checked
            	$(".subtask").click( function() {
            		if ($(this).is(":checked")) {
            			$(this).parent().parent().parent().prev('li').find('.task').attr('checked', true);
            		} else {
            			//if unclicking only remaining subtask then unclick the task as well
            			var subtasks = $(this).parent().parent().find('.subtask');
            			var checked = false;
            			if (subtasks.length > 1) {
            				$.each(subtasks, function() {
            					if ($(this).is(":checked")) {
            						checked = true;
            						return false;
            					}
            				});
            			}
            			$(this).parent().parent().parent().prev('li').find('.task').attr('checked', checked);
            		}
            	});
            	            	
            	$("#submit").click( function() {
					//users must choose at least one task from my shiurim and my sensitivity
					var errors = '';
					errors += checkInputs(10, 'My Shiurim');
					errors += checkInputs(11, 'My Sensitivity');
					
					if (errors != '') {
						alert(errors);
						return false;
					}
            	});
            	               
                $("div.inner").hide();
                $("h3").click( function() {
                    $("div.inner").slideToggle('fast');
                });
                
                $("#createNewTask").click( function() {
                	var label = $("#newLabel").val();
                	var task = $("#existingTask").val();
                	if (task == -1) {
                		task = $("#newTask").val().trim();
                	}
                	var subtask = $("#newTaskDesc").val().trim();
                	var user = <?=$user_id?>;
                	
                	if (task == '' || subtask == '') {
                		alert( "You must choose / enter a task name and task description" );
                		return false;
                	} else {
                		$.post('ajax/createTask.php', {
                			 label : label, 
                			 task : task, 
                			 subtask : subtask, 
                			 user : user
                		}, function( data ) {
                			if (data == 1) {
                				alert("Task Created");
                				window.location.href = 'student_tasks2.php'; 
                				return true;
                			} else {
                				alert(data);
                				return false;
                			}
                		});
                	}
                	return false;
                });
                
                $("#newLabel").change( function() {
                	var val = $(this).val();
                	if (val != 0) {
                		//get tasks for this label
                		var user = <?=$user_id?>;
                		$.post('ajax/getTasksForLabel.php', {
                			user : user, 
                			label: val
                		}, function(data) {
                			$("#existingTask").empty();
                			$("#existingTask").append("<option value='0'>Choose Task</option>");
                			var tasks = $.parseJSON(data);
							for (i in tasks) {
                				$("#existingTask").append("<option value='" + tasks[i] + "'>" + tasks[i] + "</option>");
                			}
                			$("#existingTask").append("<option value='-1'>Create New Task</option>");
                			$("#existingTaskDiv").show();
                		});
                		//$("#newTaskDiv").show();
                		//$("#newTaskDescDiv").show();
                		//$("#createNewTask").show();
                	} else {
                		//$("#existingTaskDiv").hide();
                		$("#newTaskDescDiv").hide();
                		$("#newTaskDiv").hide();
                		$("#createNewTask").hide(); 
                	}
                });
                
                $("#existingTask").change( function() {
                	var val = $(this).val();
                	if (val == 0) {
                		$("#newTaskDescDiv").hide();
                		$("#newTaskDiv").hide();
                		$("#createNewTask").hide();
                	} else if (val == -1) {
                		$("#newTaskDiv").show();
                		$("#newTaskDescDiv").show();
                		$("#createNewTask").show();
                	} else {
                		$("#newTaskDiv").hide();
                		$("#newTaskDescDiv").show();
                		$("#createNewTask").show();
                	}
                });
            });
            
            function checkInputs(id, tab) {
            	var inputs = $("div#" + id + " input.task");
            	var checked = 0;
            	var shiurim = ['Tehillim', 'Chumash', 'Tanya'];
            	var inShiurim = false;
				$.each(inputs, function() {
					if ($(this).is(":checked")) {
						if (id == 10) {
							if ($.inArray($(this).val(), shiurim) != -1) {
								inShiurim = true;
							}
						}
						checked++;
					}
				});
				if (!checked) {
					return "You must choose at least one task from " + tab + ".\n";
				} else if (id == 10 && !inShiurim) {
					return "You must choose at least one of Tehillim or Chumash or Tanya under " + tab + ".\n";
				} else {
					return '';
				}
            }
        </script>
    </head>
    
    <body>
        <? require 'admin_header.php'; ?>
        <h1>Setup My Scoreboard</h1>
        
        <p><b>Welcome!</b><br /><br />
			<i>Please pay attention to point requirements listed under each section.<br />
			Remember: adding more than minimum amount needed will earn you<br />
			many exciting prizes and rewards!</i><br />
			<br />
			<b>Hatzlacha Rabba!</b>
		</p>
        
        <form method="post" action="student_tasks2.php">
            
            <div class="infobox2" id="tasks" style="padding: 10px">
            	
            	<div id="new">
            		<fieldset>
            			<legend>Create New Task</legend>
            			
            			<div id="newLabelDiv">
            				<select id="newLabel">
            					<option value="0">Choose Label</option>
	            				<?
	            				foreach ($labels as $id => $label) {
	            					echo "<option value=" . $id . ">" . $label . "</option>";
	            				}
	            				?>
	            			</select><br /><br />
	            		</div>
	            			
            			<div id="existingTaskDiv" style="display: none">
	            			<select id="existingTask">
	            			</select><br /><br />
	            		</div>
            			
            			<div id="newTaskDiv" style="display: none">	            			
	            			New Task Short Name: (e.g. Modeh Ani)<br />
	            			<input type="text" id="newTask" size="30" /><br /><br />
	            		</div>
            			
            			<div id="newTaskDescDiv" style="display: none">
	            			New Task Description: (e.g. as soon as I woke Up; with my hands together)<br />
	            			<input type="text" id="newTaskDesc" size="70" /><br /><br />
	            		</div>
	            		
            			<button id="createNewTask" style="display: none">Create</button>
            		</fieldset>
            	</div>
            	
            	<div style='clear: both'></div>
            	
            	<? $j = 1; ?>
            	<? foreach ($labels as $id => $label) : ?>
            	
            		<?
            		$points = 4;
					if (in_array($id, array(7,13,15))) {
						$points = 5;
					}
					if (in_array($id, array(9,10,14))) {
						$points = 6;
					}
					if ($id == 8) {
						$points = 8;
					}
            		?>
            		
	            	<div id="<?=$id?>">
	                    <fieldset>
	                        <legend><?=$label?></legend>
	                        <p><i><?=$points?> points needed</i></p>
							<?
							echo "<ul>";
							foreach ($tasks[$id] as $task) {
								//find out if task is mandatory
								$mand = $mandatoryTasks[$id][$task['date_task_id']];
								
								echo "<li>";
								echo "<input type='checkbox' class='task' 
									name='" . $task['date_task_id'] . "' 
									value='" . $task['name'] . "'";
								if ($mand) {
									echo " checked='checked' disabled";
								}
								if (array_key_exists($task['name'], $enrolledTasks[$id])) {
									echo " checked='checked'";
								}
								if (count($subtasks[$task['date_task_id']]) == 1 || $mand) {
									echo " style='display: none'";
								}
								echo " />" . $task['name'];
								if ($task['points']) {
									echo " (" . $task['points'] . " points)";
								}
								echo "</li>";
								echo "<div style='margin-left: 20px;'>";
								echo "<ul>";
								$i = 0;
								foreach ($subtasks[$task['date_task_id']] as $subtask) {
									$subMand = $mandatoryTasks[$id][$subtask['date_task_id']];
									echo "<li>";
									echo "<input type='checkbox' class='subtask' name='" . 
										$subtask['date_task_id'] . "'";
									if ($subMand) {
										echo " checked='checked' disabled";
									}
									if (array_key_exists($subtask['name'], $enrolledTasks[$id])) {
										echo " checked='checked'";
									}
									echo " />";
									echo $subtask['name'];
									if ($subtask['points']) {
										echo " (" . $subtask['points'] . " points)";
									}
									echo "</li>";
								}
								?>
								<!--New Subtask: <input type="text" name="newSubtask[<?=$id . ':' . $task['date_task_id']?>]" class="newTask" size="30" />-->
								<? 
								echo "</ul></div>";
							}
							echo "</ul>";
							?>	
							<!--<input type="checkbox" name="new[]" /> New Task: <input type="text" name="newTask[<?=$id?>]" class="newTask" size="30" />-->
	                    </fieldset> 
	                </div>
	                
	                <? if ($j++ % 2 == 0) : ?>
	                	<div style="clear: both"></div>
	                <? endif; ?>
                
                <? endforeach; ?>
                
                <div style="clear: both"></div>
                    
                    <div style="clear: both"></div>
                    <div align="center">
                        <input type="submit" name="submit" value="submit" id="submit" />
                    </div>
                </div>
                
            </div>
            
        </form>
    </body>
</html>