<? 
$admin_auth = array('camp');
require('header.php'); 
require_once('file_save.php');
require_once('calendar.php');

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

//$group_type_id = gri('group_type_id', -1);
//$group_id = gri('group_id', -1);
//$division_id = gri('division_id', -1);

$user_id = gri('user_id', -1);
if ($user_id > -1) {
	$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
	$query = mysql_query($sql);
	$member = mysql_fetch_assoc($query);
}

$action = gr("action", "");
if ($action != "") {

	$colon_position = strpos($action, ":");	
	if ($colon_position !== false) {
		$member_group_id = substr($action, $colon_position + 1, strlen($action) - ($colon_position + 1));
		$action = substr($action, 0, $colon_position);
	}
	
	switch($action) {
		case "assign":
			$group_ids =explode(":", gr('group_ids'));
			for ($cntr = 0; $cntr < count($group_ids); $cntr++) {
				$group = mysql_fetch_assoc(mq("SELECT g.group_id, g.division_id, d.group_type_id FROM groups AS g JOIN divisions AS d USING (division_id) WHERE g.group_id=" . $group_ids[$cntr]));
				$row = mysql_fetch_assoc(mq("SELECT member_group_id FROM member_groups WHERE user_id=" . $user_id . " AND group_type_id=" . $group['group_type_id'] . " AND division_id=" . $group['division_id']));
				
				if ($row['member_group_id'] > 0) {		
					$sql = "UPDATE member_groups SET group_id=" . $group_ids[$cntr] . " WHERE member_group_id=" . $row['member_group_id'];	
					mq($sql);
				}
				else {
					$sql = "INSERT INTO member_groups SET user_id=" . $user_id . ", group_type_id=" . $group['group_type_id'] . ", division_id=" . $group['division_id'] . ", group_id=" . $group_ids[$cntr];
					mq($sql);
				}
			}		
		break;
		
		case "remove":
			$sql = "DELETE FROM member_groups WHERE member_group_id=" . $member_group_id;
			mq($sql);			
		break;
	}
	
	$action = "";
}

