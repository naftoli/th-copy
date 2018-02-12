<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
check_id_access();
$ui_type = 'school';


$admin_id = gri('admin_id', -1);
$username = gr('username');
$email = gr('email');

$auth = gr('auth');
$id = gri('id');

$action = gr('action');

if($admin_id != -1 || $username !== '') {
  $admin_row = mysql_fetch_assoc(mq('SELECT admin_id, username, first, last, admin_city, admin_state FROM admins WHERE' . ($admin_id != -1 ? " admin_id = $admin_id" : ' username = ' . ms($username))));
  if(!$admin_row) {
    echo T_('Can not locate admin. Perhaps you entered the username incorrectly.'), ' <A HREF="admin_admin.php">', T_('Back'), '</A>';
    exit;
  } else {
    $admin_id = $admin_row['admin_id'];
    $name = $admin_row['first'] . ' ' . $admin_row['last'] . ' ' . T_('of') . ' ' . $admin_row['admin_city'] . ', ' . $admin_row['admin_state'];
    unset($username);
    unset($email);
  }
} elseif($email !== '') {
  unset($admin_id);
  unset($username);
  $name = $email;
  if($action == 'edit' || $action == 'edit2') $action = '';
} else {
  echo T_('Can not locate admin. Please enter a username or email addres.'), ' <A HREF="admin_admin.php">', T_('Back'), '</A>';
  exit;
}

$school_ids = implode(',', $admin_user['auths']['school']);

if($action == 'select2' || $action == 'edit' || $action == 'edit2' || $action == 'delete') {
  switch($auth) {
    case 'school':
      $name_col = 'school_name';
      $table = 'schools';
      $table_id = 'school_id';
      break;

    case 'class':
      $name_col = "CONCAT(class_grade, ' - ', class_sub)";
      $table = 'classes';
      $table_id = 'class_id';
      break;

    case 'team':
      $name_col = 'team_name';
      $table = 'teams';
      $table_id = 'team_id';
      break;

    case 'user':
      $name_col = "CONCAT(first, ' ', last)";
      $table = 'users';
      $table_id = 'user_id';
      break;

    default:
      user_error('unknown auth', E_USER_ERROR);
      break;
  }

  $where = "AND school_id IN ($school_ids)";
  $join = "JOIN $table ON (id = $table.$table_id)";

  if($admin_user['auth'] == 'super') {
    $name_col_join = "LEFT $join"; // when the join is to display a column, not just access control
    $join = '';
    $where = '';
  } else {
    $name_col_join = $join;
  }
}

switch($action) {
  case 'select':
    break;

  case 'select2':
    if(isset($admin_id)) {
      mq("INSERT IGNORE INTO admin_auths (admin_id, auth, id, role_id) SELECT $admin_id admin_id, " . ms($auth) . " auth, $table_id id, " . nullif(gri('role_id', -1), -1) . " role_id FROM $table WHERE $table_id = " . gri('id', -1) . " $where");
      $message = T_('Added Authorization');
    } else {
      $row = mysql_fetch_assoc(mq("SELECT $table_id id, $name_col name, " . nullif(gri('role_id', -1), -2) . " role_id FROM $table WHERE $table_id = " . gri('id', -1) . " $where"));
      if($row) createInvite($email, $row['id'], $auth, $row['role_id'], $row['name']);
      $message = T_('Sent Invitation');
    }
    break;

  case 'edit':
    $role_row = mysql_fetch_assoc(mq("SELECT role_id, $name_col name FROM admin_auths $name_col_join WHERE admin_id = $admin_id AND auth = " . ms($auth) . " AND id = $id $where"));
    break;

  case 'edit2':
    mq("UPDATE admin_auths $join SET role_id = " . nullif(gri('role_id', -1), -1) . " WHERE admin_id = $admin_id AND auth = " . ms($auth) . " AND id = $id $where");
    $message = T_('Edited Role');
    break;

  case 'delete':
    if(isset($admin_id)) {
      mq("DELETE FROM admin_auths USING admin_auths $join WHERE admin_id = $admin_id AND auth = " . ms($auth) . " AND id = $id $where");
      $message = T_('Deleted Authorization');
    } else {
      mq("DELETE FROM invitations USING invitations $join WHERE invitation_id = " . ms(gr('invitation_id')) . ' AND auth = ' . ms($auth) . ' AND invitations.email = ' . ms($email) . " $where");
      $message = T_('Deleted Invitation');
    }
    break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Authorizations &amp; Invitations'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Authorizations &amp; Invitations')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>


