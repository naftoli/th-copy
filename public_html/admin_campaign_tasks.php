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

$days_of_the_week1 = array(T_('sunday'), T_('monday'), T_('tuesday'), T_('wednesday'), T_('thursday'), T_('friday'), T_('saturday'));

$days_of_the_week = array(T_('Sunday'), T_('Monday'), T_('Tuesday'), T_('Wednesday'), T_('Thursday'), T_('Friday'), T_('Shabbos'));
$period_id = gri('period_id', -1);
$period_name = "";
if ($period_id > -1) {
	$sql = "SELECT period_name FROM periods WHERE period_id=" . $period_id;
	$query = mq($sql);
	$row = mysql_fetch_assoc($query);
	$period_name = $row['period_name'];
}

$action = gr('action', '');
if ($action != '') {

	switch($action) {
		case 'add':
			echo "Tuesday:" . gr('Tuesday') . "<br />";
			echo "Thursday:" . gr('Thursday') . "<br />";
			
			$start_date = jewishtojd(gr('start_date_month'), gr('start_date_day'), gr('start_date_year'));
			
			if (gr('end_date_month') != -1 && gr('end_date_day') != -1 && gr('end_date_year') != -1) 
				$end_date = jewishtojd($end_month, $end_day, $end_year);
			else
				$end_date = "NULL";
						
			$sql = "INSERT INTO campaign_tasks (campaign_group_id, period_id, task_name, points, max_times, start_date, end_date) VALUES (" . $campaign_group_id . ", " . gr('period_id') . ", '" . gr('task_name') . "', " . gri('points') . ", " . gri('max_times') . ", " . $start_date . ", " . $end_date . ")";			
			mq($sql);
			$action = "";
		break;
		
		case 'save':
			if ($period_name == "Weekly") {
				$sql = "UPDATE campaign_tasks SET period_id=" . gr('period_id') . ", task_name='" . gr('task_name') . "', points=" . gri('points') . ", max_times=" . gr('max_times') . ", "; 
				$found = false;
				for ($cntr = 0; $cntr < count($days_of_the_week); $cntr++) {			
					 if (gr($days_of_the_week[$cntr]) == 1) {
						$sql = $sql . strtolower($days_of_the_week[$cntr]) . "=" . gr($days_of_the_week[$cntr]) . ", ";
						$found = true;
					}
				}
				if ($found == true) 
					$sql = substr($sql, 0, strlen($sql) - 2);				
				$sql = $sql . " WHERE task_id=" . gri('task_id');
			}
			else {
				$sql = "UPDATE campaign_tasks SET period_id=" . gr('period_id') . ", task_name='" . gr('task_name') . "', points=" . gri('points') . ", max_times=" . gr('max_times') . " WHERE task_id=" . gri('task_id');
			}
			
			mq($sql);
			$action = "";				
		break;
		
		case 'delete':
			$sql = "DELETE FROM campaign_tasks WHERE task_id=" . $task_id;
			mq($sql);
			$action = "";			
		break;		
	}
	
}

$delete_message = T_('Are you sure that you want to delete this campaign task?');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Campaign Tasks'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
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
			var divs_array = ["camps_div", "group_types_div", "groups_div", "campaigns_div", "campaign_groups_div", "tasks_div"];
			var divisions = "";

			var days_of_the_week = ["<?=$days_of_the_week[0];?>", "<?=$days_of_the_week[1];?>", "<?=$days_of_the_week[2];?>", "<?=$days_of_the_week[3];?>", "<?=$days_of_the_week[4];?>", "<?=$days_of_the_week[5];?>", "<?=$days_of_the_week[6];?>"];
			
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

			function check_for_weekly(slct) {
				if (slct.options[slct.selectedIndex].text == "Weekly") {
					var innerHTML = "\n<td><?=T_('Day of the Week');?>:</td>\n<td>";
					
					for (cntr = 0; cntr < days_of_the_week.length; cntr++) {
						innerHTML = innerHTML + "\n<label><input type='checkbox' name='" + days_of_the_week[cntr] + "' value='1'>" + days_of_the_week[cntr] + "</label>";
					}
					innerHTML = innerHTML + "\n</td>";
					
					document.getElementById("period_div").innerHTML = innerHTML;
				}
				else {
					document.getElementById("period_div").innerHTML = "";
				}
			}
		</SCRIPT>
	</HEAD>
	
