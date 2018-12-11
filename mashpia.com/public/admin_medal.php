<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
require_once('file_save.php');
$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'edit':
    $result = mq('SELECT medal_ord, medal_name, medal_on_image_id, medal_off_image_id FROM medals WHERE medal_ord = ' . gri('medal_ord', -1));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $medal_name = gr('medal_name');
    $medal_ord = gri('medal_ord', -1);

    $result = mq('SELECT 1 FROM medals WHERE medal_name = ' . ms($medal_name) . " AND medal_ord != $medal_ord");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit medal, this name is already used.');
      $result = mq("SELECT $medal_ord medal_ord, " . ms($medal_name) . ' medal_name, medal_on_image_id, medal_off_image_id');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      $medal_on_image_id = gri('image_on_delete', 0) ? 'NULL' : 'medal_on_image_id';
      $medal_off_image_id = gri('image_off_delete', 0) ? 'NULL' : 'medal_off_image_id';
      if(isset($_FILES['image_on'])) $medal_on_image_id = addFile($_FILES['image_on'], $medal_on_image_id);
      if(isset($_FILES['image_off'])) $medal_off_image_id = addFile($_FILES['image_off'], $medal_off_image_id);

      if($medal_on_image_id !== 'medal_on_image_id') mq('DELETE FROM files USING files JOIN medals ON (files.file_id = medals.medal_on_image_id) WHERE medal_ord = ' . gri('medal_ord', -1));
      if($medal_off_image_id !== 'medal_off_image_id') mq('DELETE FROM files USING files JOIN medals ON (files.file_id = medals.medal_off_image_id) WHERE medal_ord = ' . gri('medal_ord', -1));

      mq('UPDATE medals SET medal_name = ' . ms($medal_name) . ", medal_on_image_id = $medal_on_image_id, medal_off_image_id = $medal_off_image_id WHERE medal_ord = $medal_ord");
      $message = T_('Medal edited');
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
<TITLE><?=T_('Medals'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Medals')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_medal.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
<P>
<INPUT type="hidden" name="action" value="edit2">
<INPUT type="hidden" name="medal_ord" value="<?=$edit_row['medal_ord']?>">
<LABEL><?=T_('Name')?>:<INPUT type="text" name="medal_name" maxlength=255 value="<?=es($edit_row['medal_name'])?>"></LABEL><BR>
<LABEL><?=T_('Image On')?><BR><INPUT type="file" name="image_on" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['medal_on_image_id'])):?>
<?=T_('Uploading a new image will replace the old.')?><BR>
<LABEL><?=T_('Delete current image')?> <INPUT type="checkbox" name="image_on_delete" class="checkbox" value="1"><BR>
<?=linkImgFile($edit_row['medal_on_image_id'])?><BR>
</LABEL>
<?endif?>
<LABEL><?=T_('Image Off')?><BR><INPUT type="file" name="image_off" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['medal_off_image_id'])):?>
<?=T_('Uploading a new image will replace the old.')?><BR>
<LABEL><?=T_('Delete current image')?> <INPUT type="checkbox" name="image_off_delete" class="checkbox" value="1"><BR>
<?=linkImgFile($edit_row['medal_off_image_id'])?><BR>
</LABEL>
<?endif?>
<INPUT type="submit" value="<?=T_('Save')?>">
</P>
</FORM>

<A HREF="admin_medal.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT medal_ord, medal_name, medal_on_image_id, medal_off_image_id FROM medals ORDER BY medal_ord');?>

<TABLE CLASS="list">
<TR>
  <TH>#</TH>
  <TH><?=T_('Name')?></TH>
  <TH><?=T_('Image On')?></TH>
  <TH><?=T_('Image Off')?></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=$row['medal_ord']?></TD>
    <TD><?=es($row['medal_name'])?></TD>
    <TD><?=!is_null($row['medal_on_image_id']) ? linkImgFile($row['medal_on_image_id']) : ''?></TD>
    <TD><?=!is_null($row['medal_off_image_id']) ? linkImgFile($row['medal_off_image_id']) : ''?></TD>
    <TD><A HREF="admin_medal.php?action=edit&amp;medal_ord=<?=$row['medal_ord']?>"><?=T_('Edit Medal')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
