<?$no_login = 1;?>
<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('register_inc.php');

$cur_step = 'main';

if(!is_null($username = gr('username', NULL))) {
  $lang = gr('lang');
  $school_name = gr('school_name');
  $first = gr('first');
  $last = gr('last');
  $password = gr('password');

  $message = '';

  $school_makeup_id = gri('school_makeup_id', -1);

  if($school_makeup_id == -1)
    $message .= T_('Please pick a school type.') . '<BR>';
  else
    if(!mysql_result(mq("SELECT COUNT(*) FROM school_makeups WHERE school_makeup_id = $school_makeup_id"), 0)) $message .= T_('Invalid school type.') . '<BR>';

  $inst_id = gri('inst_id', -1);

  if($inst_id == -1)
    $message .= T_('Please pick an institution type.') . '<BR>';
  else
    if(!mysql_result(mq("SELECT COUNT(*) FROM institutions WHERE inst_id = $inst_id"), 0)) $message .= T_('Invalid institution type.') . '<BR>';

  if($school_name === '')
    $message .= T_('The school name can not be blank.') . '<BR>';
  else
    if(mysql_result(mq('SELECT COUNT(*) FROM schools WHERE school_name = ' . ms($school_name) . " AND inst_id = $inst_id"), 0)) $message .= T_('This school name has been taken for this institution type. Please choose a different name (or institution type).') . '<BR>';

  if(gr('school_gender') === '')
    $message .= T_('Please select a gender.') . '<BR>';

  if(gr('school_address1') === '')
    $message .= T_('The address can not be blank.') . '<BR>';

  if(gr('school_city') === '')
    $message .= T_('The city can not be blank.') . '<BR>';

  if(gr('school_state') === '')
    $message .= T_('The state can not be blank.') . '<BR>';

  if(gr('school_country') === '')
    $message .= T_('The country can not be blank.') . '<BR>';

  if(gr('school_phone') === '')
    $message .= T_('The phone can not be blank.') . '<BR>';

  if($first === '') $message .= T_('First name can not be blank.') . '<BR>';
  if($last === '') $message .= T_('Last name can not be blank.') . '<BR>';
  if($username === '')
    $message .= T_('Login name can not be blank.') . '<BR>';
  else
    if(mysql_result(mq('SELECT COUNT(*) FROM admins WHERE username = ' . ms($username)), 0)) $message .= T_('This login name has been taken. Please choose a different one.') . '<BR>';
  if(!array_key_exists($lang, $langs)) $message .= T_('Invalid language.') . '<BR>';
  if($password === '') $message .= T_('Password can not be blank.') . '<BR>';

  if($message === '') {
    unset($message);

    mq('INSERT INTO schools (school_name, school_name_he, school_gender, school_makeup_id, inst_id, school_number, school_address1, school_address2, school_city, school_state, school_country, school_postal, school_phone, school_era) VALUES (' . ms($school_name) . ', ' . ms(gr('school_name_he')) . ', ' . ms(gr('school_gender')) . ", $school_makeup_id, $inst_id, " . mysql_result(mq("(SELECT IFNULL(MAX(school_number), 0)+1 FROM schools schools_max)"), 0) . ', ' . ms(gr('school_address1')) . ', ' . ms(gr('school_address2')) . ', ' . ms(gr('school_city')) . ', ' . ms(gr('school_state')) . ', ' . ms(gr('school_country')) . ', ' . ms(gr('school_postal')) . ', ' . ms(gr('school_phone')) . ', 1)');
    $school_id = mysql_insert_id();

    mq('INSERT INTO admins (username, auth, password, title, first, last, lang, admin_email, admin_address1, admin_address2, admin_city, admin_state, admin_postal, admin_country, admin_phone_work, admin_phone_home, admin_phone_mobile) VALUES (' . ms($username) . ", 'inactive', " . ms($password) . ', ' . ms(gr('title')) . ', ' . ms($first) . ', ' . ms($last) . ', ' . ms($lang) . ', ' . ms(gr('admin_email')) . ', ' . ms(gr('admin_address1')) . ', ' . ms(gr('admin_address2')) . ', ' . ms(gr('admin_city')) . ', ' . ms(gr('admin_state')) . ', ' . ms(gr('admin_postal')) . ', ' . ms(gr('admin_country')) . ', ' . ms(gr('admin_phone_work')) . ', ' . ms(gr('admin_phone_home')) . ', ' . ms(gr('admin_phone_mobile')) . ')');
    $admin_id = mysql_insert_id();

    mq("INSERT INTO admin_auths (admin_id, auth, id) VALUES ($admin_id, 'school', $school_id)");

    $_POST = array('new_login' => true, 'login_username' => $username, 'login_password' => $password);
    unset($no_login);
    check_login_admin();
    header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/register_school_admin.php?school_id=' . $school_id);
  } else {
    $message .= '<BR>' . T_('Please correct these errors and try again.');
  }
}

