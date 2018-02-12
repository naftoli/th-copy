<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
check_id_access();
$school_id = gri('school_id', -1);
$ui_type = 'school';


if($admin_user['auth'] == 'super' && ($action = gr('action'))) switch($action) {
  case 'add':
    $edit_row = mysql_fetch_assoc(mq("SELECT -1 admin_id, '' username, '' auth, '' admin_email, '' title, '' first, '' last, '' lang, '' admin_address1, '' admin_address2, '' admin_city, '' admin_state, '' admin_postal, '' admin_country, '' admin_phone_work, '' admin_phone_home, '' admin_phone_mobile"));
    break;

  case 'add2':
    $username = gr('username');

    if(mysql_result(mq("SELECT GET_LOCK('admins', 30)"),0) != 1) trigger_error('could not get lock', E_USER_ERROR);

    if($username === '') {
      $username = mb_strtolower(mb_substr(gr('first'),0,1)) . preg_replace('/\P{L}/u', '', mb_strtolower(gr('last')));
      if($username === '') {
        $username = 'admin_';
        $count = '0';
      } else {
        $count = '';
      }
      while(mysql_num_rows(mq('SELECT username FROM admins WHERE username = ' . ms($username.$count)))) $count++;
      $username .= $count;
    }

    $result = mq('SELECT 1 FROM admins WHERE username = ' . ms($username));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add admin, this username is already used.');
      $edit_row = mysql_fetch_assoc(mq('SELECT -1 admin_id, ' . ms(gr('username')) . ' username, ' . ms(gr('auth')) . ' auth, ' . ms(gr('email')) . ' admin_email, ' . ms(gr('title')) . ' title, ' . ms(gr('first')) . ' first, ' . ms(gr('last')) . ' last, ' . ms(gr('lang')) . ' lang, ' . ms(gr('address1')) . ' admin_address1, ' . ms(gr('address2')) . ' admin_address2, ' . ms(gr('city')) . ' admin_city, ' . ms(gr('state')) . ' admin_state, ' . ms(gr('postal')) . ' admin_postal, ' . ms(gr('country')) . ' admin_country, ' . ms(gr('phone_work')) . ' admin_phone_work, ' . ms(gr('phone_home')) . ' admin_phone_home, ' . ms(gr('phone_mobile')) . ' admin_phone_mobile'));

      $action = 'add';
    } else {
      mq('INSERT INTO admins SET username = ' . ms($username) . ', auth = ' . ms(gr('auth')) . ', admin_email = ' . ms(gr('email')) . ', title = ' . ms(gr('title')) . ', first = ' . ms(gr('first')) . ', last = ' . ms(gr('last')) . ', lang = ' . ms(gr('lang')) . ', admin_address1 = ' . ms(gr('address1')) . ', admin_address2 = ' . ms(gr('address2')) . ', admin_city = ' . ms(gr('city')) . ', admin_state = ' . ms(gr('state')) . ', admin_postal = ' . ms(gr('postal')) . ', admin_country = ' . ms(gr('country')) . ', admin_phone_work = ' . ms(gr('phone_work')) . ', admin_phone_home = ' . ms(gr('phone_home')) . ', admin_phone_mobile = ' . ms(gr('phone_mobile')) . ', password = ' . ms(gr('password')));
      $new_admin_id = mysql_result(mq("SELECT LAST_INSERT_ID()"), 0);
      $message = T_('Admin added') . "<BR><A HREF='admin_admin_auth.php?admin_id=$new_admin_id'>" . T_('Manage Authorizations for this new Admin'). '</A>';
    }

    mq("SELECT RELEASE_LOCK('admins')");

    break;

  case 'delete':
    mq('DELETE FROM admins WHERE admin_id = ' . gri('admin_id', -1));
    mq('DELETE FROM admin_auths WHERE admin_id = ' . gri('admin_id', -1));
    $message = T_('Admin deleted');
    break;

  case 'edit':
    $edit_row = mysql_fetch_assoc(mq('SELECT admin_id, username, auth, admin_email, title, first, last, lang, admin_address1, admin_address2, admin_city, admin_state, admin_postal, admin_country, admin_phone_work, admin_phone_home, admin_phone_mobile FROM admins WHERE admin_id = ' . gri('admin_id')));
    break;

  case 'edit2':
    $admin_id = gri('admin_id', -1);
    $result = mq("SELECT 1 FROM admins WHERE admin_id != $admin_id AND username = " . ms(gr('username')));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit admin, this username is already used.');
      $edit_row = mysql_fetch_assoc(mq("SELECT $admin_id admin_id, " . ms(gr('username')) . ' username, ' . ms(gr('auth')) . ' auth, ' . ms(gr('email')) . ' admin_email, ' . ms(gr('title')) . ' title, ' . ms(gr('first')) . ' first, ' . ms(gr('last')) . ' last, ' . ms(gr('lang')) . ' lang, ' . ms(gr('address1')) . ' admin_address1, ' . ms(gr('address2')) . ' admin_address2, ' . ms(gr('city')) . ' admin_city, ' . ms(gr('state')) . ' admin_state, ' . ms(gr('postal')) . ' admin_postal, ' . ms(gr('country')) . ' admin_country, ' . ms(gr('phone_work')) . ' admin_phone_work, ' . ms(gr('phone_home')) . ' admin_phone_home, ' . ms(gr('phone_mobile')) . ' admin_phone_mobile, ' . ms(gr('password')) . ' password'));
      $action = 'edit';
    } else {
      mq('UPDATE admins SET username = ' . ms(gr('username')) . ', auth = ' . ms(gr('auth')) . ', admin_email = ' . ms(gr('email')) . ', title = ' . ms(gr('title')) . ', first = ' . ms(gr('first')) . ', last = ' . ms(gr('last')) . ', lang = ' . ms(gr('lang')) . ', admin_address1 = ' . ms(gr('address1')) . ', admin_address2 = ' . ms(gr('address2')) . ', admin_city = ' . ms(gr('city')) . ', admin_state = ' . ms(gr('state')) . ', admin_postal = ' . ms(gr('postal')) . ', admin_country = ' . ms(gr('country')) . ', admin_phone_work = ' . ms(gr('phone_work')) . ', admin_phone_home = ' . ms(gr('phone_home')) . ', admin_phone_mobile = ' . ms(gr('phone_mobile')) . (gr('password') ? ', password = ' . ms(gr('password')) : '') . " WHERE admin_id = $admin_id");
      $message = T_('Admin edited');
    }
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Manage Admins'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Manage Admins')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<?if(isset($edit_row)):?>
<P><A HREF="admin_admin.php"><?=T_('Cancel')?></A></P>

