//$(function() {
  	
		
	function display_missions(task_date, mission_one, mission_two) {
		var action = "start_marking_session_users";
		var params = [task_date, group_type_id, "1", mission_one, mission_two];	// task_date, group_type_id, group_no, mission_no_start, mission_no_end
		//var url = "../../application/php/appInterface.php?action=" + action + "&params=" + params;		
		var url = "http://www.mashpia.com/CampMotivationalSystem/dev/application/php/appInterface.php?action=" + action + "&params=" + params;	
		alert(url);
		
		alert("DONE");
		
		//$.getJSON(url, function(members) {
	
			//alert("members.length:" + members.length);
			
			/*if (members.length > 0) { 			
				document.getElementById("col_names").innerHTML = get_members(members);
				
				user_ids = user_ids.substr(0, user_ids.length - 1);
				
				// ***** FIRST MISSION ***** //
				var action2 = "start_marking_session_missions";
				var params2 = [task_date, group_id, 1];
				var url2 = "../../application/php/appInterface.php?action=" + action2 + "&params=" + params2;
				alert(url2);
				$.getJSON(url2, function(missions) {
					missions_data = missions;
					if (missions.length > 0) { 
						var html1 = get_missions(missions, mission_one);
						camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);
						
						// ***** MEMBER TASKS ***** //
						action = "start_marking_session_member_tasks";				
						params = [task_date, user_ids, camp_task_ids];				
						url = "../../application/php/appInterface.php?action=" + action + "&params=" + params;
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
							var url3 = "../../application/php/appInterface.php?action=" + action3 + "&params=" + params3;
							$.getJSON(url3, function(missions) {	
								if (missions.length > 0) { 
									camp_task_ids = "";
									html1 = get_missions(missions, mission_two);
									camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);

									
									// ***** MEMBER TASKS ***** //
									action = "start_marking_session_member_tasks";				
									params = [task_date, user_ids, camp_task_ids];				
									url = "../../application/php/appInterface.php?action=" + action + "&params=" + params;				
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
				
			}*/
			
		//});			
	  
    }  
  	
	/*function get_bot_nav(mission_one, mission_two, number_of_missions_to_show, number_of_missions) {
		var bot_nav_html = "\n";
		
		bot_nav_html = bot_nav_html + "<span class='mission_buttons'>\n";
		
		if (mission_one > 0) {
			var mission_1 = mission_one - 2;
			var mission_2 = mission_two - 2;
			bot_nav_html = bot_nav_html + "\t<a onclick='get_next_missions(" + mission_1 + ", " + mission_2 + ", " + number_of_missions_to_show + ", " + number_of_missions + ");' class='button prev' href='#'><span class='icon'></span>Previous Missions</a>\n";
		}
		
		if (number_of_missions > mission_two) 
			bot_nav_html = bot_nav_html + "\t<a class='button next' href='#' onclick='get_next_missions(" + (mission_one + 2) + ", " + (mission_two + 2) + ", " + number_of_missions_to_show + ", " + number_of_missions + ");'>Next Missions<span class='icon'></span></a>\n";
		
		bot_nav_html = bot_nav_html + "</span>\n";
		
		return bot_nav_html;		
	}
	
	function get_next_missions(first_mission, second_mission, number_of_missions_to_show, number_of_missions) {
		number_of_missions = missions_data[0].missions.length;
		
		// ********** FIRST MISSION ********** //
		camp_task_ids = "";
		var html1 = get_missions(missions_data, first_mission);
		camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);
		
		// ***** MEMBER TASKS ***** //
		action = "start_marking_session_member_tasks";				
		params = [task_date, user_ids, camp_task_ids];				
		url = "../../application/php/appInterface.php?action=" + action + "&params=" + params;				
		$.getJSON(url, function(data) {			
			var html2 = get_tasks_html(data);
			document.getElementById("mission_one").innerHTML = html1 + html2;						
			
			// ********** SECOND MISSION ********** //
			if (number_of_missions > second_mission) {
				camp_task_ids = "";
				html1 = get_missions(missions_data, second_mission);
				camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);
				
				// ***** MEMBER TASKS ***** //
				action = "start_marking_session_member_tasks";				
				params = [task_date, user_ids, camp_task_ids];				
				url = "../../application/php/appInterface.php?action=" + action + "&params=" + params;				
				$.getJSON(url, function(data) {
					var html2 = get_tasks_html(data);	
					document.getElementById("mission_two").innerHTML = html1 + html2;
					var bot_nav_html = get_bot_nav(first_mission, second_mission, number_of_missions_to_show, number_of_missions);
					document.getElementById("bot_nav").innerHTML = bot_nav_html;
				});
				// ***** MEMBER TASKS ***** //
			}
			else {
				document.getElementById("mission_two").innerHTML = html1;
				var bot_nav_html = get_bot_nav(first_mission, second_mission, number_of_missions_to_show, number_of_missions);
				document.getElementById("bot_nav").innerHTML = bot_nav_html;
				document.getElementById("mission_two").innerHTML = "";
			}
			// ********** SECOND MISSION ********** //			
			
		});
		// ***** MEMBER TASKS ***** //
		// ********** FIRST MISSION ********** //
		
	}
	
	function get_missions(missions, mission_number) {
		var return_html = "";
		
		number_of_missions = missions[0].missions.length;
				
		mission = missions[0].missions[mission_number];

		return_html = return_html + "\t\t\t\t\t\t\t<div class='row mission_name'>\n";
		return_html = return_html + "\t\t\t\t\t\t\t\t<div class='cell'>" + mission.mission_name + "</div>\n";
		return_html = return_html + "\t\t\t\t\t\t\t</div>\n";								
		
		return_html = return_html + "\t\t\t\t\t\t\t<div class='row task_names'>\n";
		
		for (cntr = 0; cntr < mission.tasks.length; cntr++) {
			task = mission.tasks[cntr];

			camp_task_ids= camp_task_ids + task.camp_task_id + ":";
									
			return_html = return_html + "\t\t\t\t\t\t\t\t<div class='cell'>\n";
			return_html = return_html + "\t\t\t\t\t\t\t\t\t<span>" + task.task_name + "</span>\n";
			return_html = return_html + "\t\t\t\t\t\t\t\t</div>\n";		
		}
		return_html = return_html + "\t\t\t\t\t\t\t</div>\n"; // task_names
		
		return return_html;		
	}
	
	
	function get_members(members) {
		var members_html = "\n";
	
		for (cntr1 = 0; cntr1 < members.length; cntr1++) {	
			var group_type = members[cntr1];
			group_id = group_type.group_id;
					
			members_html = members_html + "\t\t\t\t<div class='mission_name'>\n";
			members_html = members_html + "\t\t\t\t\t<div class='cell'>" + group_type.group_name + "</div>\n";
			members_html = members_html + "\t\t\t\t</div>\n";
					
			members_html = members_html + "\t\t\t\t<div class='row task_names'>\n";
			members_html = members_html + "\t\t\t\t\t<div class='cell'></div>\n";
			members_html = members_html + "\t\t\t\t</div>\n";
					
			for (cntr2 = 0; cntr2 < group_type.members.length; cntr2++) {	
				member = group_type.members[cntr2];
				user_ids = user_ids + member.user_id + ":";
				members_html = members_html + "\t\t\t\t<div class='cell'>" + member.name + "</div>\n";
			}
					
		}
	
		return members_html;
	}
	
	function get_tasks_html(data) {
		var return_html = "\n";
		
		for (cntr1 = 0; cntr1 < data.length; cntr1++) {	
			var members = data[cntr1];
							
			for (cntr2 = 0; cntr2 < members.member_tasks.length; cntr2++) {	
				var tasks_member = members.member_tasks[cntr2];

				return_html = return_html + "\t\t\t\t\t\t<div class='row'>\n";				
				for (cntr3 = 0; cntr3 < tasks_member.tasks.length; cntr3++) {
					var member_task_info = tasks_member.tasks[cntr3];
					
					if (member_task_info.completed == 0) 
						checked = "";
					else
						checked = "checked";
									
					return_html = return_html + "\t\t\t\t\t\t\t<div name='" + checked + "' onclick='update_member_task(this, " + member_task_info.member_task_id + ");' class='cell checkbox " + checked + "'>\n";
					return_html = return_html + "\t\t\t\t\t\t\t\t<span>\n";
					return_html = return_html + "\t\t\t\t\t\t\t\t\t<input type='checkbox' id='" + member_task_info.member_task_id + "' " + checked + ">\n";
					return_html = return_html + "\t\t\t\t\t\t\t\t</span>\n";
					return_html = return_html + "\t\t\t\t\t\t\t</div>\n";
				}
				return_html = return_html + "\t\t\t\t\t\t</div>\n";
			}
							
		}
		
		return return_html;
		
	}*/
			
//});
	
