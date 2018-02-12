<? 
$admin_auth = array('camp');
require('header.php'); 
require_once('file_save.php');
require_once('calendar.php');

$camp_id = gri('camp_id', -1);
if ($camp_id > -1) {
	$camp = mysql_fetch_assoc(mq("SELECT * FROM camps WHERE camp_id=" . $camp_id));
}

$admin_id = gri('admin_id', -1);
if ($admin_id > -1) {
	$employee = mysql_fetch_assoc(mq("SELECT * FROM admins WHERE admin_id=" . $admin_id));
}

$action = gr("action", "");
if ($action != "") {

	switch($action) {
	
		case "assign":
			if (gr("group_type_ids") != "") {
				$group_type_ids = split(";", gr("group_type_ids"));
				for ($cntr = 0; $cntr < count($group_type_ids); $cntr++) {
					$group_type_id = $group_type_ids[$cntr];
					$num_rows = mysql_num_rows(mq("SELECT * FROM staff_group_types WHERE admin_id=" . $admin_id . " AND group_type_id=" . $group_type_id));
					if ($num_rows == 0) {
						$sql = "INSERT INTO staff_group_types SET admin_id=" . $admin_id . ", group_type_id=" . $group_type_id;
						mq($sql);
					}
				}				
			}
			
			if (gr("group_ids") != "") {
				$group_ids = split(";", gr("group_ids"));
				for ($cntr = 0; $cntr < count($group_ids); $cntr++) {
					$group_id = $group_ids[$cntr];
					$num_rows = mysql_num_rows(mq("SELECT * FROM staff_groups WHERE admin_id=" . $admin_id . " AND group_id=" . $group_id));
					if ($num_rows == 0) {
						$sql = "INSERT INTO staff_groups SET admin_id=" . $admin_id . ", group_id=" . $group_id;
						mq($sql);
					}
				}				
			}
			
			if (gr("division_ids") != "") {
				$division_ids = split(";", gr("division_ids"));
				for ($cntr = 0; $cntr < count($division_ids); $cntr++) {
					$division_id = $division_ids[$cntr];
					$num_rows = mysql_num_rows(mq("SELECT * FROM staff_divisions WHERE admin_id=" . $admin_id . " AND division_id=" . $division_id));
					if ($num_rows == 0) {
						$sql = "INSERT INTO staff_divisions SET admin_id=" . $admin_id . ", division_id=" . $division_id;
						mq($sql);
					}
				}				
			}
			
		break;
		
		case "remove":
			$group_type_id = gri('group_type_id', -1);
			$group_id = gri('group_id', -1);
			$division_id = gri('division_id', -1);
			
			if ($group_type_id > -1) {	
				$sql = "DELETE FROM staff_group_types WHERE admin_id=" . $admin_id . " AND group_type_id=" . $group_type_id;
				mq($sql);
			}
			elseif ($division_id > -1) {
				$sql = "DELETE FROM staff_divisions WHERE admin_id=" . $admin_id . " AND division_id=" . $division_id;
				mq($sql);
			}
			elseif ($group_id > -1) {
				$sql = "DELETE FROM staff_groups WHERE admin_id=" . $admin_id . " AND group_id=" . $group_id;
				mq($sql);
			}			
		break;
		
	}
	
	$action = "";
}

