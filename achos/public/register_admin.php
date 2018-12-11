<?$no_login = 1;?>
<? require('header.php'); ?>
<?
$invitation_id = gr('invitation_id');
$type = gr('type');

if($invitation_id !== '') {
  $invitation = mysql_fetch_assoc(mq('SELECT invitation_id, id, auth, role_id FROM invitations WHERE invitation_id = ' . ms($invitation_id)));
  if(!$invitation)
    $invitation_id = '';
  else
    $invitation_id = $invitation['invitation_id'];
}

if($type !== '' && $invitation_id !== '') {

  if($invitation['role_id'] == -1) {
    $row = mysql_fetch_assoc(mq('SELECT role_id FROM roles WHERE role_auth = ' . ms($invitation['auth']) . ' AND role_id = ' . gri('role_id', -2)));
    $role_id = $row ? $row['role_id'] : 'NULL';
  } else {
    $role_id = is_null($invitation['role_id']) ? 'NULL' : $invitation['role_id'];
  }
if($type == 'login2') {
  $admin = mysql_fetch_assoc(mq('SELECT admin_id FROM admins WHERE username = ' . ms(gr('username')) . ' AND password = ' . ms(gr('password'))));
  if(!$admin) {
    $message = T_('Username or password was not correct. Please try again.');
    $type = 'login';
  } else {
    mq("INSERT IGNORE INTO admin_auths (admin_id, auth, id, role_id) VALUES ({$admin['admin_id']}, " . ms($invitation['auth']) . ", {$invitation['id']}, $role_id)");
    mq('DELETE FROM invitations WHERE invitation_id = ' . ms($invitation_id));
    $message = sprintf(T_('The invitation has been processed. Continue to your %sadmin%s page.'), '<A HREF="admin.php">', '</A>');
  }

} elseif($invitation_id !== '' && $type == 'register2') {

  $username = gr('username');
  $lang = gr('lang');
  $first = gr('first');
  $last = gr('last');
  $password = gr('password');

  $message = '';
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

    mq('INSERT INTO admins (username, auth, password, title, first, last, lang, admin_email, admin_address1, admin_address2, admin_city, admin_state, admin_postal, admin_country, admin_phone_work, admin_phone_home, admin_phone_mobile) VALUES (' . ms($username) . ", '', " . ms($password) . ', ' . ms(gr('title')) . ', ' . ms($first) . ', ' . ms($last) . ', ' . ms($lang) . ', ' . ms(gr('admin_email')) . ', ' . ms(gr('admin_address1')) . ', ' . ms(gr('admin_address2')) . ', ' . ms(gr('admin_city')) . ', ' . ms(gr('admin_state')) . ', ' . ms(gr('admin_postal')) . ', ' . ms(gr('admin_country')) . ', ' . ms(gr('admin_phone_work')) . ', ' . ms(gr('admin_phone_home')) . ', ' . ms(gr('admin_phone_mobile')) . ')');
    $admin_id = mysql_insert_id();

    mq("INSERT INTO admin_auths (admin_id, auth, id, role_id) VALUES ($admin_id, " . ms($invitation['auth']) . ", {$invitation['id']}, $role_id)");
    mq('DELETE FROM invitations WHERE invitation_id = ' . ms($invitation_id));

    $login_message = T_('You are now registered, and the invitation has been processed. Please login to continue.');
    $login_query_string = 'admin.php';
    $admin_auth = true;
    $_POST = array();
    unset($no_login);
    require('login.php');
    exit;
  } else {
    $type = 'register';
    $message .= '<BR>' . T_('Please correct these errors and try again.');
  }
}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Accept Invitation'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
<!--Copyright Ariel Shkedi 2007-2010-->
</HEAD>
<BODY style="margin-top: 1em;">
<DIV CLASS="body register">

<DIV class="form form_<?= $align_start ?>">

<!--
<DIV STYLE="text-align: center;">
<? foreach($langs as $lang_id => $lang_name): ?>
<FORM action="register_admin.php" method="post" accept-charset="UTF-8">
<DIV>
<INPUT type="submit" value="<?=es($lang_name)?>">
<INPUT type="hidden" name="lang" value="<?=es($lang_id)?>">
<INPUT type="hidden" name="invitation_id" value="<?=$invitation_id?>">
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

<? if($invitation_id === ''): ?>
<FORM action="register_admin.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Enter invitation code')?>: <INPUT type="text" name="invitation_id" maxlength="19" size="20"></LABEL><BR>
<INPUT type="submit" value="<?=T_('Continue')?>">
</P>
</FORM>
<? else: ?>

<? if($type === ''): ?>

<H2 style="text-align: center;"><?=T_('Accept Invitation')?></H2>

<P style="text-align: center;">
<?=sprintf(T_('Accept an invitation for %s %s.'), es(authTypeToName($invitation['auth'], false, false)), es(authToName($invitation['auth'], $invitation['id'])));?>
</P>
<P style="text-align: center;">
<?=T_('Do you already have an account with Chayolei Tzivos Hashem?')?>
</P>
<P style="text-align: center;">
<A HREF="register_admin.php?invitation_id=<?=$invitation_id?>&amp;type=login"><?=T_('Yes. I have an existing account.')?></A>
&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
<A HREF="register_admin.php?invitation_id=<?=$invitation_id?>&amp;type=register"><?=T_('No, I would like to register for an account.')?></A>
</P>

<? elseif($type=='login'): ?>

<H2 style="text-align: center;"><?=T_('Accept Invitation')?></H2>

<P style="text-align: center;">
<?=sprintf(T_('Accept an invitation for %s %s.'), es(authTypeToName($invitation['auth'], false, false)), es(authToName($invitation['auth'], $invitation['id'])));?>
</P>

