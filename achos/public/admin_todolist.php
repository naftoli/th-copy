<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
require_once('file_save.php');
$action = gr('action');
$edit_row = false;

if($admin_user['auth'] != 'super') {
  if(gr('recip') == 'school') sgr('recip', 'end_user');
}

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 todo_id, '" . ($admin_user['auth'] == 'super' ? 'school' : 'end_user') . "' recip, NULL recip_id, NULL school_id, '' todo_text, 'Medium' todo_priority, -1 category_id, NULL todo_due_date, NULL todo_file_id, '' todo_url, 'all' visibility");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    if(gr('recip') == 'school') {
      $school_id = 'NULL';
    } else {
      assure_id_school('school_id');
      if(is_null($school_id = gri('school_id'))) break;
    }
    $todo_file_id = 'NULL';
    if(isset($_FILES['file'])) $todo_file_id = addFile($_FILES['file'], $todo_file_id);
    mq('INSERT INTO todo_list (recip, recip_id, school_id, todo_text, todo_priority, category_id, todo_due_date, todo_url, visibility, todo_file_id) VALUES (' . ms(gr('recip')) . ", NULL, $school_id, " . ms(gr('todo_text')) . ', ' . ms(gr('todo_priority')) . ', ' . gri('category_id', -1) . ', ' . nullif(gr('todo_due_date'), '') . ', ' . ms(gr('todo_url')) . ', ' . ms(gr('visibility')) . ", $todo_file_id)");
    $message = T_('To-do added');
    break;

  case 'delete':
    mq('DELETE FROM todo_list_marks WHERE todo_id = ' . gri('todo_id', -1));
    mq('DELETE FROM files USING files JOIN todo_list ON (files.file_id = todo_list.todo_file_id) WHERE todo_id = ' . gri('todo_id', -1));
    mq('DELETE FROM todo_list WHERE todo_id = ' . gri('todo_id', -1));
    $message = T_('To-do deleted');
    break;

  case 'edit':
    $result = mq('SELECT todo_id, recip, recip_id, school_id, school_name, todo_text, todo_priority, category_id, todo_due_date, todo_file_id, todo_url, visibility FROM todo_list LEFT JOIN schools USING (school_id) WHERE todo_id = ' . gri('todo_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : ''));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $todo_file_id = gri('file_delete', 0) ? 'NULL' : 'todo_file_id';
    if(isset($_FILES['file'])) $todo_file_id = addFile($_FILES['file'], $todo_file_id);

    if($todo_file_id !== 'todo_file_id') mq('DELETE FROM files USING files JOIN todo_list ON (files.file_id = todo_list.todo_file_id) WHERE todo_id = ' . gri('todo_id', -1));

    mq('UPDATE todo_list SET recip_id  = NULL, todo_text = ' . ms(gr('todo_text')) . ', todo_priority = ' . ms(gr('todo_priority')) . ', category_id = ' . gri('category_id', -1) . ', todo_due_date = ' . nullif(gr('todo_due_date'), '') . ', todo_url = ' . ms(gr('todo_url')) . ', visibility = ' . ms(gr('visibility')) . ", todo_file_id = $todo_file_id WHERE todo_id = " . gri('todo_id', -1) . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : ''));
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
<TITLE><?=T_('To-do List'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('To-do List')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_todolist.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="todo_id" value="<?=$edit_row['todo_id']?>">

<LABEL><?=T_('Recipient')?>:

<? if($action == 'edit'): ?>
<?=authTypeToName($edit_row['recip'])?>
<? if($edit_row['recip'] != 'school') echo ' ', T_('of'), ' ', $edit_row['school_id'] ? es($edit_row['school_name']) : '&lt;' . T_('All schools') . '&gt;'; ?>
<BR>
<? else: ?>
<SELECT name="recip" onChange="document.getElementById('of').style.display = this.value == 'school' ? 'none' : ''">
<?if($admin_user['auth']=='super'):?><OPTION value="school" <?=$edit_row['recip']=='school' ? 'SELECTED' : ''?>><?=authTypeToName('school')?><?endif;?>
<!--<OPTION value="class" <?=$edit_row['recip']=='class' ? 'SELECTED' : ''?>><?=authTypeToName('class')?>-->
<!--<OPTION value="team" <?=$edit_row['recip']=='team' ? 'SELECTED' : ''?>><?=authTypeToName('team')?>-->
<OPTION value="user" <?=$edit_row['recip']=='user' ? 'SELECTED' : ''?>><?=authTypeToName('user')?>
<OPTION value="end_user" <?=$edit_row['recip']=='end_user' ? 'SELECTED' : ''?>><?=authTypeToName('end_user')?>
</SELECT></LABEL>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<LABEL id="of" <?=$edit_row['recip'] == 'school' ? 'style="display: none;"' : ''?>><?=T_('of')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $edit_row['school_id'] ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<? @mysql_data_seek($school_result, 0); ?>
<?endif;?>

<!--
<LABEL><?=T_('Institution')?>: <SELECT name="recip_id">
<OPTION value="" <?=is_null($edit_row['recip_id']) ? 'SELECTED' : ''?>>&lt;<?=T_('All')?>&gt;</OPTION>
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $edit_row['recip_id'] ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
-->
<?endif;?>

<LABEL><?=T_('Description')?>: <INPUT type="text" name="todo_text" maxlength="255" size="80" value="<?=es($edit_row['todo_text'])?>"></LABEL><BR>
<?=T_('Priority')?>:
<LABEL><INPUT type="radio" name="todo_priority" value="Urgent" <?= $edit_row['todo_priority'] == 'Urgent' ? 'CHECKED' : ''?>> <?=T_('Urgent')?></LABEL>
<LABEL><INPUT type="radio" name="todo_priority" value="High" <?= $edit_row['todo_priority'] == 'High' ? 'CHECKED' : ''?>> <?=T_('High')?></LABEL>
<LABEL><INPUT type="radio" name="todo_priority" value="Medium" <?= $edit_row['todo_priority'] == 'Medium' ? 'CHECKED' : ''?>> <?=T_('Medium')?></LABEL>
<LABEL><INPUT type="radio" name="todo_priority" value="Low" <?= $edit_row['todo_priority'] == 'Low' ? 'CHECKED' : ''?>> <?=T_('Low')?></LABEL>
<BR>
<LABEL><?=T_('Category')?>:  <SELECT name="category_id">
<? $result = mq('SELECT inst_name, subject_name, category_name, category_id FROM todo_categories LEFT JOIN subjects USING (subject_id) LEFT JOIN institutions USING (inst_id) ORDER BY inst_name, subject_name, category_name'); ?>
<?while($row = mysql_fetch_assoc($result)):?>
<OPTION value="<?=$row['category_id']?>" <?=$row['category_id'] == $edit_row['category_id'] ? 'SELECTED' : ''?>><?=es($row['inst_name']), ' - ', $row['subject_name'], ' / ', $row['category_name']?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<LABEL><?=T_('Due Date')?>: <INPUT type="text" name="todo_due_date_disp" READONLY value="<?=es(dateToHebrew($edit_row['todo_due_date']))?>" onClick="getDate(this.form, 'todo_due_date', false);"></LABEL><INPUT type="hidden" name="todo_due_date" value="<?=$edit_row['todo_due_date']?>"><BR>
<LABEL><?=T_('File')?><BR><INPUT type="file" name="file" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['todo_file_id'])):?>
<?=T_('Uploading a new file will replace the old.')?><BR>
<LABEL><?=T_('Delete current file')?> <INPUT type="checkbox" name="file_delete" class="checkbox" value="1"><BR>
<?=linkImgFile($edit_row['todo_file_id'])?><BR>
</LABEL>
<?endif?>
<LABEL><?=T_('URL')?>: <INPUT type="text" name="todo_url" maxlength="2048" size="80" value="<?=es($edit_row['todo_url'])?>"></LABEL><BR>
<LABEL><?=T_('Visibility')?>: <SELECT name="visibility">
<OPTION value="all" <?='all' == $edit_row['visibility'] ? 'SELECTED' : ''?>>Normal
<OPTION value="none" <?='none' == $edit_row['visibility'] ? 'SELECTED' : ''?>>Hidden
</SELECT></LABEL><BR>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_todolist.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT todo_id, recip, school_name, recip_id, todo_text, todo_priority, todo_due_date, todo_file_id, todo_url, visibility, COUNT(mark_date) marked, inst_name, subject_name, category_name FROM todo_list LEFT JOIN todo_categories USING (category_id) LEFT JOIN subjects USING (subject_id) LEFT JOIN institutions USING (inst_id) LEFT JOIN schools USING (school_id) LEFT JOIN todo_list_marks USING (todo_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' GROUP BY todo_id, todo_text ORDER BY visibility, recip, school_name, inst_name, subject_name, category_name, creation_date, todo_text, todo_id');?>