<FORM action="admin_admin.php" method="post" accept-charset="UTF-8" onSubmit="<?=$action != 'edit' ? "if(this.elements['password'].value == '') { alert('" . esq(T_('Please enter a password for this admin.')) . "'); } else " : ''?> { if(this.elements['password'].value != this.elements['password2'].value) { alert('<?=esq(T_("Passwords don't match."))?>'); } else { return true; } } this.elements['password'].focus(); return false;">
<P CLASS="rows">
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="admin_id" value="<?=$edit_row['admin_id']?>">

<LABEL><?=T_('Username')?><BR><INPUT TYPE="text" NAME="username" VALUE="<?=es($edit_row['username'])?>" MAXLENGTH="64"></LABEL> (<?=T_('Leave blank to generate a username from the First initial and the Last name')?>)<BR>
<BR>
<?=$action=='edit' ? T_('Enter a new password to change it') . '<BR>' : ''?>
<LABEL><?=T_('Password')?><BR><INPUT TYPE="text" NAME="password" VALUE="" MAXLENGTH="64"></LABEL><BR>
<LABEL><?=T_('Password-again')?><BR><INPUT TYPE="text" NAME="password2" VALUE="" MAXLENGTH="64"></LABEL><BR>
<BR>
<LABEL><?=T_('Auth')?><BR>
    <SELECT name="auth">
      <?foreach(mysql_enum_values('admins', 'auth') as $auth):?>
        <OPTION <?=$auth == $edit_row['auth'] ? 'SELECTED' : ''?>><?=es($auth)?></OPTION>
      <?endforeach;?>
    </SELECT>
