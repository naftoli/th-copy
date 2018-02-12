<?php
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

$action = gr('action', '');
if ($action != '') {

	switch($action) {
		case 'add':
			$sql = "INSERT INTO global_campaigns SET campaign_name=" . ms(gr('campaign_name')) . ", points=" . gri('points');
			mq($sql);
			$action = "";
		break;
		
		case 'save':
			$sql = "UPDATE global_campaigns SET campaign_name=" . ms(gr('campaign_name')) . ", points=" . gri('points') . " WHERE campaign_id=" . $campaign_id;
			mq($sql);
			$action = "";				
		break;
		
		case 'delete':
			$sql = "DELETE FROM global_campaigns WHERE campaign_id=" . gri('campaign_id');
			mq($sql);
			$action = "";		
		break;				
	}

}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Campaigns'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
	</HEAD>
	
	<body>
	
		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			

<? if ($action == "") : ?>
			<form name="campaigns_form" id="campaigns_form" action="admin_campaigns.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">
				<input type="hidden" name="campaign_id" id="campaign_id" value="">
				
				<a href="#" onclick="document.getElementById('action').value='add_new'; document.forms['campaigns_form'].submit();"><?=T_('Add Campaign')?></a>
			
				<br />
				<br />
				
				<H1>
					<?=T_('Campaigns')?>
				</H1>
				
				<? $query = mq("SELECT * FROM global_campaigns WHERE group_task=0 ORDER BY campaign_name"); ?>
				<div>
					<table class='pretty_grid'>
						<tr>
							<th><?=T_("Name");?></th>
							<th><?=T_("Points");?></th>
							<th><?=T_("Group");?></th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
						
						<? while ($row = mysql_fetch_assoc($query)) : ?>
						<tr>
							<td><?=$row['campaign_name'];?></td>
							<td><?=$row['points'];?></td>
							<? if ($row['group_task'] == 1) : ?>
							<td><input type="radio" disabled="disabled" checked></td>
							<? else : ?>
							<td><input type="radio" disabled="disabled"></td>
							<? endif;?>
							<td><a href="admin_missions.php?admin_id=<?=$admin_id;?>&campaign_id=<?=$row['campaign_id'];?>"><?=T_('Missions');?></a></td>
							<td><a href="#" onclick="document.getElementById('action').value='edit'; document.getElementById('campaign_id').value='<?=$row['campaign_id'];?>'; document.campaigns_form.submit();"><?=T_('Edit Campaign');?></a></td>
							<td><a href="#" onclick="var dlt = confirm('<?=T_('Are you sure that you want to delete this campaign?');?>'); if (dlt == true) { document.getElementById('action').value='delete'; document.getElementById('campaign_id').value='<?=$row['campaign_id'];?>'; document.campaigns_form.submit(); }"><?=T_('Delete Campaign');?></a></td>
						</tr>
						<? endwhile; ?>					
					</table>					
				</div>
				
				
				<br />
				<br />
				
				<H1>
					<?=T_('Group Campaigns')?>
				</H1>
				
				<? $query = mq("SELECT * FROM global_campaigns campaign_name WHERE group_task=1 ORDER BY campaign_name"); ?>
				<div>
					<table class='pretty_grid'>
						<tr>
							<th><?=T_("Name");?></th>
							<th><?=T_("Points");?></th>
							<th><?=T_("Group");?></th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
						
						<? while ($row = mysql_fetch_assoc($query)) : ?>
						<tr>
							<td><?=$row['campaign_name'];?></td>
							<td><?=$row['points'];?></td>
							<? if ($row['group_task'] == 1) : ?>
							<td><input type="radio" disabled="disabled" checked></td>
							<? else : ?>
							<td><input type="radio" disabled="disabled"></td>
							<? endif;?>
							<td><a href="admin_missions.php?admin_id=<?=$admin_id;?>&campaign_id=<?=$row['campaign_id'];?>"><?=T_('Missions');?></a></td>
							<td><a href="#" onclick="document.getElementById('action').value='edit'; document.getElementById('campaign_id').value='<?=$row['campaign_id'];?>'; document.campaigns_form.submit();"><?=T_('Edit Campaign');?></a></td>
							<td><a href="#" onclick="var dlt = confirm('<?=T_('Are you sure that you want to delete this campaign?');?>'); if (dlt == true) { document.getElementById('action').value='delete'; document.getElementById('campaign_id').value='<?=$row['campaign_id'];?>'; document.campaigns_form.submit(); }"><?=T_('Delete Campaign');?></a></td>
						</tr>
						<? endwhile; ?>					
					</table>					
				</div>
				
				<br />
			</form>
<? endif; ?>

<? if ($action == "add_new" || $action == "edit") : ?>
			<form name="campaigns_form" id="campaigns_form" action="admin_campaigns.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">
				<input type="hidden" name="campaign_id" id="campaign_id" value="<?=$campaign_id;?>">
				
				<? 
					if ($action == "edit") { 
						$row = mysql_fetch_assoc(mq("SELECT * FROM global_campaigns WHERE campaign_id=" . gri('campaign_id') )); 
						$campaign_name = $row['campaign_name'];
						$points = $row['points'];					
					}
					else {
						$campaign_name = "";
						$points = "";
					}
					
				?>
				
				<table>
					<tr>
						<td><?=T_('Campaign Name');?>:</td>
						<td><input type="text" size="100" maxlength="100" name="campaign_name" id="campaign_name" value="<?=$campaign_name;?>"></td>
					</tr>
					
					<tr>
						<td><?=T_('Points');?>:</td>
						<td><input type="text" size="4" maxlength="4" name="points" id="points" onkeypress="return number_validation(event);" value="<?=$points;?>"></td>						
					</tr>
				</table>
				
				<br />
				
				<? if ($action == "add_new") : ?>
					<input type="submit" value="<?=T_('ADD');?>" onclick="document.getElementById('action').value='add';">
					<input type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value='';">
				<? else : ?>
					<input type="submit" value="<?=T_('SAVE');?>" onclick="document.getElementById('action').value='save';">
					<input type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value='';">		
				<? endif; ?>
				
				<br />
								
			</form>
<? endif; ?>

		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
