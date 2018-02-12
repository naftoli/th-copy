<? 
$admin_auth = array('camp');
require('header.php'); 
require_once('file_save.php');
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

$employee_role_id = gri('employee_role_id', -1);

$action = gr("action", "");

if ($action == "add" || $action == "save") {
	if (gr('allow_read') == "on")
		$allow_read = 1;
	else 
		$allow_read = 0;
				
	if (gr('allow_write') == "on")
		$allow_write = 1;
	else 
		$allow_write = 0;

	if (gr('allow_delete') == "on")
		$allow_delete = 1;
	else 
		$allow_delete = 0;
}

if ($action != "") {

	switch($action) {
	
		case "add":
			$sql = "INSERT INTO employee_roles SET role_name=" . ms(gr('role_name')) . ", allow_read=" . $allow_read . ", allow_write=" . $allow_write . ", allow_delete=" . $allow_delete;
			mq($sql);
			$action = "";
		break;
		
		case "save":
			$sql = "UPDATE employee_roles SET role_name=" . ms(gr('role_name')) . ", allow_read=" . $allow_read . ", allow_write=" . $allow_write . ", allow_delete=" . $allow_delete . " WHERE employee_role_id=" . gri('employee_role_id');
			mq($sql);	
			$action = "";
		break;
		
		case "delete":
			$sql = "DELETE FROM employee_roles WHERE employee_role_id=" . gri('employee_role_id');
			mq($sql);	
			$action = "";
		break;		
		
	}
	
}

if ($action == "edit") {
	$query = mq("SELECT * FROM employee_roles WHERE employee_role_id=" . gri('employee_role_id'));
	$role = mysql_fetch_assoc($query);
	$role_name = $role['role_name'];
}

function get_roles() {
	$echo_string = "";
	
	$query = mq("SELECT * FROM employee_roles");
	$num_rows = mysql_num_rows($query);
	
	$echo_string = $echo_string . "\n<table class='pretty_grid'>";
			
	if ($num_rows == 0) {
		$echo_string = $echo_string . "\n\t<thead>";
		$echo_string = $echo_string . "\n\t\t<tr>";
		$echo_string = $echo_string . "\n\t\t\t<th>" . T_('No Roles Found') . "</th>";
		$echo_string = $echo_string . "\n\t\t</tr>";	
		$echo_string = $echo_string . "\n\t</thead>";	
	}
	else {
		$echo_string = $echo_string . "\n\t<thead>";
		$echo_string = $echo_string . "\n\t\t<tr>";
		$echo_string = $echo_string . "\n\t\t\t<th>" . T_('Role') . "</th>";
		$echo_string = $echo_string . "\n\t\t\t<th>" . T_('Read') . "</th>";
		$echo_string = $echo_string . "\n\t\t\t<th>" . T_('Write') . "</th>";
		$echo_string = $echo_string . "\n\t\t\t<th>" . T_('Delete') . "</th>";
		$echo_string = $echo_string . "\n\t\t\t<th></th>";
		$echo_string = $echo_string . "\n\t\t\t<th></th>";
		$echo_string = $echo_string . "\n\t\t</tr>";	
		$echo_string = $echo_string . "\n\t</thead>";
		
		while ($row = mysql_fetch_assoc($query)) {		
			$echo_string = $echo_string . "\n\t\t<tr>";
			$echo_string = $echo_string . "\n\t\t\t<td>" . $row['role_name'] . "</td>";
			if ($row['allow_read'] == 1)
				$echo_string = $echo_string . "\n\t\t\t<td>" . T_('Yes') . "</td>";
			else
				$echo_string = $echo_string . "\n\t\t\t<td>" . T_('No') . "</td>";
			if ($row['allow_write'] == 1)
				$echo_string = $echo_string . "\n\t\t\t<td>" . T_('Yes') . "</td>";
			else
				$echo_string = $echo_string . "\n\t\t\t<td>" . T_('No') . "</td>";
			if ($row['allow_delete'] == 1)
				$echo_string = $echo_string . "\n\t\t\t<td>" . T_('Yes') . "</td>";
			else
				$echo_string = $echo_string . "\n\t\t\t<td>" . T_('No') . "</td>";
			$echo_string = $echo_string . "\n\t\t\t<td><a href='#' onclick='document.getElementById(\"employee_role_id\").value=\"" . $row['employee_role_id'] . "\"; document.getElementById(\"action\").value=\"edit\"; document.employee_roles_form.submit();'>" . T_('Edit') . "</a></td>";
			$echo_string = $echo_string . "\n\t\t\t<td><a href='#' onclick='var dlt = confirm(\"" . T_('Are you sure that you want to delete this role?') . "\"); if (dlt == true) { document.getElementById(\"employee_role_id\").value=\"" . $row['employee_role_id'] . "\"; document.getElementById(\"action\").value=\"delete\"; document.employee_roles_form.submit(); }'>" . T_('Delete') . "</a></td>";
			$echo_string = $echo_string . "\n\t\t</tr>";	
		}
		
		
	}
	
	$echo_string = $echo_string . "\n</table>";
	
	echo $echo_string;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Employee Roles');?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript">
		</script>
	</HEAD>
	

	<body>
	
		<? include('admin_header.php'); ?>
				
		<div class="body">
			
			<? if(!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>
				
			<h2><?=T_('Employee Roles')?></h2>
						
			<h2><?=T_('Camp');?>: <label style='color:blue;'><?=$camp['camp_name'];?></label></h2>
			
<? if ($action == "") : ?>			
			<form name="employee_roles_form" id="employee_roles_form" action="admin_employee_roles.php" method="post">
				<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
				<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
				<input type="hidden" name="employee_role_id" id="employee_role_id" value="">
				<input type="hidden" name="action" id="action" value="">
				
				<a href="#" onclick="document.getElementById('action').value='add_new'; document.employee_roles_form.submit()"><?=T_('Add New Role');?></a>
				
				<br />
				<br />
				
				<div>
					<? get_roles(); ?>
				</div>
				
				<br />
			</form>
<? endif; ?>

<? if ($action == "add_new" || $action == "edit") : ?>
			<form name="employee_roles_form" id="employee_roles_form" action="admin_employee_roles.php" method="post">
				<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
				<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
				<input type="hidden" name="employee_role_id" id="employee_role_id" value="<?=$employee_role_id;?>">
				<input type="hidden" name="action" id="action" value="">
				
				<table>
					<tr>
						<td><?=T_('Role Name');?>:</td>
						<td><input type="text" name="role_name" id="role_name" size="50" maxlength="50" <? if ($action == "edit") echo "value='" . $role['role_name'] . "'"; ?>></td>
					</tr>
					<tr>
						<td colspan="2">
							<?=T_('Read');?>: <input type="checkbox" name="allow_read" id="allow_read" <? if ($action == "edit" && $role['allow_read'] == 1) echo " checked "; ?>>
							<?=T_('Write');?>: <input type="checkbox" name="allow_write" id="allow_write" <? if ($action == "edit"  && $role['allow_write'] == 1) echo " checked "; ?>>
							<?=T_('Delete');?>: <input type="checkbox" name="allow_delete" id="allow_delete" <? if ($action == "edit"  && $role['allow_delete'] == 1) echo " checked "; ?>>
						</td>
					</tr>
				</table>
				
				<br />
				
				<div>
				</div>
				
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

		</div> <!-- body -->
		
		<? include('admin_footer.php'); ?>

	</BODY>
	
</HTML>
