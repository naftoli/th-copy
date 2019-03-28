<?$no_login = 1;?>
<? require('header.php'); ?>
<?
if(!is_null($username = gr('username', NULL))) {
  $lang = gr('lang');
  $school_id = gri('school_id', -1);
  $school_type_id = gri('school_type_id', -1);
  $first = gr('first');
  $last = gr('last');
  $password = gr('password');

  $message = '';
  if($first === '') $message .= T_('First name can not be blank.') . '<BR>';
  if($last === '') $message .= T_('Last name can not be blank.') . '<BR>';
  if($username === '')
    $message .= T_('Login name can not be blank.') . '<BR>';
  else
    if(mysql_result(mq('SELECT COUNT(*) FROM users WHERE username = ' . ms($username)), 0)) $message .= T_('This login name has been taken. Please choose a different one.') . '<BR>';
  if(!array_key_exists($lang, $langs)) $message .= T_('Invalid language.') . '<BR>';
  if($password === '') $message .= T_('Password can not be blank.') . '<BR>';
  if($school_id == -1)
    $message .= T_('Please pick an affiliation.') . '<BR>';
  else
    if(!mysql_result(mq("SELECT COUNT(*) FROM schools WHERE school_id = $school_id"), 0)) $message .= T_('Invalid affiliation.') . '<BR>';
  if($school_type_id == -1)
    $message .= T_('Please pick a background.') . '<BR>';
  else
    if(!mysql_result(mq("SELECT COUNT(*) FROM school_types WHERE school_type_setting = 'personal_only' AND school_type_id = $school_type_id"), 0)) $message .= T_('Invalid background.') . '<BR>';

  if($message === '') {
    unset($message);

    if(mysql_result(mq("SELECT GET_LOCK('users', 30)"),0) != 1) trigger_error('could not get lock', E_USER_ERROR);
    $count = 0;
    do {
      if($count++ > 100000) trigger_error('could not get ID', E_USER_ERROR);
      $user_code = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
    } while(mysql_result(mq("SELECT COUNT(*) FROM users WHERE user_code = $user_code"),0) != 0);

    mq('INSERT INTO users (username, password, first, last, lang, user_code, school_type_id, school_id, class_id, team_id, user_serial, user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone) VALUES (' . ms($username) . ', ' . ms($password) . ', ' . ms($first) . ', ' . ms($last) . ', ' . ms($lang) . ", $user_code, $school_type_id, $school_id, NULL, NULL, " . mysql_result(mq("(SELECT IFNULL(MAX(user_serial), 0)+1 FROM users users_max)"), 0) . ', ' . ms(gr('user_address1')) . ', ' . ms(gr('user_address2')) . ', ' . ms(gr('user_city')) . ', ' . ms(gr('user_state')) . ', ' . ms(gr('user_postal')) . ', ' . ms(gr('user_country')). ', ' . ms(gr('user_phone')) . ')');
    $new_user_id = mysql_result(mq("SELECT LAST_INSERT_ID()"), 0);

    mq("SELECT RELEASE_LOCK('users')");

    mq("INSERT IGNORE INTO rank_marks (rank_ord, user_id, date_promoted) SELECT rank_ord, $new_user_id user_id, " . unixtojd() . ' date_promoted FROM ranks WHERE medals_required <= 0');
    $login_message = T_('You are now registered. Please login to continue.');
    $login_query_string = './';
    $_POST = array();
    unset($no_login);
    require('login.php');
    exit;
  } else {
    $message .= '<BR>' . T_('Please correct these errors and try again.');
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Register'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
<!--Copyright Ariel Shkedi 2007-2010-->
</HEAD>
<BODY>
<? include('banner2.php'); ?>
<DIV CLASS="body register">

<DIV class="form form_<?= $align_start ?>">

<DIV STYLE="text-align: center;">

<br><br><br><br><br>
<h2>This page is no longer in use. Please see new <a href="https://www.mashpia.com">Site</a></h2>
<br><br><br><br><br>
<br><br><br><br>
<br><br><br><br>
<br><br><br><br>

<? foreach($langs as $lang_id => $lang_name): ?>
<FORM action="register.php" method="post" accept-charset="UTF-8">
<DIV>
<INPUT type="submit" value="<?=es($lang_name)?>">
<INPUT type="hidden" name="lang" value="<?=es($lang_id)?>">
</DIV>
</FORM>
<? endforeach; ?>
</DIV>

<HR>

<? if(isset($message) && $message): ?>
<DIV CLASS="message">
<?= $message ?><BR>
</DIV>
<? endif; ?>

<H2 style="text-align: center;"><?=T_('Register for a new account')?></H2>

<FORM action="register.php" method="post" accept-charset="UTF-8" onSubmit="if(this.elements['password'].value != this.elements['password2'].value) { alert('<?=esq(T_("Passwords don't match."))?>'); this.elements['password'].focus(); return false; } else {return true;}">
<TABLE>
<TR>
  <TH><LABEL for="first"><?=T_('First Name')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="first" ID="first" VALUE="<?=gr('first')?>" MAXLENGTH="128"></TD>