$institution_result = mq('SELECT inst_id, inst_name FROM institutions ORDER BY inst_name');
$school_makeup_result = mq('SELECT school_makeup_id, school_makeup_name FROM school_makeups ORDER BY school_makeup_name');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=$steps[$cur_step], ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
<?=registration_HTML_head()?>
<!--Copyright Ariel Shkedi 2007-2010-->
</HEAD>
<BODY>

<?=registration_banner_new($cur_step)?>

<DIV CLASS="body register">

<DIV class="form form_<?= $align_start ?>">

<!--
<DIV STYLE="text-align: center;">
<? foreach($langs as $lang_id => $lang_name): ?>
<FORM action="register_school.php" method="post" accept-charset="UTF-8">
<DIV>
<INPUT type="submit" value="<?=es($lang_name)?>">
<INPUT type="hidden" name="lang" value="<?=es($lang_id)?>">
</DIV>
</FORM>
<? endforeach; ?>
</DIV>

<HR>
-->

<? if(isset($message) && $message): ?>
<DIV CLASS="message">
<?= $message ?><BR>
</DIV>
<? endif; ?>

<BR><BR>
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
<FORM action="register_school.php" method="post" accept-charset="UTF-8" onSubmit="if(this.elements['password'].value != this.elements['password2'].value) { alert('<?=esq(T_("Passwords don't match."))?>'); this.elements['password'].focus(); return false; } else {return true;}">
<TABLE>
<CAPTION style="font-size: 100%; font-weight: normal; padding: 40px 0px 6px; text-align: <?=$align_start?>;"><?=T_('<U>Please note</U> that a valid credit card is necessary to complete the registration.')?></CAPTION>
<TR>
  <TH colspan="2"><?=T_('Tell us about your School')?></TH>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('School Name')?><BR>
    <INPUT TYPE="text" NAME="school_name" VALUE="<?=es(gr('school_name'))?>" MAXLENGTH="255"></LABEL>
  </TD>
  <TD rowspan="2">
    <?=T_('Gender')?><BR>
      <LABEL><INPUT type="radio" name="school_gender" value="M" <?=gr('school_gender') == 'M' ? 'CHECKED' : ''?>><?=T_('Boys')?></LABEL><BR>
      <LABEL><INPUT type="radio" name="school_gender" value="F" <?=gr('school_gender') == 'F' ? 'CHECKED' : ''?>><?=T_('Girls')?></LABEL><BR>
      <LABEL><INPUT type="radio" name="school_gender" value="B" <?=gr('school_gender') == 'B' ? 'CHECKED' : ''?>><?=T_('Both')?></LABEL><BR>
    <BR>

    <?=T_('School Type')?><BR>
    <? while($row = mysql_fetch_assoc($school_makeup_result)): ?>
      <LABEL><INPUT type="radio" name="school_makeup_id" value="<?=$row['school_makeup_id']?>" <?=$row['school_makeup_id'] == gri('school_makeup_id', -1) ? 'CHECKED' : '' ?>><?=es($row['school_makeup_name'])?></LABEL><BR>
    <? endwhile; ?>
    <BR>

    <DIV style="display: none;">
    <LABEL><?=T_('Institution Type')?><BR>
    <SELECT name="inst_id">
    <? while($row = mysql_fetch_assoc($institution_result)): ?>
      <OPTION value="<?=$row['inst_id']?>" <?=$row['inst_id'] == gri('inst_id', -1) ? 'SELECTED' : '' ?>> <?=es($row['inst_name'])?>
    <? endwhile; ?>
    </SELECT></LABEL>
    </DIV>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('School Name in Hebrew Letters')?><BR>
    <SPAN style="font-size: 65%;">(<?=T_('This is how it will appear on school banner')?>)</SPAN><BR>
    <INPUT TYPE="text" NAME="school_name_he" VALUE="<?=es(gr('school_name_he'))?>" MAXLENGTH="255"></LABEL><BR>
    <?=T_("Don't have Hebrew?")?><BR>
    <A HREF="http://www.mikledet.com/" target="_blank">www.mikledet.com</A>
  </TD>
