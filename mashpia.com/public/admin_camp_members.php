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

if ($user_type == "camp" || $camp_id > -1) {
	$sql = "SELECT * FROM camps WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	$camp = mysql_fetch_assoc($query);
	$camp_name = $camp['camp_name'];
}

//if ($camp_id > -1)
//	$members_query = mq("SELECT * FROM users WHERE camp_id > 0");
//else
//	$members_query = mq("SELECT * FROM users WHERE camp_id=" . $camp_id);
//$num_members = mysql_num_rows($members_query);

$action = gr('action', '');
if ($action != "") {

	switch ($action) {
	
		case 'add':
			$username = mb_strtolower(mb_substr(gr('first'),0,1)) . preg_replace('/\P{L}/u', '', mb_strtolower(gr('last')));
			$count = '';
			while(mysql_num_rows(mq('SELECT username FROM users WHERE username = ' . ms($username.$count)))) $count++;
			$username .= $count;
			
			$user_photo_id = 'NULL';
			if (isset($_FILES['photo'])) 
				$user_photo_id = addFile($_FILES['photo'], $user_photo_id);
				
			if (mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) 
				trigger_error('could not get lock', E_USER_ERROR);
				
			$count = 0;
			do {
				if ($count++ > 100000) 
					trigger_error('could not get ID', E_USER_ERROR);
					
				$user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
			} while (mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);

			$sql = "INSERT INTO users SET user_code=" . $user_code . ", camp_id=" . $camp_id . ", username=" . ms($username) . ", email=" . ms(gr('email')) . ", first=" . ms(gr('first')) . ", last=" . ms(gr('last')) . ", first_he=" . ms(gr('first_he')) . ", last_he=" . ms(gr('last_he')) . ", lang=" . ms(gr('lang')) . ", user_serial=" . mysql_result(mq("(SELECT IFNULL(MAX(user_serial), 0)+ 1 FROM users users_max)"), 0) . ", user_address1=" . ms(gr('address1')) . ", user_address2=" . ms(gr('address2')) . ", user_city=" . ms(gr('city')) . ", user_state=" . ms(gr('state')) . ", user_postal=" . ms(gr('postal')) . ", user_country=" . ms(gr('country')) . ", user_phone=" . ms(gr('phone')) . ", user_start_date=NOW(), gender=" . ms(gr('gender')) . ", dob=" . nullif_ms(gr('dob'), '') . ", user_photo_id=" . $user_photo_id;
			mq($sql);
			$new_user_id = mysql_result(mq("SELECT LAST_INSERT_ID()"), 0);

			mq("SELECT RELEASE_LOCK('users')");

			$action = "";
		break;
		
		case "edit";
			$member_query = mq("SELECT * FROM users WHERE user_id=" . gr('member_id'));
			$member = mysql_fetch_assoc($member_query);
		break;
		
		case "save":
			$username = mb_strtolower(mb_substr(gr('first'),0,1)) . preg_replace('/\P{L}/u', '', mb_strtolower(gr('last')));
			$count = '';
			while (mysql_num_rows(mq('SELECT username FROM users WHERE username = ' . ms($username.$count)))) {
				$count++;
			}
			if ($count > 1)
				$username .= $count;

			$user_photo_id = "";
			if (isset($_FILES['photo'])) 
				$user_photo_id = addFile($_FILES['photo'], $user_photo_id);

			if ($user_photo_id != "")
				$sql = "UPDATE users SET username='" . $username . "', email=" . ms(gr('email')) . ", first=" . ms(gr('first')) . ", last=" . ms(gr('last')) . ", first_he=" . ms(gr('first_he')) . ", last_he=" . ms(gr('last_he')) . ", lang=" . ms(gr('lang')) . ", user_serial=" . mysql_result(mq("(SELECT IFNULL(MAX(user_serial), 0)+ 1 FROM users users_max)"), 0) . ", user_address1=" . ms(gr('address1')) . ", user_address2=" . ms(gr('address2')) . ", user_city=" . ms(gr('city')) . ", user_state=" . ms(gr('state')) . ", user_postal=" . ms(gr('postal')) . ", user_country=" . ms(gr('country')) . ", user_phone=" . ms(gr('phone')) . ", user_start_date=NOW(), gender=" . ms(gr('gender')) . ", dob=" . nullif_ms(gr('dob'), '') . ", user_photo_id=" . $user_photo_id . " WHERE user_id=" . gri('member_id');
			else
				$sql = "UPDATE users SET username='" . $username . "', email=" . ms(gr('email')) . ", first=" . ms(gr('first')) . ", last=" . ms(gr('last')) . ", first_he=" . ms(gr('first_he')) . ", last_he=" . ms(gr('last_he')) . ", lang=" . ms(gr('lang')) . ", user_serial=" . mysql_result(mq("(SELECT IFNULL(MAX(user_serial), 0)+ 1 FROM users users_max)"), 0) . ", user_address1=" . ms(gr('address1')) . ", user_address2=" . ms(gr('address2')) . ", user_city=" . ms(gr('city')) . ", user_state=" . ms(gr('state')) . ", user_postal=" . ms(gr('postal')) . ", user_country=" . ms(gr('country')) . ", user_phone=" . ms(gr('phone')) . ", user_start_date=NOW(), gender=" . ms(gr('gender')) . ", dob=" . nullif_ms(gr('dob'), '') . " WHERE user_id=" . gri('member_id');

			mq($sql);

			$action = "";			
		break;
		
		case "delete":
			$sql = "DELETE FROM users WHERE user_id=" . gri("member_id");
			mq($sql);
			$action = "";
		break;
		
	}
	
}

