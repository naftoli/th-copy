<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'school';
require_once('admin_ui.php');

check_id_access();
$school_id = gri('school_id', -1);
$search_user_serial = gr('search_user_serial');
$search_first = gr('search_first');
$search_last = gr('search_last');
$search_class_id = gri('search_class_id', -1);

$gender_values = gr('gender_values', '');

if ($gender_values != "") {
	$info1 = preg_split('/;/', $gender_values);
	for ($cntr = 0; $cntr < count($info1); $cntr++) {
		echo "<input type='hidden' name='INFO1' value='" . $info1[$cntr] . "'>\n";
		$info2 = preg_split('/:/', $info1[$cntr]);
		$student_id = $info2[0];
		$value = $info2[1];
		echo "<input type='hidden' name='STUDENT ID' value='" . $student_id  . "'>\n";
		echo "<input type='hidden' name='VALUE' value='" . $value  . "'>\n";
	}
	//echo "<input type='hidden' name='gender_values' value='" . $gender_values . "'>\n";
}
//if($kiosk_edit = gra('kiosk_edit')) {
//  foreach($kiosk_edit as $user_id => $setting) {
//    mq('UPDATE users SET kiosk_edit = ' . ms($setting) . ' WHERE user_id = ' . intval($user_id));
//  }
//  $message = T_('Kiosk settings updated.');
//}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Kiosk Mission Entry'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript">
			function genders(value) {			
				for (cntr = 0; cntr < document.genders_form.elements.length; cntr++) {				
					if (document.genders_form.elements[cntr].type == "radio") {				
						element_name = document.genders_form.elements[cntr].name;
						
						if (element_name.substr(0, 8) == "student_") {
							if (document.genders_form.elements[cntr].value == value) 
								document.genders_form.elements[cntr].checked = true;
							else
								document.genders_form.elements[cntr].checked = false;													
						}
												
					}					
				}				
			}
			
			function submit_form() {
				var values = new Array();
				
				for (cntr = 0; cntr < document.genders_form.elements.length; cntr++) {				
				
					if (document.genders_form.elements[cntr].type == "radio") {				
					
						if (document.genders_form.elements[cntr].checked == true) {
						
							element_name = document.genders_form.elements[cntr].name;
							
							if (element_name.substr(0, 8) == "student_") {
																
								info = element_name.split("_");
								student_id = info[1];
								value = student_id + ":" + document.genders_form.elements[cntr].value;
								values.push(value);
							}

						}
						
												
					}					
				}				
				
				values_string = values.join(";");
				document.getElementById("gender_values").value = values_string;
				document.genders_form.submit();
			}
		</SCRIPT>
	</HEAD>
	
	<BODY>
		<?include('admin_header.php');?>
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
		<DIV class="body">
		
			<DIV class="sub_menu">			
				<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
			</DIV>
			
			<H1><?=T_('Base Management')?></H1>
			
			<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
				<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
			
				<FORM action="admin_user_kiosk_gc.php" method="get" accept-charset="UTF-8">
					<P>
						<LABEL><?=T_('Select Institution')?>: 
							<SELECT name="school_id">
							<?while($school_row = mysql_fetch_assoc($school_result)):?>
								<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
							<?endwhile;?>
							</SELECT>
						</LABEL>
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
						<DIV class="infobox">
							<P>
								<?=T_('Mission Reporting Feature: Provides children the option of being able to report their missions on the kiosk.')?>
							</P>
							<DL>
								<DT><?=T_('Enable')?>:
								<DD><?=T_("Will place this feature onto the child's profile on the kiosk.")?>
								<DT><?=T_('Disable')?>:
								<DD><?=T_("Will remove this feature from the child's profile on the kiosk.")?>
								<DT><?=T_('Freeze')?>:
								<DD><?=T_("Will leave feature on the child's profile, but it will freeze their ability to report missions.")?>
							</DL>
						</DIV>
						
						<DIV class="infobox2">
							<FORM action="admin_user_kiosk_gc.php" method="get" accept-charset="UTF-8">
								<P>
									<B><?=T_('Search by')?>:</B><BR>
									<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
									<LABEL style="white-space: nowrap;"><?=T_('Serial #')?>: <INPUT type="text" name="search_user_serial" value="<?=es($search_user_serial)?>"></LABEL>
									<LABEL style="white-space: nowrap;"><?=T_('First name')?>: <INPUT type="text" name="search_first" value="<?=es($search_first)?>"></LABEL>
									<LABEL style="white-space: nowrap;"><?=T_('Last name')?>: <INPUT type="text" name="search_last" value="<?=es($search_last)?>"></LABEL>
									<?$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");?>
									<LABEL style="white-space: nowrap;"><?=T_('Platoon')?>: 
										<SELECT name="search_class_id">
											<OPTION value="-1">&lt;<?=T_('All')?>&gt;
											<?while($class_row = mysql_fetch_assoc($class_result)):?>
												<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $search_class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
											<?endwhile;?>
										</SELECT>
									</LABEL>
								</P>
								<P>
									<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
								</P>
							</FORM>
						</DIV>
						<BR>
						<BR>
						
						<FORM action="admin_user_kiosk_gc.php" method="post" name="genders_form" id="genders_form" accept-charset="UTF-8">
						
							<? $result = mq("SELECT class_grade, class_sub, user_id, username, first, last, user_serial, kiosk_edit FROM users LEFT JOIN classes USING (class_id, school_id) WHERE school_id = $school_id" . ($search_first !== '' ? ' AND first LIKE ' . ms("$search_first%") : '') . ($search_user_serial !== '' ? ' AND user_serial = ' . intval($search_user_serial) : '') . ($search_last !== '' ? ' AND last LIKE ' . ms("$search_last%") : '') . ($search_class_id != -1 ? " AND class_id = $search_class_id" : '') . ' ORDER BY class_grade, class_sub, last, first'); ?>
							
							<TABLE CLASS="list list_<?=$align_start?>">
								<THEAD>
									<TR>
										<TH><?=T_('Platoon')?></TH>
										<TH><?=T_('Name')?></TH>
										<TH><?=T_('Serial #')?></TH>
										<!--
										<TH>
											<?//=T_('Kiosk Entry')?><BR>
											<LABEL><INPUT type="radio" onClick="setRadio(this.form, ''); this.checked = false; return false;"><?=T_('Enable All')?></LABEL>
											<LABEL><INPUT type="radio" onClick="setRadio(this.form, 'off'); this.checked = false; return false;"><?=T_('Disable All')?></LABEL>
										</TH>
										-->
										<TH>
											<?//=T_('Kiosk Entry')?><BR>
											<LABEL><INPUT type="radio" name="gender_selection" id="gender_selection" onClick="genders('true');"><?=T_('Both All')?></LABEL>
											<LABEL><INPUT type="radio" name="gender_selection" id="gender_selection" onClick="genders('false');"><?=T_('Same All')?></LABEL>
										</TH>										
									</TR>
								</THEAD>
								<? while($row = mysql_fetch_assoc($result)): ?>
									<TR>
										<TD><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></TD>
										<TD><A href="admin_user.php?action=edit&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$school_id?>"><?=es("{$row['first']} {$row['last']}")?></A></TD>
										<TD><?=$row['user_serial']?></TD>
										<TD>
											<!--
											<LABEL><INPUT type="radio" name="kiosk_edit[<?//=$row['user_id']?>]" value="" <?//=$row['kiosk_edit'] === '' ? 'CHECKED' : ''?>><?//=T_('Enabled')?></LABEL>
											<LABEL><INPUT type="radio" name="kiosk_edit[<?//=$row['user_id']?>]" value="off" <?//=$row['kiosk_edit'] === 'off' ? 'CHECKED' : ''?>><?//=T_('Disabled')?></LABEL>
											<LABEL><INPUT type="radio" name="kiosk_edit[<?//=$row['user_id']?>]" value="frozen" <?//=$row['kiosk_edit'] === 'frozen' ? 'CHECKED' : ''?>><?//=T_('Frozen')?></LABEL>
											-->
											<LABEL><INPUT type="radio" name="student_<?=$row['user_id']?>" id="student_<?=$row['user_id']?>" value="true" ><?=T_('Both Genders')?></LABEL>
											<LABEL><INPUT type="radio" name="student_<?=$row['user_id']?>" id="student_<?=$row['user_id']?>" value="false"><?=T_('Same Gender')?></LABEL>
										</TD>
									</TR>
								<? endwhile; ?>
							</TABLE>
							
							<P>
								<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
								<INPUT type="hidden" name="search_first" value="<?=$search_first?>">
								<INPUT type="hidden" name="search_last" value="<?=$search_last?>">
								<INPUT type="hidden" name="search_user_serial" value="<?=$search_user_serial?>">
								<INPUT type="hidden" name="search_class_id" value="<?=$search_class_id?>">								
								<INPUT type="button" class="submit" onclick="submit_form();" value="<?=T_('Save')?>">
							</P>
							
							<input type="hidden" name="gender_values" id="gender_values" value="" /> 
							
						</FORM> <!-- genders_form -->
						
						
						<BR style="clear: both;">
						
					</DIV>
					
				</DIV>
				
			<? endif; ?>
			
			</DIV>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
