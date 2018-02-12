<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 track_id, '' track_name");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    $track_name = gr('track_name');

    $result = mq('SELECT 1 FROM tracks WHERE track_name = ' . ms($track_name));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new ladder, this name is already used.');
      $result = mq('SELECT -1 track_id, ' . ms($track_name) . ' track_name');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';
    } else {
      mq('INSERT INTO tracks (track_name) VALUES (' . ms($track_name) . ")");
      $message = T_('Ladder added');
    }
    break;

  case 'delete':
    mq('DELETE FROM tracks WHERE track_id = ' . gri('track_id', -1));
    $message = T_('Ladder deleted');
    break;

  case 'edit':
    $result = mq('SELECT track_id, track_name FROM tracks WHERE track_id = ' . gri('track_id', -1));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $track_name = gr('track_name');
    $track_id = gri('track_id', -1);

    $result = mq('SELECT 1 FROM tracks WHERE track_name = ' . ms($track_name) . " AND track_id != $track_id");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit ladder, this name is already used.');
      $result = mq("SELECT $track_id track_id, " . ms($track_name) . ' track_name');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      mq('UPDATE tracks SET track_name = ' . ms($track_name) . " WHERE track_id = $track_id");
      $message = T_('Ladder edited');
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
<TITLE><?=T_('Ladders'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Ladders')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_track.php" method="post" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="track_id" value="<?=$edit_row['track_id']?>">
<LABEL><?=T_('Name')?>:<INPUT type="text" name="track_name" maxlength=255 value="<?=es($edit_row['track_name'])?>"></LABEL><BR>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_track.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT track_id, track_name FROM tracks ORDER BY track_name');?>

<A HREF="admin_track.php?action=add"><?=T_('Add new Ladder')?></A>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('Name')?></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['track_name'])?></TD>
    <TD><A HREF="admin_track.php?action=edit&amp;track_id=<?=$row['track_id']?>"><?=T_('Edit Ladder')?></A></TD>
    <TD><A HREF="admin_track.php?action=delete&amp;track_id=<?=$row['track_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Ladder')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
