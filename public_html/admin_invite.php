<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);

if($email = gra('email')) {
  foreach($email as $user_id) {
    $user_id = intval($user_id);
    $user = mysql_fetch_assoc(mq("SELECT user_id, email, first, last FROM users WHERE user_id = $user_id AND school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '')));
    if($user) createInvite($user['email'], $user['user_id'], 'user', -1, "{$user['first']} {$user['last']}");
  }
  $message = T_('Parents Invited');
}

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Invite Parents'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Invite Parents')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_invite.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<FORM action="admin_invite.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<?=T_('Show only Platoon')?>: <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?while($class_row = mysql_fetch_assoc($class_result)):?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT> <INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?><P><A HREF="admin_school.php"><?=T_('Back to Institution list')?></A></P><?endif;?>
<P><A HREF="admin_class.php?school_id=<?=$school_id?>"><?=T_('Back to Platoon list')?></A></P>

<?$result = mq("SELECT class_grade, class_sub, user_id, username, first, last, user_serial, users.email, COUNT(invitation_id) invitations FROM users LEFT JOIN classes USING (class_id, school_id) LEFT JOIN invitations ON (users.user_id = invitations.id AND auth = 'user' AND users.email = invitations.email) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " GROUP BY class_grade, class_sub, user_id, username, first, last, user_serial, email ORDER BY class_grade, class_sub, username");?>

<FORM action="admin_invite.php" method="post" accept-charset="UTF-8">
<TABLE CLASS="pretty_grid">
<TR>
  <TH><?=T_('Send Invite')?></TH>
  <TH><?=T_('Open Invitations')?></TH>
  <TH><?=T_('Platoon')?></TH>
  <TH><?=T_('Username')?></TH>
  <TH><?=T_('Name')?></TH>
  <TH><?=T_('Serial #')?></TH>
  <TH><?=T_('Email')?></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?if($row['email'] !== ''):?><INPUT type="checkbox" name="email[]" value="<?=$row['user_id']?>"><?endif;?></TD>
    <TD><?=$row['invitations'] ? $row['invitations'] : ''?></TD>
    <TD><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></TD>
    <TD><?=es($row['username'])?></TD>
    <TD><?=es($row['first'])?> <?=es($row['last'])?></TD>
    <TD><?=$row['user_serial']?></TD>
    <TD><?=$row['email'] !== '' ? '<A HREF="mailto:' . urlencode($row['email']) . '">' . es($row['email']) . '</A>' : ''?></TD>
    <TD><A HREF="admin_user.php?action=edit&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>"><?=T_('Edit Soldier Info')?></A></TD>
<? endwhile; ?>
</TABLE>
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<INPUT type="submit" value="<?=T_('Send')?>">
</P>
</FORM>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
