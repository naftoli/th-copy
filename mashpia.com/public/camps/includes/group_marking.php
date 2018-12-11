<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

$months = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

$strrpos = strrpos($_GET['task_date'], "/");  
if ($strrpos > -1) {
	$date_array = split("/", $_GET['task_date']); 
	$task_date = gregoriantojd($date_array[0], $date_array[1], $date_array[2]);    // gregoriantojd 
	$title = $months[$date_array[0] - 1] . " " . $date_array[1] . ", " . $date_array[2];	
}
else {
	$task_date = $_GET['task_date'];
}

$group_type_id = $_GET['group_type_id'];
$division_no = $_GET['division_no'];
$mission_no = $_GET['mission_no'];
$group_type_name = $_GET['group_type_name'];
?>
			<script>	
				var task_date = "<?=$task_date;?>";
				var group_type_id = "<?=$group_type_id;?>";
				var group_type_name = "<?=$group_type_name;?>";
				
				var campaigns = "";
				var no_of_campaigns = 0;
				var campaign_no = 0;
				
				var no_of_divisions = 0;
				var division_no = 0;
				
				var no_of_groups = 0;
				var group_no = 0;
				
				var no_of_missions = 0;	
				var mission_no = 0;
							
				var group_ids = "";
				var camp_task_ids = "";
				
				$(document).ready(function() {
					var function_name = "get_group_point_campaigns";
					var parameters = [group_type_id, task_date];	
					var url = "includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;	
					//alert(url);
					$.getJSON(url, function(cmpgns) {
						if (cmpgns.length == 0) { 
							document.getElementById("bot_nav").style.display = "none";
							document.getElementById("no_tasks").style.display = "block";
						}
						else {							
							campaigns = cmpgns;
							no_of_campaigns = campaigns.length;	
							display_data();
						}
					});
				});
				
				function display_data() {
					campaign = campaigns[campaign_no];
					document.getElementById("campaign_name"). innerHTML = "<center><h1>" + campaign.campaign_name + "<h1></center>";
									
					no_of_divisions = campaign.divisions.length;
					division = campaign.divisions[division_no];
					display_division_and_groups(division);

					no_of_missions = division.missions.length;
					mission =  division.missions[mission_no];					
					display_mission_and_tasks(mission, "mission_one");
					
					get_group_tasks();
				}
				
				function get_group_tasks() {
					var function_name = "get_group_tasks";
					var parameters = [task_date, group_ids, camp_task_ids];
					var url = "includes/get_functions.php?function_name=" + function_name + "&parameters=" + parameters;
					$.getJSON(url, function(group_tasks) {
						var innerHTML = "";			
						for (gtno = 0; gtno < group_tasks.length; gtno++) {
							var group = group_tasks[gtno];								
							
							innerHTML = innerHTML + "<div class='row'>";	
							for (tno = 0; tno < group.tasks.length; tno++) {
								task = group.tasks[tno];
								
								if (task.completed == 0)
									checked = "unchecked";
								else
									checked = "checked";
									
								innerHTML = innerHTML + "<div name='checkbox_div' id='" + task.group_task_date_id + "' onclick='update_group_task(this, " + task.group_task_date_id + ");' class='cell checkbox " + checked + "'>";
								innerHTML = innerHTML + "<span>";
								innerHTML = innerHTML + "<input name='" + checked + "' type='checkbox' id='" + task.group_task_date_id + "' " + checked + ">";
								innerHTML = innerHTML + "</span>";
								innerHTML = innerHTML + "</div>";								
							}
							innerHTML = innerHTML + "</div>";
							
						}
						document.getElementById("mission_one_tasks").innerHTML = innerHTML;	

					});
				}

				function update_group_task(chckbx, group_task_date_id) {
					var function_name = "update_group_task";
					if ($(chckbx).hasClass('checked')) 
						var parameters = [group_task_date_id, 0];
					else
						var parameters = [group_task_date_id, 1];										
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
				
				function display_mission_and_tasks(mission, div_name) {
					var innerHTML = "";
					innerHTML = innerHTML + "<div class='row mission_name'>";
					innerHTML = innerHTML + "<div class='cell'>" + mission.mission_name + "</div>";
					innerHTML = innerHTML + "</div>";								
					
					innerHTML = innerHTML + "<div class='row task_names'>";
					
					camp_task_ids = "";
					for (tno = 0; tno < mission.tasks.length; tno++) {
						task = mission.tasks[tno];
						innerHTML = innerHTML + "<div class='cell'>";
						innerHTML = innerHTML + "<span>" + task.task_name + "</span>";
						camp_task_ids = camp_task_ids + task.camp_task_id + ":";
						innerHTML = innerHTML + "</div>";		
					}
					camp_task_ids = camp_task_ids.substr(0, (camp_task_ids.length - 1));
					
					innerHTML = innerHTML + "</div>"; 					
					innerHTML = innerHTML + "<div id='" + div_name + "_tasks'>";
					innerHTML = innerHTML + "</div>";
					
					document.getElementById(div_name).innerHTML = innerHTML;
				}
				
				function get_campaign(cmpgn_no) {
					campaign_no = cmpgn_no;
					division_no = 0;
					group_no;
					mission_no = 0;
					
					display_data();
				}				
				
				function next_campaign() {
					if (campaign_no < (no_of_campaigns - 1)) {
						division_no = 0;
						group_no;
						mission_no = 0;
						campaign_no++;
						
						display_data();
					}
				}	

				function prev_campaign() {
					if (campaign_no > 0) {
						division_no = 0;
						group_no = 0;
						mission_no = 0;
						campaign_no--;
						
						display_data();
					}
				}	
				
				function next_division() {
					if (division_no < (no_of_divisions - 1)) {
						group_no;
						mission_no = 0;
						division_no++;
						
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
				
				function display_division_and_groups(division) {
					var groups = division.groups;
					
					var members_html = "";
					
					members_html = members_html + "<div class='mission_name'>\n";
					members_html = members_html + "<div class='cell'>" + division.division_name + "</div>";
					members_html = members_html + "</div>";
					
					members_html = members_html + "<div class='row task_names'>";
					members_html = members_html + "<div class='cell'></div>";
					members_html = members_html + "</div>";
					
					group_ids = "";
					for (gno = 0; gno < groups.length; gno++) {	
						group = groups[gno];
						members_html = members_html + "<div class='cell'>" + group.group_name + "</div>";
						group_ids = group_ids + group.group_id + ":";
					}
					group_ids = group_ids.substr(0, (group_ids.length - 1));
					
					document.getElementById("col_names").innerHTML = members_html;
				}

				function check_all(completed) {	
					if (completed == 0)
						checked = "unchecked";
					else
						checked = "checked";
					
					var group_task_date_ids = "";
					
					var group_tasks_div = $("div[id=mission_one_tasks]");
					var checkbox_divs = $(group_tasks_div).find("div[name=checkbox_div]");
					
					for (cbno = 0; cbno < $(checkbox_divs).size(); cbno++) {
						var checkbox_div = $(checkbox_divs).get(cbno);
						group_task_date_ids = group_task_date_ids + $(checkbox_div).attr("id") + ":";
					}
					group_task_date_ids = group_task_date_ids.substr(0, group_task_date_ids.length - 1);
					
					var function_name = "update_group_tasks";
					var parameters = [group_task_date_ids, completed];
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
			</script>
			
			
		<div class="slider">
		
			<input type="hidden" name="TASK DATE" value="<?=$task_date;?>">
			
			<div class="col_title">
				<span>Group Marking</span>
			</div>				
			
			<div class="col_content" id="col_content">

				<div class="marking_buttons">
					<span class="campaign_buttons">
						<a class="button prev" id="prev_campaign" onclick="prev_campaign();" href="#"><span class="icon"></span>Previous Campaign</a>
						<a class="button next" id="next_campaign" onclick="next_campaign();" href="#"><span class="icon"></span>Next Campaign</a>
					</span>
				
					<span class="division_buttons">
						<a class="button prev" id="prev_division" onclick="prev_division();" href="#"><span class="icon"></span>Previous Division</a>
						<a class="button next" id="next_division" onclick="next_division();" href="#"><span class="icon"></span>Next Divison</a>
					</span>
						
					<span class="mission_buttons">
						<a class="button prev" id="prev_mission" onclick="prev_mission();" href="#"><span class="icon"></span>Previous Mission</a>
						<a class="button next" id="next_mission" onclick="next_mission();" href="#"><span class="icon"></span>Next Mission</a>
					</span>							
				</div>

				<br />
				<br />
				
				<center><h1><?=$group_type_name;?><h1></center>
				<center><H1><?=$title;?></H1></center>				
				<div id="campaign_name"></div>
				
				<div id="module-info" class="module">
				
					<div class="module_conntent">
					
						<div id="no_tasks" name="no_tasks" style="display:none">
							<center>No Tasks found for this date. Please try another date.</center>
						</div>
												
						<form action="content.php?output=marking" id="points_form">
						
							<div class="marking">
							
								<div class="col names" id="col_names">
								</div> <!-- <div class="col names"> -->
								
								<div class="mission_window">
								
									<div class="mission_container">
									
										<div class="missions">
										
											<div class="col mission" id="mission_one">
											</div> <!-- <div class="col mission" id="mission_one"> -->
											
											<div class="col mission" id="mission_two">
											</div> <!-- <div class="col mission" id="mission_two"> -->
											
											<div class="col mission" id="mission_three">
											</div> <!-- <div class="col mission" id="mission_three"> -->
											
										</div> <!-- <div class="missions"> -->
										
									</div> <!-- <div class="mission_container"> -->
									
								</div> <!-- <div class="mission_window"> -->
								
								<div class="clear"></div>
								
							</div> <!-- <div class="marking"> -->
							
						</div> <!-- <div class="module_conntent"> -->
					</form>
					
				</div> <!-- <div class="module"> -->
				
				
				<center>
					<div>
						<center>
							<span>
								<a class="button prev" href="#" onclick="check_all(1);">
									<span class="icon"></span>Check All
								</a>
								<a id="next_button" class="button next" href="#" onclick="check_all(0);">
									<span class="icon"></span>Un-Check All
								</a>
							</span>
						</center>					
					</div>
				</center>
				
				
			</div> <!-- <div class="col_content"> -->
				
		</div> <!-- <div class="slider"> -->
