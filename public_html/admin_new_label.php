<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
require_once('file_save.php');
$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 label_id, '' label_name, '' label_description, NULL label_image_id");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    $label_name = gr('label_name');

    $result = mq('SELECT 1 FROM labels WHERE label_name = ' . ms($label_name));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new label, this name is already used.');
      $result = mq('SELECT -1 label_id, ' . ms($label_name) . ' label_name, ' . ms(gr('label_description')) . ' label_description, NULL label_image_id');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';
    } else {
      $label_image_id = 'NULL';
      if(isset($_FILES['image'])) $label_image_id = addFile($_FILES['image'], $label_image_id);
      mq('INSERT INTO labels (label_name, label_description, label_image_id) VALUES (' . ms($label_name) . ', ' . ms(gr('label_description')) . ", $label_image_id)");
      $message = T_('Label added');
    }
    break;

  case 'delete':
    mq('DELETE FROM files USING files JOIN labels ON (files.file_id = labels.label_image_id) WHERE label_id = ' . gri('label_id', -1));
    mq('DELETE FROM labels WHERE label_id = ' . gri('label_id', -1));
    $message = T_('Label deleted');
    break;

  case 'edit':
    $result = mq('SELECT label_id, label_name, label_description, label_image_id FROM labels WHERE label_id = ' . gri('label_id', -1));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $label_name = gr('label_name');
    $label_id = gri('label_id', -1);

    $result = mq('SELECT 1 FROM labels WHERE label_name = ' . ms($label_name) . " AND label_id != $label_id");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit label, this name is already used.');
      $result = mq("SELECT $label_id label_id, " . ms($label_name) . ' label_name, ' . ms(gr('label_description')) . ' label_description, label_image_id FROM labels WHERE label_id = ' . gri('label_id', -1));
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      $label_image_id = gri('image_delete', 0) ? 'NULL' : 'label_image_id';
      if(isset($_FILES['image'])) $label_image_id = addFile($_FILES['image'], $label_image_id);

      if($label_image_id !== 'label_image_id') mq('DELETE FROM files USING files JOIN labels ON (files.file_id = labels.label_image_id) WHERE label_id = ' . gri('label_id', -1));

      mq('UPDATE labels SET label_name = ' . ms($label_name) . ', label_description = ' . ms(gr('label_description')) . ", label_image_id = $label_image_id WHERE label_id = $label_id");
      $message = T_('Label edited');
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
<TITLE><?=T_('Labels'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Labels')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_label.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="label_id" value="<?=$edit_row['label_id']?>">
<LABEL><?=T_('Name')?>:<INPUT type="text" name="label_name" maxlength=255 value="<?=es($edit_row['label_name'])?>"></LABEL><BR>
<LABEL><?=T_('Description')?>:<INPUT type="text" name="label_description" maxlength=255 value="<?=es($edit_row['label_description'])?>"></LABEL><BR>
<LABEL><?=T_('Image')?><BR><INPUT type="file" name="image" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['label_image_id'])):?>
<?=T_('Uploading a new image will replace the old.')?><BR>
<LABEL><?=T_('Delete current image')?> <INPUT type="checkbox" name="image_delete" class="checkbox" value="1"><BR>
<?=linkImgFile($edit_row['label_image_id'])?><BR>
</LABEL>
<?endif?>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_label.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT label_id, label_name, label_description FROM labels ORDER BY label_name');?>

<A HREF="admin_label.php?action=add"><?=T_('Add new Label')?></A>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('Name')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['label_name'])?></TD>
    <TD><?=es($row['label_description'])?></TD>
    <TD><A HREF="admin_label.php?action=edit&amp;label_id=<?=$row['label_id']?>"><?=T_('Edit Label')?></A></TD>
    <TD><A HREF="admin_label.php?action=delete&amp;label_id=<?=$row['label_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Label')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
