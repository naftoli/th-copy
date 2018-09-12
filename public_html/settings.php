<?
$admin_auth = array('school'); 
require('header.php');

$classes = array();
if (isset($_GET['school_id'])) {
	$school_id = (int)$_GET['school_id'];
	if ($school_id > 0) {
		$sql = "select col_show, whatsapp from schools where school_id = " . $school_id;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$col = $row['col_show'];
		//$whatsapp = $row['whatsapp'];
		// get list of classes for whatsapp
		$sql = "select class_id, class_grade, class_sub, whatsapp from classes
				where class_era = 0
				and school_id = " . $school_id . "
				order by class_grade, class_sub";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$classes[$row['class_id']] = $row;
		}
		
		$school_custom_task_settings = mysql_query("SELECT school_id, allow_parent_tasks, print_parent_tasks FROM schools WHERE school_id=$school_id");
		$school_custom_task_settings = mysql_fetch_assoc($school_custom_task_settings);
		
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Settings</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href="/styles/admin/fancy-checkbox.css" rel="stylesheet" type="text/css">
		<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
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
			table.parents {
				width: 100%;
				font-size: 1em;
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
		<?php if (isset($col)) : ?>
		<fieldset>
			<legend>Tehillim</legend>
			<input type="radio" name="tehillim" class="tehillim" value="1" 
				   <?php if ($col) echo "checked"; ?>
				   /> Show my tehillim usage stats on COL<br />
			<input type="radio" name="tehillim" class="tehillim" value="0" 
				   <?php if (!$col) echo "checked"; ?>
				   /> Do NOT show my tehillim usage stats on COL<br />
		</fieldset>
		<br />
		<? endif; ?>

		<fieldset id="setup">
			<legend>Customize</legend>
			I would like to customize the settings of:<br />
			<input type="radio" name="settingChoice" class="settingChoice" value="1"> 
			Parent Marking<br />
			<input type="radio" name="settingChoice" class="settingChoice" value="2"> 
			Mission Report Type<br />
			<input type="radio" name="settingChoice" class="settingChoice" value="3"> 
			Mission Report Language<br />
			<input type="radio" name="settingChoice" class="settingChoice" value="4"> 
			Set Tehillim Whatsapp per Class<br />
			<input type="radio" name="settingChoice" class="settingChoice" value="5"> 
			Control Custom Parent Tasks<br />
        </fieldset>
        
        <br />
<!--		Missions -->
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
		
<!--		Markings-->
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
        
<!--		Langs-->
        <fieldset id="missionLang">
        	<legend>Mission Report Language</legend>
        	Please choose how you would like to setup your mission report language:<br />
        	<input type="radio" name="langChoice" class="langChoice" value="1"> 
    		By School
    		<input type="radio" name="langChoice" class="langChoice" value="2"> 
    		By Platoon
    		<input type="radio" name="langChoice" class="langChoice" value="3"> 
    		By Soldier
        </fieldset>
        
        <fieldset id="schoolLang">
        	<legend>By School</legend>
        	<input type="radio" name="lang" class="lang" id="lang1" value="1"> 
    		Set Entire School to English<br />
    		<input type="radio" name="lang" class="lang" id="lang2" value="2"> 
    		Set Entire School to Yiddish<br />
        </fieldset>
        
        <fieldset id="platoonLang">
        	<legend>By Platoon</legend>
        </fieldset>
        
        <fieldset id="studentLang">
        	<legend>By Student</legend>
        </fieldset>
		
<!--		Special whatsapp case-->
        <fieldset id="whatsapp">
        	<legend>Tehillim Whatsapp</legend>
			<?php
			if (empty($classes)) {
				echo "No School selected.";
			} else {
				?>
				<table>
					<tr>
						<th>Class</th>
						<th>Show Tehillim Stats on Whatsapp</th>
					</tr>
					<?php
					foreach ($classes as $id => $info) {
						$grade = $info['class_grade'] . (empty($info['class_sub']) ? '' : '-' . $info['class_sub']);
						echo "<tr><td>" . $grade . "</td><td><input type='radio' name='" . $id . "' class='whatsapp' value='1'";
						if ($info['whatsapp']) echo " checked";
						echo " /> Show Stats on Whatsapp<br /><input type='radio' name='" . $id . "' class='whatsapp' value='0'";
						if (!$info['whatsapp']) echo " checked";
						echo " /> Do NOT Show Stats on Whatsapp</td></tr>";
					}
					?>
				</table>
			<? } ?>
        </fieldset>
		
<!--		Custom Parent Tasks-->
		<fieldset id="customParentTasks">
        	<legend>Custom Parent Tasks</legend>
			<p>
				<strong>Disclaimer:</strong> These settings control the custom tasks that parents will be able to create from their parent accounts in the coming weeks.
				This setting does <strong><em>not control tasks that are created by you or HQ</em></strong>. Even if they are disabled by default.
			</p>
			<br/>
        	Please select one of the following filters:<br />
        	<input type="radio" name="customParentTasksChoice" class="customParentTasksChoice" value="school"> 
    		Entire School
    		<input type="radio" name="customParentTasksChoice" class="customParentTasksChoice" value="platoon"> 
    		By Platoon
    		<input type="radio" name="customParentTasksChoice" class="customParentTasksChoice" value="student"> 
    		By Soldier
        </fieldset>
		
		<fieldset id="schoolCustomParentTasks">
        	<legend>Entire School</legend>
			<strong>Please note that changing this setting will reset all Soldiers in the school</strong> <br/><br/>
			<span>Allow Custom Tasks</span>
        	<label class="fancy-check-container">
				<input class="custom_tasks" data-level="school" data-school_id="<?=$school_id?>" type="checkbox"
					<?=$school_custom_task_settings['allow_parent_tasks'] == 1 ? "checked": "";?>/>
				<span class="fancy-check"></span>
			</label>
			<br/><br/>
			<span>Print Custom Tasks</span>
			<label class="fancy-check-container">
				<input class="print_custom_tasks" data-level="school" data-school_id="<?=$school_id?>" type="checkbox"
					<? echo $school_custom_task_settings['print_parent_tasks'] == 1 ? "checked": "";?>/>
				<span class="fancy-check"></span>
			</label>
        </fieldset>
		
		<fieldset id="platoonCustomParentTasks">
        	<legend>By Platoon</legend>
			<strong>Please note that changing this setting will reset all Soldiers in the school</strong> <br/><br/>
			<div></div>
        </fieldset>
        
        <fieldset id="studentCustomParentTasks">
        	<legend>By Student</legend>
			<div></div>
        </fieldset>
		
	</body>
	<script src="scripts/settings/customParentTasks.js?v=2.1"></script>
    <script type="text/javascript">
    	$( function() {
    		var id = <?=(int)$_GET['school_id']?>;
			
			$(".tehillim").click( function() {
				var val = $(this).val();
				$.post('ajax/updateShowTehillim.php', { val : val, school : id }, function(success) {
					alert(success);
				});
			});
			/*
			$(".whatsapp").click( function() {
				var val = $(this).val();
				$.post('ajax/updateWhatsapp.php', { val : val, school : id }, function(success) {
					alert(success);
				});
			});
    		*/
    		hideAllMissions();
    		hideAllMarking();
    		hideAllLang();
			hideAllCustomParentTasks();
			$("#whatsapp").hide();
    		
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
    		
    		function hideAllLang() {
    			$("#missionLang").hide();
    			$("#schoolLang").hide();
    			$("#platoonLang").hide();
    			$("#studentLang").hide();
    		}
    		
			function hideAllCustomParentTasks() {
                $("#customParentTasks").hide();
    			$("#schoolCustomParentTasks").hide();
    			$("#platoonCustomParentTasks").hide();
    			$("#studentCustomParentTasks").hide();
            }
			
			// controll the settings click
    		$(".settingChoice").click( function() {
    			var i = $(this).val();
    			if (i == 1) {
    				hideAllMissions();
    				hideAllLang();
					hideAllCustomParentTasks();
    				$("#parentMarking").show();
    				$(".choice").each( function() {
    					$(this).attr('checked', false);
    				});
					$("#whatsapp").hide();
    			} else if (i == 2) {
    				hideAllMarking();
    				hideAllLang();
					hideAllCustomParentTasks();
    				$("#missionReport").show();
    				$(".missionChoice").each( function() {
    					$(this).attr('checked', false);
    				});
					$("#whatsapp").hide();
    			} else if (i == 3) {
    				hideAllMarking();
    				hideAllMissions();
					hideAllCustomParentTasks();
    				$("#missionLang").show();
    				$(".langChoice").each( function() {
    					$(this).attr('checked', false);
    				});
					$("#whatsapp").hide();
    			} else if (i == 4) {
					hideAllMissions();
					hideAllMarking();
					hideAllLang();
					hideAllCustomParentTasks();
					$("#whatsapp").show();
				} else if (i == 5){
                    hideAllMissions();
					hideAllMarking();
					hideAllLang();
					$("#whatsapp").hide();
					$(".customParentTasksChoice").each( function() {
    					$(this).attr('checked', false);
    				});
					$("#customParentTasks").show();
                }
    		});
    		
    		$(".missionChoice").click( function() {
    			var i = $(this).val();
    			if (i == 1) {
    				$("#schoolMissions").show();
    				$("#platoonMissions").hide();
    				$("#studentMissions").hide();
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
    		
    		$(".langChoice").click( function() {
    			var i = $(this).val();
    			if (i == 1) {
    				$("#schoolLang").show();
    				$("#platoonLang").hide();
    				$("#studentLang").hide();
    			} else if (i == 2) {
    				getPlatoonsForLang();
    				$("#schoolLang").hide();
    				$("#studentLang").hide();
    			} else if (i == 3) {
    				alert("It may take a few seconds until it fully loads.");
    				getStudentsForLang();
    				$("#schoolLang").hide();
    				$("#platoonLang").hide();
    			}
    		});
			
			$(".customParentTasksChoice").click(function(event){
				var options = ['school', 'platoon', 'student'];
				for(var i = 0; i < options.length; i++){
					var option = options[i];
					if (option == event.target.value) {
                        $("#" + option + "CustomParentTasks").show();
						// load up the platoon info...
						if (option == "platoon") {customParentTasks.loadPlatoons(id);}
						// load up the student info...
						if (option == "student") {customParentTasks.loadStudents(id);}
                    } else {
						$("#" + option + "CustomParentTasks").hide();
					}
				}
			});
    		
    		$(".lang").click( function() {
    			var val = $(this).val();
    			$.post('ajax/updateMarking.php', {
    				id : id, 
    				setting : val, 
    				type : 'school', 
    				field : 'lang_id'
    			}, function(data) {
    				if (data == 1) {
    					alert("Updated!");
    				} else {
    					alert("No updates were performed.");
    				}
    			});
    		});
			
			customParentTasks.setupListeners();
			
    		
    		function getPlatoonsForLang() {
    			$("#platoonLang").empty();
    			$("#platoonLang").append("<legend>By Platoon</legend>");
    			//get classes / users
    			$.get('ajax/getClasses.php', {id : id, hasUsers : true}, function(data) {
    				var obj = $.parseJSON(data);
    				//build table
    				$("#platoonLang").append("<table id='grades'><tr><th>Platoon</th>\
    					<th>English</th><th>Yiddish</th></tr>");
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
							$.post('ajax/checkMission.php', {
								id : id, 
								type : 'class', 
								field : 'lang_id'
							}, function(data) {
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
    				
    				$("#platoonLang").show();
    				   				
    				$(".platoon").click( function() {
    					if ($(this).is(":checked")) {
	        				var id = $(this).attr('name');
	        				var val = $(this).val();
		        			$.post('ajax/updateMarking.php', {
		        					id : id, setting : val, type : 'class', field : 'lang_id'
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
    		
    		function getStudentsForLang() {
    			$("#studentLang").empty();
    			$("#studentLang").append("<legend>By Student</legend>");
    			//get classes / users
    			$.get('ajax/getUsersInSchool.php', {id : id}, function(data) {
    				//alert(data);
    				var users = $.parseJSON(data);
    				$("#studentLang").append("<table id='users'><tr><th>Platoon</th><th>Soldier</th>\
    					<th>English</th><th>Yiddish</th></tr>");
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
							$.post('ajax/checkMission.php', {
								id : id, 
								type : 'user', 
								field : 'lang_id'
							}, function(data) {
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
    				
    				$("#studentLang").show();
    				
    				$(".user").click( function() {
    					if ($(this).is(":checked")) {
	        				var id = $(this).attr('name');
	        				var val = $(this).val();
		        			$.post('ajax/updateMarking.php', {
		        					id : id, 
		        					setting : val, 
		        					type : 'user', 
		        					field : 'lang_id'
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
							$.post('ajax/checkMission.php', {
								id : id, 
								type : 'class', 
								field : 'pic_mission_type'
							}, function(data) {
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
							$.post('ajax/checkMission.php', {
								id : id, 
								type : 'user', 
								field : 'pic_mission_type'
							}, function(data) {
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
			
			$(".whatsapp").click( function() {
				var id = $(this).attr('name');
				var val = $(this).val();
				$.post('ajax/setWhatsapp.php', { class_id : id , val : val }, function(success) {
					if (parseInt(success)) {
						alert("Updated!");
					} else {
						alert("No updated were performed.");
					}
				});
			});
    	});
    </script>
</html>