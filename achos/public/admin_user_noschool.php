<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
$user_id = gri('user_id', -1);

switch(gr('action')) {
  case 'delete':
/*
auction_user_prizes
auction_winners
chain_marks
date_tasks_marks
date_tasks_mission_marks
date_tasks_missions_printed
marks
medal_marks
points
rank_marks
store_purchases
tanya_users
user_codes
user_tasks
user_tracks
users
*/
    break;

  case 'archive':
    mq("UPDATE users SET school_id = -3 WHERE user_id = $user_id");
    $message = T_('Archived Soldier');
    break;

  case 'assign':
    $schools = mq('SELECT school_id, school_name, inst_name, school_number FROM schools LEFT JOIN institutions USING (inst_id) ORDER BY inst_name, school_name');
    break;

  case 'assign2':
    $school_id = gri('school_id', -1);
    $classes = mq("SELECT class_id, class_grade, class_sub, class_teacher FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
    $teams = mq("SELECT team_id, team_name FROM teams WHERE school_id = $school_id ORDER BY team_name");
    break;

  case 'assign3':
    $school_id = nullif(gri('school_id', -1), -1);
    mq("UPDATE users SET school_id = $school_id, class_id = " . nullif(gri('class_id', -1), -1) . ', team_id = ' . nullif(gri('team_id', -1), -1) . (gr('user_registered_clear') ? ', user_registered = NULL' : '') . (gr('fee_id_clear') ? ', fee_id = NULL' : '') . ', user_notes = ' . ms(gr('user_notes')) . ', user_registration_fee = ' . grf('user_registration_fee') . " WHERE user_id = $user_id");
    if(gr('save_profile')) {
      header('Location: http' . (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : '') . "://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . "/admin_user.php?action=edit&school_id=$school_id&user_id=$user_id");
      exit;
    } else {
      $message = T_('Soldier assigned to new school');
    }
    break;
}

if($user_id != -1) $user = mysql_fetch_assoc(mq("SELECT first, last, user_serial, user_registered, fee_id, user_registration_fee, user_notes, package_name, fee_name FROM users LEFT JOIN school_package_fees USING (fee_id) LEFT JOIN school_packages USING (package_id) WHERE user_id = $user_id"));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('No-school Soldiers'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<!--<DIV class="left_menu"><?//include('admin_inc.php');?></DIV>-->
<H1><?=T_('No-school Soldiers')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<? if(isset($schools)): ?>
<H3><?=es($user['first'] . ' ' . $user['last'] . ' #' . $user['user_serial'])?></H3>
<FORM action="admin_user_noschool.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('School')?>: <SELECT name="school_id">
<? while($row = mysql_fetch_assoc($schools)): ?>
<OPTION value="<?=$row['school_id']?>"><?=es($row['inst_name'] . ' - ' . $row['school_name'])?>
<? endwhile; ?>
</SELECT></LABEL>
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<INPUT type="hidden" name="action" value="assign2">
<INPUT type="submit" value="<?=T_('Next&gt;&gt;')?>">
</P>
</FORM>

<? elseif(isset($classes)): ?>

<H2><?=es($user['first'] . ' ' . $user['last'] . ' #' . $user['user_serial'])?></H2>
<FORM action="admin_user_noschool.php" method="post" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Platoon')?>: <SELECT name="class_id">
  <OPTION VALUE="-1">&lt;<?=T_('N/A')?>&gt;</OPTION>
  <? while($row = mysql_fetch_assoc($classes)): ?>
    <OPTION VALUE="<?=$row['class_id']?>"><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?> <?=es($row['class_teacher'])?>
  <? endwhile; ?>
</SELECT></LABEL><BR>
<LABEL><?=T_('Squad')?>: <SELECT name="team_id">
  <OPTION VALUE="-1">&lt;<?=T_('N/A')?>&gt;</OPTION>
  <? while($row = mysql_fetch_assoc($teams)): ?>
    <OPTION VALUE="<?=$row['team_id']?>"><?=es($row['team_name'])?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL><BR>
</P>
<?if(!is_null($user['user_registered'])):?>
<P>
<?=sprintf(T_('User registered on: %s'), $user['user_registered'])?><BR>
<LABEL><?=T_('Clear registration?')?> <INPUT type="checkbox" name="user_registered_clear"></LABEL>
</P:>
<?endif;?>
<?if(!is_null($user['fee_id'])):?>
<P>
<?=es(sprintf(T_('User has fee package: %s'), $user['package_name'] . $user['fee_name']))?><BR>
<LABEL><?=T_('Remove package?')?> <INPUT type="checkbox" name="fee_id_clear"></LABEL>
</P>
<?endif;?>
<P>
<?=T_('Information entered by the previous school, edit if needed')?>:<BR>
<LABEL><?=T_('Notes')?>: <INPUT type="text" name="user_notes" value="<?=es($user['user_notes'])?>" maxlength="255" size="15"></LABEL><BR>
<LABEL><?=T_('Registration Fee Collected')?>: $<INPUT type="text" name="user_registration_fee" value="<?=es($user['user_registration_fee'])?>" maxlength="7" size="5" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 9999.99)).toFixed(2);"></LABEL><BR>
</P>
<P>
<INPUT type="hidden" name="school_id" value="<?=gri('school_id', -1)?>">
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
<INPUT type="hidden" name="action" value="assign3">
<INPUT type="submit" value="<?=T_('Save and back to no-school list')?>">
<INPUT type="submit" name="save_profile" value="<?=T_("Save and edit soldier's profile")?>">
</P>
</FORM>

<? else: ?>

<? $result = mq('SELECT user_id, first, last, user_serial, dob FROM users LEFT JOIN schools USING (school_id) WHERE schools.school_id IS NULL AND !(users.school_id <=> -3) ORDER BY last, first, user_id'); ?>

<TABLE CLASS="list list_<?=$align_start?>" style="font-size:12px">
<THEAD>
<TR>
  <TH><?=T_('Last')?></TH>
  <TH><?=T_('First')?></TH>
  <TH><?=T_('Serial #')?></TH>
  <TH><?=T_('Birthdate')?></TH>
  <TH><?=T_('Points')?></TH>
  <TH></TH>
  <TH><?=T_('Archive: This hides the student from this list, but does not delete them. This is intended so that future statistics are accurate, because deleting a student removes all data.')?></TH>
  <TH><?=T_('Delete: This removes all record of the student, including points, tehillim said, medals, ranks, etc.')?></TH>
</TR>
</THEAD>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
  <TD><?=es($row['last'])?></TD>
  <TD><?=es($row['first'])?></TD>
  <TD><?=es($row['user_serial'])?></TD>
  <TD><?=es($row['dob'])?></TD>
  <TD style="text-align: <?=$align_end?>;"><?=number_format($points = mysql_result(mq(totalMarks("WHERE user_id = {$row['user_id']}")), 0), 2)?></TD>
  <TD><A HREF="admin_user_noschool.php?user_id=<?=$row['user_id']?>&amp;action=assign"><?=T_('Assign to a school')?></A></TD>
  <TD><A HREF="admin_user_noschool.php?user_id=<?=$row['user_id']?>&amp;action=archive"><?=T_('Archive Soldier')?></A></TD>
  <TD><A HREF="admin_user_noschool.php?user_id=<?=$row['user_id']?>&amp;action=delete" onClick="return false; return confirm('<?=es(T_('Are you sure?\n\nThis can not be undone.'))?>')"><?=T_('Delete permanently')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>

</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
