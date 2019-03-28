<?
$no_login = 1;
$admin_auth = array('camp');
require('header.php'); 

$cur_step = 'main';

if (!is_null($username = gr('username', NULL))) {
	$lang = gr('lang');
	$camp_name = gr('camp_name');
	$first = gr('first');
	$last = gr('last');
	$password = gr('password');

	$message = '';

	$inst_id = 8;

	if ($camp_name === '')
		$message .= T_('The camp name can not be blank.') . '<BR>';
	else
		if (mysql_result(mq('SELECT COUNT(*) FROM camps WHERE camp_name = ' . ms($camp_name) . " AND inst_id = $inst_id"), 0)) 
			$message .= T_('This camp name has been taken for this institution type. Please choose a different name (or institution type).') . '<BR>';

	if (gr('camp_gender') === '')
		$message .= T_('Please select a gender.') . '<BR>';

	if (gr('camp_address1') === '')
		$message .= T_('The address can not be blank.') . '<BR>';

	if (gr('camp_city') === '')
		$message .= T_('The city can not be blank.') . '<BR>';

	if (gr('camp_state') === '')
		$message .= T_('The state can not be blank.') . '<BR>';

	if (gr('camp_country') === '')
		$message .= T_('The country can not be blank.') . '<BR>';

	if (gr('camp_phone') === '')
		$message .= T_('The phone can not be blank.') . '<BR>';

	if ($first === '') 
		$message .= T_('First name can not be blank.') . '<BR>';
		
	if ($last === '') 
		$message .= T_('Last name can not be blank.') . '<BR>';
		
	if ($username === '')
		$message .= T_('Login name can not be blank.') . '<BR>';
	else
		if (mysql_result(mq('SELECT COUNT(*) FROM admins WHERE username = ' . ms($username)), 0)) 
			$message .= T_('This login name has been taken. Please choose a different one.') . '<BR>';
		
	if (!array_key_exists($lang, $langs)) 
		$message .= T_('Invalid language.') . '<BR>';
		
	if ($password === '') 
		$message .= T_('Password can not be blank.') . '<BR>';
		

	if ($message === '') {
		unset($message);

		mq('INSERT INTO camps (camp_name, camp_name_he, camp_gender, inst_id, camp_address1, camp_address2, camp_city, camp_state, camp_country, camp_postal, camp_phone, camp_era) VALUES (' . ms($camp_name) . ', ' . ms(gr('camp_name_he')) . ', ' . ms(gr('camp_gender')) . ", " . $inst_id . ', ' . ms(gr('camp_address1')) . ', ' . ms(gr('camp_address2')) . ', ' . ms(gr('camp_city')) . ', ' . ms(gr('camp_state')) . ', ' . ms(gr('camp_country')) . ', ' . ms(gr('camp_postal')) . ', ' . ms(gr('camp_phone')) . ', 1)');
		$camp_id = mysql_insert_id();

		mq('INSERT INTO admins (username, auth, password, title, first, last, lang, admin_email, admin_address1, admin_address2, admin_city, admin_state, admin_postal, admin_country, admin_phone_work, admin_phone_home, admin_phone_mobile, camp_id) VALUES (' . ms($username) . ", 'inactive', " . ms($password) . ', ' . ms(gr('title')) . ', ' . ms($first) . ', ' . ms($last) . ', ' . ms($lang) . ', ' . ms(gr('admin_email')) . ', ' . ms(gr('admin_address1')) . ', ' . ms(gr('admin_address2')) . ', ' . ms(gr('admin_city')) . ', ' . ms(gr('admin_state')) . ', ' . ms(gr('admin_postal')) . ', ' . ms(gr('admin_country')) . ', ' . ms(gr('admin_phone_work')) . ', ' . ms(gr('admin_phone_home')) . ', ' . ms(gr('admin_phone_mobile')) . ', ' . $camp_id . ')');
		$admin_id = mysql_insert_id();

		mq("INSERT INTO admin_auths (admin_id, auth, id, role_id) VALUES (" . $admin_id . ", 'camp', " . $camp_id . ", 35)");

		$sql = "SELECT * FROM default_group_types";
		$query = mq($sql);
		while ($row = mysql_fetch_assoc($query)) {
			if ($row['logo_id'] > 0) 
				$insert = "INSERT INTO group_types SET camp_id=" . $camp_id . ", group_type_name=" . ms($row['group_type_name']) . ", logo_id=" . $row['logo_id'] . ", divisions=1";
			else
				$insert = "INSERT INTO group_types SET camp_id=" . $camp_id . ", group_type_name=" . ms($row['group_type_name']) . ", divisions=1";
			mq($insert);			
			$group_type_id = mysql_insert_id();
			
			$sql2 = "SELECT * FROM default_divisions";
			$query2 = mq($sql2);
			while ($row2 = mysql_fetch_assoc($query2)) {
				$insert2 = "INSERT INTO divisions SET group_type_id=" . $group_type_id . ", division_name=" . ms($row2['division_name']) . ", groups=1";
				mq($insert2);
			}
			
		}		
				
		$_POST = array('new_login' => true, 'login_username' => $username, 'login_password' => $password);
		unset($no_login);
		check_login_admin();
		header( 'Location: http://mashpia.com/admin.php' ) ;
		//header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/register_camp.php?camp_id=' . $camp_id);
	} 
	else {
		$message .= '<BR>' . T_('Please correct these errors and try again.');
	}
}

