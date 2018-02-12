<?php	
include ("get_camp_id.php");
$camp_id = get_camp_id();

include ("classes/camp_task.php");
include ("classes/period.php");
include ("classes/group_type.php");
include ("classes/division.php");


$camp_mission_id = $_GET['camp_mission_id'];
$group_task = $_GET['group_task'];

// ***** PERIODS ***** //
$periods = array();
$sql = "SELECT * FROM periods WHERE period_id<>1 AND period_id<>3";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) {
	$period = new period();
	$period->new_period($row);
	array_push($periods, $period);
}

// ***** GROUP TYPES and DIVISIONS ***** //
$group_types = array();
$sql = "SELECT * FROM group_types WHERE camp_id=" . $camp_id;
$query = mysql_query($sql);	
while ($row = mysql_fetch_assoc($query)) {
	$group_type = new group_type($row);
	$group_type->get_divisions();
	array_push($group_types, $group_type);
}

// ***** CAMP TAKS ***** //
$camp_tasks = array();
$sql = "SELECT * FROM camp_tasks WHERE camp_mission_id=" . $_GET['camp_mission_id']; 
$query = mysql_query($sql);	
while ($row = mysql_fetch_assoc($query)) {
	$camp_task = new camp_task();
	$camp_task->new_camp_task($row);
	array_push($camp_tasks, $camp_task);
}
 ?>
 
		<script>
			var camp_id = <?=$camp_id;?>;
			var camp_mission_id = <?=$camp_mission_id;?>;
			var group_task = <?=$group_task;?>;
			
            $(document).ready(function() {
			 			 
				$('.editable').editable(function(value, settings) {                    
						return(value);
					},
					{
						indicator : '<img src="images/ajax-loader-sm.gif"/>',
						submit:'<img onclick="hide_editable(this);" src="images/bullet_disk.png"/>',
						onblur:'ignore',
						width:'auto',
						height:'auto',
						style:'inherit',
						tooltip: 'Click to edit...'              
					}
				);
						 
                $(".checklist input:checked").parent().addClass("selected");
						
				//**************************************//
				// ***** Assign a task to a group ***** //
                $(".checklist .checkbox-select").die().live('click',
                    function(event) {
						var list_item = $(this).parents('li');
						var checklist = $(this).parents('.checklist');
						
						$(list_item).css({ backgroundColor: '#9fe194' }).delay(500).animate({'background-color': '#eee'}, 500, function(){$(this).css({'background-color':''})});							

						info = $(this).parents('li').attr("id").split("_");	
						var camp_task_id = info[1];
						
						var groups = $(this).parents('li').find("select.group").val();
						
						if (groups == 0) {
							alert("Please select a group type or division");
						}
						else {
							var period = $(this).parents('li').find("select.period").val();

							if (group_task == 0) {
								function_name = "assign_tasks";				
								parameters = [camp_id, camp_task_id, groups, period, group_task];
							}
							else {
								function_name = "assign_group_tasks";				
								parameters = [camp_id, camp_task_id, groups, period, group_task];							
							}
							
							var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
							$.getJSON(url, function(assign) {
								if (assign == false) 
									alert("Could not assign tasks. Please try again.");
								else {
									$(list_item).find('.checklist').removeClass("selected");
									$(checklist).addClass("selected");
									$(checklist).find(":checkbox").attr("checked","checked");
								}
							});
							
						}
                    }
                );
				// ***** Assign a task to a group ***** //
				//**************************************//
				
				//****************************************//
				// ***** Remove a task from a group ***** //
                $(".checklist .checkbox-deselect").die().live('click',
                    function(event) {
						var list_item = $(this).parents('li');
					
						var info = $(this).parents('li').attr("id").split("_");
						var camp_task_id = info[1];
						var select_value = $(this).parents('li').find('select').get(1).value;
						
						var division = select_value.indexOf("division");
						if (division > -1) {
							var info = select_value.split("_");
							var division_id = info[1];
							function_name = "remove_division_task";				
							parameters = [camp_task_id, division_id, group_task];
						}
						else {
							var info = select_value.split("_");
							var group_type_id = info[2];
							function_name = "remove_group_type_task";				
							parameters = [camp_task_id, group_type_id, group_task];
						}
												
						var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;					
						alert(url);
						$.getJSON(url, function(success) {
							if (success == false) 
								alert("Could not remove group. Please try again");
							else
								$(list_item).find('.checklist').removeClass("selected");
						});						
                    }
                );
				// ***** Remove a task from a group ***** //
				//****************************************//
				
				$(".edit a, .edit_row input[type='text']").die().live('click',
                    function(event) {
                        event.preventDefault();
                        $(this).parents('li').addClass("editing");
                    }
                );
				
                $(".save a").die().live('click',
                    function(event) {
						var list_item = $(this).parents('li');
						var id = $(list_item).attr("id");
						var indexOf = id.indexOf("cti");
						
                        event.preventDefault();
                        $(this).parents('li').removeClass("editing");
						
						if (indexOf == -1) {
							var input_one = $(list_item).find('input').get(1);
							var task_name = input_one.value;
							var input_two = $(list_item).find('input').get(2);
							var points = input_two.value;													
							parameters = [camp_mission_id, task_name, points];
							function_name = "add_camp_task";
							var url = "includes/add_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						}
						else {
							var info = id.split("_");
							var camp_task_id = info[1];	
							var input_one = $(list_item).find('input').get(0);
							var task_name = input_one.value;
							var input_two = $(list_item).find('input').get(1);
							var points = input_two.value;							
							function_name = "update_task";
							parameters = [camp_task_id, task_name, points];
							var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						}
																	
						$.getJSON(url, function(success) {
							if (success == false) {
								if (function_name == "add_camp_task") 
									alert("Add new task failed. Please try again");
								else 
									alert("Task update failed. Please try again");
							}
							else {
								if (function_name == "add_camp_task") {
									var new_id = "cti_" + success;
									$(list_item).attr("id", new_id);
								}
							}							
						});
                    }
                );
				
                $(".delete a").die().live('click',
                    function(event) {
                        event.preventDefault();
						// code to delete and once successful do something like....
						
						var list_item = $(this).parents('li');
						var id = $(list_item).attr("id");
						var info = id.split("_");
						var camp_task_id = info[1];	
						
						var function_name = "delete_camp_task";
						var parameters = [camp_task_id];
						var url = "includes/delete_functions.php?function_name=" + function_name + "&parameters=" + parameters;
						
						alert("DELETE url:" + url);

                        $(this).parents('li').slideUp();
                    }
                );
				
                $("a.add_new_row").click(
                    function(event) {
                        event.preventDefault();
						var add_row_li = $(this).parents('li');
						var sample_row_data = $(this).parents('ul').find('.sample_row').html();
						$(add_row_li).before(sample_row_data);
						$(add_row_li).prev().find('input[type="text"]').first().click().val('').focus();						
						$(add_row_li).prev().find('select.select').resetSS();
                    }
                );
				
				$('select.select').sSelect();
				
				
            });	
			
			function decode(value) {
				var new_value = decodeURIComponent(value.replace(/\+/g,  " "));
				return new_value;
			}		

			//*************************************************************************************************************************//
			// ***** When changing the group drop down we need to check to see if the groups has already been assigned this task ***** //
			function check_checklist(slctbx) {
				var info = $(slctbx).parents('li').attr("id").split("_");
				var camp_task_id = info[1];
				var groups = slctbx.value;
				function_name = "get_group_task";
				parameters = [groups, camp_task_id, group_task];					
				var url = "includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
				$.getJSON(url, function(num_rows) {
					if (num_rows > 0) 
						$(slctbx).parents('li').find('.checklist').addClass("selected");
					else 
						$(slctbx).parents('li').find('.checklist').removeClass("selected");
				});
			}
			// ***** When changing the group drop down we need to check to see if the groups has already been assigned this task ***** //			
			//*************************************************************************************************************************//			
		</script>
			
			<div class="slider">
                <div class="col_title">
					<?=$_GET['mission_name'];?>
				</div>
				
				<div class="col_content">
				
					<div class="module"> 
		
						<div class="module_content">
			
							<div class="list">
							
								<ul>									
									<? for ($tno = 0; $tno < count($camp_tasks); $tno++) : ?>
									<? $gt_selected = ""; ?>
									<? $selected = ""; ?>
									<? $camp_task_id = $camp_tasks[$tno]->camp_task_id; ?>
									
									<li id="cti_<?=$camp_task_id;?>" class="edit_row">
										<form>
											<span class="icon bullet"></span>
											
											<span class="label">
												<span class="label title">Task</span>
												<input type="text" name="task" value="<?=$camp_tasks[$tno]->task_name;?>" />
											</span>
											
											<span class="label points">
												<span class="label title">Points</span>
												<input type="text" name="points" value="<?=$camp_tasks[$tno]->points;?>" />
											</span>
											
											<select id="period" name="period" class="select period">
											<? for ($pno = 0; $pno < count($periods); $pno++) : ?>											
												<option value="<?=$periods[$pno]->period_id;?>"><?=$periods[$pno]->period_name;?></option>
											<? endfor; ?>												
											</select>
											
											<select onchange="check_checklist(this);" id="groups" class="select group">
												<option value='0'>Please Select</option>
												<? for ($gtno = 0; $gtno < count($group_types); $gtno++) : ?>
													<optgroup label="<?=$group_types[$gtno]->group_type_name;?>">
													
													<? $gt_selected = $group_types[$gtno]->get_group_task($camp_task_id, $group_task); ?>													
													<? if ($gt_selected != "") $selected = $gt_selected; ?>
													
													<option <?=$gt_selected;?> value="group_type_<?=$group_types[$gtno]->group_type_id;?>">All <?=$group_types[$gtno]->group_type_name;?></option>
													
													<? for ($dno = 0; $dno < count($group_types[$gtno]->divisions); $dno++) :?>	
														<? $div_selected = ""; ?>
														<? if ($gt_selected == "") $div_selected = $group_types[$gtno]->divisions[$dno]->get_division_task($camp_task_id, $group_task); else $div_selected = ""; ?>
														<? if ($div_selected != "") $selected = $div_selected; ?>
														<option <?=$div_selected;?> value="division_<?=$group_types[$gtno]->divisions[$dno]->division_id;?>"><?=$group_types[$gtno]->divisions[$dno]->division_name;?></option>
													<? endfor; ?>
												<? endfor; ?>
											</select>

											<span class="action">
                                                <span class="checklist <?=$selected;?>">
                                                    <input type="checkbox" id="<?=$camp_tasks[$tno]->camp_task_id;?>" class="checkbox" />
													
                                                    <span class="activate">
														<a href="#" title="Activate" class="buttonHover checkbox-select">
															<span class="icon activate"></span>
															Activate
														</a>
													</span>
																										
                                                    <span class="deactivate">
														<a href="#" title="Deactivate" class="buttonHover checkbox-deselect">
															<span class="icon deactivate"></span>
															Deactivate
														</a>
													</span>
													
                                                    <span class="edit">
														<a href="#" title="Edit">
															<span class="icon"></span>
															Edit
														</a>
													</span>
                                                    <span class="save">
														<a href="#" title="Save">
															<span class="icon"></span>
															Save
														</a>
													</span>
                                                    <span class="delete">
														<a href="#" title="Delete">
															<span class="icon"></span>
															Delete
														</a>
													</span>
                                                </span>
											</span>

											
										</form>
									</li>
									<? endfor; ?>
									
									<!-- ********** ADD NEW TASK ********** -->
									<li>
										<span class="icon add"></span>                                                
										<span class="label"><a href="#" class="add_new_row">Add New Task</a></span>
									</li>
                                        
										<div class="sample_row">
											<li class="edit_row">
												<form>
												
													<span class="action">
														<span class="checklist">
															<input type="checkbox" id="new_row" class="checkbox" />
															<span class="activate"><a href="#" title="Activate" class="buttonHover checkbox-select"><span class="icon activate"></span>Activate</a></span>
															<span class="deactivate"><a href="#" title="Deactivate" class="buttonHover checkbox-deselect"><span class="icon deactivate"></span>Deactivate</a></span>
															<span class="edit"><a href="#" title="Edit"><span class="icon"></span>Edit</a></span>
															<span class="save"><a href="#" title="Save"><span class="icon"></span>Save</a></span>
															<span class="delete"><a href="#" title="Delete"><span class="icon"></span>Delete</a></span>
															<span class="progress">Progress</span>
														</span>
													</span>
													
													
													<span class="icon bullet">
													</span>
														
													<span class="label">
														<span class="label title">Task</span>
														<input type="text" name="task" value="Enter Name" />
													</span>
														
													<span class="label points">
														<span class="label title">Points</span>
														<input type="text" name="points" value="0" />
													</span>
															
													<select id="period" name="period" class="select period">
													<? for ($pno = 0; $pno < count($periods); $pno++) : ?>											
														<option value="<?=$periods[$pno]->period_id;?>"><?=$periods[$pno]->period_name;?></option>
													<? endfor; ?>												
													</select>
											
													<select onchange="check_checklist(this);" id="groups" class="select group">
														<option value='0'>Please Select</option>
														<? for ($gtno = 0; $gtno < count($group_types); $gtno++) : ?>
															<optgroup label="<?=$group_types[$gtno]->group_type_name;?>">															
															<option <?=$gt_selected;?> value="group_type_<?=$group_types[$gtno]->group_type_id;?>">All <?=$group_types[$gtno]->group_type_name;?></option>
															
															<? for ($dno = 0; $dno < count($group_types[$gtno]->divisions); $dno++) :?>	
																<option value="division_<?=$group_types[$gtno]->divisions[$dno]->division_id;?>"><?=$group_types[$gtno]->divisions[$dno]->division_name;?></option>
															<? endfor; ?>
														<? endfor; ?>
													</select>
													
												</form>
											</li>
											
											
									</div>
									<!-- ********** ADD NEW TASK ********** -->
										
								</ul>
					
							</div>
				
						</div>
     
					</div> 
						
				</div>
     
			</div> <!-- <div class="slider"> -->
