$(function() {
  	  	
	var user_ids = "";		
	var camp_task_ids = "";
		
	var tasks_html = "";
	
	function initPageContent() {
		var action = "start_marking_session_users";
		var params = ["2455372", "164", "1", "0", "3"];		 		
		var url = "../../application/php/appInterface.php?action=" + action + "&params=" + params;		
		
		$.getJSON(url, function(data) {
	
			if (data.length > 0) { 
				var innerHTML = "\n";
							
				for (cntr1 = 0; cntr1 < data.length; cntr1++) {	
					var group_type = data[cntr1];
					var group_id = group_type.group_id;
					
					innerHTML = innerHTML + "\t\t\t\t<div class='mission_name'>\n";
					innerHTML = innerHTML + "\t\t\t\t\t<div class='cell'>" + group_type.group_name + "</div>\n";
					innerHTML = innerHTML + "\t\t\t\t</div>\n";
					
					innerHTML = innerHTML + "\t\t\t\t<div class='row task_names'>\n";
					innerHTML = innerHTML + "\t\t\t\t\t<div class='cell'></div>\n";
					innerHTML = innerHTML + "\t\t\t\t</div>\n";
					
					for (cntr2 = 0; cntr2 < group_type.members.length; cntr2++) {	
						member = group_type.members[cntr2];
						user_ids = user_ids + member.user_id + ":";
						innerHTML = innerHTML + "\t\t\t\t<div class='cell'>" + member.name + "</div>\n";
					}
					
				}
				document.getElementById("col_names").innerHTML = innerHTML;
				
				user_ids = user_ids.substr(0, user_ids.length - 1);
				
				// ***** FIRST MISSION ***** //
				var action2 = "start_marking_session_missions";
				var params2 = ["2455372", group_id, 1];
				var url2 = "../../application/php/appInterface.php?action=" + action2 + "&params=" + params2;
				$.getJSON(url2, function(data2) {	
					if (data2.length > 0) { 
						var html1 = get_mission_html_one(data2);
						camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);
						
						// ***** MEMBER TASKS ***** //
						action = "start_marking_session_member_tasks";				
						params = ["2455372", user_ids, camp_task_ids];				
						url = "../../application/php/appInterface.php?action=" + action + "&params=" + params;				
						$.getJSON(url, function(data) {
							var html2 = get_tasks_html_one(data);							
							document.getElementById("mission_one").innerHTML = html1 + html2;
						});
						// ***** MEMBER TASKS ***** //
						
						// ***** SECOND MISSION ***** //
						var action3 = "start_marking_session_missions";
						var params3 = ["2455372", group_id, 2];
						var url3 = "../../application/php/appInterface.php?action=" + action3 + "&params=" + params3;
						$.getJSON(url3, function(data3) {	
							if (data3.length > 0) { 
								camp_task_ids = "";
								html1 = get_mission_html_one(data3);
								camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);

								
								// ***** MEMBER TASKS ***** //
								action = "start_marking_session_member_tasks";				
								params = ["2455372", user_ids, camp_task_ids];				
								url = "../../application/php/appInterface.php?action=" + action + "&params=" + params;				
								$.getJSON(url, function(data) {
									html2 = get_tasks_html_one(data);							
									document.getElementById("mission_two").innerHTML = html1 + html2;
								});
								// ***** MEMBER TASKS ***** //
								
								
								
								
								
								
								// ***** THIRD MISSION ***** //
								/*var action4 = "start_marking_session_missions";
								var params4 = ["2455372", group_id, 3];
								var url4 = "../../application/php/appInterface.php?action=" + action4 + "&params=" + params4;
								$.getJSON(url4, function(data4) {	
									if (data4.length > 0) { 
										camp_task_ids = "";
										html1 = get_mission_html_one(data4);
										camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);

										
										// ***** MEMBER TASKS ***** //
										action = "start_marking_session_member_tasks";				
										params = ["2455372", user_ids, camp_task_ids];				
										url = "../../application/php/appInterface.php?action=" + action + "&params=" + params;				
										$.getJSON(url, function(data) {
											html2 = get_tasks_html_one(data);							
											document.getElementById("mission_three").innerHTML = html1 + html2;
										});
										// ***** MEMBER TASKS ***** //
										
										
										
									}
								});*/
								// ***** THIRD MISSION ***** //
								
								
								
								
								
								
								
								
								
								
								
								
							}
						});
						// ***** SECOND MISSION ***** //
						
					}
				});
				// ***** FIRST MISSION ***** //
				
			}
			
		});			
	  
    }
  	
  	initPageContent();
  	
	function get_tasks_html_one(data) {	
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
		
	}
		
	function get_mission_html_one(data) {
		var return_html = "";
		
		mission = data[0];
		
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
	
});
	
