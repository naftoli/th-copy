<?
require('db.php');
require('lang.php');

$admin_id = $_GET['admin_id'];
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
	
if (isset($_GET['division_id']))
	$division_id = $_GET['division_id'];
else
	$division_id = -1;

if (isset($_GET['user_id']))
	$user_id = $_GET['user_id'];
else
	$user_id = -1;

	
$divs = $_GET['divs'];
$div1 = strpos($divs, "1");
$div2 = strpos($divs, "2");
$div3 = strpos($divs, "3");
$div4 = strpos($divs, "4");
$div5 = strpos($divs, "5");

$form_name = $_GET['form_name'];

if ($div1 !== false) {
	echo get_camps();
}

if ($div2 !== false) {
	echo get_group_types();
}

if ($div3 !== false) {
	echo get_divisions();
}

if ($div4 !== false) {
	echo get_groups();
}

function get_divisions() {
	global $admin_id;
	global $camp_id;
	global $user_id;
	global $divs;
	global $group_type_id;
	global $group_id;
	global $division_id;
	global $div4;
	global $form_name;
	
	$strpos = strpos($divs, "3");
	$get_divs = substr($divs, $strpos + 1, strlen($divs) - 1);
	
	$return_string = "";
	
	$sql = "SELECT * FROM divisions AS d JOIN group_types AS gt USING (group_type_id) WHERE d.group_type_id=" . $group_type_id;
	$query = mq($sql);
	$num_rows = mysql_num_rows($query);
	
	$return_string = "[SPLIT] "; 
	if ($div4 === false || $num_rows == 0) {
		$return_string = $return_string . "<table class='pretty_grid'>";
		
		if ($num_rows == 0) {
			$return_string = $return_string . "<th>" . T_('No Divisions Found') . "</th>";
		}
		else {
			$return_string = $return_string . "<tr>";
			$return_string = $return_string . "<th>" . T_("Name") . "</th>";
			if ($form_name == "group_tasks_form") {
				$return_string = $return_string . "<th><input type='checkbox' id='All' name='All' onclick='check_all_group_divisions(this);'>" . T_('All') . "</th>";
			}
			else {
				$return_string = $return_string . "<th></th>";
				$return_string = $return_string . "<th></th>";
				$return_string = $return_string . "<th></th>";
			}
			$return_string = $return_string . "</tr>";
			
			$delete_message = T_("Are you sure that you want to delete this Division?");
		
			while ($row = mysql_fetch_assoc($query)) {
				$return_string = $return_string . "<tr>";
				$return_string = $return_string . "<td>" . $row['division_name'] . "</td>";
				if ($form_name == "group_tasks_form") {
					$return_string = $return_string . "<td><input type='checkbox' " . $checked . " id='gd_" . $row['division_id'] . "' name='gd_" . $row['division_id'] . "'></td>";
				}
				else {
					$return_string = $return_string . "<td><a href=\"admin_groups.php?admin_id=" . $admin_id . "&group_type_id=" . $group_type_id . "&division_id=" . $row['division_id'] . "\">" . T_('Groups') . "</a></td>";
					$return_string = $return_string . "<td><a href=\"#\" onclick=\"document.getElementById('action').value='edit'; document.getElementById('division_id').value='" . $row['division_id'] . "'; document.forms['" . $form_name. "'].submit();\">" . T_('Edit Division') . "</a></td>";
					$return_string = $return_string . "<td><a href='#' onclick='document.getElementById(\"action\").value=\"delete\"; document.getElementById(\"division_id\").value=\"" . $row['division_id'] . "\"; var dlt = confirm (\"" . $delete_message . "\"); if (dlt == true) document.forms[\"" . $form_name . "\"].submit();'>" . T_('Delete Division') . "</a></td>";
				}
				$return_string = $return_string . "</tr>";			
			}
		}
		$return_string = $return_string . "</table>";
		
	}
	else {
		$return_string = $return_string . T_('Select a Division') . ":";
		$return_string = $return_string . "<label>";
		
		$return_string = $return_string . "<select name='division_id' id='division_id' onchange='get_groups(\"" . $get_divs . "\", \"" . $form_name . "\", \"division_id\");'>";
		
		$row_num = 0;
		while ($row = mysql_fetch_assoc($query)) {
		
			if ($row_num == 0 && $division_id == -1) 
				$division_id = $row['division_id'];
					
			if ($division_id == $row['division_id']) 
				$return_string = $return_string . "<option value='" . $row['division_id'] . "' selected>" . $row['division_name'] . "</option>";
			else
				$return_string = $return_string . "<option value='" . $row['division_id'] . "'>" . $row['division_name'] . "</option>";
					
			$row_num++;		

		}
		
		$return_string = $return_string . "</select>";		
		$return_string = $return_string . "</label>";
	
	}	
	
	return $return_string;
}


