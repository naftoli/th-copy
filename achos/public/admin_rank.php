<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
require_once('file_save.php');
$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 rank_ord, '' rank_name, 0 medals_required, NULL prof_rank_image_id, rank_image_id, '' rank_color, NULL rank_background_image_id");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    $rank_name = gr('rank_name');
    $medals_required = gri('medals_required', 0);

    $result = mq('SELECT 1 FROM ranks WHERE rank_name = ' . ms($rank_name));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new rank, this name is already used.');
      $result = mq('SELECT -1 rank_ord, ' . ms($rank_name) . " rank_name, $medals_required medals_required, NULL prof_rank_image_id, NULL rank_image_id, rank_color, NULL rank_background_image_id");
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';
    } else {
      $rank_image_id = 'NULL';
      if(isset($_FILES['image'])) $rank_image_id = addFile($_FILES['image'], $rank_image_id);
      $prof_rank_image_id = 'NULL';
      if(isset($_FILES['prof_image'])) $prof_rank_image_id = addFile($_FILES['prof_image'], $prof_rank_image_id);
      $rank_background_image_id = 'NULL';
      if(isset($_FILES['background_image'])) $rank_background_image_id = addFile($_FILES['background_image'], $rank_background_image_id);
      mq('INSERT INTO ranks (rank_name, medals_required, prof_rank_image_id, rank_image_id, rank_color, rank_background_image_id) VALUES (' . ms($rank_name) . ", $medals_required, $rank_image_id, $prof_rank_image_id, " . ms(gr('rank_color')) . ", $rank_background_image_id)");
      $message = T_('Rank added');
    }
    break;

  case 'edit':
    $rank_ord = gri('rank_ord', -1);
    $result = mq("SELECT rank_ord, rank_name, medals_required, prof_rank_image_id, rank_image_id, rank_color, rank_background_image_id FROM ranks WHERE rank_ord = $rank_ord");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $rank_ord = gri('rank_ord', -1);
    $rank_name = gr('rank_name');
    $medals_required = gri('medals_required', 0);

    $result = mq('SELECT 1 FROM ranks WHERE rank_name = ' . ms($rank_name) . " AND rank_ord != $rank_ord");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit rank, this name is already used.');
      $result = mq("SELECT $rank_ord rank_ord, " . ms($rank_name) . " rank_name, $medals_required medals_required, rank_image_id, prof_rank_image_id,  " . ms(gr('rank_color')) . " rank_color, rank_background_image_id FROM ranks WHERE rank_ord = $rank_ord");
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      $rank_image_id = gri('image_delete', 0) ? 'NULL' : 'rank_image_id';
      if(isset($_FILES['image'])) $rank_image_id = addFile($_FILES['image'], $rank_image_id);

      if($rank_image_id !== 'rank_image_id') mq("DELETE FROM files USING files JOIN ranks ON (files.file_id = ranks.rank_image_id) WHERE rank_ord = $rank_ord");
      
      $prof_rank_image_id = gri('prof_image_delete', 0) ? 'NULL' : 'prof_rank_image_id';
      if(isset($_FILES['prof_image'])) $prof_rank_image_id = addFile($_FILES['prof_image'], $prof_rank_image_id);

      if($prof_rank_image_id !== 'prof_rank_image_id') mq("DELETE FROM files USING files JOIN ranks ON (files.file_id = ranks.prof_rank_image_id) WHERE rank_ord = $rank_ord");

      $rank_background_image_id = gri('background_image_delete', 0) ? 'NULL' : 'rank_background_image_id';
      if(isset($_FILES['background_image'])) $rank_background_image_id = addFile($_FILES['background_image'], $rank_background_image_id);

      if($rank_background_image_id !== 'rank_background_image_id') mq("DELETE FROM files USING files JOIN ranks ON (files.file_id = ranks.rank_background_image_id) WHERE rank_ord = $rank_ord");

      mq('UPDATE ranks SET rank_name = ' . ms($rank_name) . ", medals_required = $medals_required, rank_image_id = $rank_image_id, prof_rank_image_id = $prof_rank_image_id, rank_color = " . ms(gr('rank_color')) . ", rank_background_image_id = $rank_background_image_id WHERE rank_ord = $rank_ord");

      $message = T_('Rank edited');
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
<TITLE><?=T_('Ranks').' - '.T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Ranks')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_rank.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="rank_ord" value="<?=$edit_row['rank_ord']?>">
<LABEL><?=T_('Name')?><BR><INPUT type="text" name="rank_name" maxlength=255 value="<?=es($edit_row['rank_name'])?>"></LABEL><BR>
<LABEL><?=T_('Medals required to reach the next rank (0 means the rank is automatically awarded)')?><BR><INPUT type="text" name="medals_required" maxlength="3" value="<?=$edit_row['medals_required']?>" onChange="this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 255));"></LABEL><BR>
<LABEL><?=T_('Image')?><BR><INPUT type="file" name="image" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['rank_image_id'])):?>
<?=T_('Uploading a new image will replace the old.')?><BR>
<LABEL><?=T_('Delete current image')?> <INPUT type="checkbox" name="image_delete" class="checkbox" value="1"><BR>
<?=linkImgFile($edit_row['rank_image_id'])?><BR>
</LABEL>
<?endif?>
<LABEL>Left Image in Ranks Profile page:<?=T_('Image')?><BR><INPUT type="file" name="prof_image" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['prof_rank_image_id'])):?>
<?=T_('Uploading a new image will replace the old.')?><BR>
<LABEL><?=T_('Delete current image')?> <INPUT type="checkbox" name="prof_image_delete" class="checkbox" value="1"><BR>
<?=linkImgFile($edit_row['prof_rank_image_id'])?><BR>
</LABEL>
<?endif?>
<LABEL><?=T_('Color')?><BR><INPUT type="text" name="rank_color" maxlength=32 value="<?=es($edit_row['rank_color'])?>"></LABEL> (<?=T_('As color name or #nnnnnn')?>)<BR>
<LABEL><?=T_('Background Image')?><BR><INPUT type="file" name="background_image" class="file"></LABEL> <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<BR>
<?if(!is_null($edit_row['rank_background_image_id'])):?>
<?=T_('Uploading a new image will replace the old.')?><BR>
<LABEL><?=T_('Delete current image')?> <INPUT type="checkbox" name="background_image_delete" class="checkbox" value="1"><BR>
<?=linkImgFile($edit_row['rank_background_image_id'])?><BR>
</LABEL>
<?endif?>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_rank.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT rank_ord, rank_name, medals_required, rank_image_id, prof_rank_image_id, rank_color, rank_background_image_id FROM ranks ORDER BY rank_ord');?>

