<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('file_save.php');
$ui_type = 'school';
require_once('admin_ui.php');

$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$first = gr('first');
$last = gr('last');
$subject_id = gri('subject_id', -1);

if($enrolled = gra('enrolled')) {
  foreach($enrolled as $user_id => $val) {
    $user_id = intval($user_id);
    if($val) {
      mq("INSERT INTO user_tracks (user_id, subject_id, enrolled) SELECT user_id, $subject_id subject_id, 1 enrolled FROM users WHERE user_id = $user_id AND school_id = $school_id ON DUPLICATE KEY UPDATE enrolled = 1");
    } else {
      mq("UPDATE user_tracks JOIN users USING (user_id) SET enrolled = 0 WHERE user_id = $user_id AND school_id = $school_id AND subject_id = $subject_id");
      mq("DELETE FROM user_tracks WHERE user_id = $user_id AND subject_id = $subject_id AND track_id IS NULL AND level IS NULL AND enrolled = 0");
    }
  }
  $message = T_('Updated Enrollment status for the soldiers');
}

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_("Soldier's Campaign Enrollment"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript">
/*
 value =
   1: set on
   0: set off
  -1: toggle
*/
function setCheckboxes(form, nameRegex, value) {
  var pattern = new RegExp(nameRegex);

  for(var i = 0; i < form.elements.length; i++) {
    if(pattern.test(form.elements[i].name) && form.elements[i].type == 'checkbox') {
      form.elements[i].checked = (value == -1 ? !form.elements[i].checked : value);
    }
  }
}
</SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=T_('Base Management')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_users_subject.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<DIV class="ui_body">
<DIV class="ui_menu">
<?ui_menu();?>
</DIV>
<DIV class="content">
<H2><?=T_("Soldier's Campaign Enrollment")?></H2>
<DIV class="infobox">
<P>
<?=sprintf(T_('NOTE: You must first %senroll in campaigns%s in order to use this section.'), '<A HREF="admin_school_subjects.php?school_id=' . $school_id . '">', '</A>')?>
</P>
<P>
<?=T_('If a kiosk is available on your base, Soldiers may select their own campaigns and their selections will be visible here.')?> [Coming soon]
</P>
<P>
<?=T_('IMPORTANT: Soldiers have the last say on all campaign selections. Be sure that you do not mistakenly sign up a Soldier to a campaign without his/her knowledge.')?>
</P>
</DIV>
<DIV class="infobox2">
<FORM action="admin_users_subject.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<LABEL style="white-space: nowrap;"><?=T_('First name')?>: <INPUT type="text" name="first" value="<?=es($first)?>"></LABEL>
<LABEL style="white-space: nowrap;"><?=T_('Last name')?>: <INPUT type="text" name="last" value="<?=es($last)?>"></LABEL>
<LABEL style="white-space: nowrap;"><?=T_('Platoon')?>: <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?while($class_row = mysql_fetch_assoc($class_result)):?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL> <INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
</DIV>

<TABLE style="min-width: 100%;" cellspacing="0" cellpadding="0">
<TR>
<TH style="text-align: <?=$align_start?>; font-weight: normal;"><?=T_('<B>Step 1:</B> Select a campaign.')?></TH>
<TD style="width: 20px;" rowspan="2"></TD>
<TH style="text-align: <?=$align_start?>; font-weight: normal;"><?=T_('<B>Step 2:</B> Select Soldiers to place into the campaign you selected.')?></TH>
</TR>
<TR>
<TD class="box_head">
<FORM action="admin_users_subject.php" method="get" accept-charset="UTF-8">
<? $result = mq("SELECT subject_name, subject_id, subject_image_id FROM subjects JOIN schools USING (inst_id) JOIN school_subjects USING (school_id, subject_id) WHERE school_id = $school_id AND subject_type NOT IN ('school_points', 'home_points') ORDER BY subject_name, subject_id"); ?>
<? $col = 0; ?>
<TABLE style="table-layout: fixed;">
<TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<? if($col++ >= 2): ?>
  <? $col = 1; ?>
  </TR>
  <TR>
<? endif; ?>
<TD style="padding: 10px;">
  <LABEL>
  <INPUT type="radio" name="subject_id" value="<?=$row['subject_id']?>" <?=$row['subject_id'] == $subject_id ? 'CHECKED' : ''?> style="vertical-align: <?=is_null($row['subject_image_id']) ? 'middle' : '40px'?>;" onClick="this.form.submit();">
  <?=!is_null($row['subject_image_id']) ? linkImgFile($row['subject_image_id'], NULL, 80) . '<BR>&nbsp; &nbsp; &nbsp;' : ''?>
  <?=es($row['subject_name'])?>
  </LABEL>
</TD>
<? endwhile; ?>
</TR>
</TABLE>
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<INPUT type="hidden" name="last" value="<?=$first?>">
<INPUT type="hidden" name="first" value="<?=$last?>">
<NOSCRIPT>
<INPUT type="submit" value="Go &gt;&gt;">
</NOSCRIPT>
</P>
</FORM>
</TD>
<TD class="box_content">
<? if($subject_id == -1): ?>
<P><?=T_('Please select a campaign')?></P>
<? else: ?>
<? 

// $result = mq("SELECT users.user_id, users.first, users.last, users.username, class_grade, class_sub, 
// IFNULL(enrolled, 0) enrolled FROM users JOIN school_type_subjects USING (school_type_id) 
// LEFT JOIN user_tracks USING (user_id, subject_id) LEFT JOIN classes USING (school_id, class_id) 
// WHERE subject_id = $subject_id AND school_id = $school_id" . ($class_id != -1 ? " 
// AND class_id = $class_id" : '') . ' ORDER BY class_grade, class_sub, users.last, users.first, users.username'); 

$result = mq("
			SELECT
				users.user_id,
				users.first,
				users.last, 
				users.username,
				class_grade, 
				class_sub,
				IFNULL(enrolled, 0) enrolled 
			FROM
				users
			JOIN school_type_subjects on users.school_type_id = school_type_subjects.school_type_id
			LEFT JOIN user_tracks on users.user_id = user_tracks.user_id and school_type_subjects.subject_id = user_tracks.subject_id
			LEFT JOIN classes on users.school_id = classes.school_id and users.class_id = classes.class_id
			WHERE
				school_type_subjects.subject_id = $subject_id AND users.school_id = $school_id" . ($class_id != -1 ? " 
				AND classes.class_id = $class_id" : '') . ' ORDER BY class_grade, class_sub, users.last, users.first, users.username'); 
				?>

<FORM action="admin_users_subject.php" method="post" accept-charset="UTF-8" name="user_tracks">
<DIV>
<INPUT type="hidden" name="action" value="save">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<INPUT type="hidden" name="subject_id" value="<?=$subject_id?>">
<INPUT type="hidden" name="last" value="<?=$first?>">
<INPUT type="hidden" name="first" value="<?=$last?>">
<TABLE class="list" cellspacing="0" cellpadding="0">
<THEAD>
<TR>
  <TH>
    <?=T_('Enroll')?><BR>
    <A HREF="#" onClick="setCheckboxes(document.forms['user_tracks'], 'enrolled\\[\\d+\\]', 1); return false;"><?=T_('Select All')?></A><BR>
    <A HREF="#" onClick="setCheckboxes(document.forms['user_tracks'], 'enrolled\\[\\d+\\]', 0); return false;"><?=T_('Select None')?></A><BR>
    <A HREF="#" onClick="setCheckboxes(document.forms['user_tracks'], 'enrolled\\[\\d+\\]', -1); return false;"><?=T_('Toggle Selections')?></A>
  </TH>
  <TH><?=T_('Soldier')?></TH>
</TR>
</THEAD>
<? 
$sql = "SELECT
				users.user_id,
				users.first,
				users.last, 
				users.username,
				class_grade, 
				class_sub,
				IFNULL(enrolled, 0) enrolled 
			FROM
				users
			JOIN school_type_subjects on users.school_type_id = school_type_subjects.school_type_id
			LEFT JOIN user_tracks on users.user_id = user_tracks.user_id and school_type_subjects.subject_id = user_tracks.subject_id
			LEFT JOIN classes on users.school_id = classes.school_id and users.class_id = classes.class_id
			WHERE
				school_type_subjects.subject_id = $subject_id AND users.school_id = $school_id" . ($class_id != -1 ? " 
				AND classes.class_id = $class_id" : '') . ' ORDER BY class_grade, class_sub, users.last, users.first, users.username';
echo "<input type='hidden' name='query' value='$sql'>";
while($row = mysql_fetch_assoc($result)):?>
<TR>
  <TD>
    <INPUT type="hidden" name="enrolled[<?=$row['user_id']?>]" value="0">
    <INPUT type="checkbox" id="enrolled_<?=$row['user_id']?>" name="enrolled[<?=$row['user_id']?>]" value="1" <?=$row['enrolled'] ? 'CHECKED': ''?>>
  </TD>
  <TD><LABEL for="enrolled_<?=$row['user_id']?>"><?=$row['class_grade'], '-', $row['class_sub'], ': ', $row['first'], ' ', $row['last'], ' (', $row['username'], ')'?></LABEL></TD>
</TR>
<?endwhile;?>
</TABLE>
<P>
<INPUT type="submit" value="<?=T_('Enroll Soldiers')?>">
</P>
</DIV>
</FORM>

<? endif; ?>

</TD>
</TR>
</TABLE>

<? endif; ?>
<BR style="clear: both;">
</DIV>
</DIV>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
