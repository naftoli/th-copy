<?
require('db.php');
require('lang.php');

$user_type = $_GET['user_type'];
	
if (isset($_GET['camp_id']))
	$camp_id = $_GET['camp_id'];
else
	$camp_id = -1;
	
if (isset($_GET['group_type_id']))
	$group_type_id = $_GET['group_type_id'];
else
	$group_type_id = -1;

if (isset($_GET['group_id']))
	$group_id = $_GET['group_id'];
else
	$group_id = -1;

if (isset($_GET['campaign_id']))
	$campaign_id = $_GET['campaign_id'];
else
	$campaign_id = -1;

if (isset($_GET['task_id']))
	$task_id = $_GET['task_id'];
else
	$task_id = -1;
	
$divs = $_GET['divs'];
$div1 = strpos($divs, "1");
$div2 = strpos($divs, "2");
$div3 = strpos($divs, "3");
$div4 = strpos($divs, "4");
$div5 = strpos($divs, "5");
$div6 = strpos($divs, "6");
$div7 = strpos($divs, "7");

$form_name = $_GET['form_name'];

if ($div1 !== false) {
	echo get_camp_info();
}

if ($div2 !== false) {
	echo get_group_type_info();
}

if ($div3 !== false) {
	echo get_group_info();
}

if ($div4 !== false) {
	echo get_campaign_info();
}

if ($div5 !== false) {
	echo get_task_info();
}

if ($div7 !== false) {
	echo get_global_tasks_info();
}

function get_global_tasks_info() {
	$return_string = "[SPLIT]";
	
	$return_string = $return_string . "<input type='button' value='GO' onclick='submit_form();'><br /><br />";
	
	$return_string = $return_string . "<table class='pretty_grid'>";
	$return_string = $return_string . "<tr>";
	$return_string = $return_string . "<th>" . T_("Name") . "</th>";
	$return_string = $return_string . "<th>" . T_("Points") . "</th>";
	$return_string = $return_string . "<th>" . T_("Max Points") . "</th>";
	$return_string = $return_string . "<th><input type='checkbox' name='ALL' onclick='check_all(this);'></th>";
	$return_string = $return_string . "</tr>";
	
	$global_tasks_query = mq("SELECT * FROM camps_tasks");	
	while ($global_task = mysql_fetch_assoc($global_tasks_query)) {
		$return_string = $return_string . "<tr>";
		$return_string = $return_string . "<td>" . $global_task["task_name"] . "</td>";
		$return_string = $return_string . "<td>" . $global_task["points"] . "</td>";
		$return_string = $return_string . "<td>" . $global_task["max_times"] . "</td>";
		$return_string = $return_string . "<td><input type='checkbox' id='checkbox_" . $global_task["task_id"] . "' name='checkbox_" . $global_task["task_id"] . "'></td>";
		$return_string = $return_string . "</tr>";
	}
	
	$return_string = $return_string . "</table>";
	
	return $return_string;
}

