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

$action = gr('action', '');
if ($action != '') {

	switch($action) {
		case 'add':
			$sql = "INSERT INTO campaign_groups (campaign_id, campaign_group_name, points) VALUES (" . $campaign_id . ", '" . gr('campaign_group_name') . "', " . gri('points') . ")";
			mq($sql);
			$action = "";		
		break;
		
		case 'save':
			$sql = "UPDATE campaign_groups SET campaign_group_name='" . gr('campaign_group_name') . "', points=" . gri('points') . " WHERE campaign_group_id=" . gri('campaign_group_id');
			mq($sql);
			$action = "";				
		break;
		
		case 'delete':
			$sql = "DELETE FROM campaign_groups WHERE campaign_group_id=" . $campaign_group_id;
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
		<TITLE><?=T_('Missions'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
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
			var divs_array = ["camps_div", "group_types_div", "groups_div", "campaigns_div", "tasks_div"];
			var divisions = "";

			function verify_campaign_id() {
				var flag = true;
				try {
					var group_id = document.getElementById("campaign_id").value;
				}
				catch(err) {
					flag = false;
				}
				return flag;
			}
		</SCRIPT>
	</HEAD>
	
<? if ($action == "") : ?>	
	<body onload="get_divs('12345', 'campaign_groups_form', '');">
<? else : ?>
	<body>
<? endif; ?>		
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">		
			<DIV class="left_menu">
				<?include('admin_inc.php');?>
			</DIV>
			
			<H1>
				<?=T_('Missions')?>
			</H1>
						
			
<!-- **************************************** EDIT **************************************** -->	
<? if ($action == "edit") : ?>

	<form name="campaign_groups_form" id="campaign_groups_form" action="admin_campaign_groups.php" method="post" accept-charset="UTF-8">	
		<input type="hidden" name="action" id="action" value="save">
		<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
		<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
		<input type="hidden" name="group_type_id" id="group_type_id" value="<?=$group_type_id;?>">
		<input type="hidden" name="group_id" id="group_id" value="<?=$group_id;?>">
		<input type="hidden" name="campaign_id" id="campaign_id" value="<?=$campaign_id;?>">
		<input type="hidden" name="campaign_group_id" id="campaign_group_id" value="<?=$campaign_group_id;?>">

		<? $info = mysql_fetch_assoc(mq("SELECT cgt.group_type_name, cg.group_name, c.campaign_name FROM camp_group_types AS cgt JOIN camp_groups AS cg ON cg.group_id=" . $group_id . " JOIN campaigns as c ON c.campaign_id=" . $campaign_id . " WHERE cgt.camp_id=" . $camp_id)); ?>
		
		<h3><?=T_('Camp');?>: <label style="color:blue;"><?=$camp_name;?></label></h3>
		
		<h3><?=T_('Group Type');?>: <label style="color:blue;"><?=$info['group_type_name'];?></label>/h3>
		
		<h3><?=T_('Group');?>: <label style="color:blue;"><?=$info['group_name'];?></label></h3>
		
		<h3><?=T_('Campaign');?>: <label style="color:blue;"><?=$info['campaign_name'];?></label></h3>
		
		<? $row = mysql_fetch_assoc(mq("SELECT * FROM campaign_groups WHERE campaign_group_id=" . $campaign_group_id)); ?>
		
		<table>
			<tr>
				<td><?=T_('Name');?></td>
				<td><input type="text" name="campaign_group_name" id="campaign_group_name" size="100" maxlength="255" value="<?=$row['campaign_group_name'];?>"></td>
			</tr>
			<tr>
				<td><?=T_('Points');?></td>
				<td><input type="text" name="points" id="points"  maxlength="3" size="4" value="<?=$row['points'];?>" onkeypress="return number_validation(event);"></td>
			</tr>
			<tr>
				<td><input type="submit" value="<?=T_('SAVE');?>"></td>
				<td><input type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value=''; document.getElementById('campaign_group_id').value='';"></td>
			</tr>						
			
		</table>		
		
	</form>
	
<? endif; ?>
<!-- **************************************** EDIT **************************************** -->			
			
<!-- **************************************** ADD NEW **************************************** -->		
<? if ($action == "add_new") : ?>	

	<form name="campaign_groups_form" id="campaign_groups_form" action="admin_campaign_groups.php" method="post" accept-charset="UTF-8" onsubmit="return verify_data();">
		<input type="hidden" name="action" id="action" value="add">
		<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
		<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
		<input type="hidden" name="group_type_id" id="group_type_id" value="<?=$group_type_id;?>">
		<input type="hidden" name="group_id" id="group_id" value="<?=$group_id;?>">
		<input type="hidden" name="campaign_id" id="campaign_id" value="<?=$campaign_id;?>">

		<? $info = mysql_fetch_assoc(mq("SELECT cgt.group_type_name, cg.group_name, c.campaign_name FROM camp_group_types AS cgt JOIN camp_groups AS cg ON cg.group_id=" . $group_id . " JOIN campaigns as c ON c.campaign_id=" . $campaign_id . " WHERE cgt.camp_id=" . $camp_id)); ?>
		
		<h3><?=T_('Camp');?>: <label style="color:blue;"><?=$camp_name;?></label></h3>
		
		<h3><?=T_('Group Type');?>: <label style="color:blue;"><?=$info['group_type_name'];?></label></h3>
		
		<h3><?=T_('Group');?>: <label style="color:blue;"><?=$info['group_name'];?></label></h3>
		
		<h3><?=T_('Campaign');?>: <label style="color:blue;"><?=$info['campaign_name'];?></label></h3>
		
		<? if ($campaign_id > -1) : ?>
			<table>
				<tr>
					<td><?=T_('Mission');?></td>
					<td><input type="text" name="campaign_group_name" id="campaign_group_name" size="100" maxlength="255"></td>
				</tr>
				<tr>
					<td><?=T_('Points');?></td>
					<td><input type="text" name="points" id="points" maxlength="3" size="4" onkeypress="return number_validation(event);"></td>
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

	<form name="campaign_groups_form" id="campaign_groups_form" action="admin_campaign_groups.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="">
		<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
		<input type="hidden" name="campaign_group_id" id="campaign_group_id" value="">

		<a href="#" onclick="var add = verify_campaign_id(); if (add == true) { document.getElementById('action').value='add_new'; document.forms['campaign_groups_form'].submit(); } else { alert('<?=T_('No Campaign Found');?>'); }"><?=T_('Add New Mission')?></a>
			
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