function get_groups() {
	global $camp_id;
	global $divs;
	global $division_id;
	global $group_id;
	global $div5;
	global $form_name;
	
	$strpos = strpos($divs, "4");
	$get_divs = substr($divs, $strpos + 1, strlen($divs) - 1);
	
	$return_string = "";
	
	$sql = "SELECT * FROM groups WHERE division_id=" . $division_id;
	$query = mq($sql);
	$num_rows = mysql_num_rows($query);
	
	$return_string = "[SPLIT] "; 
	
	if ($div5 === false || $num_rows == 0 || $form_name == "group_tasks_form") {
		$return_string = $return_string . "<table class='pretty_grid'>";
		
		if ($num_rows == 0) {
			$return_string = $return_string . "<th>" . T_('No Groups Found') . "</th>";
		}
		else {
			$return_string = $return_string . "<tr>";
			$return_string = $return_string . "<th>" . T_("Name") . "</th>";
			
			if ($form_name != "group_tasks_form") {
				$return_string = $return_string . "<th></th>";
				$return_string = $return_string . "<th></th>";
				//$return_string = $return_string . "<th></th>";
			}
			else {
				$return_string = $return_string . "<th><input type='checkbox' name='All' onclick='check_all_groups(this);'>" . T_('All') . "</th>";
			}			
			
			$return_string = $return_string . "</tr>";
			
			$delete_message = T_("Are you sure that you want to delete this Group?");
		
			while ($row = mysql_fetch_assoc($query)) {
				$return_string = $return_string . "<tr>";
				$return_string = $return_string . "<td>" . $row['group_name'] . "</td>";
				
				//if ($form_name != "group_tasks_form") {
					//if ($row['divisions'] > 0) 
					//	$return_string = $return_string . "<td><a href='admin_divisions.php?admin_id=" . $admin_id . "&camp_id=" . $camp_id . "&group_type_id=" . $group_type_id . "&group_id=" . $row['group_id'] . "'>" . T_('Divisions') . "</a></td>";
					//else 
					//	$return_string = $return_string . "<td></td>";
				//}
				
				if ($form_name != "group_tasks_form") {
					$return_string = $return_string . "<td><a href=\"#\" onclick=\"document.getElementById('action').value='edit'; document.getElementById('group_id').value='" . $row['group_id'] . "'; document.forms['" . $form_name. "'].submit();\">" . T_('Edit Group') . "</a></td>";
					$return_string = $return_string . "<td><a href='#' onclick='document.getElementById(\"action\").value=\"delete\"; document.getElementById(\"group_id\").value=\"" . $row['group_id'] . "\"; var dlt = confirm (\"" . $delete_message . "\"); if (dlt == true) document.forms[\"" . $form_name . "\"].submit();'>" . T_('Delete Group') . "</a></td>";
				}
				else {
					$return_string = $return_string . "<td><input type='checkbox' id='" . $row['group_id'] . "' name='" . $row['group_id'] . "'></td>";				
				}
				
				$return_string = $return_string . "</tr>";			
			}
		}
		$return_string = $return_string . "</table>";
		
	}
	else {
		$return_string = $return_string . T_('Select a Group') . ":";
		$return_string = $return_string . "<label>";
				
		$return_string = $return_string . "<select name='group_id' id='group_id' onchange='get_groups(\"" . $get_divs . "\", \"" . $form_name . "\", \"group_id\");'>";

		$row_num = 0;
		while ($row = mysql_fetch_assoc($query)) {
		
			if ($row_num == 0 && $group_id == -1) 
				$group_id = $row['group_id'];
					
			if ($group_id== $row['group_id']) 
				$return_string = $return_string . "<option value='" . $row['group_id'] . "' selected>" . $row['group_name'] . "</option>";
			else
				$return_string = $return_string . "<option value='" . $row['group_id'] . "'>" . $row['group_name'] . "</option>";
				
			$row_num++;		
						
		}
		
		$return_string = $return_string . "</select>";		
		$return_string = $return_string . "</label>";
	
	}	
	
	return $return_string;
}