$ui_type = 'camp';
require_once('admin_ui.php');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=$action == 'add_new' ? T_('Add Camp Member') : ($action == 'edit' ? T_('Edit Camp Member') : T_('View Camp Members')), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<SCRIPT type="text/javascript">
			var user_type = "<?=$user_type;?>";
		</script>
	</HEAD>
	
	<BODY>
	
		<? include('admin_header.php'); ?>
		
		<DIV class="ui_school left">
		
			<DIV class="body">
		
				<DIV class="sub_menu">				
					<? if(!empty($message)) : ?>
						<H2><?=$message?></H2>
					<? endif; ?>
				</DIV> <!-- sub_menu -->
				
				<H1><?=T_('Camp Management')?></H1>
				
				
				<? if ($user_type == "super" && $action == "") : ?>
					<? $camps_sql = mq("SELECT * FROM camps"); ?>
						<P>
							<LABEL>
								<?=T_('Select Camp')?>: 
								<SELECT name="select_camp_id" onchange="document.forms['members_form'].elements[0].value=this.options[this.selectedIndex].value; document.members_form.submit();">
									<? $row_num = 0; ?>
									<? while($camp = mysql_fetch_assoc($camps_sql)) : ?>
									<? if ($camp_id == -1 && $row_num == 0) $camp_id = $camp['camp_id'];?>
									<? if ($camp['camp_id'] == $camp_id) : ?>
									<OPTION value="<?=$camp['camp_id']?>" selected><?=es($camp['camp_name'])?></OPTION>
									<? else :?>
									<OPTION value="<?=$camp['camp_id']?>"><?=es($camp['camp_name'])?></OPTION>
									<? endif; ?>
									<? $row_num++; ?>
									<? endwhile; ?>
								</SELECT>
							</LABEL>							
						</P>
				<? else : ?>
					<h2>
						<?=$camp_name;?>
					</h2>
				<? endif; ?>
		
				<div class="ui_body">
				
					<DIV class="ui_menu">
						<?ui_menu();?>
					</DIV> <!-- ui_menu -->
					
					<DIV class="content">
						<? if ($action == "") : ?>
							<H2>
								<?=T_('View Members');?>
							</H2>							
							
							<form name="members_form" id="members_form" action="admin_camp_members.php" method="post" accept-charset="UTF-8">
								<input type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>"> <!-- ***** DO NOT MOVE must be first element in the form ***** -->
								<input type="hidden" name="action" id="action" value="">								
								<input type="hidden" name="member_id" id="member_id" value="">
								
								<table class="list list_left">
									
									<? $members_query = mq("SELECT * FROM users WHERE camp_id=" . $camp_id); ?>
									<? $num_members = mysql_num_rows($members_query); ?>

									<? if ($num_members == 0) : ?>
										<thead>
											<tr>
												<th><?=T_('No members found');?></th>
											</td>
										</thead>
									<? else : ?>
										<thead>
											<tr>
												<td><?=T_('First');?></td>
												<td><?=T_('Last');?></td>
												<td></td>
												<td></td>
												<td></td>
												<td></td>
												<td></td>
											</td>
										</thead>
									
										<? while ($member = mysql_fetch_assoc($members_query)) : ?>
										<tr>
											<td><?=$member['first'];?></td>
											<td><?=$member['last'];?></td>
											<td><a href="#" onclick="document.getElementById('action').value='edit'; document.getElementById('member_id').value='<?=$member['user_id'];?>'; document.members_form.submit();"><?=T_('Edit');?></a></td>
											<td><a><?=T_('Remove');?></a></td>
											<td><a href="#" onclick="var dlt = confirm('<?=T_('Are you sure that you want to delete this member?');?>'); if (dlt == true) { document.getElementById('action').value='delete'; document.getElementById('member_id').value='<?=$member['user_id'];?>'; document.members_form.submit(); }"><?=T_('Delete');?></a></td>
											<td><a href="admin_member_groups.php?camp_id=<?=$camp_id;?>&user_id=<?=$member['user_id'];?>"><?=T_('Groups');?></a></td>
											<td><a href="admin_member_tasks.php?camp_id=<?=$camp_id;?>&user_id=<?=$member['user_id'];?>"><?=T_('Tasks');?></a></td>																				
										<tr>
										<? endwhile; ?>
									<? endif; ?>
									
								</table>
							</form>							
						<? endif; ?> <!-- if ($action == "") : -->
						
						<!-- ****************************** ADD NEW ****************************** -->
						<? if ($action == "add_new" || $action == "edit") : ?>
							<? if ($camp_id > 1) : ?>
								<DIV class="infobox">
									<?=T_("NOTE: Adding a member's name is for your own records only and does not register him/her in TH. (To register a child, please choose TH 5770 from the menu.)"); ?>
								</DIV>
								
								<FORM action="admin_camp_members.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
									<INPUT type="hidden" name="action" id="action" value="">
									<INPUT type="hidden" name="camp_id" id="camp_id" value="<?=$camp_id;?>">
									<? if ($action == "edit") : ?>
									<INPUT type="hidden" name="member_id" id="member_id" value="<?=$member['user_id'];?>">
									<? endif; ?>
									
									<P CLASS="rows">
									
										<LABEL>
											<?=T_('First Name')?>
											<BR>
											<INPUT TYPE="text" NAME="first" MAXLENGTH="128" <? if ($action == "edit") echo "value='" . $member['first'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Last Name')?>
											<BR>
											<INPUT TYPE="text" NAME="last" MAXLENGTH="128" <? if ($action == "edit") echo "value='" . $member['last'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Hebrew First Name')?>
											<BR>
											<INPUT TYPE="text" NAME="first_he" MAXLENGTH="128" <? if ($action == "edit") echo "value='" . $member['first_he'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Hebrew Last Name')?>
											<BR>
											<INPUT TYPE="text" NAME="last_he" MAXLENGTH="128" <? if ($action == "edit") echo "value='" . $member['last_he'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Photo')?>
											<BR>
											<?=T_('Minimum size')?>: 180x225 (<?=sprintf(T_('Larger is OK, the desired aspect ratio is: %s times as high, as it is wide'), 1.25)?>) 
											<BR>
											<? if ($action == "edit" && $member['user_photo_id'] > 0) : ?>
												<img style="height:100px; width:100px;" src="file_view.php?id=<?=$member['user_photo_id'];?>"/>
											<? endif; ?>																				
											<INPUT type="file" name="photo" class="file">
										</LABEL> 
										
										<?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B
										
										<BR>
										
										<LABEL>
											<?=T_('Email')?>
											<BR>
											<INPUT TYPE="text" NAME="email" MAXLENGTH="255" <? if ($action == "edit") echo "value='" . $member['email'] . "'"?>>
										</LABEL>
	
										<BR>
										<?=T_('Gender')?>
										<BR>
										
										<LABEL>
											<INPUT type="radio" name="gender" value="NULL" style="width: auto;" <? if ($action == "edit" && $member['gender'] != "M" && $member['gender'] != "F") echo " checked "; ?>> <?=T_('Unknown')?>

										</LABEL>
											
										<LABEL>
											<INPUT type="radio" name="gender" value="M" style="width: auto;" <? if ($action == "edit" && $member['gender'] == "M") echo " checked"; ?>> <?=T_('Male')?>
										</LABEL>
										
										<LABEL>
											<INPUT type="radio" name="gender" value="F" style="width: auto;" <? if ($action == "edit" && $member['gender'] == "F") echo " checked "; ?>> <?=T_('Female')?>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Date of birth')?>
											<BR>
											<INPUT TYPE="text" NAME="dob" MAXLENGTH="10" onChange="if(this.value != '') {var str = this.value.replace(/\D/g, '')+'00000000'; this.value = str.substring(0, 4) + '-' + str.substring(4, 6) + '-' +  str.substring(6, 8);}"  <? if ($action == "edit") echo "value='" . $member['dob'] . "'"?>> (YYYY-MM-DD)
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Language')?>
											<BR>
											<SELECT NAME="lang">
											<?
												foreach ($langs as $lang_id => $lang_name) {
													echo "<OPTION>" . es($lang_name);
												}
											?>
											</SELECT>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Address 1')?>
											<BR>
											<INPUT type="text" name="address1" maxlength=255 <? if ($action == "edit") echo "value='" . $member['user_address1'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Address 2')?>
											<BR>
											<INPUT type="text" name="address2" maxlength=255 <? if ($action == "edit") echo "value='" . $member['user_address2'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('City')?>
											<BR>
											<INPUT type="text" name="city" maxlength=255 <? if ($action == "edit") echo "value='" . $member['user_city'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('State/Province')?>
											<BR>
											<INPUT type="text" name="state" maxlength=255 <? if ($action == "edit") echo "value='" . $member['user_state'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Zip/Postal code')?>
											<BR>
											<INPUT type="text" name="postal" maxlength=255 <? if ($action == "edit") echo "value='" . $member['user_postal'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Country')?>
											<BR>
											<INPUT type="text" name="country" maxlength=255 <? if ($action == "edit") echo "value='" . $member['user_country'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<LABEL>
											<?=T_('Phone')?>
											<BR>
											<INPUT type="text" name="phone" maxlength=255 <? if ($action == "edit") echo "value='" . $member['user_phone'] . "'"?>>
										</LABEL>
										
										<BR>
										
										<? if ($action == "add_new") : ?>
										<INPUT class="submit" type="submit" value="<?=T_('ADD');?>" onclick="alert('ABOUT TO SUBMIT'); document.getElementById('action').value='add'; alert('SUBMITTING');">
										<? else :?>
										<INPUT class="submit" type="submit" value="<?=T_('SAVE');?>" onclick="document.getElementById('action').value='save';">
										<? endif; ?>
										<INPUT class="submit" type="submit" value="<?=T_('CANCEL');?>" onclick="document.getElementById('action').value='';">
									</P>
								</FORM>	
							<? else : ?> <!-- if ($camp_id > 1) : -->
								<DIV class="infobox">
									<?=T_("You must choose a camp first."); ?>
								</DIV>
								<form action="admin_user.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
									<INPUT type="hidden" name="action" value="add_new">
								</form>
							<? endif; ?> <!-- if ($camp_id > 1) : -->
						<? endif; ?> <!-- if ($action == "add_new") : -->
						<!-- ****************************** ADD NEW ****************************** -->
						
					</div>
					
				</div> <!-- ui_body -->
				
			</div> <!-- body -->
			
		</DIV> <!-- ui_school left -->
		
		<? include('admin_footer.php'); ?>

	</BODY>
	
</HTML>