<H2><?=isset($admin_id) ? T_('Authorizations for') : T_('Invitations for')?>: <?=$name?></H2>
<? if($action == 'select'): ?>
<FORM action="admin_admin_auth.php" method="get" accept-charset="UTF-8">
<P>
<?if(isset($admin_id)):?>
<INPUT type="hidden" name="admin_id" value="<?=$admin_id?>">
<?else:?>
<INPUT type="hidden" name="email" value="<?=$email?>">
<?endif;?>
<INPUT type="hidden" name="auth" value="<?=$auth?>">
<?
$school_id = gri('school_id', -1);
if($school_id == -1 && $auth != 'school'): ?>

<INPUT type="hidden" name="action" value="<?=$action?>">
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<?=T_('Select Institution')?>:<BR>
<SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>"><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT> <INPUT class="submit" type="submit" value="<?=T_('Next &gt;&gt;')?>">

<? else:

  switch($auth) {
    case 'school':
      $result = mq('SELECT school_name name, school_id id FROM schools' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name');
      break;

    case 'class':
      $result = mq("SELECT CONCAT(class_grade, ' - ', class_sub) name, class_id id FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
      break;

    case 'team':
      $result = mq("SELECT team_name name, team_id id FROM teams WHERE school_id = $school_id ORDER BY team_name");
      break;

    case 'user':
      $result = mq("SELECT CONCAT(class_grade, ' - ', class_sub, ': ', first, ' ', last, ' (', username, ')') name, user_id id FROM users LEFT JOIN classes USING (class_id, school_id) WHERE school_id = $school_id ORDER BY class_grade, class_sub, last, first");
      break;

    default:
      user_error('unknown auth', E_USER_ERROR);
      break;
  }
  $roles = mq('SELECT role_name, role_id FROM roles WHERE role_auth = ' . ms($auth));
  $existing = !isset($admin_id) ? array() : mysql_fetch_column(mq("SELECT id FROM admin_auths WHERE admin_id = $admin_id AND auth = " . ms($auth)));
?>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<?=sprintf(T_('Add Authorization to %s'), $auth)?>:<BR>
<SELECT name="id">
<?while($row = mysql_fetch_assoc($result)):?>
<OPTION value="<?=$row['id']?>" <?=in_array($row['id'], $existing) ? 'DISABLED' : ''?>><?=es($row['name'])?></OPTION>
<?endwhile;?>
</SELECT><BR>
<?=T_('Role')?>:<BR>
<SELECT name="role_id">
<OPTION value="-1">&lt;Have invitee choose&gt;
<OPTION value="-2">&lt;Other&gt;
<?while($role_row = mysql_fetch_assoc($roles)):?>
<OPTION value="<?=$role_row['role_id']?>"><?=es($role_row['role_name'])?></OPTION>
<?endwhile;?>
</SELECT><BR>

<INPUT class="submit" type="submit" value="<?=T_('Save')?>">

<? endif; ?>

</P>
</FORM>

<? elseif(isset($role_row)): ?>

<? $result = mq('SELECT role_id, role_name FROM roles WHERE role_auth = ' . ms($auth) . ' ORDER BY role_name'); ?>

<FORM action="admin_admin_auth.php" method="post" accept-charset="UTF-8">
<P>
<LABEL><?=sprintf(T_('Role for %s management of %s'), $auth, $role_row['name'])?>: <SELECT name="role_id">
<OPTION value="-1">&lt;Other&gt;
<?while($row = mysql_fetch_assoc($result)):?>
<OPTION value="<?=$row['role_id']?>" <?=$row['role_id'] == $role_row['role_id'] ? 'SELECTED' : ''?>><?=es($row['role_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL> <INPUT class="submit" type="submit" value="<?=T_('Change')?>">
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="admin_id" value="<?=$admin_id?>">
<INPUT type="hidden" name="id" value="<?=$id?>">
<INPUT type="hidden" name="auth" value="<?=$auth?>">
</P>
</FORM>

<? elseif(isset($admin_id)): ?>

<? $school_auths = mq("SELECT school_name, school_id, admin_auths.role_id, role_name FROM admin_auths LEFT JOIN schools ON (admin_auths.id = schools.school_id) LEFT JOIN roles ON (admin_auths.role_id = roles.role_id AND admin_auths.auth = roles.role_auth) WHERE auth = 'school' AND admin_id = $admin_id" . ($admin_user['auth'] == 'super' ? '' : " AND school_id IN ($school_ids)") . ' ORDER BY school_name'); ?>
<? $class_auths = mq("SELECT school_name, class_grade, class_sub, class_id, admin_auths.role_id, role_name FROM admin_auths LEFT JOIN classes ON (admin_auths.id = classes.class_id) LEFT JOIN roles ON (admin_auths.role_id = roles.role_id AND admin_auths.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'class' AND admin_id = $admin_id" . ($admin_user['auth'] == 'super' ? '' : " AND school_id IN ($school_ids)") . ' ORDER BY class_grade, class_sub'); ?>
<? $team_auths = mq("SELECT school_name, team_name, team_id, admin_auths.role_id, role_name FROM admin_auths LEFT JOIN teams ON (admin_auths.id = teams.team_id) LEFT JOIN roles ON (admin_auths.role_id = roles.role_id AND admin_auths.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'team' AND admin_id = $admin_id" . ($admin_user['auth'] == 'super' ? '' : " AND school_id IN ($school_ids)") . ' ORDER BY team_name'); ?>
<? $user_auths = mq("SELECT school_name, first, last, user_id, admin_auths.role_id, role_name FROM admin_auths LEFT JOIN users ON (admin_auths.id = users.user_id) LEFT JOIN roles ON (admin_auths.role_id = roles.role_id AND admin_auths.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'user' AND admin_id = $admin_id" . ($admin_user['auth'] == 'super' ? '' : " AND school_id IN ($school_ids)") . ' ORDER BY last, first'); ?>
<TABLE class="list">
<THEAD>
<TR>
  <TH><?=T_('Name')?></TH>
  <TH><?=T_('Role')?></TH>
  <TH></TH>
  <TH></TH>
</TR>
</THEAD>
<TBODY>
<TR>
  <TH colspan="2"><?=T_('School Authorizations')?></TH>
  <TH style="text-align: <?=$align_start?>;"><A HREF="admin_admin_auth.php?action=select&amp;admin_id=<?=$admin_id?>&amp;auth=school"><?=T_('Add School Authorization')?></A></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($school_auths)): ?>
<TR>
  <TD><?=es($row['school_name'])?></TD>
  <TD><?=is_null($row['role_id']) ? '&lt;' . T_('Other') . '&gt;' : es($row['role_name'])?></TD>
  <TD><A HREF="admin_admin_auth.php?action=delete&amp;admin_id=<?=$admin_id?>&amp;id=<?=$row['school_id']?>&amp;auth=school" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Remove Authorization')?></A></TD>
  <TD><A HREF="admin_admin_auth.php?action=edit&amp;admin_id=<?=$admin_id?>&amp;id=<?=$row['school_id']?>&amp;auth=school"><?=T_('Edit Role')?></A></TD>
</TR>
<? endwhile; ?>
</TBODY>
<TBODY>
<TR>
  <TH colspan="2"><?=T_('Class Authorizations')?></TH>
  <TH style="text-align: <?=$align_start?>;"><A HREF="admin_admin_auth.php?action=select&amp;admin_id=<?=$admin_id?>&amp;auth=class"><?=T_('Add Class Authorization')?></A></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($class_auths)): ?>
<TR>
  <TD><?=es($row['class_grade'])?> - <?=es($row['class_sub'])?><BR><?=T_('At')?>: <?=es($row['school_name'])?></TD>
  <TD><?=is_null($row['role_id']) ? '&lt;' . T_('Other') . '&gt;' : es($row['role_name'])?></TD>
  <TD><A HREF="admin_admin_auth.php?action=delete&amp;admin_id=<?=$admin_id?>&amp;id=<?=$row['class_id']?>&amp;auth=class" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Remove Authorization')?></A></TD>
  <TD><A HREF="admin_admin_auth.php?action=edit&amp;admin_id=<?=$admin_id?>&amp;id=<?=$row['class_id']?>&amp;auth=class"><?=T_('Edit Role')?></A></TD>
</TR>
<? endwhile; ?>
</TBODY>
<!--
<TBODY>
<TR>
  <TH colspan="2"><?=T_('Team Authorizations')?></TH>
  <TH style="text-align: <?=$align_start?>;"><A HREF="admin_admin_auth.php?action=select&amp;admin_id=<?=$admin_id?>&amp;auth=team"><?=T_('Add Team Authorization')?></A></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($team_auths)): ?>
<TR>
  <TD><?=es($row['team_name'])?><BR><?=T_('At')?>: <?=es($row['school_name'])?></TD>
  <TD><?=is_null($row['role_id']) ? '&lt;' . T_('Other') . '&gt;' : es($row['role_name'])?></TD>
  <TD><A HREF="admin_admin_auth.php?action=delete&amp;admin_id=<?=$admin_id?>&amp;id=<?=$row['team_id']?>&amp;auth=team" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Remove Authorization')?></A></TD>
  <TD><A HREF="admin_admin_auth.php?action=edit&amp;admin_id=<?=$admin_id?>&amp;id=<?=$row['team_id']?>&amp;auth=team"><?=T_('Edit Role')?></A></TD>
</TR>
<? endwhile; ?>
</TBODY>
-->
<TBODY>
<TR>
  <TH colspan="2"><?=T_('User Authorizations')?></TH>
  <TH style="text-align: <?=$align_start?>;"><A HREF="admin_admin_auth.php?action=select&amp;admin_id=<?=$admin_id?>&amp;auth=user"><?=T_('Add User Authorization')?></A></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($user_auths)): ?>
