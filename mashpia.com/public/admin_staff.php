<? 
$admin_auth = array('camp');
require('header.php'); 
require_once('file_save.php');
require_once('calendar.php');

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
if ($camp_id > -1) {
	$camp = mysql_fetch_assoc(mq("SELECT camp_name FROM camps WHERE camp_id=" . $camp_id));
	$camp_name = $camp['camp_name'];
}

$admin_id = gri('admin_id', -1);
$employee_id = gri('employee_id', -1);
if ($employee_id > -1) {
	$query = mq("SELECT * FROM admins WHERE admin_id=" . $employee_id);
	$employee = mysql_fetch_assoc($query);
}


$action = gr('action', '');
if ($action != "") {

	switch ($action) {	
	
		case "add":
			$username = "";
			$flag = false;
			
			$count = 0;
			do {
				if ($count == 0) 
					$username = $_POST['first'] . $_POST['last'];
				else 
					$username = $_POST['first'] . $_POST['last'] . $count;
				
				$sql = "SELECT count(*) AS total FROM admins WHERE username='" . $username . "'";
				$row = mysql_fetch_assoc(mysql_query($sql));
				$total = $row['total'];
				
				if ($total == 0)
					$flag = true;
					
				$count++;
				
			} while ($flag == false);
			
			$sql = "INSERT INTO admins SET username='" . $username . "', first='" . $_POST['first'] . "', last=" . ms(gr('last')) . ", admin_address1=" . ms(gr('address1')) . ", admin_address2=" . ms(gr('address2')) . ", admin_city=" . ms(gr('city')). ", admin_state=" . ms(gr('state')) . ", admin_country=" . ms(gr('country')) . ", admin_postal=" . ms(gr('postal')) . ", admin_phone_work=" . ms(gr('phone_work')) . ", admin_phone_home=" . ms(gr('phone_home')) . ", admin_phone_mobile=" . ms(gr('admin_phone_mobile')) . ", password=" . ms(gr('password1')) . ", admin_email=" . ms(gr('email')) . ", employee_role_id=" . gr('employee_role_id');
			echo $sql . "<br />";
			mq($sql);
			$new_admin_id = mysql_insert_id();
			$sql = "INSERT INTO admin_auths SET admin_id=" . $new_admin_id . ", auth='camp', id=" . $camp_id;
			echo $sql . "<br />";
			mq($sql);
			$action = "";
		break;
		
		case "save":
			$sql = "UPDATE admins SET username='" . $_POST['first'] . $_POST['last'] . "', first='" . $_POST['first'] . "', last=" . ms(gr('last')) . ", lang=" . ms(gr('lang')) . ", admin_address1=" . ms(gr('address1')) . ", admin_address2=" . ms(gr('address2')) . ", admin_city=" . ms(gr('city')). ", admin_state=" . ms(gr('state')) . ", admin_country=" . ms(gr('country')) . ", admin_postal=" . ms(gr('postal')) . ", admin_phone_work=" . ms(gr('phone_work')) . ", admin_phone_home=" . ms(gr('phone_home')) . ", admin_phone_mobile=" . ms(gr('phone_mobile')) . ", password=" . ms(gr('password1')) . ", admin_email=" . ms(gr('email'))  . ", employee_role_id=" . gr('employee_role_id') . " WHERE admin_id=" . $employee_id;
			mq($sql);
			$action = "";
		break;
		
		case "delete":
			echo $sql . "<br />";
			mq($sql);
			$sql = "DELETE FROM admin_auths WHERE admin_id=" . $employee_id;
			mq($sql);
			$action = "";
		break;
			
	}
	
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Staff');?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="functions.js"></SCRIPT>
		
		<SCRIPT type="text/javascript">
			var fields = new Array("first", "last", "address1", "city", "state", "country", "postal", "phone_work", "phone_home", "email");
			var message = "<?=T_('You must enter a value in ');?>";
			
			function validate_fields() {
				var valid = true;
				
				for (cntr = 0; cntr < fields.length; cntr++) {
					if (document.getElementById(fields[cntr]).value == "") {
						document.getElementById(fields[cntr]).focus();
						alert(message + fields[cntr]);
						valid = false;
						break;
					}
				}
								
				if (valid == true && (document.getElementById("password1").value != document.getElementById("password2").value) ) {
					document.getElementById("password1").focus();
					alert("<?=T_('Passwords do not match');?>");
					valid = false;
				}
				
				return valid;
			}		
		</script>
	</HEAD>
	
	<body>
	
		<? include('admin_header.php'); ?>
				
		<DIV class="body">
		
			<? if(!empty($message)) : ?>
				<H2><?=$message?></H2>
			<? endif; ?>
				
<!-- **************************************** NO ACTION **************************************** -->
<? if ($action == "") : ?>
						
			<form name="staff_form" id="staff_form" action="admin_staff.php" method="post" accept-charset="UTF-8">
				<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">					
				<input type="hidden" name="camp_id" id="camp" value="<?=$camp_id;?>">
				<input type="hidden" name="employee_id" id="employee_id" value="">
				<input type="hidden" name="action" id="action" value="">
				
				<br />
				
				<a href="#" onclick="document.getElementById('action').value='add_new'; document.staff_form.submit();"><?=T_("Add New Staff Member");?></a>
				
				<h2><?=T_('Camp');?>: <label style="color:blue;"><?=$camp_name;?></label></h2>
				
				<table class="pretty_grid">
					<thead>
						<tr>
							<th><?=T_('Name');?></th>
							<th></th>
							<th></th>
							<th></th>
							<!--<th></th>-->
						</tr>
					</thead>
					
					<? $query = mq("SELECT * FROM admins AS a JOIN admin_auths AS aa ON (a.admin_id=aa.admin_id AND aa.auth='camp' AND aa.id=" . $camp_id . " AND role_id IS NULL)"); ?>
					<? while ( $row = mysql_fetch_assoc($query) ) : ?>
					<tr>
						<td><?=$row['first'];?> <?=$row['last'];?></td>
						<td><a href="#" onclick="document.getElementById('employee_id').value='<?=$row['admin_id'];?>'; document.getElementById('action').value='edit'; document.staff_form.submit();"><?=T_("Edit");?></a></td>
						<td><a href="#" onclick="var del = confirm ('<?=T_("Are you sure that you want to delete this employee?");?>'); if (del == true) { document.getElementById('employee_id').value='<?=$row['admin_id'];?>'; document.getElementById('action').value='delete'; document.staff_form.submit(); }"><?=T_("Delete");?></a></td>
						<td><a href="admin_staff_groups.php?camp_id=<?=$camp_id;?>&admin_id=<?=$row['admin_id'];?>"><?=T_("Groups");?></a></td>
					</tr>
					<? endwhile; ?>
					
				</table>
			</form>
							
			<br />
			<br />
<? endif; ?> <!-- if ($action == "") : -->
<!-- **************************************** NO ACTION **************************************** -->

<!-- **************************************** EDIT or ADD NEW **************************************** -->
<? if ($action == "edit" || $action == "add_new") : ?>
	<form name="staff_form" id="staff_form" action="admin_staff.php" method="post" accept-charset="UTF-8">
		<input type="hidden" name="action" id="action" value="">
		<input type='hidden' name='camp_id' id='camp_id' value="<?=$camp_id;?>">
		<input type='hidden' name='employee_id' id='employee_id' value="<?=$employee_id;?>">
		<input type="hidden" name="admin_id" id="admin_id" value="<?=$admin_id;?>">
	
		<table>
			<tr><td><label><?=T_("First");?>:</td><td><input type="text" name="first" id="first" <? if ($action == "edit") echo "value='" . $employee['first'] . "'" ?>></td></tr></label>
			<tr><td><label><?=T_("Last");?>:</td><td><input type="text" name="last" id="last" <? if ($action == "edit") echo "value='" . $employee['last'] . "'" ?>></td></tr></label>
						
			<tr>
				<td>
					<label>
						<?=T_('Language')?>:</td><td>
						<SELECT name="lang" id="lang">
							<? foreach($langs as $lang_id => $lang_name) : ?>
								<? if ($action == "edit" && $employee['lang'] == $lang_id) : ?>
								<OPTION selected value="<?=$lang_id?>"><?=es($lang_name);?></OPTION>
								<? else :?>
								<OPTION value="<?=$lang_id?>"><?=es($lang_name);?></OPTION>
								<? endif; ?>
							<? endforeach; ?>
						</SELECT>
					</label>
				</td>
			</tr>
								
			<tr><td><label><?=T_("Address 1");?>:</label></td><td><input type="text" name="address1" id="address1" <? if ($action == "edit") echo "value='" . $employee['admin_address1'] . "'" ?>></td></tr>
			<tr><td><label><?=T_("Address 2");?>:</label></td><td><input type="text" name="address2" id="address2" <? if ($action == "edit") echo "value='" . $employee['admin_address2'] . "'" ?>></td></tr>
			<tr><td><label><?=T_("City");?>:</label></td><td><input type="text" name="city" id="city"  <? if ($action == "edit") echo "value='" . $employee['admin_city'] . "'" ?>></td></tr>
			<tr><td><label><?=T_("State");?>:</label></td><td><input type="text" name="state" id="state" <? if ($action == "edit") echo "value='" . $employee['admin_state'] . "'" ?>></td></tr>
			<tr><td><label><?=T_("Country");?>:</label></td><td><input type="text" name="country" id="country" <? if ($action == "edit") echo "value='" . $employee['admin_country'] . "'" ?>></td></tr>
			<tr><td><label><?=T_("Postal");?>:</label></td><td><input type="text" name="postal" id="postal" <? if ($action == "edit") echo "value='" . $employee['admin_postal'] . "'" ?>></td></tr>			
			<tr><td><label><?=T_("Phone Work");?>:</label></td><td><input type="text" name="phone_work" id="phone_work" <? if ($action == "edit") echo "value='" . $employee['admin_phone_work'] . "'" ?>></td></tr>
			<tr><td><label><?=T_("Phone Home");?>:</label></td><td><input type="text" name="phone_home" id="phone_home" <? if ($action == "edit") echo "value='" . $employee['admin_phone_home'] . "'" ?>></td></tr>
			<tr><td><label><?=T_("Phone Mobile");?>:</label></td><td><input type="text" name="phone_mobile" id="phone_mobile" <? if ($action == "edit") echo "value='" . $employee['admin_phone_mobile'] . "'" ?>></td></tr>
			<tr><td><label><?=T_("Email");?>:</label></td><td><input type="text" name="email" id="email" <? if ($action == "edit") echo "value='" . $employee['admin_email'] . "'" ?>></td></tr>
			<tr><td><label><?=T_("Password");?>:</label></td><td><input type="password" name="password1" id="password1" <? if ($action == "edit") echo "value='" . $employee['password'] . "'" ?>></td></tr>
			<tr><td><label><?=T_("Re-enter Password");?>:</label></td><td><input type="password" name="password2" id="password2"  <? if ($action == "edit") echo "value='" . $employee['password'] . "'" ?>></td></tr>
			
			<tr>
				<? if ($action == "edit") : ?>
				<td>
					<label><input type="button" value="SAVE" onclick="var submit_form = validate_fields(); if (submit_form == true) { document.getElementById('action').value='save'; document.forms['staff_form'].submit(); }"></label>
				</td>
				<td>
					<label><input type="button" value="CANCEL" onclick="document.getElementById('action').value=''; document.forms['staff_form'].submit();"></label>
				</td>
				<? else : ?>
				<td>
					<label><input type="button" value="ADD" onclick="var submit_form = validate_fields(); if (submit_form == true) { document.getElementById('action').value='add'; document.forms['staff_form'].submit(); }"></label>
				</td>
				<td>
					<label><input type="button" value="CANCEL" onclick="document.getElementById('action').value=''; document.forms['staff_form'].submit();"></label>
				</td>
				<? endif; ?>
			</tr>
			
		</table>
	
	</form>
<? endif; ?> <!-- if ($action == "edit") : -->
<!-- **************************************** EDIT or ADD NEW **************************************** -->
				
		</div> <!-- body -->
		
		<? include('admin_footer.php'); ?>

	</BODY>
	
</HTML>
