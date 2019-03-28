<? 
$admin_auth = array('school', 'class'); 
require('header.php'); 
$ui_type = 'school';
require_once('admin_ui.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);

if ($auth_mode == 'class') {
	if(gr('action') != 'edit' && gr('action') != 'edit2') sgr('action', 'edit');
}

$edit_row = false;

if ($school_id != -1 && $action = gr('action')) {

	switch($action) {
	
		case 'add':
			$result = mq("SELECT -1 class_id, '' class_grade, '' class_sub, '' class_teacher, '' email, '' cell, 6 default_level");
			$edit_row = mysql_fetch_assoc($result);
		break;

		case 'add2':
			$class_grade = gr('class_grade');
			$class_sub = gr('class_sub');
			$default_level = max(6, min(gri('default_level', 6), 14));

			$result = mq("SELECT 1 FROM classes WHERE class_era = 0 AND school_id = $school_id AND class_grade = " . ms($class_grade) . ' AND class_sub = ' . ms($class_sub));

			if (mysql_num_rows($result)) {
				$message = T_('Unable to add new platoon, this grade-sub is already used.');
				$result = mq('SELECT -1 class_id, ' . ms($class_grade) . ' class_grade, ' . ms($class_sub) . ' class_sub, ' . ms(gr('class_teacher')) . " class_teacher, $default_level default_level");
				$edit_row = mysql_fetch_assoc($result);
				$action = 'add';
			} else {
				$username = mysql_real_escape_string($_POST['username']);
				$password = mysql_real_escape_string($_POST['password']);
				$name = mysql_real_escape_string($_POST['name']);
				$email = mysql_real_escape_string($_POST['email']);
				$cell = mysql_real_escape_string($_POST['cell']);
				/*
				$insert = true;
				$sql = "select * from admins where username = '" . $username . "'";
				$result = mysql_query($sql);
				if (mysql_num_rows($result) > 0) {
					$message = "This username is already being used. Please choose a different one.";
					//echo $message; exit;
					$insert = false;
				}
				
				if ($insert) {
				*/
					mq("INSERT INTO classes (school_id, class_grade, class_sub) VALUES ($school_id, " . ms($class_grade) . ',' . ms($class_sub) . ")");
					$message = T_('Platoon added');
					$class_id = mysql_insert_id();
					/*
					$sql = "update classes set
							class_teacher = '" . $name . "',
							email = '" . $email . "',
							cell = '" . $cell . "' 
							where class_id = " . $class_id;
					//echo $sql; exit;
					@mysql_query($sql);
					
					$sql = "insert into admins
							set username = '" . $username . "',
							password = '" . $password . "',
							last = '" . $name . "',
							admin_email = '" . $email . "',
							admin_phone_mobile = '" . $cell . "'";
					if ($result = mysql_query($sql)) {
						
						$admin_id = mysql_insert_id();
						$sql = "insert into admin_auths
								set admin_id = " . $admin_id . ",
								id = " . $class_id . ",
								auth = 'class',
								role_id = 13";
						@mysql_query($sql);
					}
					*/
				//}
			}
		break;
		

		case 'delete':
			mq("DELETE FROM classes WHERE school_id = $school_id AND class_id = $class_id AND NOT EXISTS (SELECT class_id FROM users WHERE users.school_id = classes.school_id AND users.class_id = classes.class_id)");
			mq("DELETE FROM admin_auths WHERE auth = 'class' AND id = $class_id");
			$message = T_('Platoon deleted');
		break;
		
		case 'edit':
			$result = mq("SELECT class_id, class_grade, class_sub, class_teacher, email, cell, default_level FROM classes WHERE school_id = $school_id AND class_id = $class_id AND class_era = 0");
			$edit_row = mysql_fetch_assoc($result);
		break;

		case 'edit2':
			$class_grade = gr('class_grade'); 
			$class_sub = gr('class_sub');
			$default_level = max(6, min(gri('default_level', 6), 14));
			$result = mq('SELECT 1 FROM classes WHERE class_grade = ' . ms($class_grade) . ' AND class_sub = ' . ms($class_sub) . " AND class_era = 0 AND school_id = $school_id AND class_id != $class_id");
			
			if (mysql_num_rows($result)) {
				$message = T_('Unable to edit platoon, this grade-sub is already used.');
				$result = mq("SELECT $class_id class_id, " . ms($class_grade) . ' class_grade, ' . ms($class_sub) . ' class_sub, ' . ms(gr('class_teacher')) . " class_teacher, $default_level default_level");
				$edit_row = mysql_fetch_assoc($result);
				$action = 'edit';
			} 
			else {
				mq('UPDATE classes SET class_grade = ' . ms($class_grade) . ', class_sub = ' . ms($class_sub) . " WHERE class_id = $class_id AND class_era = 0");
				//$name = mysql_real_escape_string($_POST['name']);
				//$email = mysql_real_escape_string($_POST['email']);
				//$cell = mysql_real_escape_string($_POST['cell']);
				//mq('UPDATE classes SET class_grade = ' . ms($class_grade) . ', class_sub = ' . ms($class_sub) . ", class_teacher = '" . $name . "', email = '" . $email . "',
				//  cell = '" . $cell . "' WHERE class_id = $class_id AND class_era = 0");
				/*
				// get admin id
				$sql5 = "select admin_id from admin_auths where id = " . $class_id . " and auth = 'class' and role_id = 13";
				$result5 = mysql_query($sql5);
				$row5 = mysql_fetch_assoc($result5);
				$teacher_id = $row['admin_id'];
				if ($teacher_id) {
					// update admin account
					$sql6 = "update admins
							set last = '" . $name . "',
							admin_email = '" . $email . "',
							admin_phone_mobile = '" . $cell . "'
							where admin_id = " . $teacher_id;
					@mysql_query($sql6);
				}
				*/
				$message = T_('Platoon edited');
			}
		break;

		default:
			user_error('unknown action', E_USER_ERROR);
		break;
	}	
}

if ($auth_mode != 'class') 
	$school_result = mq('SELECT school_id, school_name FROM schools' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Platoons'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
                <script type='text/javascript'>
					/*
                    $(function() {
                        $("#mainForm").submit( function() {
                            var teacher = $("#teacher").val();
                            //var email = $("#email").val();
                            if ((teacher == "")) {
                                alert("Teacher is mandatory.\nPlease try again.");
                                return false;
                            }
                        });
                    });
                    */
                </script>
	</HEAD>
	
	<BODY>
		<?include('admin_header.php');?>
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">
					<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
				</DIV>
			
				<H1><?=T_('Base Management')?></H1>
				
				<?if($admin_user['auth'] == 'super' || $auth_mode == 'school' && count($admin_user['auths']['school']) > 1):?>
					<FORM action="admin_class.php" method="get" accept-charset="UTF-8">
						<P>
							<?=T_('Select Institution')?>: 
							<SELECT name="school_id">
							<?while($school_row = mysql_fetch_assoc($school_result)):?>
								<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
							<?endwhile;?>
							</SELECT> 
							<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
						</P>
					</FORM>
				<?endif;?>
				
				<?if($school_id == -1):?>
					<?=T_('Please select an Institution.')?>
				<?else:?>
					<DIV class="ui_body">
					
						<DIV class="ui_menu">
							<?ui_menu();?>
						</DIV>
						
						<DIV class="content">
						
							<H1><?=T_('Platoons')?></H1>
							
							<DIV class="infobox">
							Here you will see information about your platoons
							</DIV>
							
							<BR>
							<BR>
							
							<?if($edit_row):?>
							
								<?if($auth_mode != 'class'):?>
									<P><A HREF="admin_class.php?school_id=<?=$school_id?>"><?=T_('Cancel')?></A></P>
								<?endif;?>
								
								<h2>Class Info</h2>

								<FORM action="admin_class.php" method="post" accept-charset="UTF-8" id='mainForm'>
									<P CLASS="rows">
										<INPUT type="hidden" name="action" value="<?=$action?>2">
										<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
										<INPUT type="hidden" name="class_id" value="<?=$edit_row['class_id']?>">
										<LABEL>
										* <?=T_('Grade')?>
										<BR>
											<SELECT name="class_grade" id='grade'>
												<?foreach(mysql_enum_values('classes', 'class_grade') as $grade):?>
												<OPTION <?=$grade == $edit_row['class_grade'] ? 'SELECTED' : ''?>><?=es($grade)?></OPTION>
												<?endforeach;?>
											</SELECT>
										</LABEL>
										<BR>
										
										<LABEL><?=T_('Sub')?> (If required, please include additional details such as "Boys" or "Kita Alef-1.")<BR><INPUT type="text" name="class_sub" maxlength=255 value="<?=es($edit_row['class_sub'])?>"></LABEL><BR>
										<!--
										<? //if ($edit_row['class_id'] == -1) : ?>
										<h2>Teacher Info</h2>
										<table>
											<tr>
												<td>Name:</td>
												<td><input type="text" name="name" id="name" value="<?=$edit_row['class_teacher']?>" /></td>
											</tr>
											<tr>
												<td>Email:</td>
												<td><input type="text" name="email" id="email" value="<?=$edit_row['email']?>" /></td>
											</tr>
											<tr>
												<td>Cell:</td>
												<td><input type="text" name="cell" id="cell" value="<?=$edit_row['cell']?>" /></td>
											</tr>
											<?php if ($edit_row['class_id'] == -1) : ?>
											<tr>
												<td>Username:</td>
												<td><input type="text" name="username" id="username" /></td>
											</tr>
											<tr>
												<td>Password:</td>
												<td><input type="text" name="password" id="password" /></td>
											</tr>
											<? endif; ?>
										</table>
										<br />
										<? //endif; ?>
										-->
										<!--<LABEL>* <?=T_('Teacher')?><BR><INPUT id='teacher' type="text" name="class_teacher" maxlength=255 value="<?=es($edit_row['class_teacher'])?>"></LABEL><BR>-->
                                                                                <!--
                                                                                <label>* Teacher's Email<br /><input id='email' type="text" name="email" value="<?=isset($edit_row['email'])?es($edit_row['email']):''?>" /></label><br />
                                                                                <label>Cell Phone<br /><input type="text" name="cell" value="<?=isset($edit_row['cell'])?es($edit_row['cell']):''?>" /></label><br />
                                                                                <!--
                                                                                <LABEL><?=T_('Default Year')?><BR><INPUT type="text" name="default_level" maxlength=2 value="<?=es($edit_row['default_level'])?>" onChange="this.value = Math.max(6, Math.min(parseInt('0'+this.value, 10), 14));"> (6 - 14)</LABEL><BR>
										-->
										<br />
                                                                                <INPUT id="addNewClass" class="submit" type="submit" value="<?=$action == 'edit' ? T_('Save') : T_('Add New Class')?>">
									</P>
								</FORM>

							<?else:?>

								<?$result = mq("SELECT class_id, class_era, class_grade, class_sub, class_teacher, default_level, (SELECT COUNT(*) FROM users WHERE users.school_id = classes.school_id AND users.class_id = classes.class_id) students FROM classes WHERE school_id = $school_id ORDER BY class_era, class_grade, class_sub");?>

								<TABLE CLASS="list" style="font-size:12px;">
									<THEAD>
										<TR>
											<TH><?=T_('Grade')?></TH>
											<TH><?=T_('Sub')?></TH>	
											<TH><?=T_('Teacher')?></TH>
											<TH><?=T_('Default Year')?></TH>
											<TH><?=T_('# Soldiers')?></TH>
											<TH><?=T_('Points')?></TH>
											<TH></TH>
											<TH></TH>
											<TH></TH>
										</TR>
									</THEAD>
									<? while($row = mysql_fetch_assoc($result)): ?>
									<TR>
										<TD><?=es($row['class_grade'])?></TD>
										<TD><?=es($row['class_sub'])?></TD>
										<TD><?=es($row['class_teacher'])?></TD>
										<TD><?=$row['default_level']?></TD>
										<TD><?=$row['students']?></TD>
										<TD><?=number_format(mysql_result(mq(totalMarks("JOIN users USING (user_id) WHERE school_id = $school_id AND class_id = {$row['class_id']}")), 0), 0)?></TD>
										<TD><?if($row['class_era'] == 0):?><A HREF="admin_class.php?action=edit&amp;class_id=<?=$row['class_id']?>&amp;school_id=<?=$school_id?>"><?=T_('Edit Platoon Info')?></A><?else:?><?=sprintf(T_('This class is from %s. Please move all the soldiers to current classes, or remove them from your school, and then delete this class.'), $row['class_era'])?><?endif;?></TD>
										<TD><?if(!$row['students']):?><A HREF="admin_class.php?action=delete&amp;class_id=<?=$row['class_id']?>&amp;school_id=<?=$school_id?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Platoon')?></A><?else:?><?=T_("Can't delete, has soldiers")?><?endif;?></TD>
										<TD><A HREF="admin_user.php?search_class_id=<?=$row['class_id']?>&amp;school_id=<?=$school_id?>"><?=T_('Manage Soldiers')?></A></TD>
									</TR>
									<? endwhile; ?>
								</TABLE>
							<? endif; ?>
							
							<? if ( !isset( $_GET['action'] ) ) { ?>
							<p><br /><a href="admin_class.php?action=add&school_id=<?=$_GET['school_id']?>"><input type='button' value='Add New Class'></p>
							<? } ?>
							
							<BR style="clear: both;">
							
						</DIV>
												
					</DIV>
					
				<? endif; ?>
				
				</DIV>
				
			</DIV>
			
			<? include('admin_footer.php'); ?>
	</BODY>
	
	<script>
		$("#addNewClass").click( function(e) {
			e.preventDefault();
			var action = '<?=$action?>';
			//if (action == 'add') {
			//	$.ajax({
			//		async : false,
			//		data : {
			//			user : $("#username").val()
			//		},
			//		type : "POST",
			//		url : 'ajax/checkUsername.php',
			//		success : function( error ) {
			//			if (parseInt(error) == 1) {
			//				alert('This username is already being used. Please choose a different one.');
			//				return false;
			//			} else {
			//				// make sure all fields are filled out
			//				var name = $("#name").val();
			//				var email = $("#email").val();
			//				var cell = $("#cell").val();
			//				var username = $("#username").val();
			//				var password = $("#password").val();
			//				var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
			//				
			//				function isAlphabetic(sText) {
			//					var ValidChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
			//					var IsAlphabetic=false;
			//					var Char;			
			//					for (i = 0; i < sText.length; i++){
			//						Char = sText.charAt(i);
			//						if (ValidChars.indexOf(Char) != -1){
			//							IsAlphabetic = true;
			//							break;
			//						}
			//					}
			//					return IsAlphabetic;
			//				}
			//				
			//				if (name == '' || email == '' || cell == '' || username == '' || password == '') {
			//					alert('All fields are mandatory.');
			//					return false;
			//				} else if (reg.test(email) != true) {
			//					alert("Invalid email address.");
			//					return false;
			//				} else if (isAlphabetic(cell)) {
			//					alert("Cell phone number can only contain numbers.");
			//					return false;
			//				} else if (cell.length < 9) {
			//					alert("Cell phone must contain at least 9 numbers.");
			//					return false;
			//				} else if (name.length < 3) {
			//					alert("Name must have at least 3 characters.");
			//					return false;
			//				} else {
			//					$("#mainForm").submit();
			//				}
			//			}
			//		}
			//	});
			//} else {
				$("#mainForm").submit();
			//}
		});
	</script>
</HTML>