function get_task_info() {
	global $camp_id;
	global $group_type_id;
	global $group_id;
	global $campaign_id;
	global $task_id;
	global $form_name;
	global $divs;
	global $div6;
	
	$tasks_query = mq("SELECT * FROM campaign_tasks WHERE campaign_id=" . $campaign_id);
	$num_rows = mysql_num_rows($tasks_query);

	$return_string = "[SPLIT]";

	if ($div6 === false || $num_rows == 0) {
		$return_string = $return_string . "<table class='pretty_grid'>";
	
		if ($num_rows == 0) {
			$return_string = $return_string . "<th>" . T_('No Campaign Tasks Found') . "</th>";
		}
		else {
			$return_string = $return_string . "<tr>";
			$return_string = $return_string . "<th>" . T_("Name") . "</th>";
			$return_string = $return_string . "<th>" . T_("Points") . "</th>";
			$return_string = $return_string . "<th>" . T_("Max Times") . "</th>";
			$return_string = $return_string . "<th></th>";
			$return_string = $return_string . "<th></th>";
			$return_string = $return_string . "</tr>";
			
			$delete_message = T_("Are you sure that you want to delete this Campaign Task?");
		
			while ($task = mysql_fetch_assoc($tasks_query)) {
					$return_string = $return_string . "<tr>";
					$return_string = $return_string . "<td>" . $task['task_name'] . "</td>";
					$return_string = $return_string . "<td>" . $task['points'] . "</td>";
					$return_string = $return_string . "<td>" . $task['max_times'] . "</td>";
					$return_string = $return_string . "<td><a href=\"#\" onclick=\"document.getElementById('action').value='edit'; document.getElementById('task_id').value='" . $task['task_id'] . "'; document.forms['" . $form_name. "'].submit();\">" . T_('Edit Task') . "</a></td>";
					$return_string = $return_string . "<td><a href='#' onclick='document.getElementById(\"action\").value=\"delete\"; document.getElementById(\"task_id\").value=\"" . $task['task_id'] . "\"; var dlt = confirm (\"" . $delete_message . "\"); if (dlt == true) document.forms[\"" . $form_name . "\"].submit();'>" . T_('Delete Task') . "</a></td>";
					$return_string = $return_string . "</tr>";			
			}
		}
		$return_string = $return_string . "</table>";
	}
	else {
		$return_string = $return_string . T_('Select a Campaign Task') . ":";
		$return_string = $return_string . "<label>";	
		$return_string = $return_string . "<select name='task_id' id='task_id' onchange='get_divs(\"" . $divs . "\", \"" . $form_name . "\", \"task_id\");'>";

		$row_num = 0;
		while ($task = mysql_fetch_assoc($tasks_query)) {
			if ($row_num == 0 && $task_id == -1) 
				$task_id = $task['task_id'];
				
			if ($task_id == $task['task_id']) 
				$return_string = $return_string . "<option value='" . $task['task_id'] . "' selected>" . $task['task_name'] . "</option>";
			else
				$return_string = $return_string . "<option value='" . $task['task_id'] . "'>" . $task['task_name'] . "</option>";
				
			$row_num++;		
		}
		
		$return_string = $return_string . "</select>";		
		$return_string = $return_string . "</label>";

		if ($form_name != "global_tasks_form") {
			$return_string = $return_string . "<br /><br />";
			
			$task = mysql_fetch_assoc(mq("SELECT * FROM campaign_tasks WHERE task_id=" . $task_id));
			
			$return_string = $return_string . T_("Miles") . ":<input type='text' name='miles' id='miles' value='" . $task['points'] . "'><br />";
			$return_string = $return_string . T_("Left Circle") . ":<input type='text' name='left_circle' id='left_circle' value='" . $task['points'] . "'><br />";
			$return_string = $return_string . T_("Right Circle") . ":<input type='text' name='right_circle' id='right_circle' value='" . $task['points'] . "'><br />";
			$return_string = $return_string . T_("# of Cards") . ":<input type='text' name='number_of_cards' id='number_of_cards' value='10'><br />";
					
			$return_string = $return_string . "<br /><input type='button' value=" . T_('GO') . " onclick='print_cards();'>";
		}
	}
	
	return $return_string;
}