<FORM action="register_admin.php" method="post" accept-charset="UTF-8">
<TABLE>
<TR>
<TH><LABEL for="username"><?=T_('Username')?>:</LABEL></TH>
<TD><INPUT type="text" name="username" id="username" size=64 maxlength=64></TD>
</TR>
<TR>
<TH><LABEL for="password"><?=T_('Password')?>:</LABEL></TH>
<TD><INPUT type="password" name="password" id="password" size=64 maxlength=64 value=""></TD>
</TR>
<? if($invitation['role_id'] == -1): ?>
<TR>
  <TH><LABEL for="role_id"><?=sprintf(T_('What is your relationship to the %s?'), es(authTypeToName($invitation['auth'], false, false)))?></LABEL></TH>
  <TD>
    <SELECT name="role_id" id="role_id">
      <OPTION VALUE="=2">Other
      <? $result = mq('SELECT role_id, role_name FROM roles WHERE role_auth = ' . ms($invitation['auth']) . ' ORDER BY role_name'); ?>
      <? while($row = mysql_fetch_assoc($result)): ?>
        <OPTION VALUE="<?=$row['role_id']?>" <?=$row['role_id'] == gri('role_id') ? 'SELECTED' : '' ?>><?=es($row['role_name'])?></OPTION>
      <? endwhile; ?>
    </SELECT><BR>
  </TD>
</TR>
<? endif; ?>
<TR>
  <TH>
    <INPUT type="hidden" name="type" value="login2">
    <INPUT type="hidden" name="invitation_id" value="<?=$invitation_id?>">
    <INPUT TYPE="submit" VALUE="<?=T_('Continue')?>">
  </TH>
</TR>
</TABLE>
</FORM>

<?elseif($type == 'register'):?>

<H2 style="text-align: center;"><?=T_('Accept Invitation')?></H2>

<P style="text-align: center;">
<?=sprintf(T_('Accept an invitation for %s %s.'), es(authTypeToName($invitation['auth'], false, false)), es(authToName($invitation['auth'], $invitation['id'])));?>
</P>

<FORM action="register_admin.php" method="post" accept-charset="UTF-8" onSubmit="if(this.elements['password'].value != this.elements['password2'].value) { alert('<?=esq(T_("Passwords don't match."))?>'); this.elements['password'].focus(); return false; } else {return true;}">
<TABLE>
<? if($invitation['role_id'] == -1): ?>
<TR>
  <TH><LABEL for="role_id"><?=sprintf(T_('What is your relationship to the %s?'), es(authTypeToName($invitation['auth'], false, false)))?></LABEL></TH>
  <TD>
    <SELECT name="role_id" id="role_id">
      <OPTION VALUE="=2">Other
      <? $result = mq('SELECT role_id, role_name FROM roles WHERE role_auth = ' . ms($invitation['auth']) . ' ORDER BY role_name'); ?>
      <? while($row = mysql_fetch_assoc($result)): ?>
        <OPTION VALUE="<?=$row['role_id']?>" <?=$row['role_id'] == gri('role_id') ? 'SELECTED' : '' ?>><?=es($row['role_name'])?></OPTION>
      <? endwhile; ?>
    </SELECT><BR>
  </TD>
</TR>
<? endif; ?>
<TR>
  <TH><LABEL for="title"><?=T_('Title')?>:</LABEL></TH>
  <TD>
    <SELECT name="title" id="title">
      <?foreach(mysql_enum_values('admins', 'title') as $title):?>
        <OPTION <?=$title == gr('title') ? 'SELECTED' : ''?>><?=es($title)?></OPTION>
      <?endforeach;?>
    </SELECT>
  </TD>
</TR>
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
<TR style="display: none;">
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
<TR>
  <TH><LABEL for="admin_email"><?=T_('Email Address')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="admin_email" ID="admin_email" VALUE="<?=es(gr('admin_email'))?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="admin_address1"><?=T_('Address 1')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="admin_address1" ID="admin_address1" VALUE="<?=es(gr('admin_address1'))?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="admin_address2"><?=T_('Address 2')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="admin_address2" ID="admin_address2" VALUE="<?=es(gr('admin_address2'))?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="admin_city"><?=T_('City')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="admin_city" ID="admin_city" VALUE="<?=es(gr('admin_city'))?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="admin_state"><?=T_('State')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="admin_state" ID="admin_state" VALUE="<?=es(gr('admin_state'))?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="admin_postal"><?=T_('Postal code')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="admin_postal" ID="admin_postal" VALUE="<?=es(gr('admin_postal'))?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="admin_country"><?=T_('Country')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="admin_country" ID="admin_country" VALUE="<?=es(gr('admin_country'))?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="admin_phone_work"><?=T_('Work Phone (+ext)')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="admin_phone_work" ID="admin_phone_work" VALUE="<?=es(gr('admin_phone_work'))?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="admin_phone_home"><?=T_('Home Phone')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="admin_phone_home" ID="admin_phone_home" VALUE="<?=es(gr('admin_phone_home'))?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH><LABEL for="admin_phone_mobile"><?=T_('Mobile Phone')?>:</LABEL></TH>
  <TD><INPUT TYPE="text" NAME="admin_phone_mobile" ID="admin_phone_mobile" VALUE="<?=es(gr('admin_phone_mobile'))?>" MAXLENGTH="255"></TD>
</TR>
<TR>
  <TH>
    <INPUT type="hidden" name="type" value="register2">
    <INPUT type="hidden" name="invitation_id" value="<?=$invitation_id?>">
    <INPUT TYPE="submit" VALUE="<?=T_('Register')?>">
  </TH>
</TR>
</TABLE>

</FORM>

<?endif;?>
<?endif;?>

</DIV>
</DIV>
</BODY>
</HTML>
