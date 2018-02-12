<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('register_inc.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
if($school_id == -1) die('Unknown school, please login to your admin page, and click the register link.');

if(gr('save')) {
  foreach(gra('admin_role') as $admin_id => $role_id) {
    $admin_id = intval($admin_id);
    $role_id = nullif(intval($role_id), -1);

    mq("UPDATE admin_auths SET role_id = $role_id WHERE admin_id = $admin_id AND auth = 'school' AND id = $school_id");
  }

  $school_row = mysql_fetch_assoc(mq("SELECT school_name FROM schools WHERE school_id = $school_id"));
  foreach(gra('invite') as $data) {
    $role_id = nullif(intval($data['role']), -2);
    $email = $data['email'];
    if($email !== '') createInvite($data['email'], $school_id, 'school', $role_id, $school_row['school_name']);
  }

  header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/register_school_shipping.php?school_id=' . $school_id);
  exit;
}

$roles = mq("SELECT role_id, role_name FROM roles WHERE role_auth = 'school'");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=$steps['admin'], ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
<?=registration_HTML_head()?>
<SCRIPT type="text/javascript">
function checkFields(form) {
  if(!form.elements['confirm'].checked) {
    alert('<?=T_('You need to confirm before continuing.')?>');
    form.elements['confirm'].focus();
    return false;
  }
  return true;
}

var id = 0;
function addInvite(el) {
  id++;
  tr = el.parentNode.insertRow(el.rowIndex);
  tr.innerHTML = '<TD colspan="2"><LABEL><?=T_('Email address')?>: <INPUT type="text" name="invite[' + id + '][email]"><\/LABEL><\/TD><TD style="white-space: nowrap;"><?=T_('Role')?>: <SELECT name="invite[' + id + '][role]"><OPTION value="-1">&lt;<?=T_('Have invitee choose')?>&gt;<OPTION value="-2">&lt;<?=T_('Other')?>&gt;<? while($role = mysql_fetch_assoc($roles)): ?><OPTION value="<?=$role['role_id']?>"><?=esq(es($role['role_name']))?><? endwhile; ?><\/SELECT><? mysql_data_seek($roles, 0); ?><\/TD>';
}
</SCRIPT>
<!--Copyright Ariel Shkedi 2007-2010-->
</HEAD>
<BODY>

<?=registration_banner_existing('admin')?>

<DIV CLASS="body register">

<DIV class="form form_<?= $align_start ?>">

<? if(isset($message) && $message): ?>
<DIV CLASS="message" style="text-align: center;">
<?= $message ?><BR>
</DIV>
<? endif; ?>

<BR><BR>

<FORM action="register_school_admin.php" method="post" accept-charset="UTF-8" onSubmit="return checkFields(this);">
<TABLE>
<CAPTION><?=T_("Please create one administrator account for the school principal and one for the program director (if they are not the same person). These are the only two required, but you may add as many additional accounts as you'd like.")?></CAPTION>
<TR>
  <TH colspan="0"><?=T_('Existing Administrators')?></TH>
</TR>
<? $result = mq("SELECT admin_id, username, title, first, last, role_id FROM admin_auths JOIN admins USING (admin_id) WHERE admin_auths.auth = 'school' AND id = $school_id ORDER BY username"); ?>
<? if(!mysql_num_rows($result)): ?>
<TR>
  <TD colspan="0"><?=T_('None')?></TD>
</TR>
<? else: ?>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
  <TD><?=es($row['username'])?></TD>
  <TD><?=es($row['title'])?> <?=es($row['first'])?> <?=es($row['last'])?></TD>
  <TD style="white-space: nowrap;">
    <?=T_('Role')?>:
    <SELECT name="admin_role[<?=$row['admin_id']?>]">
      <OPTION value="-1" <?=is_null($row['role_id']) ? 'SELECTED' : ''?>>&lt;<?=T_('Other')?>&gt;
      <? while($role = mysql_fetch_assoc($roles)): ?>
        <OPTION value="<?=$role['role_id']?>" <?=$role['role_id'] == $row['role_id'] ? 'SELECTED' : ''?>><?=es($role['role_name'])?>
      <? endwhile; ?>
    </SELECT>
    <? mysql_data_seek($roles, 0); ?>
  </TD>
</TR>
<? endwhile; ?>
<? endif; ?>
<TR>
  <TH colspan="0"><?=T_('Pending Invitations')?></TH>
</TR>
<? $result = mq("SELECT invitation_id, email, role_id, role_name, invitation_date FROM invitations LEFT JOIN roles USING (role_id) WHERE id = $school_id AND auth = 'school' ORDER BY invitation_date DESC"); ?>
<? if(!mysql_num_rows($result)): ?>
<TR>
  <TD colspan="0"><?=T_('None')?></TD>
</TR>
<? else: ?>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
  <TD><?=es($row['email'])?></TD>
  <TD style="white-space: nowrap;"><?=T_('Since')?>: <?=es($row['invitation_date'])?></TD>
  <TD><?=is_null($row['role_name']) ? '&lt;' . ($row['role_id'] == -1 ? T_('Have invitee choose') : T_('Other')) . '&gt;' : es($row['role_name'])?></TD>
</TR>
<? endwhile; ?>
<? endif; ?>
<TR>
  <TH colspan="0"><?=T_('Invite New Administrators')?></TH>
</TR>
<TR>
  <TD colspan="2"><LABEL><?=T_('Email address')?>: <INPUT type="text" name="invite[0][email]"></LABEL></TD>
  <TD style="white-space: nowrap;">
    <?=T_('Role')?>:
    <SELECT name="invite[0][role]">
      <OPTION value="-1">&lt;<?=T_('Have invitee choose')?>&gt;
      <OPTION value="-2">&lt;<?=T_('Other')?>&gt;
      <? while($role = mysql_fetch_assoc($roles)): ?>
        <OPTION value="<?=$role['role_id']?>"><?=es($role['role_name'])?>
      <? endwhile; ?>
    </SELECT>
    <? mysql_data_seek($roles, 0); ?>
  </TD>
</TR>
<TR>
  <TD colspan="0"><A HREF="#" onClick="addInvite(this.parentNode.parentNode); return false;">&uArr;<?=T_('Add another invitation row')?>&uArr;</A> (<?=T_('To remove a row, simply leave the email address blank.')?>)</TD>
</TR>
</TABLE>

<P>
<?=T_('You can remove, or otherwise manage, admins and invitations using the Manage Admins menu once you have registered.')?>
</P>
<P>
<?=T_('It is the responsibily of the Principal to make sure the Program Director responds to the invitation, and signs up.')?><BR>
<LABEL><INPUT type="checkbox" name="confirm"> <?=T_('Please confirm that you agree to have a Program Director.')?></LABEL>
</P>
<P style="text-align: <?=$align_end?>;">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT TYPE="submit" name="save" VALUE="<?=T_('Save &amp; Continue')?>">
</P>

</FORM>

</DIV>
</DIV>

<?=registration_tail()?>

</BODY>
</HTML>
