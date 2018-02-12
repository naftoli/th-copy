<?
require 'header.php';
	
require_once '../db.php';
require_once '../class.achosCustomization.php'; 
$ac = new AchosCustomization();
$ac->setStudent($_SESSION['user_id']);

$ac->setTasks();
$tasks = $ac->getTasks();
$taskPoints = $ac->getTaskPoints();
$ac->setEnrolledTasks();
$enrolledTasks = $ac->getEnrolledTasks();
$mandatoryTasks = $ac->getMandatoryTasks();
$labels = $ac->getLabels();
?>
<!doctype html>
<html class="no-js" lang="">
    <head>
    	<? include 'inc/head.php' ?>
        <title></title>
    </head>
		
    <body class="page-goals">
        <header class="navbar" id="top" role="banner">
            <div class="container">
                <div class="navbar-header">
                	<h1>My Goals</h1>
                </div>
            </div>
        </header>
        
        <div class="container">
            <div class="content">
                <form name="goals" method="post" action="setGoals.php">
                	<p>
                        <button id="save" class="btn btn-danger btn-sm">Save</button>
                    </p>
                <?
                foreach ($labels as $id => $label) {
                	/*
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
					 * 
					 */
                ?>
                
                <div class="panel panel-default">
                	<div class="panel-heading"><i class="glyphicon glyphicon-chevron-right"></i><?=$label?>
                		<!--<div class="pull-right small points"><?=$points?> Points Needed</div>-->
                	</div>
                	
                	<div class="collapse">
                        <div class="panel-body" id="<?=$id?>">
                        	<!--
                        	<div class="label label-danger addNew"><i class="glyphicon glyphicon-plus"></i> Add New Task to <?=$label?></div>
							<div class="form-group newGroup">
								<br />
                                <input type="text" class="form-control newTask" value="New Task" />
                                <input type="text" class="form-control newSubtask" value="Description" />
                                <button id="create" class="btn btn-danger btn-sm">Create</button>
                            </div>
							-->
		                	<?
		                	foreach ($tasks[$id] as $task) {
								//find out if task is mandatory
								$mand = $mandatoryTasks[$id][$task['date_task_id']];
		                	?>
                	
                        	<div class="task-group">
                        		<!--
                                <div class="dropdown pull-right">
                                    <button class="btn btn-link btn-sm dropdown-toggle" type="button" data-toggle="dropdown"><span class="caret"></span></button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Action</a></li>
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Another action</a></li>
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Something else here</a></li>
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#">Separated link</a></li>
                                    </ul>
                                </div>
                               -->
                                <div class="actions">
                                	<!--<a href="#" class="btn btn-link btn-xs"><i class="glyphicon glyphicon-plus add"></i></a>-->
                                	<? if ($task['created_by']) : ?>
                                	<a href="#" class="btn btn-link btn-xs"><i class="glyphicon glyphicon-edit edit"></i></a>
                                	<a href="#" class="btn btn-link btn-xs"><i class="glyphicon glyphicon-trash delete"></i></a>
                                	<? endif; ?>
                                </div>
                                <div class="task-header">
                                	<?
                                	//find out if task is mandatory
									$mand = $mandatoryTasks[$id][$task['date_task_id']];
									
									echo "<h4><input type='checkbox' class='task' 
										name='" . $task['date_task_id'] . "' 
										value='" . $task['name'] . "'";
									if ($mand) {
										echo " checked='checked' disabled";
									}
									if (array_key_exists($task['name'], $enrolledTasks[$id])) {
										echo " checked='checked'";
									}
									//if (count($subtasks[$task['date_task_id']]) == 1 || $mand) {
									//	echo " style='display: none'";
									//}
									echo " /> <span style='font-size: 14px;'>" . $task['name'] . "</span>";
									if (!empty($task['description'])) 
										echo "<span style='font-size: 11px;'> - " . $task['description'] . "</span>";
									if ($task['points']) {
										echo " <span style='font-size: 11px; font-weight: bold'>(" . $task['points'] . " points)</span>";
									}
									echo "</h4>";
									?>
                               	</div>
                                <!--
                                <ul class="list-unstyled">
                                	<?
                                	foreach ($subtasks[$task['date_task_id']] as $subtask) {
                                		echo "<li>";
                                		if ($subtask['created_by']) {
                                		?>
                                		<div class="actions">
		                                	<a href="#" class="btn btn-link btn-xs"><i class="glyphicon glyphicon-edit editSub"></i></a>
		                                	<a href="#" class="btn btn-link btn-xs"><i class="glyphicon glyphicon-trash deleteSub"></i></a>
		                                </div>
                                		<?
										}
										$subMand = $mandatoryTasks[$id][$subtask['date_task_id']];
                                    	echo "<input type='checkbox' class='subtask' name='" . 
											$subtask['date_task_id'] . "'";
										if ($subMand) {
											echo " checked='checked' disabled";
										}
										if (array_key_exists($subtask['name'], $enrolledTasks[$id])) {
											echo " checked='checked'";
										}
										echo " /> ";
										echo "<span class='subName'>" . $subtask['name'] . "</span>";
										if ($subtask['points']) {
											echo " (" . $subtask['points'] . " points)";
										}
										echo "</li>";
                                    } 
                                    ?>
                                </ul>
                                -->
                                <div class="form-group">
                                    <input type="text" class="form-control sub" />
                                </div>
                            </div>
                            
                            <? } ?>
                            
                        </div>
                    </div>
                </div>
                
                <? } ?>
                
                </form>
                
            </div>
        </div>
         
    	<? include 'inc/footer.php' ?>

    	<? include 'inc/foot.php' ?>
    	
    	<script>
    		$( function() {
    			$(".form-group").hide();
    			
    			$(".addNew").click( function() {
    				var newInput = $(this).parent().find(".newGroup");
    				newInput.show();
    				newInput.find(".newTask").select();
    			});
    			
    			$("#create").click( function() {
    				var user = <?=$_SESSION['user_id']?>;
    				var label = $(this).parent().parent().attr('id');
    				var desc = $(this).parent().find('.newSubtask').val().trim();
    				var task = $(this).parent().find('.newTask').val().trim();    				
    				var add = confirm("Are you sure you want to add " + task + " ?");
    				if (add) {
	    				$.post('../ajax/createTask.php', {
                			 label : label, 
                			 task : task, 
                			 desc : desc, 
                			 user : user
                		}, function( data ) {
                			if (data == 1) {
                				alert("Task Created");
                				window.location.href = 'goals.php';
                			} else {
            					alert(data);
                			}
                		});
	    			} else {
	    				//$(this).val('');
	    				$(this).parent().hide();
	    			}
    				return false;
    			});
    			/*
    			$(".add").click( function() {
    				var input = $(this).parent().parent().parent().find(".form-group");
    				input.show();
    				input.find(".sub").focus();
    			});
    			
    			$(".sub").blur( function() {
    				var user = <?=$_SESSION['user_id']?>;
    				var label = $(this).parent().parent().parent().attr('id');
    				var subtask = $(this).val().trim();
    				var task = $(this).parent().parent().find("h4.task-header").text().trim();    				
    				var add = confirm("Are you sure you want to add \"" + subtask + "\" to " + task + " ?");
    				if (add) {
	    				$.post('../ajax/createTask.php', {
                			 label : label, 
                			 task : task, 
                			 subtask : subtask, 
                			 user : user
                		}, function( data ) {
                			if (data == 1) {
                				alert("Task Created");
                				window.location.href = 'goals.php';
                			} else {
            					alert(data);
                			}
                		});
	    			} else {
	    				//$(this).val('');
	    				$(this).parent().hide();
	    			}
    			});
    			*/
    			$(".delete").click( function() {
    				var user_id = <?=$_SESSION['user_id']?>;
    				var task = $(this).parent().parent().parent().find('h4.task-header').text().trim();
    				var label = $(this).parent().parent().parent().parent().attr('id');
    				var del = confirm("Are you sure you want to delete " + task + " ?");
    				if (del) {
	    				$.post('../ajax/deleteTask.php', {
	    					task : task, 
	    					label : label, 
	    					is_task : 1, 
	    					user_id : user_id
	    				}, function( data ) {
	    					if (data == 1) {
	    						alert("Deleted.");
	    						window.location.href = "goals.php";
	    					} else {
	    						alert( data );
	    					}
	    				});
	    			}
    			});
    			/*
    			$(".deleteSub").click( function() {
    				var user_id = <?=$_SESSION['user_id']?>;
    				var task = $(this).parent().parent().parent().text().trim();
    				var label = $(this).parent().parent().parent().parent().parent().parent().attr('id');
    				var del = confirm("Are you sure you want to delete " + task + " ?");
    				if (del) {
	    				$.post('../ajax/deleteTask.php', {
	    					task : task, 
	    					label : label, 
	    					is_task : 0, 
	    					user_id : user_id
	    				}, function( data ) {
	    					if (data == 1) {
	    						alert("Deleted.");
	    						window.location.href = "goals.php";
	    					} else {
	    						alert( data );
	    					}
	    				});
	    			}
    			});
    			*/
    			$(".edit").click( function() {
    				var h4 = $(this).parent().parent().parent().find('h4.task-header');
    				var task = h4.text().trim();
    				h4.html("<input type='text' size='" + task.length + "' class='editTask' value='" + task + "' />");
    				h4.after("<input class='oldTask' type='hidden' value='" + task + "' />");
    				h4.find("input").select();
    				
    				$(".editTask").blur( function() {
	    				var user = <?=$_SESSION['user_id']?>;
	    				var label = $(this).parent().parent().parent().parent().attr('id');
	    				var oldTask = $(this).parent().parent().find('.oldTask').val();
	    				var newTask = $(this).val().trim();
	    				$.post('../ajax/editTask.php', {
	    					user : user, 
	    					label : label, 
	    					oldTask : oldTask, 
	    					newTask : newTask
	    				}, function( data ) {
	    					if (data == 1) {
	    						alert("Task has been changed.");
	    						window.location.href = "goals.php";
	    					} else {
	    						alert( data );
	    					}
	    				});
	    			});
    			});
    			/*
    			$(".editSub").click( function() {
    				var sub = $(this).parent().parent().parent().find('.subName');
    				var subtask = sub.text().trim();
    				sub.html("<input type='text' size='" + subtask.length + "' class='editSubTask' value='" + subtask + "' />");
    				sub.after("<input class='oldSub' type='hidden' value='" + subtask + "' />");
    				sub.find("input").select();
    				
    				$(".editSubTask").blur( function() {
    					var user = <?=$_SESSION['user_id']?>;
    					var label = $(this).parent().parent().parent().parent().parent().attr('id');
    					var oldTask = $(this).parent().parent().find('.oldSub').val();
    					var newTask = $(this).val().trim();
    					$.post('../ajax/editTask.php', {
	    					user : user, 
	    					label : label, 
	    					oldTask : oldTask, 
	    					newTask : newTask
	    				}, function( data ) {
	    					if (data == 1) {
	    						alert("Task has been changed.");
	    						window.location.href = "goals.php";
	    					} else {
	    						alert( data );
	    					}
	    				});
    				});
    			});
    			*/
    		})
    	</script>
        
    </body>
</html>		