</TR>
<TR>
  <TH><LABEL for="last"><?=T_('Last Name')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="last" ID="last" VALUE="<?=gr('last')?>" MAXLENGTH="128"></TD>
</TR>
<TR>
  <TH><LABEL for="username"><?=T_('Login Name')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="username" ID="username" VALUE="<?=gr('username')?>" MAXLENGTH="64"></TD>
</TR>
<TR>
  <TH><LABEL for="lang"><?=T_('Language')?>:</LABEL></TH>
  <TD>
    <SELECT NAME="lang" ID="lang">
      <?
        foreach($langs as $lang_id => $lang_name) {
          echo "<OPTION value='$lang_id'" . ($lang_id == $lang ? ' SELECTED' : '') . ">$lang_name";
        }
      ?>
    </SELECT>
  </TD>
</TR>
<TR>
  <TH><LABEL for="password"><?=T_('Password')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="password" ID="password" VALUE="<?=gr('password')?>" MAXLENGTH="64"></TD>
</TR>
<TR>
  <TH><LABEL for="password2"><?=T_('Password-again')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="password2" ID="password2" VALUE="<?=gr('password2')?>" MAXLENGTH="64"></TD>
</TR>
<? $result = mq('SELECT school_name, school_id, inst_name, school_country, school_state, school_city FROM schools JOIN institutions USING (inst_id) ORDER BY inst_name, inst_id, school_name, school_id'); ?>
<TR>
  <TH><LABEL for="school_id"><?=T_('Affiliation')?>:</LABEL></TH>
  <TD>
    <SELECT NAME="school_id" ID="school_id">
      <OPTION value="-1">&lt;<?=T_('Please pick an affiliation')?>&gt;
      <? while($row = mysql_fetch_assoc($result)): ?>
        <OPTION value="<?=$row['school_id']?>" <?=gri('school_id') == $row['school_id'] ? 'SELECTED' : ''?>><?=es($row['inst_name'])?> - <?=es($row['school_name'])?>; <?=es($row['school_city'])?>, <?=es($row['school_state'])?> <?=es($row['school_country'])?>
      <? endwhile; ?>
    </SELECT>
  </TD>
</TR>
<?$result = mq("SELECT school_type_id, school_type_name FROM school_types WHERE school_type_setting = 'personal_only' ORDER BY school_type_name");?>
<TR>
  <TH><LABEL for="school_id"><?=T_('Background')?>:</LABEL></TH>
  <TD>
    <SELECT NAME="school_type_id" ID="school_type_id">
      <OPTION value="-1">&lt;<?=T_('Please pick a background')?>&gt;
      <? while($row = mysql_fetch_assoc($result)): ?>
        <OPTION VALUE="<?=$row['school_type_id']?>" <?=gri('school_type_id') == $row['school_type_id'] ? 'SELECTED' : ''?>><?=es($row['school_type_name'])?></OPTION>
      <? endwhile; ?>
    </SELECT>
  </TD>
</TR>
<TR>
  <TH><LABEL for="user_address1"><?=T_('Address 1')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="user_address1" ID="user_address1" VALUE="<?=gr('user_address1')?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="user_address2"><?=T_('Address 2')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="user_address2" ID="user_address2" VALUE="<?=gr('user_address2')?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="user_city"><?=T_('City')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="user_city" ID="user_city" VALUE="<?=gr('user_city')?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="user_state"><?=T_('State')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="user_state" ID="user_state" VALUE="<?=gr('user_state')?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="user_postal"><?=T_('Postal code')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="user_postal" ID="user_postal" VALUE="<?=gr('user_postal')?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="user_country"><?=T_('Country')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="user_country" ID="user_country" VALUE="<?=gr('user_country')?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="user_phone"><?=T_('Phone')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="user_phone" ID="user_phone" VALUE="<?=gr('user_phone')?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><INPUT TYPE="submit" VALUE="<?=T_('Register')?>"></TH>
</TR>
</TABLE>

</FORM>

</DIV>
</DIV>
</BODY>
</HTML>
