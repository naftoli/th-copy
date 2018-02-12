<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();
$task_date = $_GET['task_date'];
$group_type_id = $_GET['group_type_id'];
$group_no = $_GET['group_no'];
?>
			<script>			
				var task_date = "<?=$task_date;?>";
				var group_type_id = "<?=$group_type_id;?>";
				var group_no = <?=$group_no;?>;
				
				var group_id = 0;
				var user_ids = "";
				var camp_task_ids = "";				
				var inner_html = "";
				var no_of_missions = 0;
				
				$(document).ready(function() {
					var action = "get_marking_group_members";
					var params = [task_date, group_type_id, group_no];	
					var url = "../application/php/appInterface.php?action=" + action + "&params=" + params;	
					$.getJSON(url, function(group_members) {
						get_group_members(group_members);
						
						
						action = "get_number_of_missions";
						params = [task_date, group_id];
						url = "../application/php/appInterface.php?action=" + action + "&params=" + params;	
						$.getJSON(url, function(number_of_missions) {
							no_of_missions = number_of_missions;
							
							// ***** FIRST MISSION ***** //
							action = "get_group_date_tasks";
							params = [task_date, group_id, 1];
							url = "../application/php/appInterface.php?action=" + action + "&params=" + params;	
							$.getJSON(url, function(mission_one) {
								inner_html = get_missions(mission_one, 1);		
								//document.getElementById("mission_one").innerHTML = inner_html;

								// ***** MEMBER TASKS ***** //
								camp_task_ids = camp_task_ids.substr(0, (camp_task_ids.length - 1));
								action = "member_date_tasks";				
								params = [task_date, user_ids, camp_task_ids];				
								url = "../application/php/appInterface.php?action=" + action + "&params=" + params;
								$.getJSON(url, function(data) {
									inner_html = inner_html + get_tasks_html(data);								
									document.getElementById("mission_one").innerHTML = inner_html;								
											
									// ***** SECOND MISSION ***** //
									camp_task_ids = "";
									inner_html = "";
									action = "get_group_date_tasks";
									params = [task_date, group_id, 2];
									url = "../application/php/appInterface.php?action=" + action + "&params=" + params;	
									$.getJSON(url, function(mission_two) {
										inner_html = get_missions(mission_two, 2);

										// ***** MEMBER TASKS ***** //
										camp_task_ids = camp_task_ids.substr(0, (camp_task_ids.length - 1));
										action = "member_date_tasks";				
										params = [task_date, user_ids, camp_task_ids];				
										url = "../application/php/appInterface.php?action=" + action + "&params=" + params;
										$.getJSON(url, function(data) {
											inner_html = inner_html + get_tasks_html(data);								
											document.getElementById("mission_two").innerHTML = inner_html;
										});
										// ***** MEMBER TASKS ***** //
										
									});
									// ***** SECOND MISSION ***** //
									
								});
								// ***** MEMBER TASKS ***** //
								
							});
							// ***** FIRST MISSION ***** //
						
						});
						
					});									
										
				});
				
				function get_missions(missions_data, mission_number) {
					var innerHTML = "";
					
					mission = missions_data[0];
					
					innerHTML = innerHTML + "\t\t\t\t\t\t\t<div class='row mission_name'>\n";
					innerHTML = innerHTML + "\t\t\t\t\t\t\t\t<div class='cell'>" + mission.mission_name + "</div>\n";
					innerHTML = innerHTML + "\t\t\t\t\t\t\t</div>\n";								
					
					innerHTML = innerHTML + "\t\t\t\t\t\t\t<div class='row task_names'>\n";					
					for (cntr = 0; cntr < mission.tasks.length; cntr++) {
						task = mission.tasks[cntr];
						camp_task_ids = camp_task_ids + task.camp_task_id + ":";
												
						innerHTML = innerHTML + "\t\t\t\t\t\t\t\t<div class='cell'>\n";
						innerHTML = innerHTML + "\t\t\t\t\t\t\t\t\t<span>" + task.task_name + "</span>\n";
						innerHTML = innerHTML + "\t\t\t\t\t\t\t\t</div>\n";		
					}
					innerHTML = innerHTML + "\t\t\t\t\t\t\t</div>\n"; 

					return innerHTML;
				}
				
				function get_tasks_html(data) {
					var innerHTML = "\n";
					
					for (cntr1 = 0; cntr1 < data.length; cntr1++) {	
						var members = data[cntr1];
										
						for (cntr2 = 0; cntr2 < members.member_tasks.length; cntr2++) {	
							var tasks_member = members.member_tasks[cntr2];

							innerHTML = innerHTML + "\t\t\t\t\t\t<div class='row'>\n";				
							for (cntr3 = 0; cntr3 < tasks_member.tasks.length; cntr3++) {
								var member_task_info = tasks_member.tasks[cntr3];
								
								if (member_task_info.completed == 0) 
									checked = "";
								else
									checked = "checked";
												
								innerHTML = innerHTML + "\t\t\t\t\t\t\t<div name='" + checked + "' onclick='update_member_task(this, " + member_task_info.member_task_id + ");' class='cell checkbox " + checked + "'>\n";
								innerHTML = innerHTML + "\t\t\t\t\t\t\t\t<span>\n";
								innerHTML = innerHTML + "\t\t\t\t\t\t\t\t\t<input name='" + checked + "' type='checkbox' id='" + member_task_info.member_task_id + "' " + checked + ">\n";
								innerHTML = innerHTML + "\t\t\t\t\t\t\t\t</span>\n";
								innerHTML = innerHTML + "\t\t\t\t\t\t\t</div>\n";
							}
							innerHTML = innerHTML + "\t\t\t\t\t\t</div>\n";
						}
										
					}
					
					return innerHTML;					
				}	
				
				function get_group_members(group_members) {
					var members_html = "\n";
				
					var group = group_members[0];
					var last_group =  group.last_group;
					group_id = group.group_id;
					
					members_html = members_html + "\t\t\t\t<div class='mission_name'>\n";
					members_html = members_html + "\t\t\t\t\t<div class='cell'>" + group.group_name + "</div>\n";
					members_html = members_html + "\t\t\t\t</div>\n";
								
					members_html = members_html + "\t\t\t\t<div class='row task_names'>\n";
					members_html = members_html + "\t\t\t\t\t<div class='cell'></div>\n";
					members_html = members_html + "\t\t\t\t</div>\n";
								
					for (cntr2 = 0; cntr2 < group.members.length; cntr2++) {	
						member = group.members[cntr2];
						user_ids = user_ids + member.user_id + ":";
						members_html = members_html + "\t\t\t\t<div class='cell'>" + member.name + "</div>\n";
					}
				
					document.getElementById("col_names").innerHTML = members_html;
					
					button_1 = "";
					button_2 = "";
					button_3 = "";
					button_4 = "";
					
					if (group_no != 1) 
						button_1 = "<a class='button prev' href=''><span class='icon'></span>Previous Group</a>";
					
					if (last_group == false) {						
						//var next_group_no = group_no + 1;
						//alert("group_no:" + group_no + " next_group_no:" + next_group_no);						
						//button_2 = "<a class='button next' href='content.php?output=marking?task_date=" + task_date + "&group_type_id=" + group_type_id + "&group_no=" + next_group_no + "'><span class='icon'></span>Next Group</a>";
						button_2 = "<a onclick='get_next_group(this);' class='button next' href='#'><span class='icon'></span>Next Group</a>";						
					}
					
					document.getElementById("bot_nav").innerHTML = "<span class='mission_buttons'>" + 
									button_1 + 
									button_2 + 					
									"</span>				" + 
									"<span class='bunk_buttons'>" + 
									button_3 + 
									button_4 + 
									"</span>";	

					user_ids = user_ids.substr(0, user_ids.length - 1);
					
				}	

				function get_next_group(next_a_tag) {
					//e.preventDefault();
					var url = "content.php?output=marking&task_date=" + task_date + "&group_type_id=" + group_type_id + "&group_no=" + (group_no + 1);					
					$(next_a_tag).attr('href',url);
					alert(url);
					slideForward(next_a_tag);					
				}
				
				function update_member_task(chckbx, member_task_id) {
					var checked = $(chckbx).attr("name");
					
					var span = $(chckbx).find("span").get(0);
					
					var action = "save_member_mark";
					if (checked == "checked") {
						var params = [member_task_id, 0];
						//$(span).css("background-image", "url(http://www.mashpia.com/CampMotivationalSystem/dev/presentation/images/chk_off.png)");						
						//$(span).css("background", "url('images/chk_off.png') no-repeat scroll 0 0 transparent");
						$(span).parent().addClass('checked'); 
						//$(span).css("opacity", ".3"); 
						$(chckbx).attr("name", "");
					}
					else {
						var params = [member_task_id, 1];
						//$(span).css("background-image", "url(http://www.mashpia.com/CampMotivationalSystem/dev/presentation/images/chk_on.png)");
						//$(span).css("background", "url('images/chk_on.png') no-repeat scroll 0 0 transparent"); 
						$(span).parent().removeClass('checked'); 
						//$(span).css("opacity", ".3"); 
						$(chckbx).attr("name", "checked");
					}
					
					var url = "../application/php/appInterface.php?action=" + action + "&params=" + params;					
					$.getJSON(url, function(error_code) {
						if (error_code == 0) {
						}
						else {
							alert("Could not update mark.");
						}
					});
				}				
			</script>
			
		<div class="slider">
			
			<div class="col_title">
				<span>Camp Marking</span>
				<!--<a class="slider_back">back</a>-->
			</div>				
				
			<div class="col_content" id="col_content">
			
				<div id="module-info" class="module">
				
					<div class="module_conntent">
					
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
				
				<div class="bot_nav" id="bot_nav">
				</div>
				
			</div> <!-- <div class="col_content"> -->
				
		</div> <!-- <div class="slider"> -->