</TR>

<TR>
  <TH colspan="2"><?=T_('School Address')?></TH>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Address 1')?><BR>
    <INPUT TYPE="text" NAME="school_address1" VALUE="<?=es(gr('school_address1'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Address 2')?><BR>
    <INPUT TYPE="text" NAME="school_address2" VALUE="<?=es(gr('school_address2'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('City')?><BR>
    <INPUT TYPE="text" NAME="school_city" VALUE="<?=es(gr('school_city'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('State')?><BR>
    <INPUT TYPE="text" NAME="school_state" VALUE="<?=es(gr('school_state'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Postal code')?><BR>
    <INPUT TYPE="text" NAME="school_postal" VALUE="<?=es(gr('school_postal'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Country')?><BR>
    <INPUT TYPE="text" NAME="school_country" VALUE="<?=es(gr('school_country'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Phone')?><BR>
    <INPUT TYPE="text" NAME="school_phone" VALUE="<?=es(gr('school_phone'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>

<TR>
  <TH colspan="2"><?=T_('Who is the principal of this school?')?></TH>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Title')?><BR>
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
    <LABEL><?=T_('First Name')?><BR>
    <INPUT TYPE="text" NAME="first" VALUE="<?=es(gr('first'))?>" MAXLENGTH="128">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Last Name')?><BR>
    <INPUT TYPE="text" NAME="last" VALUE="<?=es(gr('last'))?>" MAXLENGTH="128">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Mobile Phone')?><BR>
    <INPUT TYPE="text" NAME="admin_phone_mobile" VALUE="<?=es(gr('admin_phone_mobile'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Email Address')?><BR>
    <INPUT TYPE="text" NAME="admin_email" VALUE="<?=es(gr('admin_email'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Work Phone (+ext)')?><BR>
    <INPUT TYPE="text" NAME="admin_phone_work" VALUE="<?=es(gr('admin_phone_work'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
  <TD>
    <LABEL><?=T_('Home Phone')?><BR>
    <INPUT TYPE="text" NAME="admin_phone_home" VALUE="<?=es(gr('admin_phone_home'))?>" MAXLENGTH="255">
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
  <TH colspan="2"><?=T_('Principal Login')?></TH>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Login Name')?><BR>
    <INPUT TYPE="text" NAME="username" VALUE="<?=es(gr('username'))?>" MAXLENGTH="64">
    </LABEL>
  </TD>
  <TD style="display: none;">
    <LABEL><?=T_('Language')?><BR>
    <SELECT NAME="lang" ID="lang">
      <?
        foreach($langs as $lang_id => $lang_name) {
          echo "<OPTION value='$lang_id'" . ($lang_id == $lang ? ' SELECTED' : '') . ">$lang_name";
        }
      ?>
    </SELECT>
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Password')?><BR>
    <INPUT TYPE="text" NAME="password" VALUE="<?=es(gr('password'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
<TR>
  <TD>
    <LABEL><?=T_('Re-enter Password')?><BR>
    <INPUT TYPE="text" NAME="password2" VALUE="<?=es(gr('password2'))?>" MAXLENGTH="255">
    </LABEL>
  </TD>
</TR>
</TABLE>

<P style="text-align: <?=$align_end?>;">
<INPUT TYPE="submit" VALUE="<?=T_('Register School &amp; Continue')?>">
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
