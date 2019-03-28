<? $admin_auth = array(); ?>
<? require('header.php'); // requires db.php and admin_auth.php as well?>
<?
// gr(key: string) -> get key from GET or POST. Found in header.php#74
$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    // mq -> db.php#276
    $result = mq("SELECT -1 role_id, '' role_name, '' role_auth");
    $edit_row = mysql_fetch_assoc($result);
    break;

  // another type of add (takes a name and auth as well)
  case 'add2':
    $role_name = gr('role_name');
    $role_auth = gr('role_auth');
    // see if it already exists in the db with this query
    $result = mq('SELECT 1 FROM roles WHERE role_name= ' . ms($role_name) . ' AND role_auth = ' . ms($role_auth));
    // if the user exists throw and error and go back to just plain old 'add'
    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new role, this name is already used.');
      $result = mq('SELECT -1 role_id, ' . ms($role_name) . ' role_name, '. ms($role_auth) . ' role_auth');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';
    } else { // add the new role into the table
      mq("INSERT INTO roles (role_name, role_auth) VALUES (" . ms($role_name) . ', ' . ms($role_auth) . ")");
      $message = T_('Role added');
    }
    break;
  // Delete the role id
  case 'delete':
    mq('DELETE FROM roles WHERE role_id = ' . gri('role_id', -1));
    //should I NULL out admin_auths using this role?
    $message = T_('Role deleted');
    break;

  case 'edit':
    $result = mq('SELECT role_id, role_name FROM roles WHERE role_id = ' . gri('role_id', -1));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $role_name = gr('role_name');
    // gri() header.php#106 but with a default value able to be passed in
    $role_id = gri('role_id', -1);
    // check if the role exists with a different ID
    $result = mq('SELECT 1 FROM roles WHERE role_name = ' . ms($role_name) . " AND role_id != $role_id AND role_auth = (SELECT role_auth FROM roles WHERE role_id = $role_id)");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit role, this name is already used.');
      $result = mq("SELECT role_id, " . ms($role_name) . " role_name, role_auth FROM roles WHERE role_id = $role_id");
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      mq('UPDATE roles SET role_name = ' . ms($role_name) . " WHERE role_id = $role_id");
      $message = T_('Role edited');
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
<TITLE><?=T_('Roles'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Roles')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($edit_row):?>

<FORM action="admin_role.php" method="post" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="role_id" value="<?=$edit_row['role_id']?>">
<?if(isset($edit_row['role_auth'])):?>
<LABEL><?=T_('Type')?><BR><SELECT name="role_auth">
<? foreach(mysql_enum_values('roles','role_auth') as $role_auth): ?>
  <OPTION value="<?=$role_auth?>" <?=$role_auth == $edit_row['role_auth'] ? 'SELECTED' : '' ?>><?=authTypeToName($role_auth, false)?>
<? endforeach; ?>
</SELECT></LABEL><BR>
<?endif;?>
<LABEL><?=T_('Name')?><BR><INPUT type="text" name="role_name" maxlength=255 value="<?=es($edit_row['role_name'])?>"></LABEL><BR>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_role.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT role_id, role_auth, role_name FROM roles ORDER BY role_auth, role_name');?>

<A HREF="admin_role.php?action=add"><?=T_('Add new Role')?></A>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('Type')?></TH>
  <TH><?=T_('Name')?></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es(authTypeToName($row['role_auth']))?></TD>
    <TD><?=es($row['role_name'])?></TD>
    <TD><A HREF="admin_role.php?action=edit&amp;role_id=<?=$row['role_id']?>"><?=T_('Edit Role')?></A></TD>
    <TD><A HREF="admin_role.php?action=delete&amp;role_id=<?=$row['role_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Role')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
