<? $admin_auth = array(); ?>
<? require('header.php'); ?>
<?
$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 school_makeup_id, '' school_makeup_name");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    $school_makeup_name = gr('school_makeup_name');

    $result = mq('SELECT 1 FROM school_makeups WHERE school_makeup_name = ' . ms($school_makeup_name));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new school type, this name is already used.');
      $result = mq('SELECT -1 school_makeup_id, ' . ms($school_makeup_name) . ' school_makeup_name');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';
    } else {
      mq('INSERT INTO school_makeups (school_makeup_name) VALUES (' . ms($school_makeup_name) . ')');
      $message = T_('School type added');
    }
    break;

  case 'delete':
    mq('DELETE FROM school_makeups WHERE school_makeup_id = ' . gri('school_makeup_id', -1));
    $message = T_('School type deleted');
    break;

  case 'edit':
    $result = mq('SELECT school_makeup_id, school_makeup_name FROM school_makeups WHERE school_makeup_id = ' . gri('school_makeup_id', -1));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $school_makeup_name = gr('school_makeup_name');
    $school_makeup_id = gri('school_makeup_id', -1);

    $result = mq('SELECT 1 FROM school_makeups WHERE school_makeup_name = ' . ms($school_makeup_name) . " AND school_makeup_id != $school_makeup_id");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit school type, this name is already used.');
      $result = mq("SELECT $school_makeup_id school_makeup_id, " . ms($school_makeup_name) . ' school_makeup_name');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      mq('UPDATE school_makeups SET school_makeup_name = ' . ms($school_makeup_name) . " WHERE school_makeup_id = $school_makeup_id");
      $message = T_('School type edited');
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
<TITLE><?=T_('School Types'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('School Types')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_school_makeup.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="school_makeup_id" value="<?=$edit_row['school_makeup_id']?>">
<LABEL><?=T_('Name')?>:<INPUT type="text" name="school_makeup_name" maxlength=255 value="<?=es($edit_row['school_makeup_name'])?>"></LABEL><BR>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_school_makeup.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT school_makeup_id, school_makeup_name FROM school_makeups ORDER BY school_makeup_name');?>

<A HREF="admin_school_makeup.php?action=add"><?=T_('Add new school type')?></A>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('Name')?></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['school_makeup_name'])?></TD>
    <TD><A HREF="admin_school_makeup.php?action=edit&amp;school_makeup_id=<?=$row['school_makeup_id']?>"><?=T_('Edit school type')?></A></TD>
    <TD><A HREF="admin_school_makeup.php?action=delete&amp;school_makeup_id=<?=$row['school_makeup_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete school type')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
