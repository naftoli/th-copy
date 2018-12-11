<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'programs';
require_once('admin_ui.php');

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);

$subject_id = gri('subject_id', -1);
$subject = mysql_fetch_assoc(mq("SELECT subject_name, subject_type, school_subjects.subject_id enrolled FROM subjects JOIN schools USING (inst_id) LEFT JOIN school_subjects USING (subject_id, school_id) WHERE subjects.subject_id = $subject_id AND school_id = $school_id"));

if($subject && gr('enroll')) {
  mq("INSERT IGNORE INTO school_subjects (school_id, subject_id) VALUES ($school_id, $subject_id)");
  $message = sprintf(T_('Enrolled into %s.'), es($subject['subject_name']));
  $subject['enrolled'] = true;
} elseif($subject && gr('un_enroll')) {
  mq("DELETE FROM school_subjects WHERE school_id = $school_id AND subject_id = $subject_id");
  $message = sprintf(T_('Removed from %s.'), es($subject['subject_name']));
  $subject['enrolled'] = false;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Campaigns - Enroll'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=T_('Campaigns')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_school_subjects.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<?endif;?>

<? if($school_id == -1): ?>
<P><?=T_('Please select an Institution.')?></P>
<? else: ?>
<DIV class="ui_body">
<DIV class="ui_menu">
<?ui_menu();?>
</DIV>
<DIV class="content">
<? if(!$subject): ?>
<P><?=T_('Please select a campaign.')?></P>
<? else: ?>
<H2><?=es($subject['subject_name']), ' - ', T_('Enroll')?></H2>
<FORM action="admin_school_subjects.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<? if($subject['enrolled']): ?>
<INPUT type="submit" name="un_enroll" value="<?=T_('Remove my base from this campaign')?>">
<? else: ?>
<INPUT type="submit" name="enroll" value="<?=T_('Enroll my base into this campaign')?>">
<? endif; ?>
</P>
</FORM>
<? endif; ?>
<BR style="clear: both;">
</DIV>
</DIV>
<? endif; ?>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