</LABEL><BR>
<LABEL><?=T_('Title')?><BR>
    <SELECT name="title">
      <?foreach(mysql_enum_values('admins', 'title') as $title):?>
        <OPTION <?=$title == $edit_row['title'] ? 'SELECTED' : ''?>><?=es($title)?></OPTION>
      <?endforeach;?>
    </SELECT>
</LABEL><BR>
<LABEL><?=T_('First Name')?><BR><INPUT TYPE="text" NAME="first" VALUE="<?=es($edit_row['first'])?>" MAXLENGTH="128"></LABEL><BR>
<LABEL><?=T_('Last Name')?><BR><INPUT TYPE="text" NAME="last" VALUE="<?=es($edit_row['last'])?>" MAXLENGTH="128"></LABEL><BR>
<LABEL><?=T_('Email')?><BR><INPUT TYPE="text" NAME="email" VALUE="<?=es($edit_row['admin_email'])?>" MAXLENGTH="255"></LABEL><BR>
<LABEL><?=T_('Language')?><BR>
    <SELECT NAME="lang">
      <?
        foreach($langs as $lang_id => $lang_name) {
          echo "<OPTION value='$lang_id'" . ($lang_id == $edit_row['lang'] ? ' SELECTED' : '') . ">" . es($lang_name);
        }
      ?>
    </SELECT></LABEL><BR>