function get_campaign_info() {
	global $camp_id;
	global $group_type_id;
	global $group_id;
	global $campaign_id;
	global $form_name;
	global $divs;
	global $div5;	
	
	$return_string = "[SPLIT]";
	
	$campaigns_query = mq("SELECT * FROM campaigns WHERE group_id=" . $group_id);
	$num_rows = mysql_num_rows($campaigns_query); 

	if ($div5 === false) {		
		$return_string = $return_string . "<table class='pretty_grid'>";
				
		if ($num_rows == 0) {
			$return_string = $return_string . "<th>" . T_('No Campaigns Found') . "</th>";
		}
		else {
			$return_string = $return_string . "<tr>";
			$return_string = $return_string . "<th>" . T_("Name") . "</th>";
			$return_string = $return_string . "<th>" . T_("Points") . "</th>";
			$return_string = $return_string . "<th></th>";
			$return_string = $return_string . "<th></th>";
			$return_string = $return_string . "</tr>";
		
			$delete_message = T_("Are you sure that you want to delete this Campaign?");
			
			while ($campaign = mysql_fetch_assoc($campaigns_query)) {
				$return_string = $return_string . "<tr>";
				$return_string = $return_string . "<td>" . $campaign['campaign_name'] . "</td>";
				$return_string = $return_string . "<td>" . $campaign['points'] . "</td>";
				$return_string = $return_string . "<td><a href=\"#\" onclick=\"document.getElementById('action').value='edit'; document.getElementById('campaign_id').value='" . $campaign['campaign_id'] . "'; document.forms['" . $form_name. "'].submit();\">" . T_('Edit Group') . "</a></td>";
				$return_string = $return_string . "<td><a href='#' onclick='document.getElementById(\"action\").value=\"delete\"; document.getElementById(\"campaign_id\").value=\"" . $campaign['campaign_id'] . "\"; var dlt = confirm (\"" . $delete_message . "\"); if (dlt == true) document.forms[\"" . $form_name . "\"].submit();'>" . T_('Delete Group') . "</a></td>";
				$return_string = $return_string . "</tr>";			
			}
		}
		
		$return_string = $return_string . "</table>";
	 }
	 else {		
		$return_string = $return_string . T_('Select a Campaign') . ":";
		$return_string = $return_string . "<label>";
		
		$return_string = $return_string . "<select name='campaign_id' id='campaign_id' onchange='get_divs(\"" . $divs . "\", \"" . $form_name . "\", \"campaign_id\");'>";
		
		$row_num = 0;
		while ($campaign = mysql_fetch_assoc($campaigns_query)) {
			if ($row_num == 0 && $campaign_id == -1) 
				$campaign_id = $campaign['campaign_id'];
				
			if ($campaign_id == $campaign['campaign_id']) 
				$return_string = $return_string . "<option value='" . $campaign['campaign_id'] . "' selected>" . $campaign['campaign_name'] . "</option>";
			else
				$return_string = $return_string . "<option value='" . $campaign['campaign_id'] . "'>" . $campaign['campaign_name'] . "</option>";
				
			$row_num++;		
		}
		
		$return_string = $return_string . "</select>";		
		$return_string = $return_string . "</label>";		
	 }
	 
	return $return_string;
}

function get_group_info() {
	global $camp_id;
	global $group_type_id;
	global $group_id;
	global $form_name;
	global $divs;
	global $div4;

	$return_string = "";
	
	$groups_query = mq("SELECT * FROM camp_groups WHERE group_type_id=" . $group_type_id);
	$num_rows = mysql_num_rows($groups_query); 
	
	if ($div4 === false) {		
		$return_string = "[SPLIT]";
		$return_string = $return_string . "<table class='pretty_grid'>";
		
		if ($num_rows == 0) {
			$return_string = $return_string . "<th>" . T_('No Groups Found') . "</th>";
		}
		else {
			while ($group = mysql_fetch_assoc($groups_query)) {
				$return_string = $return_string . "<tr>";
				$return_string = $return_string . "<td>" . $group['group_name'] . "</td>";
				$return_string = $return_string . "<td><a href=\"#\" onclick=\"document.getElementById('action').value='edit'; document.getElementById('group_id').value='" . $group['group_id'] . "'; document.forms['" . $form_name. "'].submit();\">" . T_('Edit Group') . "</a></td>";
				$return_string = $return_string . "<td><a href='#' onclick='document.getElementById(\"action\").value=\"delete\"; document.getElementById(\"group_id\").value=\"" . $group['group_id'] . "\"; var dlt = confirm (\"" . T_('Are you sure that you want to delete this Group') . "\"'); if (dlt == true) document.forms[\"" . $form_name . "\"].submit();'>" . T_('Delete Group') . "</a></td>";
				$return_string = $return_string . "</tr>";			
			}
		}
		
		$return_string = $return_string . "</table>";
	 }
	 else {		
		$return_string = "[SPLIT]";
		$return_string = $return_string . T_('Select a Group') . ":";
		$return_string = $return_string . "<label>";
		
		$return_string = $return_string . "<select name='group_id' id='group_id' onchange='get_divs(\"" . $divs . "\", \"" . $form_name . "\", \"group_id\");'>";
		
		$row_num = 0;
		while ($group = mysql_fetch_assoc($groups_query)) {
			if ($row_num == 0 && $group_id == -1) 
				$group_id = $group['group_id'];
				
			if ($group_id == $group['group_id']) 
				$return_string = $return_string . "<option value='" . $group['group_id'] . "' selected>" . $group['group_name'] . "</option>";
			else
				$return_string = $return_string . "<option value='" . $group['group_id'] . "'>" . $group['group_name'] . "</option>";
				
			$row_num++;		
		}
		
		$return_string = $return_string . "</select>";
		
		$return_string = $return_string . "</label>";		
	 }
	 
	 return $return_string;	
}