<TR>
  <TD><?=es($row['first'])?> <?=es($row['last'])?><BR><?=T_('At')?>: <?=es($row['school_name'])?></TD>
  <TD><?=is_null($row['role_id']) ? '&lt;' . T_('Other') . '&gt;' : es($row['role_name'])?></TD>
  <TD><A HREF="admin_admin_auth.php?action=delete&amp;admin_id=<?=$admin_id?>&amp;id=<?=$row['user_id']?>&amp;auth=user" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Remove Authorization')?></A></TD>
  <TD><A HREF="admin_admin_auth.php?action=edit&amp;admin_id=<?=$admin_id?>&amp;id=<?=$row['user_id']?>&amp;auth=user"><?=T_('Edit Role')?></A></TD>
</TR>
<? endwhile; ?>
</TBODY>
</TABLE>

<?else:?>

<? $school_auths = mq("SELECT school_name, school_id, role_name, invitations.role_id, invitation_date, invitation_id FROM invitations LEFT JOIN schools ON (invitations.id = schools.school_id) LEFT JOIN roles ON (invitations.role_id = roles.role_id AND invitations.auth = roles.role_auth) WHERE auth = 'school' AND email = " . ms($email) . ($admin_user['auth'] == 'super' ? '' : " AND school_id IN ($school_ids)") . ' ORDER BY school_name, invitation_date'); ?>
<? $class_auths = mq("SELECT class_grade, class_sub, class_id, role_name, school_name, invitations.role_id, invitation_date, invitation_id FROM invitations LEFT JOIN classes ON (invitations.id = classes.class_id) LEFT JOIN roles ON (invitations.role_id = roles.role_id AND invitations.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'class' AND email = " . ms($email) . ($admin_user['auth'] == 'super' ? '' : " AND school_id IN ($school_ids)") . ' ORDER BY class_grade, class_sub'); ?>
<? $team_auths = mq("SELECT team_name, team_id, role_name, school_name, invitations.role_id, invitation_date, invitation_id FROM invitations LEFT JOIN teams ON (invitations.id = teams.team_id) LEFT JOIN roles ON (invitations.role_id = roles.role_id AND invitations.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'team' AND email = " . ms($email) . ($admin_user['auth'] == 'super' ? '' : " AND school_id IN ($school_ids)") . ' ORDER BY team_name'); ?>
<? $user_auths = mq("SELECT first, last, user_id, role_name, school_name, invitations.role_id, invitation_date, invitation_id FROM invitations LEFT JOIN users ON (invitations.id = users.user_id) LEFT JOIN roles ON (invitations.role_id = roles.role_id AND invitations.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'user' AND invitations.email = " . ms($email) . ($admin_user['auth'] == 'super' ? '' : " AND school_id IN ($school_ids)") . ' ORDER BY last, first'); ?>

