<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$subject_id = gri('subject_id', -1);
$mission_id = gri('mission_id', -1);

if(gr('action') == 'export') {
  require_once('export.php');
  export("SELECT user_id, username, first, last, user_serial, user_address1, user_address2, user_city, user_state, user_postal, user_country, user_phone, class_grade, class_sub, class_teacher, school_id, school_name, school_number, school_address1, school_address2, school_city, school_state, school_country, school_postal, school_phone FROM users LEFT JOIN schools USING (school_id) LEFT JOIN classes USING (class_id, school_id) WHERE EXISTS (SELECT task_id FROM missions JOIN mission_tasks USING (mission_id) JOIN marks USING (task_id) WHERE subject_id = $subject_id AND mission_id = $mission_id AND marks.user_id = users.user_id) " .  ($school_id != -1 ? " AND school_id = $school_id" : '') . ($class_id != -1 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, username", 'participants');
  exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Mission Participants'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Mission Participants')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<FORM action="admin_participant.php" method="get" accept-charset="UTF-8">
<P>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<?endif;?>
<? $subject_result = mq('SELECT subject_id, subject_name, inst_name FROM subjects JOIN institutions USING (inst_id) ' . ($admin_user['auth'] != 'super' ? ' WHERE inst_id IN (' . implode(',', $admin_user['inst_ids']) . ')' : '') . ' ORDER BY inst_name, subject_name'); ?>
<LABEL><?=T_('Select Subject')?>:
<SELECT name="subject_id" id="subject_id">
  <? while($row = mysql_fetch_assoc($subject_result)): ?>
    <OPTION VALUE="<?=$row['subject_id']?>" <?=$subject_id == $row['subject_id'] ? 'SELECTED' : '' ?>><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name'])?></OPTION>
      <? endwhile; ?>
</SELECT></LABEL><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<?if($subject_id == -1):?>
<?=T_('Please select an institution and a subject.')?>
<?else:?>
<HR>
<FORM action="admin_participant.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<?if($school_id != -1):?>
<?=T_('Show Platoon')?>: <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?$result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");?>
<?while($row = mysql_fetch_assoc($result)):?>
<OPTION value="<?=$row['class_id']?>" <?=$row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT><BR>
<?endif;?>

<?=T_('Show Mission')?>: <SELECT name="mission_id">
<?$result = mq("SELECT mission_id, mission_name FROM missions WHERE subject_id = $subject_id ORDER BY mission_name");?>
<?while($row = mysql_fetch_assoc($result)):?>
<OPTION value="<?=$row['mission_id']?>" <?=$row['mission_id'] == $mission_id ? 'SELECTED' : ''?>><?=es($row['mission_name'])?></OPTION>
<?endwhile;?>
</SELECT><BR>

<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<?if($mission_id == -1):?>
<?=T_('Please choose a Mission.')?>
<?else:?>

<A HREF="admin_participant.php?action=export&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>&amp;subject_id=<?=$subject_id?>&amp;mission_id=<?=$mission_id?>"><?=T_('Export Participants')?></A><BR>

<?$result = mq("SELECT class_grade, class_sub, user_id, username, first, last, user_serial, team_name, school_id, class_id FROM users LEFT JOIN classes USING (class_id, school_id) LEFT JOIN teams USING (team_id, school_id) WHERE EXISTS (SELECT task_id FROM missions JOIN mission_tasks USING (mission_id) JOIN marks USING (task_id) WHERE subject_id = $subject_id AND mission_id = $mission_id AND marks.user_id = users.user_id) " .  ($school_id != -1 ? " AND school_id = $school_id" : '') . ($class_id != -1 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, username");?>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('Platoon')?></TH>
  <TH><?=T_('Username')?></TH>
  <TH><?=T_('Name')?></TH>
  <TH><?=T_('Serial #')?></TH>
  <TH><?=T_('Birthdate')?></TH>
  <TH><?=T_('Squad')?></TH>
  <TH></TH>
  <TH></TH>
  <TH></TH>
  <TH></TH>
</TR>
<?while($row = mysql_fetch_assoc($result)):?>
<TR>
    <TD><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></TD>
    <TD><?=es($row['username'])?></TD>
    <TD><?=es($row['first'])?> <?=es($row['last'])?></TD>
    <TD><?=$row['user_serial']?></TD>
    <TD>Fixme</TD>
    <TD><?=es($row['team_name'])?></TD>
    <TD><A HREF="admin_user.php?action=edit&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$row['school_id']?>&amp;class_id=<?=$class_id?>"><?=T_('Edit Soldier Info')?></A></TD>
    <TD><A HREF="admin_user_track.php?action=edit&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$row['school_id']?>&amp;class_id=<?=$class_id?>"><?=T_("Edit Soldier's ladders/years")?></A></TD>
    <TD><A HREF="admin_report.php?action=edit&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$row['school_id']?>&amp;class_id=<?=$class_id?>"><?=T_("Soldier Report")?></A></TD>
    <!--<TD><A HREF="index.php?auth_become=<?=$row['user_id']?>"><?=T_("Act as Soldier")?></A></TD>-->
<?endwhile;?>
</TABLE>
<?endif;?>
<?endif;?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