//$institution_result = mq('SELECT inst_id, inst_name FROM institutions ORDER BY inst_name');
//$school_makeup_result = mq('SELECT school_makeup_id, school_makeup_name FROM school_makeups ORDER BY school_makeup_name');

$steps = array('main' => T_('Camp Registration'), 'admin' => T_('Account Setup'), 'shipping' => T_('Shipping'), 'package' => T_('Package'), 'billing' => T_('Billing'),);

function registration_HTML_head() {
	return '<LINK href="register_styles.css" rel="stylesheet" type="text/css">';
}

function registration_banner_existing($step) {
	global $steps, $school_id;

	$ret = '<DIV class="registration_banner"><H1>' . T_('Tzivos Hashem') . ' - ' . $steps[$step] .'</H1>';

	$i = 1;
	$ret .= '<TABLE class="progress" cellspacing="0" cellpadding="0"><TR>';
	foreach($steps as $step_name => $cur_step) {
		$ret .= '<TD' . ($step_name == $step ? ' class="current"' : '') . "><A HREF='register_school_{$step_name}.php?school_id=$school_id'>$cur_step</A><BR><SPAN>" . sprintf(T_('Step %d of %d'), $i++, count($steps)) . '</SPAN></TD>';
	}
	$ret .= '</TR></TABLE></DIV>';
	
	return $ret;
}

function registration_banner_new($step) {
	global $steps;

	$ret = '<DIV class="registration_banner"><H1>' . T_('Tzivos Hashem') . ' - ' . $steps[$step] .'</H1>';

	$i = 1;
	$ret .= '<TABLE class="progress" cellspacing="0" cellpadding="0"><TR>';
	foreach($steps as $step_name => $cur_step) {
		$ret .= '<TD' . ($step_name == $step ? ' class="current"' : '') . "><INPUT type='submit' name='register_{$step_name}' value='{$cur_step}'><BR><SPAN>" . sprintf(T_('Step %d of %d'), $i++, count($steps)) . '</SPAN></TD>';
	}
	$ret .= '</TR></TABLE></DIV>';
	
	return $ret;
}

