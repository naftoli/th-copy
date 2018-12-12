<? 
$admin_auth = array('camp'); 

require('header.php');
require_once('calendar.php');
require_once('file_save.php');
require_once('card_printer.php');

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
	
$camp_name = "";	
if ($user_type == "camp" || $camp_id > -1) {
	$sql = "SELECT * FROM camps WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	$camp = mysql_fetch_assoc($query);
	$camp_name = $camp['camp_name'];
}

$action = gr('action', '');
$group_type_id = gri('group_type_id', -1);
$group_id = gri('group_id', -1);
$campaign_id = gri('campaign_id', -1);
$campaign_group_id = gri('campaign_group_id', -1);
$task_id = gri('task_id', -1);

if ($action == "add_tasks") {
	$tasks_ids =explode(":", gr('tasks_ids'));
	
	for ($cntr = 0; $cntr < count($tasks_ids); $cntr++) {
		$sql = "SELECT * FROM camps_tasks WHERE task_id=" . $tasks_ids[$cntr];
		$tasks_info = mysql_fetch_assoc(mq($sql));
		
		$sql = "INSERT INTO campaign_tasks SET campaign_group_id=" . $campaign_group_id . ", task_name='" . $tasks_info["task_name"] . "', points=" . $tasks_info["points"] . ", max_times=" . $tasks_info["max_times"];
		mq($sql);
	}
	$action = "";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Global Tasks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
			var admin_id = "<?=$admin_id;?>";
			var user_type = "<?=$user_type;?>";
			var camp_id = "<?=$camp_id;?>";
			var camp_name = "<?=$camp_name;?>";
			var group_type_id = "<?=$group_type_id;?>";
			var group_id = "<?=$group_id;?>";
			var campaign_id = "<?=$campaign_id;?>";
			var campaign_group_id = "<?=$campaign_group_id;?>";
			var task_id = "<?=$task_id;?>";
			var divs_array = ["camps_div", "group_types_div", "groups_div", "campaigns_div", "campaign_groups_div", "tasks_div", "global_tasks_div"];
			var divisions = "";
						
			function check_all(chckbx) {
				if (chckbx.checked)
					checked = true;
				else
					checked = false;
					
				var elements = document.getElementById("global_tasks_form").elements;
				
				for (cntr = 0; cntr < elements.length; cntr++) {
					if (elements[cntr].type == "checkbox") 
						elements[cntr].checked = checked;
				}			
			}
			
			function submit_form() {
				var task_ids = "";
				
				var elements = document.getElementById("global_tasks_form").elements;
				for (cntr = 0; cntr < elements.length; cntr++) {
					if (elements[cntr].type == "checkbox") 
						if (elements[cntr].checked) {
							if (elements[cntr].name != "ALL") {
								var split = elements[cntr].name.split("_");
								task_ids = task_ids + split[1] + ":"; 
							}
						}
				}
				
				if (task_ids.length > 0)
					task_ids = task_ids.substr(0, task_ids.length - 1);
					
				document.getElementById("tasks_ids").value = task_ids;
				document.getElementById("action").value = "add_tasks";
				
				document.global_tasks_form.submit();
			}
		</SCRIPT>		
	</HEAD>

<? if ($action == "") : ?>	
	<body onload="get_divs('1234567', 'global_tasks_form', '');">
<? else : ?>
	<body>
<? endif; ?>		
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			
			<DIV class="left_menu">
				<? include('admin_inc.php'); ?>
			</DIV>
			
				<H1>
					<?=T_('Global Tasks')?>
				</H1>
				
				<form name="global_tasks_form" id="global_tasks_form" action="admin_global_tasks.php" method="post" accept-charset="UTF-8">
					<input type="hidden" id="admin_id" name="admin_id" value="<?=$admin_id;?>">
					<input type="hidden" id="action" name="action" value="">
					<input type="hidden" id="tasks_ids" name="tasks_ids" value="">
					
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
							
					<div id="tasks_div" style="display:none;">
					</div>
					
					<div id="global_tasks_div">
					</div>
					
					<br />
					<br />
					
					<div style="clear: both;">
					</div>
				
						
		</DIV> <!-- body -->
		
		<DIV class="noprint">
			<? include('admin_footer.php'); ?>
		</DIV>
		
	</BODY>
	
</HTML>
