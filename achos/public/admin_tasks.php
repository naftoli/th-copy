<?php
$admin_auth = array('camp'); 

require('header.php');
require_once('calendar.php');

$days_of_the_week = array(T_('Sunday'), T_('Monday'), T_('Tuesday'), T_('Wednesday'), T_('Thursday'), T_('Friday'), T_('Shabbos'));

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

$campaign_id = gri('campaign_id', -1);
$mission_id = gri('mission_id', -1);
$task_id = gri('task_id', -1);

$action = gr('action', '');
if ($action != '') {

	switch($action) {
		case 'add':
			$start_date = cal_to_jd(CAL_GREGORIAN, gr('start_month'), gr('start_day'), gr('start_year'));
			$end_date = cal_to_jd(CAL_GREGORIAN, gr('end_month'), gr('end_day'), gr('end_year'));			

			$period = mysql_fetch_assoc(mq("SELECT period_name FROM periods WHERE period_id=" . gri('period_id')));
			$period_name = strtolower($period['period_name']);
			
			if ($period_name != "weekly") {
				$sql = "INSERT INTO global_tasks SET mission_id=" . $mission_id . ", task_name=" . ms(gr('task_name')) . ", camp_type_id=" . gri('camp_type_id') . ", level_id=" . gri('level_id') . ", period_id=" . gri('period_id') . ", points=" . gri('points') . ", max_times=" . gri('max_times') . ", start_date=" . $start_date . ", end_date=" . $end_date;
				mq($sql);
			}
			else {
				$sql = "INSERT INTO global_tasks SET mission_id=" . $mission_id . ", task_name=" . ms(gr('task_name')) . ", camp_type_id=" . gri('camp_type_id') . ", level_id=" . gri('level_id') . ", period_id=" . gri('period_id') . ", points=" . gri('points') . ", max_times=" . gri('max_times') . ", start_date=" . $start_date . ", end_date=" . $end_date;				
				
				$week_days = strtolower(serialize($_POST['week_days']));
				for ($cntr = 0; $cntr < count($days_of_the_week); $cntr++) {
					$day = strtolower($days_of_the_week[$cntr]);
					$strpos = strpos($week_days, $day);
					
					if ($strpos !== false) 
						$sql = $sql . ", " . $day . "=1 ";
				}
				
				mq($sql);
			}
						
			$action = "";
		break;
		
		case 'save':
			$period = mysql_fetch_assoc(mq("SELECT period_name FROM periods WHERE period_id=" . gri('period_id')));
			$period_name = strtolower($period['period_name']);
		
			$start_date = cal_to_jd(CAL_GREGORIAN, gr('start_month'), gr('start_day'), gr('start_year'));
			$end_date = cal_to_jd(CAL_GREGORIAN, gr('end_month'), gr('end_day'), gr('end_year'));					

			if ($period_name != "weekly") {
				$sql = "UPDATE global_tasks SET task_name=" . ms(gr('task_name')) . ", camp_type_id=" . gri('camp_type_id') . ", level_id=" . gri('level_id') . ", period_id=" . gri('period_id') . ", points=" . gri('points') . ", max_times=" . gri('max_times') . ", start_date=" . $start_date . ", end_date=" . $end_date;// . " WHERE task_id=" . $task_id;
				for ($cntr = 0; $cntr < count($days_of_the_week); $cntr++) {
					$day = strtolower($days_of_the_week[$cntr]);
					$sql = $sql . ", " . $day . "=0 ";
				}
				$sql =  $sql . " WHERE task_id=" . $task_id;				
				mq($sql);
			}
			else {
				$sql = "UPDATE global_tasks SET task_name=" . ms(gr('task_name')) . ", camp_type_id=" . gri('camp_type_id') . ", level_id=" . gri('level_id') . ", period_id=" . gri('period_id') . ", points=" . gri('points') . ", max_times=" . gri('max_times') . ", start_date=" . $start_date . ", end_date=" . $end_date;
				$week_days = strtolower(serialize($_POST['week_days']));
				for ($cntr = 0; $cntr < count($days_of_the_week); $cntr++) {
					$day = strtolower($days_of_the_week[$cntr]);
					$strpos = strpos($week_days, $day);
					
					if ($strpos !== false) 
						$sql = $sql . ", " . $day . "=1 ";
					else 
						$sql = $sql . ", " . $day . "=0 ";
				}
				$sql = $sql . " WHERE task_id=" . $task_id;				
				mq($sql);
			}
			mq($sql);
			$action = "";				
		break;
		
		case 'delete':
			$sql = "DELETE FROM global_tasks WHERE task_id=" . gri('task_id');
			mq($sql);
			$action = "";		
		break;				
	}

}

