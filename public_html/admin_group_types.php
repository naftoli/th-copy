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


$group_type_id = gri('group_type_id', -1);

$action = gr('action', '');
if ($action != '') {

	switch($action) {
		case 'add':
			if (gr('divisions') == "on")
				$divisions = 1;
			else
				$divisions = 0;
				
			$sql = "INSERT INTO group_types SET camp_id=" . $camp_id .  ", group_type_name=" . ms(gr('group_type_name')) . ", divisions=" . $divisions;
			mq($sql);
			$group_type_id = mysql_insert_id();
			
			if ($divisions == 0) {
				$sql = "INSERT INTO groups SET group_type_id=" . $group_type_id . ", group_name='', divisions=0";
				mq($sql);
				$group_id = mysql_insert_id();
				
				$sql = "INSERT INTO divisions SET group_id=" . $group_id . ", division_name=''";
				mq($sql);
			}
			
			$action = "";
		break;
		
		case 'save':
			if (gr('divisions') == "on")
				$divisions = 1;
			else
				$divisions = 0;
		
			$sql = "UPDATE group_types SET group_type_name=" . ms(gr('group_type_name')) . ", divisions=" . $divisions . " WHERE group_type_id=" . $group_type_id;
			mq($sql);
			$action = "";
		break;
		
		case 'delete':
			$sql = "DELETE FROM group_types WHERE group_type_id=" . $group_type_id;
			mq($sql);
			
			$sql1 = "SELECT * FROM divisions WHERE group_type_id=" . $group_type_id;
			$query1 = mq($sql1);
			while ($row1 = mysql_fetch_assoc($query1)) {
				$sql2 = "DELETE FROM groups WHERE division_id=" . $row1['division_id'];
				mq($sql2);
			}
			
			$sql = "DELETE FROM divisions WHERE group_type_id=" . $group_type_id;
			mq($sql);
			
			$action = "";
		break;		
		
	}

}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Group Types'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="ajax_get_groups.js"></SCRIPT>
		
		<script type="text/javascript">	
			var admin_id = "<?=$admin_id;?>";
			var user_type = "<?=$user_type;?>";
			var camp_id = "<?=$camp_id;?>";
			var group_type_id = "<?=$group_type_id;?>";
			var divs_array = ["div_1", "div_2"];
			var divisions = "";
		</script>
	</HEAD>
	
<? if ($action == "") : ?>	
	<body onload="get_groups('12', 'groups_form');">
<? else : ?>
	<body>
<? endif; ?>		

		<? include('admin_header.php'); ?>
		
		<DIV CLASS="body">
		
			<H1>
				<?=T_('Group Types')?>
			</H1>
			
			<? if (!empty($message)) : ?>
				<H2>
					<?=$message?>
				</H2>
			<? endif; ?>
			
<!-- **************************************** ADD NEW or EDIT **************************************** -->			
<? if ($action == 'add_new' || $action == "edit") : ?>

	<form action="admin_group_types.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="">		
		<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
		<? if ($action == "edit") : ?>
		<input type="hidden" name="group_type_id" id="group_type_id" value="<?=$group_type_id;?>">
		<? endif; ?>
		
		<h2><?=T_('Camp');?>: <label style="color:blue;"><?=$camp_name;?></label></h2>
		
		<? 
			if ($action == "edit") {
				$sql = "SELECT * FROM group_types WHERE group_type_id=" . $group_type_id;
				$query = mq($sql);
				$row = mysql_fetch_assoc($query);
				$group_type_name = $row['group_type_name'];
				if ($row['divisions'] == 1) 
					$checked = " checked ";
				else
					$checked = "";
			}
			else {
				$group_type_name = "";
			}
		?>
		
		<table>
			<tr><td><?=T_('Group Type');?>:<input type="text" name="group_type_name" id="group_type_name" value="<?=$group_type_name;?>"></td></tr>
			<tr><td><?=T_('Divisions');?>:<input type="checkbox" <? if ($action == 'add_new') echo " checked ";?> <? if ($action == 'edit') echo $checked; ?> name="divisions" id="divisions"></td></tr>			
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

	<form name="groups_form" id="groups_form" action="admin_group_types.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="">
		<? if ($camp_id > 0) : ?>
		<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
		<? endif; ?>
		<input type="hidden" name="group_type_id" id="group_type_id" value="">
		
		<a href="#" onclick="document.getElementById('action').value='add_new'; document.forms['groups_form'].submit();"><?=T_('Add New Group Type')?></a>
			
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
	
<? endif; ?> <!-- if ($action == "") : -->
<!-- **************************************** NO ACTION **************************************** -->			

		</DIV>
	
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