<A HREF="admin_todolist.php?action=add"><?=T_('Add new To-do')?></A>

<TABLE CLASS="list" style="font-size:10px">
<TR>
  <TH><?=T_('Visibility')?></TH>
  <TH><?=T_('Recipient')?></TH>
  <TH><?=T_('Sepcific Recipient')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('Priority')?></TH>
  <TH><?=T_('Category')?></TH>
  <TH><?=T_('Due Date')?></TH>
  <TH><?=T_('File')?></TH>
  <TH><?=T_('URL')?></TH>
  <TH><?=T_('# Marked')?></TH>
  <TH></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['visibility'] == 'all' ? '' : ($row['visibility'] == 'none' ? 'Hidden' : $row['visibility']))?></TD>
    <TD><?=authTypeToName($row['recip']), $row['school_name'] ? ' ' . T_('of') . ' ' . es($row['school_name']) : ''?></TD>
    <TD><?=$row['recip_id'] ? authTypeToName($row['recip'], false, false) . ': ' . es(authToName($row['recip'], $row['recip_id'])) : ''?></TD>
    <TD><?=es($row['todo_text'])?></TD>
    <TD><?=$row['todo_priority']?></TD>
    <TD><?=es($row['inst_name']), ' - ', es($row['subject_name']), ' / ', es($row['category_name'])?></TD>
    <TD><?=es(dateToHebrew($row['todo_due_date']))?></TD>
    <TD><?if(!is_null($row['todo_file_id'])):?><A HREF="file_view.php?id=<?=$row['todo_file_id']?>&amp;m=d"><?=T_('View File')?>&raquo;</A><?endif;?></TD>
    <TD><?if($row['todo_url']):?><A HREF="<?=es($row['todo_url'])?>"><?=T_('Goto Link')?>&raquo;</A><?endif;?></TD>
    <TD><?=$row['marked']?></TD>
    <TD><A HREF="admin_todolist.php?action=edit&amp;todo_id=<?=$row['todo_id']?>"><?=T_('Edit To-do')?></A></TD>
    <TD><A HREF="admin_todolist.php?action=delete&amp;todo_id=<?=$row['todo_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete To-do')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