$day_values = array();
$disabled = "disabled";
if ($action == "edit") { 
	$row = mysql_fetch_assoc(mq("SELECT cmt.*, p.period_name FROM global_tasks AS cmt LEFT JOIN periods AS p USING (period_id) WHERE task_id=" . gri('task_id') )); 
	$task_name = $row['task_name'];
	$points = $row['points'];
	$period_id = $row['period_id'];
	$period_name = strtolower($row['period_name']);
	$max_times = $row['max_times'];	
	$start_date = jdtogregorian($row['start_date']);
	$mdy = explode("/", $start_date);
	$start_month = $mdy[0];
	$start_day = $mdy[1];
	$start_year = $mdy[2];
	$end_date = jdtogregorian($row['end_date']); 
	$mdy = explode("/", $end_date);
	$end_month = $mdy[0];
	$end_day = $mdy[1];
	$end_year = $mdy[2];
	
	if ($period_name == "weekly")
		$disabled = "";
		
	for ($cntr = 0; $cntr < count($days_of_the_week); $cntr++) {
		$day = strtolower($days_of_the_week[$cntr]);
		array_push($day_values, $row[$day]);
	}	
}
else {
	$task_name = "";
	$points = "";
	$period_id = "";
	$period_name = "";
	$max_times = "";
	
	$start_month = 0;
	$start_day = 0;
	$start_year = 0;
	
	$end_month = 0;
	$end_day = 0;
	$end_year = 0;
	
	$day_values = array(0,0,0,0,0,0,0);	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Tasks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="ajax_get_campaigns.js"></SCRIPT>
		<script type="text/javascript">	
			var start_month = "<?=$start_month;?>";
			var start_day = "<?=$start_day;?>";
			var start_year = "<?=$start_year;?>";
			
			var end_month = "<?=$end_month;?>";
			var end_day = "<?=$end_day;?>";
			var end_year = "<?=$end_year;?>";
			
			var campaign_id = "<?=$campaign_id;?>";	
			var mission_id = "<?=$mission_id;?>";			
			var divs_array = ["div_1", "div_2", "div_3"];
			
			var dates_set = false;
			
			function checkbox_ability(slct) {
				document.getElementById("max_times").value = "";
				
				if (slct.options[slct.selectedIndex].text == "Weekly") 
					var disabled = false;
				else 
					var disabled = true;
				
				for(cntr = 0; cntr < document.tasks_form.elements.length; cntr++) {
					if (document.tasks_form.elements[cntr].type == "checkbox")
						document.tasks_form.elements[cntr].disabled = disabled;
				}

				if (disabled == false) {
					document.getElementById("max_times").value = "";
					document.getElementById("max_times").disabled = true;
				}
				else {
					document.getElementById("max_times").disabled = false;
				}
			}
			
			function get_date_divs(div1, div2, form_name) {
				var url = "ajax_get_dates.php";
				
				if (div1 != "" && div2 == "") {
					url = url + "?day=" + document.getElementById("start_day").value + "&month=" + document.getElementById("start_month").value + "&year=" + document.getElementById("start_year").value + "&form_name=" + form_name;
				}
				else if (div1 == "" && div2 != "") {
					url = url + "?day=" + document.getElementById("end_day").value + "&month=" + document.getElementById("end_month").value + "&year=" + document.getElementById("end_year").value + "&form_name=" + form_name;
				}
				else {
					url = url  + "?form_name=" + form_name;
				}
				
				var http = getHTTPObjct();
				http.open("GET", url, true);
				
				http.onreadystatechange = function() {
				
					if (http.readyState == 4 && http.status == 200) {	
						divs = http.responseText.split("[SPLIT]");
						
						if (div1 != "") 
							document.getElementById("start_date").innerHTML = divs[0];
						if (div2 != "") 
							document.getElementById("end_date").innerHTML = divs[1];
							
						if (start_day > 0 && dates_set == false) {
							if (start_day > document.getElementById("start_day").length) {							
								for (cntr = 0; cntr < start_day  - document.getElementById("start_day").length; cntr++) {
									document.getElementById("start_day").options[document.getElementById("start_day").options.length] = new Option(document.getElementById("end_day").length + cntr + 1,  document.getElementById("end_day").length + cntr, false, false);
								}    
							}
						
							document.getElementById("start_day").selectedIndex = (start_day - 1);
							document.getElementById("start_month").selectedIndex = (start_month - 1);
							
							if (end_day > document.getElementById("end_day").length) {							
								for (cntr = 0; cntr < end_day  - document.getElementById("end_day").length; cntr++) {
									document.getElementById("end_day").options[document.getElementById("end_day").options.length] = new Option(document.getElementById("end_day").length + cntr + 1,  document.getElementById("end_day").length + cntr, false, false);
								}    
							}								
							
							document.getElementById("end_day").selectedIndex = (end_day - 1);
							document.getElementById("end_month").selectedIndex = (end_month - 1);							
							dates_set = true;
						}
							
					}
			
				}
				http.send(null);				
			}
			
			function calculate_max_times() {
				var period_select = document.getElementById("period_id");
				if (period_select.options[period_select.selectedIndex].text == "Weekly") 
					var disabled = false;
				else 
					var disabled = true;
			
				if (disabled == false) {
					var max_times = 0;
					var checked = "";
					var week_days = document.tasks_form["week_days[]"];
					for (cntr = 0; cntr < week_days.length; cntr++) {
						if (week_days[cntr].checked == true)
							if (week_days[cntr].value != "Shabbos")
								checked = checked + week_days[cntr].value.substr(0, 3);
							else
								checked = checked + "Sat";
					}
					
					var one_day = 1000 * 60 * 60 * 24;

					var start_date = new Date(document.getElementById("start_month").value + "/" + document.getElementById("start_day").value + "/" + document.getElementById("start_year").value);
					var end_date = new Date(document.getElementById("end_month").value + "/" + document.getElementById("end_day").value + "/" + document.getElementById("end_year").value);
					
					var difference = Math.ceil((start_date.getTime() - end_date.getTime()) / (one_day)) * -1;
					
					for (cntr = 1; cntr < difference; cntr++) {
						var new_date = new Date(start_date.getTime() + (one_day * cntr));
						var day_of_the_week = new_date.toString().substr(0, 3);
						var strpos = checked.indexOf(day_of_the_week);
						if (strpos > -1) {
							max_times++;
						}
					}
					
					document.getElementById("max_times").value = max_times;
				}
			}
		</script>		

	</HEAD>
	
<? if ($action == "") : ?>	
	<body onload="get_campaigns('123', 'tasks_form');">
<? else : ?>
	<body onload="get_date_divs('start_date', 'end_date', 'tasks_form');">
<? endif; ?>		

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

<? if ($action == "") : ?>
			<form name="tasks_form" id="tasks_form" action="admin_tasks.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">
				<!--<input type="hidden" name="mission_id" id="mission_id" value="<?//=$mission_id;?>">-->
				<input type="hidden" name="task_id" id="task_id" value="<?=$task_id;?>">
				
				<a href="#" onclick="document.getElementById('action').value='add_new'; document.forms['tasks_form'].submit();"><?=T_('Add Task')?></a>
			
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
			</form>
<? endif; ?>

<? if ($action == "add_new" || $action == "edit") : ?>
			<form name="tasks_form" id="tasks_form" action="admin_tasks.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">
				<input type="hidden" name="campaign_id" id="campaign_id" value="<?=$campaign_id;?>">
				<input type="hidden" name="mission_id" id="mission_id" value="<?=$mission_id;?>">				
				<? if ($action == "edit") : ?>
				<input type="hidden" name="task_id" id="task_id" value="<?=$task_id;?>">
				<? endif; ?>
				
				<? 
					if ($action == "edit") { 
						$row = mysql_fetch_assoc(mq("SELECT * FROM global_tasks WHERE task_id=" . gri('task_id') )); 
						$task_name = $row['task_name'];
						$points = $row['points'];
						$camp_type_id = $row['camp_type_id'];
						$level_id = $row['level_id'];
						$period_id = $row['period_id'];
						$max_times = $row['max_times'];	
						$start_date = jdtogregorian($row['start_date']);
						$mdy = explode("/", $start_date);
						$start_month = $mdy[0];
						$start_day = $mdy[1];
						$start_year = $mdy[2];
						$end_date = jdtogregorian($row['end_date']); 
						$mdy = explode("/", $end_date);
						$end_month = $mdy[0];
						$end_day = $mdy[1];
						$end_year = $mdy[2];
					}
					else {
						$task_name = "";
						$points = "";
						$period_id = "";
						$camp_type_id = "";
						$max_times = "";
					}
					
				?>
				
				<? $campaign = mysql_fetch_assoc(mq("SELECT m.mission_name, c.campaign_name FROM missions AS m JOIN campaigns AS c USING (campaign_id) WHERE m.mission_id=" . $mission_id)); ?>
				<h3><?=T_('Campaign');?>: <label style="color:blue;"><?=$campaign['campaign_name'];?></label></h3>
				<h3><?=T_('Mission');?>: <label style="color:blue;"><?=$campaign['mission_name'];?></label></h3>
				
				<table>
					<tr>
						<td><?=T_('Task Name');?>:</td>
						<td><input type="text" size="100" maxlength="100" name="task_name" id="task_name" value="<?=$task_name;?>"></td>
					</tr>
										
					<tr>
						<td><?=T_('Camp Type');?>:</td>
						<td>
							<select id="camp_type_id" name="camp_type_id">
								<option value="0"><?=T_('All Camps');?></option>
								<? $types_query = mq("SELECT * FROM camp_types"); ?>
								<? while ($camp_type = mysql_fetch_assoc($types_query)) : ?>
									<? if ($camp_type_id == $camp_type['camp_type_id']) : ?>
									<option selected value="<?=$camp_type['camp_type_id'];?>"><?=$camp_type['camp_type'];?></option>
									<? else : ?>
									<option value="<?=$camp_type['camp_type_id'];?>"><?=$camp_type['camp_type'];?></option>
									<? endif; ?>
								<? endwhile; ?>
							</select>
						</td>
					</tr>
					
					<tr>
						<td><?=T_('Level');?>:</td>
						<td>
							<select id="level_id" name="level_id">
								<? $levels_query = mq("SELECT * FROM levels"); ?>
								<? while ($level = mysql_fetch_assoc($levels_query)) : ?>
									<? if ($level_id == $level['level_id']) : ?>
									<option selected value="<?=$level['level_id'];?>"><?=$level['level_name'];?></option>
									<? else : ?>
									<option value="<?=$level['level_id'];?>"><?=$level['level_name'];?></option>
									<? endif; ?>
								<? endwhile; ?>
							</select>
						</td>
					</tr>					
					
					<tr>
						<td><?=T_('Period');?>:</td>
						<td>
							<select id="period_id" name="period_id" onchange="checkbox_ability(this);">
								<? $periods_sql = "SELECT * FROM periods"; ?>
								<? $periods_query = mq($periods_sql); ?>
								<? while ($period = mysql_fetch_assoc($periods_query)) : ?>
									<? if ($period_id == $period['period_id']) : ?>
									<option selected value="<?=$period['period_id'];?>"><?=$period['period_name'];?></option>
									<? else : ?>
									<option value="<?=$period['period_id'];?>"><?=$period['period_name'];?></option>
									<? endif; ?>
								<? endwhile; ?>
							</select>
						</td>
					</tr>
					
					<tr>				
						<td><?=T_('Day of the Week');?>:</td>
						<td>
							<div id="days_of_the_week">
								<? for ($cntr = 0; $cntr < count($days_of_the_week); $cntr++) : ?>
								<label>
									<? if ($day_values[$cntr] == 1) : ?>
									<input <?=$disabled;?> checked type="checkbox" id="week_days[]" name="week_days[]" value="<?=$days_of_the_week[$cntr];?>" onclick="calculate_max_times();">
									<? else : ?>
									<input <?=$disabled;?> type="checkbox" id="week_days[]" name="week_days[]" value="<?=$days_of_the_week[$cntr];?>" onclick="calculate_max_times();">
									<? endif; ?>
									<?=$days_of_the_week[$cntr];?>
								</label>
								<? endfor; ?>
							</div>
						</td>
					<tr>
					
					<tr>
						<td><?=T_('Points');?>:</td>
						<td><input type="text" size="4" maxlength="3" name="points" id="points" onkeypress="return number_validation(event);" value="<?=$points;?>"></td>
					</tr>
					
					<tr>
						<td><?=T_('Max Times');?>:</td>
						<td><input type="text" size="3" maxlength="2" name="max_times" id="max_times" onkeypress="return number_validation(event);" value="<?=$max_times;?>"></td>
					</tr>	


					<tr>
						<td><?=T_('Start Date')?>:</td>
						<td id="start_date"></td>
					</tr>
					
					<tr>
						<td><?=T_('End Date')?>:</td>
						<td id="end_date"></td>
					</tr>
										
				</table>
				
				<br />
				
				<? if ($action == "add_new") : ?>
					<input type="submit" value="<?=T_('ADD');?>" onclick="document.getElementById('max_times').disabled=''; document.getElementById('action').value='add';">
					<input type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value='';">
				<? else : ?>
					<input type="submit" value="<?=T_('SAVE');?>" onclick="document.getElementById('max_times').disabled=''; document.getElementById('action').value='save';">
					<input type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value='';">		
				<? endif; ?>
				
				<br />
								
			</form>
<? endif; ?>

		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
