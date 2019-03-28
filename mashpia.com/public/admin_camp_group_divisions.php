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


$camp_group_id = gri('camp_group_id', -1);
$camp_division_id = gri('camp_division_id', -1);
$camp_group_division_id = gri('camp_group_division_id', -1);

$action = gr('action', '');
if ($action != '') {

	switch($action) {
		case 'add':
			$sql = "INSERT INTO camp_groups_divisions SET camp_division_id=" . $camp_division_id .  ", group_division_name=" . ms(gr('group_division_name'));
			mq($sql);
			$action = "";
			$group_id = -1;
		break;
		
		case 'save':
			$sql = "UPDATE camp_groups_divisions SET group_division_name=" . ms(gr('group_division_name')) . " WHERE camp_group_division_id=" . $camp_group_division_id;
			mq($sql);
			$action = "";
			$group_id = -1;
		break;
		
		case 'delete':
			$sql = "DELETE FROM camp_groups_divisions WHERE camp_group_division_id=" . $camp_group_division_id;
			mq($sql);
			$action = "";
			$group_id = -1;
		break;		
		
	}

}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Divisions'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="ajax_get_levels.js"></SCRIPT>
		<script type="text/javascript">	
			var admin_id = "<?=$admin_id;?>";
			var user_type = "<?=$user_type;?>";
			var camp_id = "<?=$camp_id;?>";
			var camp_group_id = "<?=$camp_group_id;?>";
			var camp_division_id = "<?=$camp_division_id;?>";
			var camp_group_division_id = "<?=$camp_group_division_id;?>";
			
			var divs_array = ["div_1", "div_2", "div_3", "div_4"];
			var divisions = "";
		</script>		
		
	</HEAD>
	
<? if ($action == "") : ?>	
	<body onload="get_levels('1234', 'groups_divisions_form');">
<? else : ?>
	<body>
<? endif; ?>		

		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			<H1>
				<?=T_('Divisions')?>
			</H1>
			
			<? if (!empty($message)) : ?>
				<H2>
					<?=$message?>
				</H2>
			<? endif; ?>
			
<!-- **************************************** ADD NEW or EDIT **************************************** -->			
<? if ($action == 'add_new' || $action == "edit") : ?>

	<form name="groups_divisions_form" id="groups_divisions_form" action="admin_camp_group_divisions.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="">		
		<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
		<input type="hidden" name="camp_group_id" id="camp_group_id" value="<?=$camp_group_id;?>">
		<input type="hidden" name="camp_division_id" id="camp_division_id" value="<?=$camp_division_id;?>">
		<input type="hidden" name="camp_group_division_id" id="camp_group_division_id" value="<?=$camp_group_division_id;?>">
		
		
		<h2><?=T_('Camp');?>: <label style="color:blue;"><?=$camp_name;?></label></h2>
		
		<? $row = mysql_fetch_assoc(mq("SELECT cd.division_name, cg.group_name FROM camp_divisions AS cd JOIN camp_groups AS cg USING (camp_group_id) WHERE cd.camp_division_id=" . $camp_division_id)); ?>
		<h2><?=T_('Group Type');?>: <label style="color:blue;"><?=$row['group_name'];?></label></h2>
		
		<h2><?=T_('Group');?>: <label style="color:blue;"><?=$row['division_name'];?></label></h2>
		
		<? 
			if ($action == "edit") {
				$sql = "SELECT group_division_name FROM camp_groups_divisions WHERE camp_group_division_id=" . $camp_group_division_id;
				$query = mq($sql);
				$row = mysql_fetch_assoc($query);
				$group_division_name = $row['group_division_name'];
			}
			else {
				$group_division_name = "";
			}
		?>
		
		<br />
				
		<table>
			<tr><td><?=T_('Division Name');?>:<input type="text" size="100" maxlength="100" name="group_division_name" id="group_division_name" value="<?=$group_division_name;?>"></td></tr>
		</table>

		<br />
		
		<? if ($action == "add_new") : ?>
			<input type="submit" value="<?=T_('ADD');?>" onclick="document.getElementById('action').value='add';">
			<input type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value='';">
		<? else : ?>
			<input type="submit" value="<?=T_('SAVE');?>" onclick="document.getElementById('action').value='save';">
			<input type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value='';">		
		<? endif; ?>
	</form>
	
<? endif; ?> <!-- if ($action == 'add_new') : -->
<!-- **************************************** ADD NEW or EDIT **************************************** -->			

<!-- **************************************** NO ACTION **************************************** -->			
<? if ($action == "") : ?>

	<form name="groups_divisions_form" id="groups_divisions_form" action="admin_camp_group_divisions.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="">
		<? if ($camp_id > 0) : ?>
		<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
		<? endif; ?>
	
		<input type="hidden" name="camp_group_division_id" id="camp_group_division_id" value="<?=$camp_group_division_id;?>">
		
		<a href="#" onclick="document.getElementById('action').value='add_new'; document.forms['groups_divisions_form'].submit();"><?=T_('Add New Division')?></a>
			
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
		<br />
		
		<div id="div_4">
		</div>		
		
		<br />
				
	</form>
	
<? endif; ?> <!-- if ($action == "") : -->
<!-- **************************************** NO ACTION **************************************** -->			

		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
