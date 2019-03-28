<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

$months = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
$date_array =explode("/", $_GET['task_date']); 
$title = $months[$date_array[0] - 1] . " " . $date_array[1] . ", " . $date_array[2];
$task_date = gregoriantojd($date_array[0], $date_array[1], $date_array[2]);
$group_type_id = $_GET['group_type_id'];
$missions_display = $_GET['display']; // 0 = Unmarked ; 1 = Marked ; 2 = All
?>
			<script>
				var task_date = "<?=$task_date;?>";
				var group_type_id = "<?=$group_type_id;?>";
				var group_no = 0;
				var mission_no = 0;
				var missions_display = "<?=$missions_display;?>";				
				
				var divisions = "";
				var no_of_divisions = 0;
				var division_no = 0;
				
				var grp_mmbrs = "";
				var no_of_groups = 0;
				var group_no = 0;
				
				var mssns = "";
				var no_of_missions = 0;
				var mission_no = 0;
				
				var user_ids = "";
				var camp_task_ids = "";
				
				$(document).ready(function() {
					document.getElementById("prev_division").disabled = true;
					
					$('.bunk_buttons .prev').addClass('disabled').unbind('click').click(function(e){e.preventDefault();});
					$('.mission_buttons .prev').addClass('disabled').unbind('click').click(function(e){e.preventDefault();});
					
					var function_name = "get_marking_divisions";
					var parameters = [task_date, group_type_id];
					var url = "includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;					
					
					$.getJSON(url, function(dvsns) {	
						if (dvsns.length == 0) { 
							document.getElementById("bot_nav").style.display = "none";
							document.getElementById("no_tasks").style.display = "block";
						}
						else {
							no_of_divisions = dvsns.length;
							divisions = dvsns;							
							
							display_data();
						}
			
					});
					
				});
								
				function display_group_and_members(group_name, group_members) {
					user_ids = "";
					
					var members_html = "\n";
				
					members_html = members_html + "\t\t\t\t<div class='mission_name'>\n";
					members_html = members_html + "\t\t\t\t\t<div class='cell'>" + group_name + "</div>\n";
					members_html = members_html + "\t\t\t\t</div>\n";
							
					members_html = members_html + "\t\t\t\t<div class='row task_names'>\n";
					members_html = members_html + "\t\t\t\t\t<div class='cell'></div>\n";
					members_html = members_html + "\t\t\t\t</div>\n";
								
					for (gno = 0; gno < group_members.length; gno++) {	
						member = group_members[gno];
						user_ids = user_ids + member.user_id + ":";
						members_html = members_html + "\t\t\t\t<div class='cell'>" + member.first + " " + member.last + "</div>\n";
					}
					user_ids = user_ids.substr(0, user_ids.length - 1);
					
					document.getElementById("col_names").innerHTML = members_html;					
				}
				
				function prev_mission() {
					if (mission_no > 0) {
						mission_no--;
						display_data();
					}
				}
				
				function next_mission() {					
					if (mission_no < (no_of_missions - 1)) {
						mission_no++;
						display_data();
					}
				}
				
				function prev_group() {
					if (group_no > 0) {
						group_no--;
						display_data();
					}
				}
				
				function next_group() {
					if (group_no < (no_of_groups - 1)) {
						group_no++;
						display_data();
					}
				}
								
				function prev_division() {
					if (division_no > 0) {
						group_no = 0;
						mission_no = 0;
					
						division_no--;
						display_data();
					}
				}
				
				function next_division() {
					if (division_no < (no_of_divisions - 1)) {
						group_no = 0;
						mission_no = 0;
					
						division_no++;
						display_data();
					}
					
				}

				function display_mission(mission, div_name) {
					camp_task_ids = "";
					
					var innerHTML = "";
					innerHTML = innerHTML + "\t\t\t\t\t\t\t<div class='row mission_name'>\n";
					innerHTML = innerHTML + "\t\t\t\t\t\t\t\t<div class='cell'>" + mission.mission_name + "</div>\n";
					innerHTML = innerHTML + "\t\t\t\t\t\t\t</div>\n";								
					
					innerHTML = innerHTML + "\t\t\t\t\t\t\t<div class='row task_names'>\n";					
					for (tno = 0; tno < mission.tasks.length; tno++) {
						task = mission.tasks[tno];
						//alert("camp_task_id:" + camp_task_id);
						camp_task_ids = camp_task_ids + task.camp_task_id + ":";
												
						innerHTML = innerHTML + "\t\t\t\t\t\t\t\t<div class='cell'>\n";
						innerHTML = innerHTML + "\t\t\t\t\t\t\t\t\t<span>" + task.task_name + "</span>\n";
						innerHTML = innerHTML + "\t\t\t\t\t\t\t\t</div>\n";		
					}
					innerHTML = innerHTML + "\t\t\t\t\t\t\t</div>\n"; 
					
					camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);
					
					innerHTML = innerHTML + "<div id='" + div_name + "_tasks'>";
					innerHTML = innerHTML + "</div>";
					
					document.getElementById(div_name).innerHTML = innerHTML;				
				}
				
				function display_data() {
					// ***** DISPLAY NEW DIVISION ***** //
					division = divisions[division_no];
					document.getElementById("division_name").innerHTML = "<center>" + division.division_name + "</center>";
						
					// ***** DISPLAY NEW GROUP AND ITS MEMBERS ***** //
					groups = division.groups;
					no_of_groups = groups.length;
					group = groups[group_no];	
					display_group_and_members(group.group_name, group.members);
						
					// ***** DISPLAY NEW MISSION AND ITS TASKS ***** //
					missions = group.missions;	
					no_of_missions = missions.length;
					mission = missions[mission_no];
					display_mission(mission, "mission_one");
					get_member_tasks();
				}
				
				function get_member_tasks() {
					var function_name = "get_member_tasks";
					var parameters = [task_date, user_ids, camp_task_ids];
					var url = "includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					
					//alert(url);
					
					$.getJSON(url, function(member_tasks) {
						var innerHTML = "";								
						for (mno = 0; mno < member_tasks.length; mno++) {
							var member =  member_tasks[mno];	
							
							innerHTML = innerHTML + "\t\t\t\t\t\t<div class='row'>\n";	
							for (mtno = 0; mtno < member.member_tasks.length; mtno++) {
								member_task = member.member_tasks[mtno];
								
								// ***** DISPLAY ****** //
								if (member_task.completed == 1)
									checked = "checked";
								else
									checked = "unchecked";
									
								innerHTML = innerHTML + "\t\t\t\t\t\t\t<div name='checkbox_div' id='" + member_task.member_task_id + "' onclick='update_member_task(this, " + member_task.member_task_id + ");' class='cell checkbox " + checked + "'>\n";
								innerHTML = innerHTML + "\t\t\t\t\t\t\t\t<span>\n";
								innerHTML = innerHTML + "\t\t\t\t\t\t\t\t\t<input name='" + checked + "' type='checkbox' id='" + member_task.member_task_id + "' " + checked + ">\n";
								innerHTML = innerHTML + "\t\t\t\t\t\t\t\t</span>\n";
								innerHTML = innerHTML + "\t\t\t\t\t\t\t</div>\n";								
							}
							innerHTML = innerHTML + "\t\t\t\t\t\t</div>\n";
							
						}
						document.getElementById("mission_one_tasks").innerHTML = innerHTML;	
					});
				}				
				
				function check_all(completed) {	
					if (completed == 0)
						checked = "unchecked";
					else
						checked = "checked";
					
					var member_task_ids = "";
					
					var member_tasks_div = $("div[id=mission_one_tasks]");
					var checkbox_divs = $(member_tasks_div).find("div[name=checkbox_div]");
					
					for (cbno = 0; cbno < $(checkbox_divs).size(); cbno++) {
						var checkbox_div = $(checkbox_divs).get(cbno);
						member_task_ids = member_task_ids + $(checkbox_div).attr("id") + ":";
					}
					member_task_ids = member_task_ids.substr(0, member_task_ids.length - 1);
					
					var function_name = "update_member_tasks";
					var parameters = [member_task_ids, completed];
					var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;

					$.getJSON(url, function(success) {
						if (success == true) {
							for (cbno = 0; cbno < $(checkbox_divs).size(); cbno++) {															
								var checkbox_div = $(checkbox_divs).get(cbno);
								$(checkbox_div).removeClass('checked');
								$(checkbox_div).removeClass('unchecked');
								$(checkbox_div).addClass(checked);								
							}
						}
						else {
							alert("Update failed. Please try again");
						}
					});
				}
				
				function update_member_task(chckbx, member_task_id) {
					var function_name = "update_member_task";
					if ($(chckbx).hasClass('checked')) 
						var parameters = [member_task_id, 0];
					else
						var parameters = [member_task_id, 1];
					var url = "includes/edit_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(success) {
					
						if (success == true) {
							if ($(chckbx).hasClass('checked')) {
								$(chckbx).removeClass('checked');
								$(chckbx).addClass('unchecked');							
							}
							else {
								$(chckbx).removeClass('unchecked');
								$(chckbx).addClass('checked');							
							}
						}
						else {
							alert("Error updating task. Please try again");
						}
						
					});
				}				
			</script>
			
		<div class="slider">
		
			<input type="hidden" name="TASK DATE" value="<?=$task_date;?>">
			
			<div class="col_title">
				<span>Camp Marking</span>
			</div>				
				
			<div class="col_content" id="col_content">
			
				<h1><?=$title;?></h1>
				
				<h2 id="division_name"></h2>
				
				
				
					<div class="marking_buttons">
							<span class="division_buttons">
								<a class="button prev" id="prev_division" onclick="prev_division();" href="#"><span class="icon"></span>Previous Division</a>
								<a class="button next" id="next_division" onclick="next_division();" href="#"><span class="icon"></span>Next Divison</a>
							</span>
							
							<span class="group_buttons">
								<a class="button prev" id="prev_group" onclick="prev_group();" href="#"><span class="icon"></span>Previous Group</a>
								<a class="button next" id="next_group" onclick="next_group();" href="#"><span class="icon"></span>Next Group</a>
							</span>

							<span class="mission_buttons">
								<a class="button prev" id="prev_mission" onclick="prev_mission();" href="#"><span class="icon"></span>Previous Mission</a>
								<a class="button next" id="next_mission" onclick="next_mission();" href="#"><span class="icon"></span>Next Mission</a>
							</span>							
					</div>
					<div class="check_all_links">
							<span class="check_all">
								<a class="check_all" onclick="check_all(1);" href="#"><span class="icon"></span>Check All</a>
							</span>							
							<span class="uncheck_all">
								<a class="uncheck_all" onclick="check_all(0);" href="#"><span class="icon"></span>Uncheck All</a>
							</span>
					</div>
				
				
				
				<div id="module-info" class="module">
				
					<div class="module_conntent">
					
						<div id="no_tasks" name="no_tasks" class="no_tasks" style="display:none">
							No Tasks found for this date. Please try another date.
						</div>
						
						<div class="marking">
							
							<div class="col names" id="col_names">
							</div> 
								
							<div class="mission_window">
								
								<div class="mission_container">									
									<div class="missions">										
										<div class="col mission" id="mission_one">
										</div> 
											
										<div class="col mission" id="mission_two">
										</div> 																					
									</div> 										
								</div>
									
							</div>
								
							<div class="clear"></div>
								
						</div> <!-- <div class="marking"> -->
							
					</div> <!-- <div class="module_conntent"> -->						
					
				</div> <!-- <div class="module"> -->				
				
			</div> <!-- <div class="col_content"> -->
				
		</div> <!-- <div class="slider"> -->