<A HREF="admin_rank.php?action=add"><?=T_('Add new Rank')?></A>
<P>
<?=T_("Note: Ranks can't be deleted or have their order changed using the website, so please create them in the proper order.")?>
</P>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('#')?></TH>
  <TH><?=T_('Name')?></TH>
  <TH><?=T_('Total Medals required to reach this rank')?></TH>
  <TH><?=T_('Image')?></TH>
  <TH><?=T_('3d Image')?></TH>
  <TH><?=T_('Color')?></TH>
  <TH><?=T_('Background Image')?></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=$row['rank_ord']?></TD>
    <TD><?=es($row['rank_name'])?></TD>
    <TD><?=$row['medals_required']?></TD>
    <TD><?=!is_null($row['rank_image_id']) ? linkImgFile($row['rank_image_id'], 50) : ''?></TD>
    <TD><?=!is_null($row['prof_rank_image_id']) ? linkImgFile($row['prof_rank_image_id'], 50) : ''?></TD>
    <TD><?if($row['rank_color'] !== ''):?><?=es($row['rank_color'])?><BR><DIV style="width: 50px; height: 50px; border: 1px solid black; background-color: <?=es($row['rank_color'])?>;">&nbsp;</DIV><?endif;?></TD>
    <TD><?=!is_null($row['rank_background_image_id']) ? linkImgFile($row['rank_background_image_id'], 50) : ''?></TD>
    <TD><A HREF="admin_rank.php?action=edit&amp;rank_ord=<?=$row['rank_ord']?>"><?=T_('Edit Rank')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
