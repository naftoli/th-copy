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
$mission_id = gri('mission_id', -1);

$action = gr('action', '');
if ($action != '') {

	switch($action) {
		case 'add':
			$sql = "INSERT INTO global_missions  SET campaign_id=" . $campaign_id . ", mission_name=" . ms(gr('mission_name')) . ", sequence=" . gri('sequence') . ", points=" . gri('points');
			mq($sql);
			$action = "";
		break;
		
		case 'save':
			$sql = "UPDATE global_missions  SET mission_name=" . ms(gr('mission_name')) . ", sequence=" . gri('sequence') . ", points=" . gri('points') . " WHERE mission_id=" . $mission_id;
			mq($sql);
			$action = "";				
		break;
		
		case 'delete':
			$sql = "DELETE FROM global_missions  WHERE mission_id=" . gri('mission_id');
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
		<SCRIPT type="text/javascript" src="ajax_get_campaigns.js"></SCRIPT>
		<script type="text/javascript">	
			var campaign_id = "<?=$campaign_id;?>";			
			var divs_array = ["div_1", "div_2"];
		</script>		

	</HEAD>
	
<? if ($action == "") : ?>	
	<body onload="get_campaigns('12', 'missions_form');">
<? else : ?>
	<body>
<? endif; ?>		

		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			<H1>
				<?=T_('Campaign Missions')?>
			</H1>
			
			<? if (!empty($message)) : ?>
				<H2>
					<?=$message?>
				</H2>
			<? endif; ?>

<? if ($action == "") : ?>
			<form name="missions_form" id="missions_form" action="admin_missions.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">
				<input type="hidden" name="mission_id" id="mission_id" value="">
				
				<a href="#" onclick="document.getElementById('action').value='add_new'; document.forms['missions_form'].submit();"><?=T_('Add Mission')?></a>
			
				<br />
				<br />
				
				<div id="div_1">
				</div>
				
				<br />
				<br />
				
				<div id="div_2">
				</div>
								
				<br />
			</form>
<? endif; ?>

<? if ($action == "add_new" || $action == "edit") : ?>
			<form name="missions_form" id="missions_form" action="admin_missions.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="">
				<? if ($action == "add_new") : ?>
				<input type="hidden" name="campaign_id" id="campaign_id" value="<?=$campaign_id;?>">
				<? else : ?>
				<input type="hidden" name="campaign_id" id="campaign_id" value="<?=$campaign_id;?>">
				<input type="hidden" name="mission_id" id="mission_id" value="<?=$mission_id;?>">
				<? endif; ?>
				
				<? 
					if ($action == "edit") { 
						$row = mysql_fetch_assoc(mq("SELECT * FROM global_missions  WHERE mission_id=" . gri('mission_id') )); 
						$mission_name = $row['mission_name'];
						$sequence = $row['sequence'];
						$points = $row['points'];					
					}
					else {
						$mission_name = "";
						$sequence = "";
						$points = "";
					}
					
				?>
				
				<? $campaign = mysql_fetch_assoc(mq("SELECT * FROM global_campaigns WHERE campaign_id=" . $campaign_id)); ?>
				<h3><?=T_('Campaign');?>: <label style="color:blue;"><?=$campaign['campaign_name'];?></label></h3>
				
				<table>
					<tr>
						<td><?=T_('Mission Name');?>:</td>
						<td><input type="text" size="100" maxlength="100" name="mission_name" id="mission_name" value="<?=$mission_name;?>"></td>
					</tr>
					
					<tr>
						<td><?=T_('Sequence #');?>:</td>
						<td><input type="text" size="4" maxlength="3" name="sequence" id="sequence" onkeypress="return number_validation(event);" value="<?=$sequence;?>"></td>						
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
