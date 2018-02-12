<?php
$admin_auth = array('camp'); 

require('header.php');
require_once('calendar.php');

$days_of_the_week = array("monday", "tuesday", "wednesday", "thursday", "friday", "shabbos", "sunday");

$admin_id = gri("admin_id");

// ***** Determine if the user is a camp director or a super user ***** //
if ($admin_user['auth'] == "super")
	$user_type = "super";
else
	$user_type = "camp";
// ***** Determine if the user is a camp director or a super user ***** //

// ***** Camp Information ***** //
if ($user_type == "camp") {
	$camp_id = $admin_user['auths']['camp'][0]; 
	$authorization = mysql_fetch_assoc(mq("SELECT r.role_auth FROM admin_auths AS aa JOIN roles AS r USING (role_id) WHERE aa.admin_id=" . $admin_user['admin_id']));
	$role_auth = $authorization['role_auth'];
}
else {
	$camp_id = gri('camp_id', -1);
}

if ($user_type == "camp" || $camp_id > -1) {
	$sql = "SELECT * FROM camps WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	$camp = mysql_fetch_assoc($query);
	$camp_name = $camp['camp_name'];
}
// ***** Camp Information ***** //

$group_type_id = gri('group_type_id', -1);
$group_id = gri('group_id', -1);
$division_id = gri('division_id', -1);

$written = 0;

$action = gr('action', '');
if ($action != "") {

	switch($action) {
	
		case "assign":
			$group_ids = split(":", gr("group_ids"));
			$camp_task_ids = split(":", gr("camp_task_ids"));
			
			for ($cntr1 = 0; $cntr1 < count($group_ids); $cntr1++) {
				$group_id = $group_ids[$cntr1];
				
				$query1 = mq("SELECT * FROM member_groups WHERE group_id=" . $group_id);
				while ($row1 = mysql_fetch_assoc($query1)) {
					$user_id = $row1['user_id'];
						
					for ($cntr2 = 0; $cntr2 < count($camp_task_ids); $cntr2++) {
						$camp_task_id = $camp_task_ids[$cntr2];
					
						$find = "SELECT * FROM group_tasks WHERE group_id=" . $group_id . " AND camp_task_id=" . $camp_task_id;
						$find_query = mq($find);
						$num_rows = mysql_num_rows($find_query);
						if ($num_rows == 0) {
							$insert = "INSERT INTO group_tasks SET group_id=" . $group_id . ", camp_task_id=" . $camp_task_id;
							mq($insert);
						}
						$query2 = mq("SELECT ct.*, p.period_name FROM camp_tasks AS ct JOIN periods AS p USING (period_id) WHERE camp_task_id=" . $camp_task_id);
						$row2 = mysql_fetch_assoc($query2);
						
						$start_date = $row2['start_date'];
						$end_date = $row2['end_date'];
						$period_name = $row2['period_name'];
						
						if (strtolower($period_name) == "once") {
						
						}
						elseif (strtolower($period_name) == "daily") {
							$todays_day = date("j"); 
							$todays_month = date("n"); 
							$todays_year = date("Y"); 
							$today_jd = cal_to_jd  (CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);
						
							if ($start_date < $today_jd) 
								$starting_date = $today_jd;
							else
								$starting_date = $start_date;
							
							$difference = $end_date - $starting_date;
							
							for ($cntr = 1; $cntr < $difference; $cntr++) {
								$task_date = $starting_date + $cntr;
								$sql = "INSERT INTO member_tasks SET user_id=" . $user_id . " , group_id=" . $group_id . " , camp_task_id=" . $camp_task_id . " , task_date=" . $task_date . " , completed=0";
								mq($sql);
							}
						
						}
						elseif (strtolower($period_name) == "weekly") {
							$todays_day = date("j"); 
							$todays_month = date("n"); 
							$todays_year = date("Y"); 
							$today_jd = cal_to_jd  (CAL_GREGORIAN, $todays_month,  $todays_day, $todays_year);
							
							if ($start_date < $today_jd) 
								$starting_date = $today_jd;
							else
								$starting_date = $start_date;
							
							$difference = $end_date - $starting_date;
							
							for ($cntr = 1; $cntr < $difference; $cntr++) {
								$task_date = $starting_date + $cntr;								
								
								if ($today_jd >= $start_date) {
								
									$day_of_the_week = strtolower(jddayofweek($task_date, 1));
									if ($day_of_the_week == "saturday")
										$day_of_the_week = "shabbos";
									
									if ($row2[$day_of_the_week ] == 1) {
										$written++;
										$sql = "INSERT INTO member_tasks SET user_id=" . $user_id . " , group_id=" . $group_id . " , camp_task_id=" . $camp_task_id . " , task_date=" . $task_date . " , completed=0";
										mq($sql);
									}
									
									
								}
								
							}
							
						}
						
					}
					
				}
				
			}			
		break;
	}
	
}