<TABLE class="list">
<THEAD>
<TR>
  <TH><?=T_('Name')?></TH>
  <TH><?=T_('Role')?></TH>
  <TH><?=T_('Invitation Date')?></TH>
  <TH></TH>
</TR>
</THEAD>
<TBODY>
<TR>
  <TH colspan="2"><?=T_('School Invitations')?></TH>
  <TH style="text-align: <?=$align_start?>;"><A HREF="admin_admin_auth.php?action=select&amp;email=<?=urlencode($email)?>&amp;auth=school"><?=T_('Add School Invitation')?></A></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($school_auths)): ?>
<TR>
  <TD><?=es($row['school_name'])?></TD>
  <TD><?=$row['role_id'] == '-1' ? '&lt;' . T_('Have invitee choose') . '&gt;' : (is_null($row['role_id']) ? '&lt;' . T_('Other') . '&gt;' : es($row['role_name']))?></TD>
  <TD><?=$row['invitation_date']?></TD>
  <TD><A HREF="admin_admin_auth.php?action=delete&amp;email=<?=urlencode($email)?>&amp;invitation_id=<?=$row['invitation_id']?>&amp;auth=school" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Remove Invitation')?></A></TD>
</TR>
<? endwhile; ?>
</TBODY>
<TBODY>
<TR>
  <TH colspan="2"><?=T_('Class Invitations')?></TH>
  <TH style="text-align: <?=$align_start?>;"><A HREF="admin_admin_auth.php?action=select&amp;email=<?=urlencode($email)?>&amp;auth=class"><?=T_('Add Class Invitation')?></A></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($class_auths)): ?>