function get_divisions() {
	global $camp_id;
	global $user_id;

	$sql = "";
	$sql = $sql . "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name, g.group_id, g.group_name, mg1.division_id AS division_member, mg2.group_id AS group_member ";
	$sql = $sql . "FROM group_types AS gt ";
	$sql = $sql . "JOIN divisions AS d USING (group_type_id) ";
	$sql = $sql . "JOIN groups AS g USING (division_id) ";
	$sql = $sql . "LEFT JOIN member_groups AS mg1 ON (mg1.division_id=g.division_id AND mg1.user_id=" . $user_id . ") ";
	$sql = $sql . "LEFT JOIN member_groups AS mg2 ON (mg2.group_id=g.group_id AND mg2.user_id=" . $user_id . ") ";
	$sql = $sql . "WHERE camp_id=" . $camp_id . " ";
	$sql = $sql . "ORDER BY gt.group_type_name, d.division_id, g.group_id";

	//$sql = "SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name, g.group_id, g.group_name, mg.member_group_id, mg.group_id AS group_member FROM group_types AS gt JOIN divisions AS d USING (group_type_id) JOIN groups AS g USING (division_id) LEFT JOIN member_groups AS mg ON (mg.division_id=g.division_id AND mg.user_id=" . $user_id . ") WHERE camp_id=" . $camp_id . " ORDER BY gt.group_type_name, d.division_id, g.group_id";
	//echo $sql . "<br />";
	
	//$query = mq("SELECT gt.group_type_id, gt.group_type_name, d.division_id, d.division_name, g.group_id, g.group_name, mg.member_group_id, mg.group_id AS group_member FROM group_types AS gt JOIN divisions AS d USING (group_type_id) JOIN groups AS g USING (division_id) LEFT JOIN member_groups AS mg ON (mg.division_id=g.division_id AND mg.user_id=" . $user_id . ") WHERE camp_id=" . $camp_id . " ORDER BY gt.group_type_name, d.division_id, g.group_id");
	$query = mq($sql);
	
	$prev_group_type_name = "";
	$prev_division_name = "";
	
	$echo_string = "\n\n<table class='pretty_grid' id='divisions_table' name='divisions_table'>";
	$echo_string = $echo_string . "\n\t<thead>";
	$echo_string = $echo_string . "\n\t\t<tr>\n";
	$echo_string = $echo_string .  "\n\t\t\t<th>" . T_('Group Type') . "</th>\n";
	$echo_string = $echo_string .  "\n\t\t\t<th>" . T_('Division') . "</th>\n";
	$echo_string = $echo_string .  "\n\t\t\t<th>" . T_('Group') . "</th>\n";
	$echo_string = $echo_string .  "\n\t\t</tr>\n";	
	$echo_string = $echo_string .  "\n\t</thead>\n";
	
	while ($row = mysql_fetch_assoc($query)) {
		$echo_string = $echo_string . "\t<tr>\n";		
		
		if ($prev_group_type_name != $row['group_type_name']) {
			$echo_string = $echo_string . "\t\t<td>" . $row['group_type_name'] . "</td>\n";			
		}
		else {
			$echo_string = $echo_string . "\t\t<td>&nbsp</td>\n";
		}
		
		if ($prev_division_name != $row['division_name'])
			$echo_string = $echo_string . "\t\t<td>" . $row['division_name'] . "</td>\n";
		else
			$echo_string = $echo_string . "\t\t<td>&nbsp</td>\n";
			
			
		if ($row['group_member'] > 0) 
			$echo_string = $echo_string . "\t\t<td><input type='radio'  checked id='group_" . $row['group_id'] . "' name='division_" . $row['division_id'] . "' value='" . $row['group_id'] . "'>" . $row['group_name'] . "</td>\n";
		else 
			$echo_string = $echo_string . "\t\t<td><input type='radio' id='group_" . $row['group_id'] . "' name='division_" . $row['division_id'] . "' value='" . $row['group_id'] . "'>" . $row['group_name'] . "</td>\n";
		
		$echo_string = $echo_string . "\t</tr>\n";
		
		$prev_group_type_name = $row['group_type_name'];
		$prev_division_name = $row['division_name'];
	}
	$echo_string = $echo_string . "</table>\n\n";
	
	echo $echo_string;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Member Divisions');?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<SCRIPT type="text/javascript" src="ajax_get_levels.js"></SCRIPT>
		<script type="text/javascript">
			function get_group_ids() {
				var group_ids = "";
				var divisions_table = document.getElementById("divisions_table");	
				var inputs = divisions_table.getElementsByTagName("input");
				
				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					if (type == "radio") {
						if (inputs[cntr].checked == true) {
							group_ids = group_ids + inputs[cntr].value + ":";
						}
					}
				}
				
				if (group_ids.length > 0) {
					group_ids = group_ids.substr(0, group_ids.length - 1);
					document.getElementById("group_ids").value = group_ids;
					document.member_groups_form.submit();
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
				
			<H1><?=T_('Member Divisions')?></H1>
			
			<form name="member_groups_form" id="member_groups_form" action="admin_member_groups.php" method="post" onsubmit="get_group_ids();";>
				<input type="hidden" name="action" id="action" value="">
				<input type="hidden" name="user_id" id="user_id" value="<?=$user_id;?>">
				<input type="hidden" name="group_ids" id="group_ids" value="">
				
				<h2><?=T_('Camp');?>: <label style="color:blue;"><?=$camp_name;?></label></h2>
				<h2><?=T_('Member');?>: <label style="color:blue;"><?=$member['first'];?> <?=$member['last'];?></label></h2>
				
				<input type="submit" value="ASSIGN" onclick="document.getElementById('action').value='assign';">
				
				<? get_divisions(); ?>
			</form>
			
			<br />
		</div> <!-- body -->
		
		<? include('admin_footer.php'); ?>

	</BODY>
	
</HTML>
