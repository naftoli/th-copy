<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
require_once('file_save.php');
$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 inst_id, '' inst_name, NULL inst_logo_id");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    $inst_name = gr('inst_name');

    $result = mq('SELECT 1 FROM institutions WHERE inst_name = ' . ms($inst_name));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new institution type, this name is already used.');
      $result = mq('SELECT -1 inst_id, ' . ms($inst_name) . ' inst_name, NULL inst_logo_id');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';
    } else {
      $inst_logo_id = 'NULL';
      if(isset($_FILES['logo'])) $inst_logo_id = addFile($_FILES['logo'], $inst_logo_id);
      mq('INSERT INTO institutions (inst_name, inst_logo_id) VALUES (' . ms($inst_name) . ", $inst_logo_id)");
      $message = T_('Institution type added');
    }
    break;

  case 'delete':
    mq('DELETE FROM files USING files JOIN institutions ON (files.file_id = institutions.inst_logo_id) WHERE inst_id = ' . gri('inst_id', -1));
    mq('DELETE FROM institutions WHERE inst_id = ' . gri('inst_id', -1));
    $message = T_('Institution type deleted');
    break;

  case 'edit':
    $result = mq('SELECT inst_id, inst_name, inst_logo_id FROM institutions WHERE inst_id = ' . gri('inst_id', -1));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $inst_name = gr('inst_name');
    $inst_id = gri('inst_id', -1);

    $result = mq('SELECT 1 FROM institutions WHERE inst_name = ' . ms($inst_name) . " AND inst_id != $inst_id");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit institution type, this name is already used.');
      $result = mq("SELECT $inst_id inst_id, " . ms($inst_name) . " inst_name, inst_logo_id FROM institutions WHERE inst_id = $inst_id");
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      $inst_logo_id = gri('logo_delete', 0) ? 'NULL' : 'inst_logo_id';
      if(isset($_FILES['logo'])) $inst_logo_id = addFile($_FILES['logo'], $inst_logo_id);

      if($inst_logo_id !== 'inst_logo_id') mq("DELETE FROM files USING files JOIN institutions ON (files.file_id = institutions.inst_logo_id) WHERE inst_id = $inst_id");

      mq('UPDATE institutions SET inst_name = ' . ms($inst_name) . ", inst_logo_id = $inst_logo_id WHERE inst_id = $inst_id");
      $message = T_('Institution type edited');
    }
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
<TITLE><?=T_('Institution Types'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Institution Types')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_inst.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="inst_id" value="<?=$edit_row['inst_id']?>">
<LABEL><?=T_('Name')?>:<INPUT type="text" name="inst_name" maxlength=255 value="<?=es($edit_row['inst_name'])?>"></LABEL><BR>
<LABEL><?=T_('Logo')?><BR><INPUT type="file" name="logo" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['inst_logo_id'])):?>
<?=T_('Uploading a new logo will replace the old.')?><BR>
<LABEL><?=T_('Delete current logo')?> <INPUT type="checkbox" name="logo_delete" class="checkbox" value="1"><BR>
<?=linkImgFile($edit_row['inst_logo_id'])?><BR>
</LABEL>
<?endif?>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_inst.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT inst_id, inst_name FROM institutions ORDER BY inst_name');?>

<A HREF="admin_inst.php?action=add"><?=T_('Add new institution type')?></A>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('Name')?></TH>
  <TH></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['inst_name'])?></TD>
    <TD><A HREF="admin_inst.php?action=edit&amp;inst_id=<?=$row['inst_id']?>"><?=T_('Edit institution type')?></A></TD>
    <TD><A HREF="admin_school.php?inst_id=<?=$row['inst_id']?>"><?=T_('Manage Institutions')?></A></TD>
    <TD><A HREF="admin_inst.php?action=delete&amp;inst_id=<?=$row['inst_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete institution type')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
