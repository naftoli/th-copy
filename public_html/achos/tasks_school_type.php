<? $req_school_type_setting = array('self_managed', 'personal_only'); ?>
<? require('header.php'); ?>
<?
$school_types = mq('SELECT school_type_id, school_type_name FROM school_types WHERE school_type_setting = ' . ms($user['settings']) . ' ORDER BY school_type_name');
if($school_type_id = gri('school_type_id')) {
  mq("UPDATE users JOIN school_types SET users.school_type_id = school_types.school_type_id WHERE users.user_id = {$user['user_id']} AND school_types.school_type_id = $school_type_id AND FIND_IN_SET('self', school_type_settings)");
  require('auth.php');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Change Tzivos Hashem Type'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<? include('banner.php'); ?>
<DIV CLASS="body">

<? if(isset($message) && $message): ?>
<DIV CLASS="message">
<?= $message ?>
</DIV>
<? endif; ?>

<TABLE CLASS="split" CELLSPACING=0 CELLPADDING=0>
<TR>
<TH></TH>
<TD CLASS="special"><? include('specials.php'); ?></TD>
<TH></TH>
</TR>
<TR>
<TD CLASS="tasks"><? include('todo.php'); ?></TD>
<TD CLASS="middle form form_<?= $align_start ?>">

<FORM action="tasks_school_type.php" method="post" accept-charset="UTF-8">
<H2><?=T_('Change Tzivos Hashem Type')?></H2>
<P>
<LABEL><?=T_('Tzivos Hashem Type')?>:
<SELECT name="school_type_id">
  <? while($row = mysql_fetch_assoc($school_types)): ?>
    <OPTION VALUE="<?=$row['school_type_id']?>" <?=$row['school_type_id'] == $user['school_type_id'] ? 'SELECTED' : '' ?>><?=es($row['school_type_name'])?></OPTION>
  <? endwhile; ?>
</SELECT></LABEL><BR>
<INPUT type="submit" value="<?=T_('Save')?>">
</P>
</FORM>

</TD>
<TD CLASS="menu menu_<?=$align_end?>"><? include('menu_tasks.php'); ?></TD>
</TR>
</TABLE>
</DIV>
</BODY>
</HTML>