function get_group_types() {
	global $camp_id;
	global $admin_id;
	global $group_type_id;
	global $divs;
	global $div3;
	global $form_name;
	
	$strpos = strpos($divs, "2");
	$get_divs = substr($divs, $strpos + 1, strlen($divs) - 1);
	
	$return_string = "";
	
	$sql = "SELECT * FROM group_types WHERE camp_id=" . $camp_id;
	$query = mq($sql);
	$num_rows = mysql_num_rows($query);
	
	$return_string = "[SPLIT] "; 
	if ($div3 === false || $num_rows == 0) {
		$return_string = $return_string . "<table class='pretty_grid'>";
		
		if ($num_rows == 0) {
			$return_string = $return_string . "<th>" . T_('No Groups Found') . "</th>";
		}
		else {
			$return_string = $return_string . "<tr>";
			$return_string = $return_string . "<th>" . T_("Name") . "</th>";
			$return_string = $return_string . "<th></th>";
			$return_string = $return_string . "<th></th>";
			$return_string = $return_string . "<th></th>";
			$return_string = $return_string . "</tr>";
			
			$delete_message = T_("Are you sure that you want to delete this Group Type?");
		
			while ($row = mysql_fetch_assoc($query)) {
				$return_string = $return_string . "<tr>";				
				$return_string = $return_string . "<td>" . $row['group_type_name'] . "</td>";
				
				if ($row['divisions'] > 0) 
					$return_string = $return_string . "<td><a href='admin_divisions.php?admin_id=" . $admin_id . "&camp_id=" . $camp_id . "&group_type_id=" . $row['group_type_id'] . "'>" . T_('Divisions') . "</a></td>";
				else 
					$return_string = $return_string . "<td></td>";
				
				$return_string = $return_string . "<td><a href=\"#\" onclick=\"document.getElementById('action').value='edit'; document.getElementById('group_type_id').value='" . $row['group_type_id'] . "'; document.forms['" . $form_name. "'].submit();\">" . T_('Edit Group Type') . "</a></td>";
				$return_string = $return_string . "<td><a href='#' onclick='document.getElementById(\"action\").value=\"delete\"; document.getElementById(\"group_type_id\").value=\"" . $row['group_type_id'] . "\"; var dlt = confirm (\"" . $delete_message . "\"); if (dlt == true) document.forms[\"" . $form_name . "\"].submit();'>" . T_('Delete Group Type') . "</a></td>";
				$return_string = $return_string . "</tr>";			
			}
		}
		$return_string = $return_string . "</table>";
		
	}
	else {
		$return_string = $return_string . T_('Select a Group Type') . ":";
		$return_string = $return_string . "<label>";
		
		$return_string = $return_string . "<select name='group_type_id' id='group_type_id' onchange='get_groups(\"" . $get_divs . "\", \"" . $form_name . "\", \"campaign_group_id\");'>";
		
		$row_num = 0;
		while ($row = mysql_fetch_assoc($query)) {
		
			if ($row['divisions'] > 0) {
				if ($row_num == 0 && $group_type_id == -1) 
					$group_type_id = $row['group_type_id'];
					
				if ($group_type_id== $row['group_type_id']) 
					$return_string = $return_string . "<option value='" . $row['group_type_id'] . "' selected>" . $row['group_type_name'] . "</option>";
				else
					$return_string = $return_string . "<option value='" . $row['group_type_id'] . "'>" . $row['group_type_name'] . "</option>";
				
				$row_num++;
			}
		}
		
		$return_string = $return_string . "</select>";		
		$return_string = $return_string . "</label>";
	
	}
	
	return $return_string;
}

function get_camps() {
	global $camp_id;
	global $divs;
	global $form_name;
	global $user_type;
	
	$strpos = strpos($divs, "1");
	$get_divs = substr($divs, $strpos + 1, strlen($divs) - 1);
	
	$return_string = "";
	
	if ($user_type == "camp" || $camp_id > -1) {
		$camp = mysql_fetch_assoc(mq("SELECT * FROM camps WHERE camp_id=" . $camp_id));
		$return_string = "<h2>" . T_('Camp') . ": <label style='color:blue;'>" . $camp['camp_name'] . "</label></h2>";		
	}
	else {
		$camps_query = mq("SELECT * FROM camps");
		
		$return_string = T_('Select a Camp') . ":";
		$return_string = $return_string . "<label>";
		
		$return_string = $return_string . "<select name='camp_id' id='camp_id' onchange='get_groups(\"" . $get_divs . "\", \"" . $form_name . "\", \"camp_id\");'>";
		
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