function registration_tail() {
	$ret = '<DIV class="registration_tail">&nbsp;</DIV>';
	return $ret;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
	<TITLE><?=$steps[$cur_step], ' - ', T_('Tzivos Hashem Management System')?></TITLE>
	<LINK href="styles.css" rel="stylesheet" type="text/css">
	<?=registration_HTML_head()?>
	<!--Copyright Ariel Shkedi 2007-2010-->
	</HEAD>

	<BODY>

		<?=registration_banner_new($cur_step); ?>

		<DIV CLASS="body register">

			<DIV class="form form_<?= $align_start ?>">

				<? if(isset($message) && $message): ?>
				<DIV CLASS="message">
					<?= $message ?><BR>
				</DIV>
				<? endif; ?>

				<BR>
				
				<BR>
				
					<H1><?=T_('Just Think')?></H1>
					
					<P>
						<?=sprintf(T_('As an official Tzivos Hashem base, your school will access over $1,200,000%s of programming, school curricula and technology during the coming year.'), '<A href="#thanks">*</A>')?>
					</P>
					
					<P>
						<?=T_('To help you take full advantage of this program, we ask you to confirm that:')?>
					</P>
					
					<UL>
						<LI><?=T_('You are the school principal responsible for supervising Tzivos Hashem.')?>
						<LI><?=T_("You are familiar with the basic format of this program, and how it works seamlessly with your school's curricula. (Full support is available at all times.)")?>
						<LI><?=T_('You have designated or will designate a program director who is fully committed to the ongoing growth of Tzivos Hashem on your base.')?>
					</UL>
					
					<FORM action="register_camp.php" method="post" accept-charset="UTF-8" onSubmit="if(this.elements['password'].value != this.elements['password2'].value) { alert('<?=esq(T_("Passwords don't match."))?>'); this.elements['password'].focus(); return false; } else {return true;}">
					
						<TABLE>
							<CAPTION style="font-size: 100%; font-weight: normal; padding: 40px 0px 6px; text-align: <?=$align_start?>;"><?=T_('<U>Please note</U> that a valid credit card is necessary to complete the registration.')?></CAPTION>
							
							<TR>
								<TH colspan="2"><?=T_('Tell us about your Camp')?></TH>
							</TR>
							
							<TR>
								<TD>
									<LABEL><?=T_('Camp Name')?>
										<BR>
										<INPUT TYPE="text" NAME="camp_name" VALUE="<?=es(gr('camp_name'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
								
								<TD rowspan="2">
									<?=T_('Gender')?>
									<BR>
									<LABEL>
										<INPUT type="radio" name="camp_gender" value="M" <?=gr('camp_gender') == 'M' ? 'CHECKED' : ''?>><?=T_('Boys')?>
									</LABEL>
									
									<BR>
									
									<LABEL>
										<INPUT type="radio" name="camp_gender" value="F" <?=gr('camp_gender') == 'F' ? 'CHECKED' : ''?>><?=T_('Girls')?>
									</LABEL>
									
									<BR>
									
									<LABEL>
										<INPUT type="radio" name="camp_gender" value="B" <?=gr('camp_gender') == 'B' ? 'CHECKED' : ''?>><?=T_('Both')?>
									</LABEL>
									
									<BR>
									
									<BR>
    
									
								</TD>
								
							</TR>
							
							<TR>
								<TD>
									<LABEL>
										<?=T_('Camp Name in Hebrew Letters')?>
										<BR>
										<SPAN style="font-size: 65%;">(<?=T_('This is how it will appear on school banner')?>)</SPAN><BR>
										<INPUT TYPE="text" NAME="camp_name_he" VALUE="<?=es(gr('camp_name_he'))?>" MAXLENGTH="255">
									</LABEL>
									
									<BR>
									
									<?=T_("Don't have Hebrew?")?>
										
									<BR>
										
									<A HREF="http://www.mikledet.com/" target="_blank">www.mikledet.com</A>
								</TD>
							</TR>
							
							<TR>
								<TH colspan="2"><?=T_('Camp Address')?></TH>
							</TR>
							
							<TR>
								<TD>
									<LABEL>
											<?=T_('Address 1')?>
										<BR>
										<INPUT TYPE="text" NAME="camp_address1" VALUE="<?=es(gr('camp_address1'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
								<TD>
									<LABEL>
										<?=T_('Address 2')?>
										<BR>
										<INPUT TYPE="text" NAME="camp_address2" VALUE="<?=es(gr('camp_address2'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
							</TR>
							
							<TR>
								<TD>
									<LABEL>
										<?=T_('City')?>
										<BR>
										<INPUT TYPE="text" NAME="camp_city" VALUE="<?=es(gr('camp_city'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
								
								<TD>
									<LABEL>
										<?=T_('State')?>
										<BR>
										<INPUT TYPE="text" NAME="camp_state" VALUE="<?=es(gr('camp_state'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
							</TR>
							
							<TR>
								<TD>
									<LABEL>
										<?=T_('Postal code')?>
										<BR>
										<INPUT TYPE="text" NAME="camp_postal" VALUE="<?=es(gr('camp_postal'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
								<TD>
									<LABEL>
										<?=T_('Country')?>
										<BR>
										<INPUT TYPE="text" NAME="camp_country" VALUE="<?=es(gr('camp_country'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
							</TR>

							<TR>
								<TD>
									<LABEL>
										<?=T_('Phone')?>
										<BR>
										<INPUT TYPE="text" NAME="camp_phone" VALUE="<?=es(gr('camp_phone'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
							</TR>

							<TR>
								<TH colspan="2"><?=T_('Who is the director of this camp?')?></TH>
							</TR>
							
							<TR>
								<TD>
									<LABEL>
										<?=T_('Title')?>
										<BR>
										<SELECT name="title">
											<?foreach(mysql_enum_values('admins', 'title') as $title):?>
											<OPTION <?=$title == gr('title') ? 'SELECTED' : ''?>><?=es($title)?></OPTION>
											<?endforeach;?>
										</SELECT>
									</LABEL>
								</TD>
							</TR>
							
							<TR>
								<TD>
									<LABEL>
											<?=T_('First Name')?>
										<BR>
										<INPUT TYPE="text" NAME="first" VALUE="<?=es(gr('first'))?>" MAXLENGTH="128">
									</LABEL>
								</TD>
								<TD>
									<LABEL>
										<?=T_('Last Name')?>
										<BR>
										<INPUT TYPE="text" NAME="last" VALUE="<?=es(gr('last'))?>" MAXLENGTH="128">
									</LABEL>
								</TD>
							</TR>
							
							<TR>
								<TD>
									<LABEL>
										<?=T_('Mobile Phone')?>
										<BR>
										<INPUT TYPE="text" NAME="admin_phone_mobile" VALUE="<?=es(gr('admin_phone_mobile'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
						</TR>
							
							<TR>
								<TD>
									<LABEL>
										<?=T_('Work Phone (+ext)')?>
										<BR>
										<INPUT TYPE="text" NAME="admin_phone_work" VALUE="<?=es(gr('admin_phone_work'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
								<TD>
									<LABEL>
										<?=T_('Home Phone')?>
										<BR>
										<INPUT TYPE="text" NAME="admin_phone_home" VALUE="<?=es(gr('admin_phone_home'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>
								<TD>
									<LABEL>
										<?=T_('Email Address')?>
										<BR>
										<INPUT TYPE="text" NAME="admin_email" VALUE="<?=es(gr('admin_email'))?>" MAXLENGTH="255">
									</LABEL>
								</TD>

							</TR>
							