function  get_campaign_missions() {
	global $camp_id;
	
	$sql = "SELECT * FROM camp_campaigns WHERE camp_id=" . $camp_id . " ORDER BY campaign_name";
	$query = mq($sql);	
	
	while ($row = mysql_fetch_assoc($query)) {	
		$c = $row['camp_campaign_id'];
		
		$echo_string = "\n<table style='width:100%; border:solid 2px #B0ACA2;' id='campaign_" . $row['camp_campaign_id'] . "' name='campaign_" . $row['camp_campaign_id'] . "'>\n";
		
		// ***** CAMPAIGN ***** //
		$echo_string = $echo_string . "\t\t<thead><!-- CAMPAIGN -->\n";
		$echo_string = $echo_string . "\t\t\t<tr>\n";
		$echo_string = $echo_string . "\t\t\t\t\t<th align='left' style='background-color:#FDF8EA;'>\n";
			
		$echo_string = $echo_string . "\t\t\t\t\t<input type='checkbox' id='" . $c . "' name='" . $c . "' onclick='check_missions(this, " . $row['camp_campaign_id'] . ");'>\n";
		$echo_string = $echo_string . "\t\t\t\t\t<label style='color:#996600;'>" . $row['campaign_name'] . "</label>\n";
			
		$echo_string = $echo_string . "\t\t\t\t\t</th>\n";									
		$echo_string = $echo_string . "\t\t\t</tr>\n";			
		$echo_string = $echo_string . "\t\t</thead><!-- END CAMPAIGN -->\n";
		// ***** CAMPAIGN ***** //
		
		// ***** MISSIONS ***** //
		$sql2 = "SELECT * FROM camp_missions WHERE camp_campaign_id=" . $row['camp_campaign_id'];
		$query2 = mq($sql2);
		while ($row2 = mysql_fetch_assoc($query2)) {
			$c_m = $c . ":" . $row2['camp_mission_id'];
			
			$echo_string = $echo_string . "\t\t<tbody style='display:none; background-color:#E4EDD1;'><!-- MISSION -->\n";
			$echo_string = $echo_string . "\t\t\t<tr>\n";
			$echo_string = $echo_string . "\t\t\t\t\t<td>\n";
			$echo_string = $echo_string . "\t\t\t\t\t<input type='checkbox' id='" . $c_m . "' name='" . $c_m . "' onclick='check_tasks(this, " . $row['camp_campaign_id'] . ",\"" . $c_m . "\");'>";
			$echo_string = $echo_string . "<label style='color:#336600;'><b>" . $row2['mission_name'] . "</b></label>\n";
			$echo_string = $echo_string . "\t\t\t\t\t</td>\n";
			$echo_string = $echo_string . "\t\t\t</tr>\n";
			$echo_string = $echo_string . "\t\t</tbody><!-- END MISSION -->\n";
			
			// ***** TASKS ***** //
			$sql3 = "SELECT * FROM camp_tasks WHERE camp_mission_id=" . $row2['camp_mission_id'];
			$query3 = mq($sql3);
			$task_number = 0;
			$echo_string = $echo_string . "\t\t<tbody style='display:none;'><!-- TASKS -->\n";
			$number_of_tasks_per_row = 5;
			while ($row3 = mysql_fetch_assoc($query3)) {
				$c_m_t = $c_m . ":" . $row3['camp_task_id'];
				
				$remainder = $task_number % $number_of_tasks_per_row;
			
				if ($remainder == 0) 
					$echo_string = $echo_string . "\t\t\t<tr>\n";
				
				$echo_string = $echo_string . "\t\t\t\t\t<td>\n";
				$echo_string = $echo_string . "\t\t\t\t\t<input type='checkbox' id='" . $c_m_t . "' name='" . $c_m_t . "'><label>" . $row3['task_name'] . "</label>\n";
				$echo_string = $echo_string . "\t\t\t\t\t</td>\n";
				
				if ($remainder == ($number_of_tasks_per_row - 1)) 
					$echo_string = $echo_string . "\t\t\t</tr>\n";
				
				$task_number++;
			}
			$echo_string = $echo_string . "\t\t</tbody><!-- END TASKS -->\n";
			// ***** TASKS ***** //
		}
		// ***** MISSIONS ***** //
		
		$echo_string = $echo_string . "</table>\n<br />";
		
		echo $echo_string;
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Assign Missions'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="ajax_get_groups.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="ajax_get_campaigns.js"></SCRIPT>
		<script type="text/javascript">	
			var admin_id = "<?=$admin_id;?>";
			var user_type = "<?=$user_type;?>";
			var camp_id = "<?=$camp_id;?>";
			var group_type_id = "<?=$group_type_id;?>";
			var group_id = "<?=$group_id;?>";
			var division_id = "<?=$division_id;?>";
			
			var divs_array = ["div_1", "div_2", "div_3", "div_4"];
		
			var display = "";
			var checked = true;
			
			function check_missions(chckbx, campaign_id) {
				if (chckbx.checked == true) 
					display = "block";
				else
					display = "none";
					
				var campaign_table = document.getElementById("campaign_" + campaign_id);
				var table_bodies = campaign_table.getElementsByTagName("tbody");
				
				for (cntr = 0; cntr < table_bodies.length; cntr++) {
					table_bodies[cntr].style.display = display;
				}
				
				var inputs = campaign_table.getElementsByTagName("input");
				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					if (type == "checkbox") {
						inputs[cntr].checked = chckbx.checked;
					}	
				}				
			}
			
			function check_tasks(chckbx, campaign_id, mission_info) {
				var campaign_table = document.getElementById("campaign_" + campaign_id);
				var inputs = campaign_table.getElementsByTagName("input");

				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					if (type == "checkbox") {
						var checkbox_name = inputs[cntr].getAttribute("name");
						var info_array = checkbox_name.split(":");
						if (info_array.length == 3) {
							if (info_array[0] + ":" + info_array[1] == mission_info) {
								inputs[cntr].checked = chckbx.checked;
							}
						}
						
					}	
				}				
			}
			
			function check_all_groups(chckbx) {
				var div_4 = document.getElementById("div_4");
				
				var inputs = div_4.getElementsByTagName("input");
				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					if (type == "checkbox") {
						inputs[cntr].checked = chckbx.checked;
					}	
				}
			}
			
			function assign_missions() {
				var group_ids = "";
				var div_4 = document.getElementById("div_4");
				var inputs = div_4.getElementsByTagName("input");
				
				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");					
					if (type == "checkbox") {
						var checkbox_name = inputs[cntr].getAttribute("name").toLowerCase();
						if (checkbox_name != "All" && inputs[cntr].checked == true) {
							group_ids = group_ids + checkbox_name + ":";
						}
					}	
				}
				
				var camp_task_ids = "";				
				var campaigns_div = document.getElementById("campaigns_div");
				var inputs = campaigns_div.getElementsByTagName("input");

				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					if (type == "checkbox" && inputs[cntr].checked == true) {
						var checkbox_name = inputs[cntr].getAttribute("name");
						var info_array = checkbox_name.split(":");
						if (info_array.length == 3) {							
							camp_task_ids = camp_task_ids + info_array[2] + ":";
						}						
					}	
				}
				
				if (group_ids.length > 0 && camp_task_ids.length > 0) {
					group_ids = group_ids.substr(0, group_ids.length - 1);
					camp_task_ids = camp_task_ids.substr(0, camp_task_ids.length - 1);
					document.getElementById("camp_task_ids").value = camp_task_ids;
					document.getElementById("group_ids").value = group_ids;
					document.getElementById("action").value = "assign";
					document.group_tasks_form.submit();
				}				
			}
		</script>		

	</HEAD>
	
	<body onload="get_groups('1234', 'group_tasks_form');">
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			<H1>
				<?=T_('Tasks')?>
			</H1>
			
			<? if (!empty($message)) : ?>
				<H2>
					<?=$message?>
				</H2>
			<? endif; ?>

			<form name="group_tasks_form" id="group_tasks_form" action="admin_assign_group_tasks.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action">
				<input type="hidden" name="group_ids" id="group_ids">
				<input type="hidden" name="camp_task_ids" id="camp_task_ids">
				
				<br />
				<br />
				
				<div id="div_1">
				</div>		
				
				<br />
				<br />
				
				<div id="div_2">
				</div>		
				
				<br />
				<br />
				
				<div id="div_3">
				</div>		
				
				<br />
				<br />
			
				<div id="div_4">
				</div>
				
				<br />
				<br />
			
				<input type="button" value="ASSIGN" onclick="assign_missions();">
				
				<br />
				<br />
				
				<div id="campaigns_div">
					<? get_campaign_missions(); ?>
				</div> <!-- ALL CAMPAIGNS DIV -->
				
			</form>

		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