<? if ($action == "") : ?>	
	<body onload="get_divs('123456', 'tasks_form', '');">
<? else : ?>
	<body>
<? endif; ?>		
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">		
			<DIV class="left_menu">
				<?include('admin_inc.php');?>
			</DIV>
			
			<H1>
				<?=T_('Tasks')?>
			</H1>
				
<!-- **************************************** EDIT **************************************** -->	
<? if ($action == "edit") : ?>
	
	<form action="admin_campaign_tasks.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="save">
		<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
		<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
		<input type="hidden" name="group_type_id" id="group_type_id" value="<?=$group_type_id;?>">
		<input type="hidden" name="group_id" id="group_id" value="<?=$group_id;?>">
		<input type="hidden" name="campaign_id" id="campaign_id" value="<?=$campaign_id;?>">
		<input type="hidden" name="campaign_group_id" id="campaign_group_id" value="<?=$campaign_group_id;?>">
		<input type="hidden" name="task_id" id="task_id" value="<?=$task_id;?>">

		<? $info = mysql_fetch_assoc(mq("SELECT cgt.group_type_name, cg.group_name, c.campaign_name, cg2.campaign_group_name FROM camp_group_types AS cgt JOIN camp_groups AS cg ON cg.group_id=" . $group_id . " JOIN campaigns AS c ON c.campaign_id=" . $campaign_id . " JOIN campaign_groups AS cg2 ON c.campaign_id=cg2.campaign_id WHERE cgt.camp_id=" . $camp_id)); ?>
		
		<h3><?=T_('Camp');?>: <label style="color:blue;"><?=$camp_name;?></h3>
		
		<h3><?=T_('Group Type');?>: <label style="color:blue;"><?=$info['group_type_name'];?></label></h3>
		
		<h3><?=T_('Group');?>: <label style="color:blue;"><?=$info['group_name'];?></label></h3>
		
		<h3><?=T_('Campaign');?>: <label style="color:blue;"><?=$info['campaign_name'];?></label></h3>
		
		<h3><?=T_('Mission');?>: <label style="color:blue;"><?=$info['campaign_group_name'];?></label></h3>
		
		<? $task = mysql_fetch_assoc(mq("SELECT * FROM campaign_tasks WHERE task_id=" . $task_id)); ?>
		<? $periods_query = mq("SELECT * FROM periods"); ?>
		<? $weekly_flag = false; ?>
		
		<table>
			<tr>
				<td style="width:100px;"><?=T_('Repeat');?>: </td>
				<td>
					<select id="period_id" name="period_id" onchange="check_for_weekly(this);">
						<? while ($row = mysql_fetch_assoc($periods_query)) : ?>
							<? if ($task['period_id'] == $row['period_id']) : ?>
								<? if ($row['period_name'] == "Weekly") $weekly_flag = true; ?>
								<option value="<?=$row['period_id'];?>" selected><?=$row['period_name'];?></option>
							<? else : ?>
								<option value="<?=$row['period_id'];?>"><?=$row['period_name'];?></option>
							<? endif; ?>
						<? endwhile; ?>
					</select>
				</td>
			</tr>
			
			<tr id="period_div">				
				<? if ($weekly_flag == true) : ?>
					<tr>
						<td><?=T_('Day of the Week');?>:</td>
						<td>
						<? for ($cntr = 0; $cntr < count($days_of_the_week1); $cntr++) : ?>

							<? if ($task[$days_of_the_week1[$cntr]] == 1) : ?>
							<label><input type='checkbox' checked name='<?=$days_of_the_week[$cntr];?> value='1'><?=$days_of_the_week[$cntr];?></label>
							<? else : ?>
							<label><input type='checkbox' name='<?=$days_of_the_week[$cntr];?> value='1'><?=$days_of_the_week[$cntr];?></label>
							<? endif; ?>
						<? endfor; ?>
						</td>
					</tr>
				<? endif; ?>
			<tr>
				
			<tr>
				<td><?=T_('Start Date')?>:</td>
				<td>
					<SELECT name="start_date_day" id="start_date_day">
						<? selectDay($start_date_day); ?>
					</SELECT>
					<SELECT name="start_date_month" id="start_date_month">
						<? selectMonth($start_date_month); ?>
					</SELECT>
					<SELECT name="start_date_year" id="start_date_year">
						<? selectYear($start_date_year); ?>
					</SELECT>
				</td>
			</tr>
				
			<tr>
				<td><?=T_('End Date')?>:</td>
				<td>
					<SELECT name="end_date_day" id="end_date_day">
						<OPTION value="-1"></OPTION>
						<? selectDay($end_date_day); ?>
					</SELECT>
					<SELECT name="end_date_month" id="end_date_month">
						<OPTION value="-1"></OPTION>
						<? selectMonth($end_date_month); ?>
					</SELECT>
					<SELECT name="end_date_year" id="end_date_year">
						<OPTION value="-1"></OPTION>
						<? selectYear($end_date_year); ?>
					</SELECT>
				</td>
			</tr>
			
			<tr>
				<td><?=T_('Name');?></td>
				<td><input type="text" name="task_name" id="task_name" size="100" maxlength="255" value="<?=$task['task_name'];?>"></td>
			</tr>
			<tr>
				<td><?=T_('Points');?></td>
				<td><input type="text" name="points" id="points"  maxlength="2" size="4" value="<?=$task['points'];?>" onkeypress="return number_validation(event);"></td>
			</tr>
			<tr>
				<td><?=T_('Max Times');?></td>
				<td><input type="text" name="max_times" id="max_times"  maxlength="2" size="4" value="<?=$task['max_times'];?>" onkeypress="return number_validation(event);"></td>
			</tr>			
			<tr>
				<td><input type="submit" value="<?=T_('SAVE');?>"></td>
				<td><input type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value=''; document.getElementById('task_id').value='';"></td>
			</tr>						
			
		</table>		
		
	</form>
	
