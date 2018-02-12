<? 
$admin_auth = array('camp'); 

require('header.php');
require_once('calendar.php');

$admin_id = gri("admin_id");

// ***** Determine if the user is a camp director or a super user ***** //
if ($admin_user['auth'] == "super")
	$user_type = "super";
else
	$user_type = "camp";
// ***** Determine if the user is a camp director or a super user ***** //

if ($user_type == "camp") 
	$camp_id = $admin_user['auths']['camp'][0]; 
else {
	$camp_id = gri('camp_id', -1);
}
	
if ($user_type == "camp" || $camp_id > -1) {
	$sql = "SELECT * FROM camps WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	$camp = mysql_fetch_assoc($query);
	$camp_name = $camp['camp_name'];
}

$group_type_id = gri('group_type_id', -1);
$group_id = gri('group_id', -1);
$campaign_id = gri('campaign_id', -1);
$campaign_group_id = gri('campaign_group_id', -1);
$task_id = gri('task_id', -1);

$action = gr('action', '');
if ($action != "") {

	switch($action) {
		case "assign":
			$user_ids = split(":", gr("user_ids"));
			for ($cntr = 0; $cntr < count($user_ids); $cntr++) {
				$sql = "INSERT INTO member_points SET user_id=" . $user_ids[$cntr] . ", task_id=" . $task_id . ", points=" . gri("points") . ", points_date=CURDATE()";
				mq($sql);				
			}
						
			$action = "";		
		break;		
	}
	
}

$delete_message = T_('Are you sure that you want to delete this campaign task?');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Assign Member Points'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
			var admin_id = "<?=$admin_id;?>";
			var user_type = "<?=$user_type;?>";
			var camp_id = "<?=$camp_id;?>";
			var group_type_id = "<?=$group_type_id;?>";
			var group_id = "<?=$group_id;?>";
			var campaign_id = "<?=$campaign_id;?>";
			var campaign_group_id = "<?=$campaign_group_id;?>";
			var task_id = "<?=$task_id;?>";
			var divs_array = ["camps_div", "group_types_div", "groups_div", "campaigns_div", "campaign_groups_div", "tasks_div", "members_div"];
			var divisions = "";

			function check_all(chckbx) {
				if (chckbx.checked)
					checked = true;
				else
					checked = false;
					
				var elements = document.getElementById("member_points_form").elements;
				
				for (cntr = 0; cntr < elements.length; cntr++) {
					if (elements[cntr].type == "checkbox") { 
						if (elements[cntr].name != "ALL") { 						
							if (document.getElementById("td_" + elements[cntr].name).innerHTML < document.getElementById("max_times").value)
								elements[cntr].checked = checked;
						}
						
					}
				}			
			}
			
			function verify_campaign_group_id() {
				var flag = true;
				try {
					var campaign_group_id = document.getElementById("campaign_group_id").value;
				}
				catch(err) {
					flag = false;
				}
				return flag;
			}

			function get_user_ids() {
				var user_ids = "";
				var flag = true;
				
				var elements = document.getElementById("member_points_form").elements;
				
				for (cntr = 0; cntr < elements.length; cntr++) {
					if (elements[cntr].type == "checkbox" && elements[cntr].name != "ALL") {
						if (elements[cntr].checked == true)
							user_ids = user_ids + elements[cntr].name + ":";
					}
				}			
				
				if (user_ids.length > 0) {
					user_ids = user_ids.substr(0, user_ids.length - 1);
					alert(user_ids);
					document.getElementById("action").value = "assign";
					document.getElementById("user_ids").value = user_ids;
				}
				else {
					flag = false;
				}
				
				return flag;
			}
			
			function verify_max_times(chckbx) {
				if (document.getElementById("td_" + chckbx.name).innerHTML >= document.getElementById("max_times").value) {
					chckbx.checked = false;
					alert("<?=T_('Maximum number of times has been reached');?>");
				}
			}
		</SCRIPT>
	</HEAD>
	
<? if ($action == "") : ?>	
	<body onload="get_divs('1234567', 'member_points_form', '');">
<? else : ?>
	<body>
<? endif; ?>		
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">		
			<DIV class="left_menu">
				<?include('admin_inc.php');?>
			</DIV>
			
			<H1>
				<?=T_('Assign Member Points')?>
			</H1>
						
			
<!-- **************************************** NO ACTION **************************************** -->			
<? //if ($action == "") : ?>

			<form name="member_points_form" id="member_points_form" action="admin_assign_member_points.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">
				<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
				<input type="hidden" name="user_ids" id="user_ids" value="">
				
				<br />
				<br />
				
				<div id="camps_div">
				</div>		
				
				<br />
				<br />
				
				<div id="group_types_div">
				</div>	
				
				<br />
				<br />
				
				<div id="groups_div">
				</div>	
				
				<br />
				<br />
				
				<div id="campaigns_div">
				</div>	
				
				<br />
				<br />
				
				<div id="campaign_groups_div">
				</div>
				
				<br />
				<br />
				
				<div id="tasks_div">
				</div>
				
				<br />
				<br />
				
				<div id="members_div">
				</div>
			</form>
	
<? //endif; ?> <!-- if ($action == "") : -->
<!-- **************************************** NO ACTION **************************************** -->			
			

		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
