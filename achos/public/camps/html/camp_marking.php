<?php
?>
			<script>
			
	var group_id = "";  	
	var user_ids = "";		
	var camp_task_ids = "";

	var number_of_missions_to_show = 2;
	var number_of_missions = 0;
	
	var tasks_html = "";
	
	var missions_data = "";
	var member_tasks_one = "";
	var task_date = 2455386;
	
			
	display_missions(task_date, 0, 1);
		
	function display_missions(task_date, mission_one, mission_two) {
		var action = "start_marking_session_users";
		var params = [task_date, "164", "1", mission_one, mission_two];	// task_date, group_type_id, group_no, mission_no_start, mission_no_end
		var url = "../application/php/appInterface.php?action=" + action + "&params=" + params;	
		
		alert(url);
		
		$.getJSON(url, function(members) {
	
			alert(members.length);
			
			if (members.length > 0) { 			
				document.getElementById("col_names").innerHTML = get_members(members);
				
				user_ids = user_ids.substr(0, user_ids.length - 1);
				
				// ***** FIRST MISSION ***** //
				var action2 = "start_marking_session_missions";
				var params2 = [task_date, group_id, 1];
				var url2 = "../application/php/appInterface.php?action=" + action2 + "&params=" + params2;
				$.getJSON(url2, function(missions) {
					missions_data = missions;
					if (missions.length > 0) { 
						var html1 = get_missions(missions, mission_one);
						camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);
						
						// ***** MEMBER TASKS ***** //
						action = "start_marking_session_member_tasks";				
						params = [task_date, user_ids, camp_task_ids];				
						url = "../application/php/appInterface.php?action=" + action + "&params=" + params;
						$.getJSON(url, function(data) {
							member_tasks_one = data;
							var html2 = get_tasks_html(data);	
							document.getElementById("mission_one").innerHTML = html1 + html2;
						});
						// ***** MEMBER TASKS ***** //
						
						// ***** SECOND MISSION ***** //
						if (mission_two > 0) {							
							var action3 = "start_marking_session_missions";
							var params3 = [task_date, group_id, 2];
							var url3 = "../application/php/appInterface.php?action=" + action3 + "&params=" + params3;
							$.getJSON(url3, function(missions) {	
								if (missions.length > 0) { 
									camp_task_ids = "";
									html1 = get_missions(missions, mission_two);
									camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);

									
									// ***** MEMBER TASKS ***** //
									action = "start_marking_session_member_tasks";				
									params = [task_date, user_ids, camp_task_ids];				
									url = "../application/php/appInterface.php?action=" + action + "&params=" + params;				
									$.getJSON(url, function(data) {
										html2 = get_tasks_html(data);							
										document.getElementById("mission_two").innerHTML = html1 + html2;
									});
									// ***** MEMBER TASKS ***** //
									
								}
							});						
						}
						// ***** SECOND MISSION ***** //
						
					}
					
					if (number_of_missions_to_show < number_of_missions) {
						document.getElementById("bot_nav").innerHTML = get_bot_nav(mission_one, mission_two, number_of_missions_to_show, number_of_missions);
					}
					
				});
				// ***** FIRST MISSION ***** //
				
			}
			
		});			
	  
    }  
			
			</script>
			
		<div class="slider">
			
			<div class="col_title">
				<span>Camp Marking</span><a class="slider_back">back</a>
			</div>				
				
			<div class="col_content" id="col_content">
			
				<div id="module-info" class="module">
				
					<div class="module_conntent">
					
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
					
				</div> <!-- <div class="module"> -->
				
				<div class="bot_nav" id="bot_nav">
					<span class="mission_buttons">
						<!--<a class="button prev" href="#"><span class="icon"></span>Previous Missions</a>
						<a class="button next" href="#"><span class="icon">Next Missions</span></a>-->
					</span>				
				</div>
				
			</div> <!-- <div class="col_content"> -->
				
		</div> <!-- <div class="slider"> -->
