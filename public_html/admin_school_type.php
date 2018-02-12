<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 school_type_id, '' school_type_name, '' school_type_setting");
    $edit_row = mysql_fetch_assoc($result);
    $subject_ids = array();
    break;

  case 'add2':
    $school_type_name = gr('school_type_name');
    $subject_ids = gra('subject_ids', array());
    $school_type_setting = gr('school_type_setting');

    $result = mq('SELECT 1 FROM school_types WHERE school_type_name = ' . ms($school_type_name));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new tzivos hashem type, this name is already used.');
      $result = mq('SELECT -1 school_type_id, ' . ms($school_type_name) . ' school_type_name, '. ms($school_type_setting) . ' school_type_setting');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';
    } else {
      mq("INSERT INTO school_types (school_type_name, school_type_setting) VALUES (" . ms($school_type_name) . ', ' . ms($school_type_setting) . ")");
      foreach($subject_ids as $subject_id) {
        mq("INSERT INTO school_type_subjects (subject_id, school_type_id) VALUE ($subject_id, LAST_INSERT_ID())");
      }
      $message = T_('Tzivos Hashem Type added');
    }
    break;

  case 'delete':
    mq('DELETE FROM school_types WHERE school_type_id = ' . gri('school_type_id', -1));
    $message = T_('Tzivos Hashem Type deleted');
    break;

  case 'edit':
    $result = mq('SELECT subject_id FROM school_type_subjects WHERE school_type_id = ' . gri('school_type_id', -1));
    $subject_ids = mysql_fetch_column($result);
    $result = mq('SELECT school_type_id, school_type_name, school_type_setting FROM school_types WHERE school_type_id = ' . gri('school_type_id', -1));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $school_type_name = gr('school_type_name');
    $school_type_id = gri('school_type_id', -1);
    $subject_ids = array_filter(gra('subject_ids', array()), 'is_numeric');
    $school_type_setting = gr('school_type_setting');

    $result = mq('SELECT 1 FROM school_types WHERE school_type_name = ' . ms($school_type_name) . " AND school_type_id != $school_type_id");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit school_type, this name is already used.');
      $result = mq("SELECT $school_type_id school_type_id, " . ms($school_type_name) . ' school_type_name, '. ms($school_type_setting) . ' school_type_setting');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      mq('UPDATE school_types SET school_type_name = ' . ms($school_type_name) . ', school_type_setting = '. ms($school_type_setting) . " WHERE school_type_id = $school_type_id");
      mq("DELETE FROM school_type_subjects WHERE school_type_id = $school_type_id AND subject_id NOT IN (" . implode(',', array_merge(array(-1), $subject_ids)) . ")");
      foreach($subject_ids as $subject_id) {
        mq("INSERT IGNORE INTO school_type_subjects (subject_id, school_type_id) VALUE ($subject_id, $school_type_id)");
      }
      $message = T_('Tzivos Hashem Type edited');
    }
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}

$subject_result = mq('SELECT subjects.subject_id, subjects.subject_name, institutions.inst_name FROM subjects LEFT JOIN institutions USING (inst_id) ORDER BY institutions.inst_name, subjects.subject_name');

$result = mq('SELECT school_type_name FROM school_types ORDER BY school_type_name');
$menu_school_type = '<UL>';
while($row = mysql_fetch_assoc($result)) $menu_school_type .= "<LI>{$row['school_type_name']}";
$menu_school_type .= '</UL>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Tzivos Hashem Types'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Tzivos Hashem Types')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_school_type.php" method="post" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="school_type_id" value="<?=$edit_row['school_type_id']?>">
<LABEL><?=T_('Name')?><BR><INPUT type="text" name="school_type_name" maxlength=255 value="<?=es($edit_row['school_type_name'])?>"></LABEL><BR>
<LABEL><?=T_('Subjects')?><BR><SELECT name="subject_ids[]" multiple size="10">
      <? while($row = mysql_fetch_assoc($subject_result)): ?>
        <OPTION VALUE="<?=$row['subject_id']?>" <?=in_array($row['subject_id'], $subject_ids) ? 'SELECTED' : '' ?>><?=es($row['inst_name'])?> - <?=es($row['subject_name'])?></OPTION>
      <? endwhile; ?>
</SELECT></LABEL><BR>
<?=T_('Setting')?><BR>
<? foreach(mysql_enum_values('school_types','school_type_setting') as $type): ?>
  <LABEL><INPUT type="radio" NAME="school_type_setting" VALUE="<?=$type?>" <?=$type == $edit_row['school_type_setting'] ? 'CHECKED' : '' ?>><?=
$type == 'managed' ? T_('Fully managed by admins.') :
 ($type == 'managed_personal' ? T_('Fully managed, but users can create personal tasks.') :
 ($type == 'self_managed' ? T_('Users can create personal tasks, and can control their ladder, year and tzivos hashem type.') :
 ($type == 'personal_only' ? T_('Only personal tasks will be used.') :
 $type
))) ?></LABEL><BR>
<? endforeach; ?><BR>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_school_type.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT school_type_id, school_type_name, school_type_setting FROM school_types ORDER BY school_type_name');?>

<A HREF="admin_school_type.php?action=add"><?=T_('Add new Tzivos Hashem Type')?></A>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('Name')?></TH>
  <TH><?=T_('Setting')?></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['school_type_name'])?></TD>
    <TD><?=es($row['school_type_setting'])?></TD>
    <TD><A HREF="admin_school_type.php?action=edit&amp;school_type_id=<?=$row['school_type_id']?>"><?=T_('Edit Tzivos Hashem Type')?></A></TD>
    <TD><A HREF="admin_school_type.php?action=delete&amp;school_type_id=<?=$row['school_type_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Tzivos Hashem Type')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