function get_group_type_info() {
	global $camp_id;
	global $group_type_id;
	global $div3;
	global $form_name;
	global $divs;
	
	$return_string = "";
	
	 $group_types_query = mq("SELECT * FROM camp_group_types WHERE camp_id=" . $camp_id);
	 $num_rows = mysql_num_rows($group_types_query); 
	 
	 if ($div3 === false) {
		$return_string = "[SPLIT]<table class='pretty_grid'>";
		
		if ($num_rows == 0) {
			$return_string = $return_string . "<th>" . T_('No Group Types Found') . "</th>";
		}
		else {
			while ($group_type = mysql_fetch_assoc($group_types_query)) {
				$return_string = $return_string . "<tr>";
				$return_string = $return_string . "<td>" . $group_type['group_type_name'] . "</td>";
				$return_string = $return_string . "<td><a href=\"#\" onclick=\"document.getElementById('action').value='edit'; document.getElementById('group_type_id').value='" . $group_type['group_type_id'] . "'; document.forms['" . $form_name. "'].submit();\">" . T_('Edit Group Type') . "</a></td>";		
				$return_string = $return_string . "<td><a href='#' onclick='document.getElementById(\"action\").value=\"delete\"; document.getElementById(\"group_type_id\").value=\"" . $group_type['group_type_id'] . "\"; var dlt = confirm (\"" . T_('Are you sure that you want to delete this Group Type') . "\"'); if (dlt == true) document.forms[\"" . $form_name . "\"].submit();'>" . T_('Delete Group Type') . "</a></td>";
				$return_string = $return_string . "</tr>";			
			}
		}
		
		$return_string = $return_string . "</table>";
	 }
	 else {
		$return_string = "[SPLIT]";
		$return_string = $return_string . T_('Select a Group Type') . ":";
		$return_string = $return_string . "<label>";
		
		$return_string = $return_string . "<select name='group_type_id' id='group_type_id' onchange='get_divs(\"" . $divs . "\", \"" . $form_name . "\", \"group_type_id\");'>";
		
		$row_num = 0;
		while ($group_type = mysql_fetch_assoc($group_types_query)) {
			if ($row_num == 0 && $group_type_id == -1) 
				$group_type_id = $group_type['group_type_id'];
				
			if ($group_type_id == $group_type['group_type_id']) 
				$return_string = $return_string . "<option value='" . $group_type['group_type_id'] . "' selected>" . $group_type['group_type_name'] . "</option>";
			else
				$return_string = $return_string . "<option value='" . $group_type['group_type_id'] . "'>" . $group_type['group_type_name'] . "</option>";
				
			$row_num++;		
		}
		
		$return_string = $return_string . "</select>";
		
		$return_string = $return_string . "</label>";		
	 }
	 
	 return $return_string;
	 
}

function get_camp_info() {
	global $camp_id;
	global $divs;
	global $form_name;
	global $user_type;
	
	$return_string = "";
	
	if ($user_type == "camp") {
		$camp = mysql_fetch_assoc(mq("SELECT * FROM camps WHERE camp_id=" . $camp_id));
		$return_string = "<h2>Camp:" . $camp['camp_name'] . "</h2>";		
	}
	else {
		$camps_query = mq("SELECT * FROM camps");
		
		$return_string = T_('Select a Camp') . ":";
		$return_string = $return_string . "<label>";
		
		$return_string = $return_string . "<select name='camp_id' id='camp_id' onchange='get_divs(\"" . $divs . "\", \"" . $form_name . "\", \"camp_id\");'>";
		
		$row_num = 0;
		while ($camp = mysql_fetch_assoc($camps_query)) {
			if ($row_num == 0 && $camp_id == -1) 
				$camp_id = $camp['camp_id'];
				
			if ($camp_id == $camp['camp_id']) 
				$return_string = $return_string . "<option value='" . $camp['camp_id'] . "' selected>" . $camp['camp_name'] . "</option>";
			else
				$return_string = $return_string . "<option value='" . $camp['camp_id'] . "'>" . $camp['camp_name'] . "</option>";
				
			$row_num++;
		}
		
		$return_string = $return_string . "</select>";
		
		$return_string = $return_string . "</label>";
	}
	
	return $return_string;
}
?>