<TR>
  <TD><?=es($row['class_grade'])?> - <?=es($row['class_sub'])?><BR><?=T_('At')?>: <?=es($row['school_name'])?></TD>
  <TD><?=$row['role_id'] == '-1' ? '&lt;' . T_('Have invitee choose') . '&gt;' : (is_null($row['role_id']) ? '&lt;' . T_('Other') . '&gt;' : es($row['role_name']))?></TD>
  <TD><?=$row['invitation_date']?></TD>
  <TD><A HREF="admin_admin_auth.php?action=delete&amp;email=<?=urlencode($email)?>&amp;invitation_id=<?=$row['invitation_id']?>&amp;auth=class" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Remove Invitation')?></A></TD>
</TR>
<? endwhile; ?>
</TBODY>
<!--
<TBODY>
<TR>
  <TH colspan="2"><?=T_('Team Invitations')?></TH>
  <TH style="text-align: <?=$align_start?>;"><A HREF="admin_admin_auth.php?action=select&amp;email=<?=urlencode($email)?>&amp;auth=team"><?=T_('Add Team Invitation')?></A></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($team_auths)): ?>
<TR>
  <TD><?=es($row['team_name'])?><BR><?=T_('At')?>: <?=es($row['school_name'])?></TD>
  <TD><?=$row['role_id'] == '-1' ? '&lt;' . T_('Have invitee choose') . '&gt;' : (is_null($row['role_id']) ? '&lt;' . T_('Other') . '&gt;' : es($row['role_name']))?></TD>
  <TD><?=$row['invitation_date']?></TD>
  <TD><A HREF="admin_admin_auth.php?action=delete&amp;email=<?=urlencode($email)?>&amp;invitation_id=<?=$row['invitation_id']?>&amp;auth=team" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Remove Invitation')?></A></TD>
</TR>
<? endwhile; ?>
</TBODY>
-->
<TBODY>
<TR>
  <TH colspan="2"><?=T_('User Invitations')?></TH>
  <TH style="text-align: <?=$align_start?>;"><A HREF="admin_admin_auth.php?action=select&amp;email=<?=urlencode($email)?>&amp;auth=user"><?=T_('Add User Invitation')?></A></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($user_auths)): ?>
<TR>
  <TD><?=es($row['first'])?> <?=es($row['last'])?><BR><?=T_('At')?>: <?=es($row['school_name'])?></TD>
  <TD><?=$row['role_id'] == '-1' ? '&lt;' . T_('Have invitee choose') . '&gt;' : (is_null($row['role_id']) ? '&lt;' . T_('Other') . '&gt;' : es($row['role_name']))?></TD>
  <TD><?=$row['invitation_date']?></TD>
  <TD><A HREF="admin_admin_auth.php?action=delete&amp;email=<?=urlencode($email)?>&amp;invitation_id=<?=$row['invitation_id']?>&amp;auth=user" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Remove Invitation')?></A></TD>
</TR>
<? endwhile; ?>
</TBODY>
</TABLE>

<?endif;?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