<? endif; ?>
<!-- **************************************** EDIT **************************************** -->			

				
<!-- **************************************** ADD NEW **************************************** -->		
<? if ($action == "add_new") : ?>	

	<form action="admin_campaign_tasks.php" method="post" accept-charset="UTF-8" onsubmit="return verify_data();">
		<input type="hidden" name="action" id="action" value="add">
		<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
		<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
		<input type="hidden" name="group_type_id" id="group_type_id" value="<?=$group_type_id;?>">
		<input type="hidden" name="group_id" id="group_id" value="<?=$group_id;?>">
		<input type="hidden" name="campaign_id" id="campaign_id" value="<?=$campaign_id;?>">
		<input type="hidden" name="campaign_group_id" id="campaign_group_id" value="<?=$campaign_group_id;?>">
		
		<? $info = mysql_fetch_assoc(mq("SELECT cgt.group_type_name, cg.group_name, c.campaign_name, cg2.campaign_group_name FROM camp_group_types AS cgt JOIN camp_groups AS cg ON cg.group_id=" . $group_id . " JOIN campaigns AS c ON c.campaign_id=" . $campaign_id . " JOIN campaign_groups AS cg2 ON c.campaign_id=cg2.campaign_id WHERE cgt.camp_id=" . $camp_id)); ?>
		
		<h3><?=T_('Camp');?>: <label style="color:blue;"><?=$camp_name;?></label></h3>
		
		<h3><?=T_('Group Type');?>: <label style="color:blue;"><?=$info['group_type_name'];?></label></h3>
		
		<h3><?=T_('Group');?>: <label style="color:blue;"><?=$info['group_name'];?></label></h3>
		
		<h3><?=T_('Campaign');?>: <label style="color:blue;"><?=$info['campaign_name'];?></label></h3>
		
		<h3><?=T_('Campaign Group');?>: <label style="color:blue;"><?=$info['campaign_group_name'];?></label></h3>
		
		<? $periods_query = mq("SELECT * FROM periods"); ?>
		
		<? if ($campaign_id > -1) : ?>
			<table>
				<tr>
					<td style="width:100px;"><?=T_('Repeat');?>: </td>
					<td>
						<select id="period_id" name="period_id" onchange="check_for_weekly(this);">
							<? while ($row = mysql_fetch_assoc($periods_query)) : ?>
								<option value="<?=$row['period_id'];?>"><?=$row['period_name'];?></option>
							<? endwhile; ?>
						</select>
					</td>
				</tr>
								
				<tr id="period_div">
				<tr>
				
				<tr>
					<td><?=T_('Start Date')?>:</td>
					<td>
						<SELECT name="start_date_day" id="start_date_day">
							<? selectDay($start_date_day); ?>
						</SELECT>
						<SELECT name="start_date_month" id="start_date_month">
							<? selectMonth($start_date_month); ?>
						</SELECT>
						<SELECT name="start_date_year" id="start_date_year">
							<? selectYear($start_date_year); ?>
						</SELECT>
					</td>
				</tr>
				
				<tr>
					<td><?=T_('End Date')?>:</td>
					<td>
						<SELECT name="end_date_day" id="end_date_day">
							<OPTION value="-1"></OPTION>
							<? selectDay($end_date_day); ?>
						</SELECT>
						<SELECT name="end_date_month" id="end_date_month">
							<OPTION value="-1"></OPTION>
							<? selectMonth($end_date_month); ?>
						</SELECT>
						<SELECT name="end_date_year" id="end_date_year">
							<OPTION value="-1"></OPTION>
							<? selectYear($end_date_year); ?>
						</SELECT>
					</td>
				</tr>
				
				<tr>
					<td><?=T_('Task Name');?>: </td>
					<td align="left"><input type="text" name="task_name" id="task_name" size="100" maxlength="255"></td>
				</tr>
				<tr>
					<td><?=T_('Points');?>: </td>
					<td><input type="text" name="points" id="points" maxlength="2" size="4" onkeypress="return number_validation(event);"></td>
				</tr>
				<tr>
					<td><?=T_('Max Times');?>: </td>
					<td><input type="text" name="max_times" id="max_times" maxlength="2" size="4" onkeypress="return number_validation(event);"></td>
				</tr>
				<tr>
					<td><input type="submit" value="<?=T_('ADD');?>" onclick="document.getElementById('action').value='add';"></td>
					<td><input type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value='';"></td>
				</tr>				
			</table>
		<? endif; ?> <!-- if ($campaign_id > -1) : -->
		
	</form>
<? endif; ?>
<!-- **************************************** ADD NEW **************************************** -->			
			
<!-- **************************************** NO ACTION **************************************** -->			
<? if ($action == "") : ?>

	<form name="tasks_form" id="tasks_form" action="admin_campaign_tasks.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="">
		<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
		<input type="hidden" name="task_id" id="task_id" value="">

		<a href="#" onclick="var add = verify_campaign_group_id(); if (add == true) { document.getElementById('action').value='add_new'; document.forms['tasks_form'].submit(); } else { alert('<?=T_('No Mission Found');?>'); } "><?=T_('Add New Task')?></a>
			
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
		
	</form>
	
<? endif; ?> <!-- if ($action == "") : -->
<!-- **************************************** NO ACTION **************************************** -->			
			

		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
