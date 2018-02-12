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

$camp_campaign_id = 0;
$camp_mission_id = 0;

$action = gr('action', '');

if ($action == "copy") {
	$campaign_ids = split(":", gr("campaign_ids"));
	$campaign_mission_ids = split(":", gr("campaign_mission_ids"));
	$task_ids = split(":", gr("task_ids"));

	// ********** CAMPAIGNS ********** //
	for ($cntr1 = 0; $cntr1 < count($campaign_ids); $cntr1++) {	
		$sql1 = "SELECT * FROM global_campaigns WHERE campaign_id=" . $campaign_ids[$cntr1];
		$query1 = mq($sql1);
		$row1 = mysql_fetch_assoc($query1);

		$test_query1 = mq("SELECT * FROM camp_campaigns WHERE camp_id=" . $camp_id . " AND campaign_id=" . $row1['campaign_id']);		
		$num_rows1 = mysql_num_rows($test_query1);		

		if ($num_rows1 == 0) {
			$sql2 = "INSERT INTO camp_campaigns SET campaign_id=" . $row1['campaign_id'] . ", camp_id=" . $camp_id . ", campaign_name=" . ms($row1['campaign_name']) . ", points=" . $row1['points'];
			mq($sql2);					
			$camp_campaign_id = mysql_insert_id();
		}
		else {
			$test_row1 = mysql_fetch_assoc($test_query1);
			$camp_campaign_id = $test_row1['camp_campaign_id'];
		}
		
		// ********** MISSIONS ********** //
		$sql3 = "SELECT * FROM global_missions WHERE campaign_id=" . $campaign_ids[$cntr1];
		$query3 = mq($sql3);
		while ($row3 = mysql_fetch_assoc($query3)) {

			if (in_array($row3['mission_id'], $campaign_mission_ids)) {
			
				$test_query2 = mq("SELECT * FROM camp_missions WHERE camp_campaign_id=" . $camp_campaign_id . " AND mission_name=" . ms($row3['mission_name']));
				$num_rows2 = mysql_num_rows($test_query2);		
			
				if ($num_rows2 == 0) {
					$sql4 = "INSERT INTO camp_missions SET camp_mission_id=" . $row3['mission_id'] . ", camp_campaign_id=" . $camp_campaign_id . ", mission_name=" . ms($row3['mission_name']) . ", points=" . $row3['points']  .  ", sequence=" . $row3['sequence'] ;
					mq($sql4);
					$camp_mission_id = mysql_insert_id();
				}
				else {
					$test_row2 = mysql_fetch_assoc($test_query2);
					$camp_mission_id = $test_row2['camp_mission_id'];
				}
				
				// ********** TASKS ********** //
				$sql5 = "SELECT * FROM global_tasks WHERE mission_id=" . $row3['mission_id'];
				$query5 = mq($sql5);
				while ($row5 = mysql_fetch_assoc($query5)) {
				
					if (in_array($row5['task_id'], $task_ids)) {
					
						$test_query3 = mq("SELECT * FROM camp_tasks WHERE task_id=" . $row5['task_id'] . " AND camp_mission_id=" . $camp_mission_id);		
						$num_rows3 = mysql_num_rows($test_query3);		
					
						if ($num_rows3 == 0) {
							$sql6 = "INSERT INTO camp_tasks SET task_id=" . $row5['task_id'] . ", camp_mission_id=" . $camp_mission_id . ", camp_type_id=" . $row5['camp_type_id'] . ", level_id=" . $row5['level_id'] . ", task_name=" . ms($row5['task_name']) . ", period_id=" . $row5['period_id'] . ", points=" . $row5['points'] . ", max_times=" . $row5['max_times'] . ", start_date=" . $row5['start_date'] . ", end_date=" . $row5['end_date']  . ", monday=" . $row5['monday']  . ", tuesday=" . $row5['tuesday']  . ", wednesday=" . $row5['wednesday'] . ", thursday=" . $row5['thursday'] . ", friday=" . $row5['friday'] . ", shabbos=" . $row5['shabbos'] . ", sunday=" . $row5['sunday'];
							mq($sql6);
						}						
					}
				}
				// ********** TASKS ********** //
				
			}
			
		}
		// ********** MISSIONS ********** //
		
	}
	// ********** CAMPAIGNS ********** //
	
	$action = "";
}