<LABEL><?=T_('Address 1')?><BR><INPUT type="text" name="address1" value="<?=es($edit_row['admin_address1'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Address 2')?><BR><INPUT type="text" name="address2" value="<?=es($edit_row['admin_address2'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('City')?><BR><INPUT type="text" name="city" value="<?=es($edit_row['admin_city'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('State/Province')?><BR><INPUT type="text" name="state" value="<?=es($edit_row['admin_state'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Zip/Postal code')?><BR><INPUT type="text" name="postal" value="<?=es($edit_row['admin_postal'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Country')?><BR><INPUT type="text" name="country" value="<?=es($edit_row['admin_country'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Phone - Work')?><BR><INPUT type="text" name="phone_work" value="<?=es($edit_row['admin_phone_work'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Phone - Home')?><BR><INPUT type="text" name="phone_home" value="<?=es($edit_row['admin_phone_home'])?>" maxlength=255></LABEL><BR>
<LABEL><?=T_('Phone - Mobile')?><BR><INPUT type="text" name="phone_mobile" value="<?=es($edit_row['admin_phone_mobile'])?>" maxlength=255></LABEL><BR>
<INPUT class="submit" type="submit" value="<?=$action=='edit' ? T_('Edit') : T_('Add new')?>">
</P>
</FORM>

<?else:?>

<? if($admin_user['auth'] == 'super'): ?>
<A HREF="admin_admin.php?action=add"><?=T_('Directly Add a new admin')?></A><BR>
<BR>
<? endif; ?>
<FORM action="admin_admin_auth.php" method="get" accept-charset="UTF-8">
<H4><?=T_('Invite a new admin by email')?>:</H4>
<P>
<LABEL><?=T_('Email Address')?>: <INPUT type="text" name="email" maxlength="255"></LABEL>
<INPUT type="submit" value="Invite&gt;&gt;">
</P>
</FORM>
<FORM action="admin_admin_auth.php" method="get" accept-charset="UTF-8">
<H4><?=T_('Manage Authorizations by username')?>:</H4>
<P>
<LABEL><?=T_('Username')?>: <INPUT type="text" name="username" maxlength="255"></LABEL>
<INPUT type="submit" value="Manage&gt;&gt;">
</P>
</FORM>
<BR>

<? $include = gra('include', array('user')); ?>
<FORM action="admin_admin.php" method="get" accept-charset="UTF-8">
<P>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<LABEL><?=T_('Show Admins and Invitees associated with institution')?>:<BR>
<SELECT name="school_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<?endif;?>
<?=T_("Don't include Admins and Invitees who only have")?>:
<INPUT type="hidden" name="include[]" value="">
<LABEL><INPUT type="checkbox" name="include[]" value="school" <?=in_array('school', $include) ? 'CHECKED' : ''?>><?=T_('School Authorizations')?></LABEL>
<LABEL><INPUT type="checkbox" name="include[]" value="class" <?=in_array('class', $include) ? 'CHECKED' : ''?>><?=T_('Class Authorizations')?></LABEL>
<LABEL><INPUT type="checkbox" name="include[]" value="team" <?=in_array('team', $include) ? 'CHECKED' : ''?>><?=T_('Team Authorizations')?></LABEL>
<LABEL><INPUT type="checkbox" name="include[]" value="user" <?=in_array('user', $include) ? 'CHECKED' : ''?>><?=T_('User Authorizations')?></LABEL>
<BR>
<INPUT class="submit" type="submit" value="<?=T_('Show')?>">
</P>
</FORM>

<? $school_ids = $school_id == -1 ? implode(',', $admin_user['auths']['school']) : $school_id; ?>

<?
$having = '';
foreach(array('school', 'class', 'team', 'user') as $type) {
  if(!in_array($type, $include)) $having .= "auths_$type != 0 OR ";
}
$having .= '(';
foreach(array('school', 'class', 'team', 'user') as $type) {
  if(in_array($type, $include)) $having .= "auths_$type = 0 AND ";
}
$having .= 'true)';

$result = mq("
SELECT   admins.admin_id, username, admins.auth, first, last, admin_city, admin_state,
         COUNT(IF(admin_auths.auth = 'school', id, NULL)) auths_school,
         COUNT(IF(admin_auths.auth = 'class', id, NULL)) auths_class,
         COUNT(IF(admin_auths.auth = 'team', id, NULL)) auths_team,
         COUNT(IF(admin_auths.auth = 'user', id, NULL)) auths_user
FROM     admins LEFT JOIN admin_auths USING (admin_id)" .
($admin_user['auth'] == 'super' && $school_id == -1 ? '' : "
WHERE    admin_auths.auth = 'school' AND id IN ($school_ids) OR
         admin_auths.auth = 'class' AND id IN (SELECT class_id FROM classes WHERE school_id IN ($school_ids)) OR
         admin_auths.auth = 'team' AND id IN (SELECT team_id FROM teams WHERE school_id IN ($school_ids)) OR
         admin_auths.auth = 'user' AND id IN (SELECT user_id FROM users WHERE school_id IN ($school_ids))"
) . "
GROUP BY admin_id
HAVING $having
ORDER BY admins.auth DESC,
         username
");
?>
<DIV class="infobox" style="text-align:center;">
	Here you will a list of people with admin access level
</DIV>
<TABLE CLASS="list" style="font-size:12px; width:100%;">
<!--<CAPTION><?=T_('Admins')?></CAPTION>-->
<THEAD>
<TR>
  <TH><?=T_('Auth')?></TH>
  <TH><?=T_('Username')?></TH>
  <TH><?=T_('Name')?></TH>
  <!--<TH><?=T_('City, State')?></TH>-->
  <TH><?=$school_id == -1 ? T_('Schools') : T_('School Admin') ?></TH>
  <TH><?=T_('Classes')?></TH>
  <!-- <TH><?=T_('Teams')?></TH> -->
  <TH><?=T_('Users')?></TH>
  <TH></TH>
<? if($admin_user['auth'] == 'super'): ?>
  <!--<TH></TH>
  <TH></TH>-->
<? endif; ?>
</TR>
</THEAD>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['auth'])?></TD>
    <TD><?=es($row['username'])?></TD>
    <TD><?=es($row['first'])?> <?=es($row['last'])?></TD>
    <!--<TD><?=es($row['admin_city'])?>, <?=es($row['admin_state'])?></TD>-->
    <TD style="text-align: <?=$align_end?>;"><?=$row['auths_school'] ? ($school_id == -1 ? $row['auths_school'] : '&#10004;') : ''?></TD>
    <TD style="text-align: <?=$align_end?>;"><?=$row['auths_class'] ? $row['auths_class'] : ''?></TD>
    <!-- <TD style="text-align: <?=$align_end?>;"><?=$row['auths_team'] ? $row['auths_team'] : ''?></TD> -->
    <TD style="text-align: <?=$align_end?>;"><?=$row['auths_user'] ? $row['auths_user'] : ''?></TD>
    <TD><A HREF="admin_admin_auth.php?admin_id=<?=$row['admin_id']?>"><?=T_('Manage Authorizations')?></A>
<? if($admin_user['auth'] == 'super'): ?>
    <br/><A HREF="admin_admin.php?action=edit&amp;admin_id=<?=$row['admin_id']?>"><?=T_('Edit Admin Info')?></A>
    <br/><A HREF="admin_admin.php?action=delete&amp;admin_id=<?=$row['admin_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Admin')?></A>
<? endif; ?>
    </TD>
</TR>
<? endwhile; ?>
</TABLE>
<BR><BR>
<? $result = mq("
SELECT   email,
         COUNT(IF(auth = 'school', id, NULL)) auths_school,
         COUNT(IF(auth = 'class', id, NULL)) auths_class,
         COUNT(IF(auth = 'team', id, NULL)) auths_team,
         COUNT(IF(auth = 'user', id, NULL)) auths_user
FROM     invitations" .
($admin_user['auth'] == 'super' && $school_id == -1 ? '' : "
WHERE    auth = 'school' AND id IN ($school_ids) OR
         auth = 'class' AND id IN (SELECT class_id FROM classes WHERE school_id IN ($school_ids)) OR
         auth = 'team' AND id IN (SELECT team_id FROM teams WHERE school_id IN ($school_ids)) OR
         auth = 'user' AND id IN (SELECT user_id FROM users WHERE school_id IN ($school_ids))"
) . "
GROUP BY email
HAVING $having
ORDER BY email
");
?>
<DIV class="infobox" style="text-align:center;">
	Here you will a list of Invitees that were invited
</DIV>
<TABLE CLASS="list" style="font-size:12px; width:100%;">
<!--<CAPTION><?=T_('Invitees')?></CAPTION>-->
<THEAD>
<TR>
  <TH><?=T_('Email')?></TH>
  <TH><?=T_('School Invitations')?></TH>
  <TH><?=T_('Class Invitations')?></TH>
  <!-- <TH><?=T_('Team  Invitations')?></TH> -->
  <TH><?=T_('User Invitations')?></TH>
  <TH></TH>
</TR>
</THEAD>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['email'])?></TD>
    <TD style="text-align: <?=$align_end?>;"><?=$row['auths_school'] ? $row['auths_school'] : ''?></TD>
    <TD style="text-align: <?=$align_end?>;"><?=$row['auths_class'] ? $row['auths_class'] : ''?></TD>
    <!-- <TD style="text-align: <?=$align_end?>;"><?=$row['auths_team'] ? $row['auths_team'] : ''?></TD> -->
    <TD style="text-align: <?=$align_end?>;"><?=$row['auths_user'] ? $row['auths_user'] : ''?></TD>
    <TD><A HREF="admin_admin_auth.php?email=<?=urlencode($row['email'])?>"><?=T_('Manage Invitations')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>

<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