function get_groups() {
	global $camp_id;
	global $admin_id;
	
	$gt_g = "";
	
	$echo_string = "\n<table class='pretty_grid'>\n";
	$echo_string = $echo_string . "\t<thead>\n";
	$echo_string = $echo_string . "\t\t<tr>\n";
	$echo_string = $echo_string . "\t\t\t<th>" . T_('Group Type') . "</th>\n";
	$echo_string = $echo_string . "\t\t\t<th>" . T_('Division') . "</th>\n";	
	$echo_string = $echo_string . "\t\t\t<th>" . T_('Group') . "</th>\n";
	$echo_string = $echo_string . "\t\t</tr>\n";	
	$echo_string = $echo_string . "\t</thead>\n";

	$sql = "";
	$sql = $sql . "SELECT gt.group_type_id, gt.group_type_name, gt.divisions, d.division_id, d.division_name, d.groups, g.group_id, g.group_name, sgt.group_type_id AS group_type_member, sd.division_id AS division_member, sg.group_id AS group_member ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "LEFT JOIN staff_group_types AS sgt ON (sgt.admin_id=" . $admin_id . " AND sgt.group_type_id=gt.group_type_id) ";
	$sql = $sql . "JOIN divisions AS d ON (gt.group_type_id=d.group_type_id) ";
	$sql = $sql . "LEFT JOIN staff_divisions AS sd ON (sd.admin_id=" . $admin_id . " AND sd.division_id=d.division_id) ";
	$sql = $sql . "JOIN groups AS g ON (g.division_id=d.division_id) ";	
	$sql = $sql . "LEFT JOIN staff_groups AS sg ON (sg.admin_id=" . $admin_id . " AND sg.group_id=g.group_id) ";	
	$sql = $sql . "WHERE gt.camp_id=" . $camp_id . " "; 
	$sql = $sql . "ORDER BY gt.group_type_name,  d.division_id, g.group_id";
	
	$query = mq($sql);
	
	$prev_group_type_name = "";
	$prev_division_id = "";
	while ($row = mysql_fetch_assoc($query)) {
		$echo_string = $echo_string . "\t\t<tr>\n";
		
		if ($row['group_type_member'] > 0) {
			$checked = " checked ";
			$disabled = " disabled ";
			$style = " style='color:blue;' ";
			$a_tag = "<a href='admin_staff_groups.php?admin_id=" . $admin_id . "&camp_id=" . $camp_id . "&action=remove&group_type_id=" . $row['group_type_id'] . "'>" . T_('Remove') . "</a> ";			
		}
		else {
			$checked = "";
			$disabled = "";
			$style = "";					
			$a_tag = "";
		}
		
		if ($prev_group_type_name != $row['group_type_name']) {
			$echo_string = $echo_string . "\t\t\t<td>\n";
			$echo_string = $echo_string . "\t\t\t\t<input type='checkbox' " . $disabled . $checked . " id='" . $row['group_type_id'] . "' name='" . $row['group_type_id'] . "' onclick='check_divisions(this);'>\n";
			$echo_string = $echo_string . "\t\t\t\t" . $a_tag . "<label " . $style . ">" . $row['group_type_name'] . "</label>\n";
			$echo_string = $echo_string . "\t\t\t</td>\n";
		}
		else {
			$echo_string = $echo_string . "\t\t\t<td>&nbsp;</td>\n";
		}
		
		// ***** DIVISIONS ***** //
		if ($prev_division_id != $row['division_id']) {
			$gt_g = $row['group_type_id'] . ":" . $row['division_id'];
			
			if ($row['division_member'] > 0) {
				$checked = " checked ";
				$disabled = " disabled ";
				$style = " style='color:blue;' ";
				if ($row['divisions'] > 0)
					$a_tag = "<a href='admin_staff_groups.php?admin_id=" . $admin_id . "&camp_id=" . $camp_id . "&action=remove&division_id=" . $row['division_id'] . "'>" . T_('Remove') . "</a> ";
				else
					$a_tag = "";
			}
			else {
				$checked = "";
				$disabled = "";
				$style = "";					
				$a_tag = "";
			}
			
			$echo_string = $echo_string . "\t\t\t<td>\n";
			if ($row['divisions'] > 0) 
				$echo_string = $echo_string . "\t\t\t\t<input type='checkbox' " . $disabled . $checked . " id='" . $gt_g . "' name='" . $gt_g . "' onclick='check_groups(this)'>\n";
			else
				$echo_string = $echo_string . "\t\t\t\t<input type='checkbox' " . $disabled . $checked . " id='" . $gt_g . "' name='" . $gt_g . "' style='display:none'>\n";
			$echo_string = $echo_string . "\t\t\t\t" . $a_tag . "<label " . $style . ">" . $row['division_name'] . "<label>\n";
			$echo_string = $echo_string . "\t\t\t</td>\n";
		}
		else {
			$echo_string = $echo_string . "\t\t\t<td>&nbsp;</td>\n";
		}
		// ***** DIVISIONS ***** //
		
		// ***** GROUPS ***** //
		$gt_g_d = $gt_g . ":" . $row['group_id'];
		
		$echo_string = $echo_string . "\t\t\t<td>\n";
		
		if ($row['group_member'] > 0) {
			$checked = " checked ";	
			if ($row['divisions'] > 0)
				$a_tag = "<a href='admin_staff_groups.php?admin_id=" . $admin_id . "&camp_id=" . $camp_id . "&action=remove&group_id=" . $row['group_id'] . "'>" . T_('Remove') . "</a> ";
			else
				$a_tag = "";
			$disabled = " disabled ";
			$style = " style='color:blue;' ";	
		}
		else {
			$checked = "";
			$a_tag = "";
			$disabled = "";
			$style = "";
		}
		
		if ($row['groups'] > 0) 
			$echo_string = $echo_string . "\t\t\t\t<input type='checkbox' " . $disabled . $checked . " id='" . $gt_g_d . "' name='" . $gt_g_d . "'>\n";
		else
			$echo_string = $echo_string . "\t\t\t\t<input type='checkbox' " . $disabled . $checked . " id='" . $gt_g_d . "' name='" . $gt_g_d . "' style='display:none'>\n";
		$echo_string = $echo_string . "\t\t\t\t" . $a_tag . "<label " . $style . ">" . $row['group_name'] . "</label>\n";
		$echo_string = $echo_string . "\t\t\t</td>\n";
		// ***** GROUPS ***** //
		
		$echo_string = $echo_string . "\t\t<tr>\n";
		
		$prev_group_type_name = $row['group_type_name'];
		$prev_division_id = $row['division_id'];
	}
	
	$echo_string = $echo_string . "\n</table>\n";
	
	echo $echo_string;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Staff Groups');?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript">
			function check_divisions(chckbx) {
				var staff_groups_form = document.getElementById("staff_groups_form");
				var inputs = staff_groups_form.getElementsByTagName("input");
				
				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					if (type == "checkbox") {
						var name = inputs[cntr].getAttribute("name");
						var info = name.split(":");
						if (info.length > 1) {
							if (info[0] == chckbx.name) {
								inputs[cntr].checked = chckbx.checked;
							}
						}
					}
				}
			}
			
			function check_groups(chckbx) {
				var staff_groups_form = document.getElementById("staff_groups_form");
				var inputs = staff_groups_form.getElementsByTagName("input");
				
				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					if (type == "checkbox") {
						var name = inputs[cntr].getAttribute("name");
						var info = name.split(":");
						if (info.length > 2) {
							var division_name = info[0] + ":" + info[1];
							if (division_name == chckbx.name) {
								inputs[cntr].checked = chckbx.checked;
							}
						}
					}
				}
			}

			function get_divisions() {
				var group_type_ids = "";
				var group_ids = "";
				var division_ids = "";

				var staff_groups_form = document.getElementById("staff_groups_form");
				var inputs = staff_groups_form.getElementsByTagName("input");
				
				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					if (type == "checkbox") {
						if (inputs[cntr].checked) {
							var name = inputs[cntr].getAttribute("name");
							var info = name.split(":");
							if (info.length > 2) 
								group_ids = group_ids + info[2] + ";";
							else if (info.length > 1) 
								division_ids = division_ids + info[1] + ";";								
							else 
								group_type_ids = group_type_ids + info[0] + ";";
						}
					}
				}
				
				if (division_ids.length > 0 || group_ids.length > 0 || group_type_ids.length > 0) {
					if (group_type_ids.length > 0)
						group_type_ids = group_type_ids.substr(0, group_type_ids.length - 1);
						
					if (group_ids.length > 0) 
						group_ids = group_ids.substr(0, group_ids.length - 1);
					
					if (division_ids.length) 					
						division_ids = division_ids.substr(0, division_ids.length - 1);
					
					//alert("group_type_ids:" + group_type_ids + "\ndivision_ids:" + division_ids + "\ngroup_ids:" + group_ids);
									
					document.getElementById("group_type_ids").value = group_type_ids;
					document.getElementById("group_ids").value = group_ids;
					document.getElementById("division_ids").value = division_ids;
					
					document.getElementById("action").value = "assign";
					return true;
				}
				else {
					return false;
				}
			
			}
		</script>
	</HEAD>
	

	<body>
	
		<? include('admin_header.php'); ?>
				
		<div class="body">
			
			<? if(!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>
				
			<h2><?=T_('Staff Groups')?></h2>
						
			<h2><?=T_('Camp');?>: <label style='color:blue;'><?=$camp['camp_name'];?></label></h2>

			<h2><?=T_('Employee');?>: <label style='color:blue;'><?=$employee['first'];?> <?=$employee['last'];?></label></h2>
			
			<form name="staff_groups_form" id="staff_groups_form" action="admin_staff_groups.php" method="post">
				<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
				<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
				<input type="hidden" name="action" id="action">
				<input type="hidden" name="group_type_ids" id="group_type_ids">
				<input type="hidden" name="group_ids" id="group_ids">
				<input type="hidden" name="division_ids" id="division_ids">
								
				
				<input type="submit" value="<?=T_('ASSIGN');?>" onclick="return get_divisions();">
				
				<? get_groups(); ?>
			</form>

		</div> <!-- body -->
		
		<? include('admin_footer.php'); ?>

	</BODY>
	
</HTML>