function  get_missions() {
	$sql = "SELECT * FROM global_campaigns ORDER BY campaign_name";
	$query = mq($sql);	
	
	while ($row = mysql_fetch_assoc($query)) {	
		$c = "c_" . $row['campaign_id'];
		
		$echo_string = "\n<table style='width:100%; border:solid 2px #B0ACA2;' id='campaign_" . $row['campaign_id'] . "' name='campaign_" . $row['campaign_id'] . "'>\n";
		
		// ***** CAMPAIGN ***** //
		$echo_string = $echo_string . "\t\t<thead><!-- CAMPAIGN -->\n";
		$echo_string = $echo_string . "\t\t\t<tr>\n";
		$echo_string = $echo_string . "\t\t\t\t\t<th align='left' style='background-color:#FDF8EA;'>\n";
			
		$echo_string = $echo_string . "\t\t\t\t\t<input type='checkbox' id='" . $c . "' name='" . $c . "' onclick='check_missions(this, " . $row['campaign_id'] . ");'>\n";
		$echo_string = $echo_string . "\t\t\t\t\t<label style='color:#996600;'>" . $row['campaign_name'] . "</label>\n";
			
		$echo_string = $echo_string . "\t\t\t\t\t</th>\n";									
		$echo_string = $echo_string . "\t\t\t</tr>\n";			
		$echo_string = $echo_string . "\t\t</thead><!-- END CAMPAIGN -->\n";
		// ***** CAMPAIGN ***** //
		
		// ***** MISSIONS ***** //
		$sql2 = "SELECT * FROM global_missions WHERE campaign_id=" . $row['campaign_id'];
		$query2 = mq($sql2);
		while ($row2 = mysql_fetch_assoc($query2)) {
			$c_m = "c_" . $row['campaign_id'] . ":m_" . $row2['mission_id'];
			
			$echo_string = $echo_string . "\t\t<tbody style='display:none; background-color:#E4EDD1;'><!-- MISSION -->\n";
			$echo_string = $echo_string . "\t\t\t<tr>\n";
			$echo_string = $echo_string . "\t\t\t\t\t<td>\n";
			$echo_string = $echo_string . "\t\t\t\t\t<input type='checkbox' id='" . $c_m . "' name='" . $c_m . "' onclick='check_tasks(this, " . $row['campaign_id'] . ",\"" . $c_m . "\");'>";
			$echo_string = $echo_string . "<label style='color:#336600;'><b>" . $row2['mission_name'] . "</b></label>\n";
			$echo_string = $echo_string . "\t\t\t\t\t</td>\n";
			$echo_string = $echo_string . "\t\t\t</tr>\n";
			$echo_string = $echo_string . "\t\t</tbody><!-- END MISSION -->\n";
			
			// ***** TASKS ***** //
			$sql3 = "SELECT * FROM global_tasks WHERE mission_id=" . $row2['mission_id'];
			$query3 = mq($sql3);
			$task_number = 0;
			$echo_string = $echo_string . "\t\t<tbody style='display:none;'><!-- TASKS -->\n";
			$number_of_tasks_per_row = 5;
			while ($row3 = mysql_fetch_assoc($query3)) {
				$c_m_t = "c_" . $row['campaign_id'] . ":m_" . $row2['mission_id'] . ":t_" . $row3['task_id'];
				
				$remainder = $task_number % $number_of_tasks_per_row;
			
				if ($remainder == 0) 
					$echo_string = $echo_string . "\t\t\t<tr>\n";
				
				$echo_string = $echo_string . "\t\t\t\t\t<td>\n";
				$echo_string = $echo_string . "\t\t\t\t\t<input type='checkbox' id='" . $c_m_t . "' name='" . $c_m_t . "'><label>" . $row3['task_name'] . "</label>\n";
				$echo_string = $echo_string . "\t\t\t\t\t</td>\n";

				
				$echo_string = $echo_string . "\t\t\t\t\t<input type='hidden' name='REMAINDER' value='" . $remainder . "'>\n";
				$echo_string = $echo_string . "\t\t\t\t\t<input type='hidden' name='TASK NUMBER->' value='" . $task_number . "'>\n";
								
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
		<TITLE><?=T_('Global Tasks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
			var admin_id = "<?=$admin_id;?>";
			var user_type = "<?=$user_type;?>";
			var camp_id = "<?=$camp_id;?>";
			var camp_name = "<?=$camp_name;?>";
						
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
			
			function check_all_group_divisions(chckbx) {
				var div_4 = document.getElementById("div_4");
				
				var inputs = div_4.getElementsByTagName("input");
				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					if (type == "checkbox") {
						inputs[cntr].checked = chckbx.checked;
					}	
				}
			}
			
			function copy_campaigns() {
				var campaign_ids = "";
				var campaign_mission_ids = "";
				var task_ids = "";
				
				var campaigns_div = document.getElementById("campaigns_div");
				var inputs = campaigns_div.getElementsByTagName("input");

				for (cntr = 0; cntr < inputs.length; cntr++) {
					var type = inputs[cntr].getAttribute("type");
					
					if (type == "checkbox" && inputs[cntr].checked == true) {
						var checkbox_name = inputs[cntr].getAttribute("name");
						var info_array = checkbox_name.split(":");
												
						if (info_array.length == 1) {
							var task_array = info_array[0].split("_");	
							var campaign_id = task_array[1];
							campaign_ids = campaign_ids + campaign_id + ":";
						}
						else if (info_array.length == 2) {	
							var task_array = info_array[1].split("_");	
							var campaign_mission_id = task_array[1];
							campaign_mission_ids = campaign_mission_ids + campaign_mission_id + ":";							
						}
						else if (info_array.length == 3) {	
							var task_array = info_array[2].split("_");							
							var task_id = task_array[1];
							task_ids = task_ids + task_id + ":";							
						}
						
					}
					
				}
				
				if (task_ids.length > 0) {
					campaign_ids = campaign_ids.substr(0, campaign_ids.length - 1);
					campaign_mission_ids = campaign_mission_ids.substr(0, campaign_mission_ids.length - 1);
					task_ids = task_ids.substr(0, task_ids.length - 1);
					
					document.getElementById("campaign_ids").value = campaign_ids;
					document.getElementById("campaign_mission_ids").value = campaign_mission_ids;
					document.getElementById("task_ids").value = task_ids;
					
					document.getElementById("action").value = "copy";
					document.global_tasks_form.submit();
				}				
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
				
				<form name="global_tasks_form" id="global_tasks_form" action="admin_global_campaigns.php" method="post" accept-charset="UTF-8">
					<input type="hidden" id="admin_id" name="admin_id" value="<?=$admin_id;?>">
					<input type="hidden" id="camp_id" name="camp_id" value="<?=$camp_id;?>">
					<input type="hidden" id="action" name="action" value="">					
					<input type="hidden" id="campaign_ids" name="campaign_ids" value="">
					<input type="hidden" id="campaign_mission_ids" name="campaign_mission_ids" value="">
					<input type="hidden" id="task_ids" name="task_ids" value="">
					
					<? if ($camp_id == -1) : ?>
						<p>
							<label><?=T_('Select a Camp');?>: </label>
							<select name="camp_id" id="camp_id">
								<? $query = mq("SELECT camp_id, camp_name FROM camps"); ?>
								<? while ($row = mysql_fetch_assoc($query)) : ?>
								<option value="<?=$row['camp_id'];?>"><?=$row['camp_name'];?></option>
								<? endwhile; ?>
							</select>
						</p>
					<? else : ?>
						<h2><?=T_('Camp');?>: <label style="color:blue;"><?=$camp_name;?></label></h2>
					<? endif; ?>
					
					<br />
					<br />
					
					<input type="button" value="COPY" onclick="copy_campaigns();">
					<br />
					<br />
					
					<div id="campaigns_div">
						<? get_missions(); ?>
					</div> <!-- ALL CAMPAIGNS DIV -->
									
				</form>
				
		</DIV> <!-- body -->
		
		<DIV class="noprint">
			<? include('admin_footer.php'); ?>
		</DIV>
		
	</BODY>
	
</HTML>
