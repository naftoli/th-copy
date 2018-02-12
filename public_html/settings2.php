<?
$admin_auth = array('school'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Settings</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
        	fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                font-size: 16px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .middle {
            	text-align: center;
            	margin: auto;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Settings</h1>
        
        <fieldset id="setup">
        	<legend>Customize</legend>
        	I would like to customize the settings of:<br />
			<input type="radio" name="settingChoice" class="settingChoice" value="1"> 
			Parent Marking<br />
			<input type="radio" name="settingChoice" class="settingChoice" value="2"> 
			Mission Report Type<br />
        </fieldset>
        
        <fieldset id="parentMarking">
        	<legend>Parent Marking</legend>
        	Please choose how you would like to setup your parent marking:<br />
        	<input type="radio" name="choice" class="choice" value="1"> 
    		By School
    		<input type="radio" name="choice" class="choice" value="2"> 
    		By Platoon
    		<input type="radio" name="choice" class="choice" value="3"> 
    		By Soldier
        </fieldset>
        <br />
        
        <fieldset id="schoolMarking">
        	<legend>By School</legend>
        	<input type="radio" name="marking" class="marking" id="marking1" value="1"> 
    		ALLOW all parents in my school to mark their children's missions<br />
    		<input type="radio" name="marking" class="marking" id="marking2" value="0"> 
    		DO NOT ALLOW all parents in my school to mark their children's missions<br />
        </fieldset>
        
        <fieldset id="platoonMarking">
        	<legend>By Platoon</legend>
        </fieldset>
        
        <fieldset id="studentMarking">
        	<legend>By Student</legend>
        </fieldset>
        
        <fieldset id="missionReport">
        	<legend>Mission Report Type</legend>
        	Please choose how you would like to setup your mission report type:<br />
        	<input type="radio" name="missionChoice" class="missionChoice" value="1"> 
    		By School
    		<input type="radio" name="missionChoice" class="missionChoice" value="2"> 
    		By Platoon
    		<input type="radio" name="missionChoice" class="missionChoice" value="3"> 
    		By Soldier
        </fieldset>
        
        <fieldset id="schoolMissions">
        	<legend>By School</legend>
        	<input type="radio" name="missionType" class="missionType" value="1"> 
        	All students should get No Picture Missions<br />
        	<input type="radio" name="missionType" class="missionType" value="2"> 
        	All students should get Small Picture Missions
        </fieldset>
        
        <fieldset id="platoonMissions">
        	<legend>By Platoon</legend>
        </fieldset>
        
        <fieldset id="studentMissions">
        	<legend>By Student</legend>
        </fieldset>
	</body>
	
	<script src="jquery-1.8.1.min.js"></script>
    <script type="text/javascript">
    	$( function() {
    		var id = <?=(int)$_GET['school_id']?>;
    		
    		hideAllMissions();
    		hideAllMarking();
    		
    		function hideAllMissions() {
    			$("#missionReport").hide();
    			$("#schoolMissions").hide();
    			$("#platoonMissions").hide();
    			$("#studentMissions").hide();
    		}
    		
    		function hideAllMarking() {
    			$("#parentMarking").hide();
	    		$("#schoolMarking").hide();
	    		$("#platoonMarking").hide();
	    		$("#studentMarking").hide();
    		}
    		
    		$(".settingChoice").click( function() {
    			var i = $(this).val();
    			if (i == 1) {
    				hideAllMissions();
    				$("#parentMarking").show();
    				$(".choice").each( function() {
    					$(this).attr('checked', false);
    				});
    			} else if (i == 2) {
    				hideAllMarking();
    				$("#missionReport").show();
    				$(".missionChoice").each( function() {
    					$(this).attr('checked', false);
    				});
    			}
    		});
    		
    		$(".missionChoice").click( function() {
    			var i = $(this).val();
    			if (i == 1) {
    				$("#schoolMissions").show();
    			} else if (i == 2) {
    				getPlatoonsForMissions();      
    				$("#schoolMissions").hide();
    				$("#studentMissions").hide();
    			} else if (i == 3) {
    				alert("It may take a few seconds until it fully loads.");
    				getStudentsForMissions();
    				$("#schoolMissions").hide();
    				$("#platoonMissions").hide();
    			}
    		});
    		
    		$(".missionType").click( function() {
    			var val = $(this).val();
    			$.post('ajax/updateMarking.php', {
    				id : id, 
    				setting : val, 
    				type : 'school', 
    				field : 'pic_mission_type'
    			}, function(data) {
    				if (data == 1) {
    					alert("Updated!");
    				} else {
    					alert("No updates were performed.");
    				}
    			});
    		});
    		
    		function getPlatoonsForMissions() {
    			$("#platoonMissions").empty();
    			$("#platoonMissions").append("<legend>By Platoon</legend>");
    			//get classes / users
    			$.get('ajax/getClasses.php', {id : id, hasUsers : true}, function(data) {
    				var obj = $.parseJSON(data);
    				//build table
    				$("#platoonMissions").append("<table id='grades'><tr><th>Platoon</th>\
    					<th>No Picture Missions</th><th>Small Picture Missions</th></tr>");
    				for (o in obj) {
    					$("#grades").append("<tr><td>" + obj[o] + "</td>\
    						<td class='middle'><input class='platoon' type='radio' id='" + o + "' name='" + o + "' value='1' /></td>\
    						<td class='middle'><input class='platoon' type='radio' id='" + o + "' name='" + o + "' value='2' /></td></tr>");
    				}
    				$("#grades").append("</table>"); 
    				
    				$("#grades tr").each( function(i, v) {
    					if (i != 0) {
							var id = $(this).find("input").attr('id');
							var t = this;
							$.post('ajax/checkPicMissionType.php', {id : id, type : 'class'}, function(data) {
	    						if (data) {
	    							$(t).find("input").each( function() {
	    								if ($(this).val() == data) {
	    									$(this).attr('checked', true);
	    								}
	    							});
	    						}
	    					});
	    				}
    				});
    				
    				$("#platoonMissions").show();
    				   				
    				$(".platoon").click( function() {
    					if ($(this).is(":checked")) {
	        				var id = $(this).attr('name');
	        				var val = $(this).val();
		        			$.post('ajax/updateMarking.php', {
		        					id : id, setting : val, type : 'class', field : 'pic_mission_type'
		        				}, function(data) {
		        				if (data == 1) {
		        					alert("Updated!");
		        				} else {
		        					alert("No updates were performed.");
		        				}
		        			});
		        		}
	        		});
    			});
    		}
    		
    		function getStudentsForMissions() {
    			$("#studentMissions").empty();
    			$("#studentMissions").append("<legend>By Student</legend>");
    			//get classes / users
    			$.get('ajax/getUsersInSchool.php', {id : id}, function(data) {
    				//alert(data);
    				var users = $.parseJSON(data);
    				$("#studentMissions").append("<table id='users'><tr><th>Platoon</th><th>Soldier</th>\
    					<th>No Picture Missions</th><th>Small Picture Missions</th></tr>");
    				for (grade in users) {
    					for (sub in users[grade]) {
        					for (user in users[grade][sub]) {
        						$("#users").append("<tr><td>" + grade + "-" + sub + "</td>\
        							<td>" + users[grade][sub][user] + "</td>\
        							<td class='middle'><input class='user' type='radio' id='" + user + "' name='" + user + "' value='1' /></td>\
        							<td class='middle'><input class='user' type='radio' id='" + user + "' name='" + user + "' value='2' /></td></tr>");
        					}
        				}
    				}
    				$("#users").append("</table>");
    				
    				$("#users tr").each( function(i, v) {
    					if (i != 0) {
							var id = $(this).find("input").attr('id');
							var t = this;
							$.post('ajax/checkPicMissionType.php', {id : id, type : 'user'}, function(data) {
	    						if (data) {
	    							$(t).find("input").each( function() {
	    								if ($(this).val() == data) {
	    									$(this).attr('checked', true);
	    								}
	    							});
	    						}
	    					});
	    				}
    				});
    				
    				$("#studentMissions").show();
    				
    				$(".user").click( function() {
    					if ($(this).is(":checked")) {
	        				var id = $(this).attr('name');
	        				var val = $(this).val();
		        			$.post('ajax/updateMarking.php', {
		        					id : id, setting : val, type : 'user', field : 'pic_mission_type'
		        				}, function(data) {
		        				if (data == 1) {
		        					alert("Updated!");
		        				} else {
		        					alert("No updates were performed.");
		        				}
		        			});
		        		}
	        		});
    			});
    		}
    		    		
    		$(".choice").click( function() {
    			var i = $(this).val();
    			if (i == 1) {
    				$("#schoolMarking").show();
					$("#platoonMarking").hide();
					$("#studentMarking").hide();
    			} else if (i == 2) {
    				getPlatoons();      
    				$("#schoolMarking").hide();
    				$("#studentMarking").hide();
    			} else if (i == 3) {
    				alert("It may take a few seconds until it fully loads.");
    				getStudents();
    				$("#schoolMarking").hide();
    				$("#platoonMarking").hide();       				
    			}
    		});
    		
    		$(".marking").click( function() {
    			var val = $(this).val();
    			$.post('ajax/updateMarking.php', {id : id, setting : val, type : 'school'}, function(data) {
    				if (data == 1) {
    					alert("Updated!");
    				} else {
    					alert("No updates were performed.");
    				}
    			});
    		});
    		
    		function getPlatoons() {
    			$("#platoonMarking").empty();
    			$("#platoonMarking").append("<legend>By Platoon</legend>");
    			//get classes / users
    			$.get('ajax/getClasses.php', {id : id, hasUsers : true}, function(data) {
    				var obj = $.parseJSON(data);
    				//build table
    				$("#platoonMarking").append("<table id='grades'><tr><th>Platoon</th><th>Marking</th></tr>");
    				for (o in obj) {
    					$("#grades").append("<tr><td>" + obj[o] + "</td><td><input class='grade' type='checkbox' id='" + o + "' name='" + o + "' /></td></tr>");
    				};
    				$("#grades").append("</table>"); 
    				
    				$.each($(".grade"), function() {
    					var grade = $(this).attr('name');
    					$.post('ajax/checkMarking.php', {id : grade, type : 'class'}, function(data) {
    						if (data == 1) {
    							$("#" + grade).attr('checked', true);
    						}
    						$("#platoonMarking").show();
    					});
    				});       				
    				
    				$(".grade").click( function() {
	        			var id = $(this).attr('name');
	        			var val = 0;
	        			if ($(this).is(":checked")) {
	        				val = 1;
	        			}
	        			$.post('ajax/updateMarking.php', {id : id, setting : val, type : 'class'}, function(data) {
	        				if (data == 1) {
	        					alert("Updated!");
	        				} else {
	        					alert("No updates were performed.");
	        				}
	        			});
	        		});
    			});
    		}
    		
    		function getStudents() {
    			$("#studentMarking").empty();
    			$("#studentMarking").append("<legend>By Student</legend>");
    			//get classes / users
    			$.get('ajax/getUsersInSchool.php', {id : id}, function(data) {
    				//alert(data);
    				var users = $.parseJSON(data);
    				$("#studentMarking").append("<table id='users'><tr><th>Platoon</th><th>Soldier</th><th>Marking</th></tr>");
    				for (grade in users) {
    					for (sub in users[grade]) {
        					for (user in users[grade][sub]) {
        						$("#users").append("<tr><td>" + grade + "-" + sub + "</td><td>" + users[grade][sub][user] + "</td><td><input class='user' type='checkbox' id='" + user + "' name='" + user + "' /></td></tr>");
        					}
        				}
    				}
    				$("#users").append("</table>");
    				
    				var users = $(".user");
    				$(users).each( function() {
    					var user = $(this).attr('name');
    					$.post('ajax/checkMarking.php', {id : user, type : 'user'}, function(data) {
    						if (data == 1) {
    							$("#" + user).attr('checked', true);
    						}
    						$("#studentMarking").show();
    					});
    				});
    				
    				$(".user").click( function() {
    					var id = $(this).attr('name');
	        			var val = 0;
	        			if ($(this).is(":checked")) {
	        				val = 1;
	        			}
	        			$.post('ajax/updateMarking.php', {id : id, setting : val, type : 'user'}, function(data) {
	        				if (data == 1) {
	        					alert("Updated!");
	        				} else {
	        					alert("No updates were performed.");
	        				}
	        			});
    				});
    			});
    		}
    	});
    </script>
</html>