<!--
<TR>
  <TD>
    <LABEL><?=T_('Address 1')?><BR>
    <INPUT TYPE="text" NAME="admin_address1" VALUE="<?=es(gr('admin_address1'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Address 2')?><BR>
    <INPUT TYPE="text" NAME="admin_address2" VALUE="<?=es(gr('admin_address2'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('City')?><BR>
    <INPUT TYPE="text" NAME="admin_city" VALUE="<?=es(gr('admin_city'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('State')?><BR>
    <INPUT TYPE="text" NAME="admin_state" VALUE="<?=es(gr('admin_state'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Postal code')?><BR>
    <INPUT TYPE="text" NAME="admin_postal" VALUE="<?=es(gr('admin_postal'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Country')?><BR>
    <INPUT TYPE="text" NAME="admin_country" VALUE="<?=es(gr('admin_country'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
-->

								<TR>
									<TH colspan="2"><?=T_('Director Login')?></TH>
								</TR>
								
								<TR>
									<TD>
										<LABEL>
											<?=T_('Login Name')?>
											<BR>
											<INPUT TYPE="text" NAME="username" VALUE="<?=es(gr('username'))?>" MAXLENGTH="64">
										</LABEL>
									</TD>
									<TD style="display: none;">
										<LABEL>
											<?=T_('Language')?>
											<BR>
											<SELECT NAME="lang" ID="lang">
												<? foreach($langs as $lang_id => $lang_name) {
													echo "<OPTION value='$lang_id'" . ($lang_id == $lang ? ' SELECTED' : '') . ">$lang_name";
												} ?>
											</SELECT>
										</LABEL>
									</TD>
								</TR>
								
								<TR>
									<TD>
										<LABEL>
											<?=T_('Password')?>
											<BR>
											<INPUT TYPE="text" NAME="password" VALUE="<?=es(gr('password'))?>" MAXLENGTH="255">
										</LABEL>
									</TD>
								</TR>
								
								<TR>
									<TD>
										<LABEL>
											<?=T_('Re-enter Password')?>
											<BR>
											<INPUT TYPE="text" NAME="password2" VALUE="<?=es(gr('password2'))?>" MAXLENGTH="255">
										</LABEL>
									</TD>
								</TR>
							</TABLE>

							<P style="text-align: <?=$align_end?>;">
								<INPUT TYPE="submit" VALUE="<?=T_('Register Camp &amp; Continue')?>">
							</P>
							

						</FORM>

					</DIV>	

					<P>
						<A name="thanks">*</A><?=T_("Special thanks to Tzivos Hashem, Merkos L'inyonei Chinuch, Anash.com and all the businesses and individuals who have given so much of their time and resources to help bring this program to all Chabad schools.")?>
					</P>

				</DIV>

				<?=registration_tail()?>

	</BODY>
	
</HTML>
