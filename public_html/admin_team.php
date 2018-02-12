<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'school';
require_once('admin_ui.php');

$action = gr('action');
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    $result = mq("SELECT -1 team_id, '' team_name");
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'add2':
    $team_name = gr('team_name');

    $result = mq("SELECT 1 FROM teams WHERE school_id = $school_id AND team_name = " . ms($team_name));

    if(mysql_num_rows($result)) {
      $message = T_('Unable to add new squad, this name is already used.');
      $result = mq('SELECT -1 team_id, ' . ms($team_name) . ' team_name');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'add';
    } else {
      mq("INSERT INTO teams (school_id, team_name) VALUES ($school_id, " . ms($team_name) . ')');
      $message = T_('Squad added');
    }
    break;

  case 'delete':
    mq("DELETE FROM teams WHERE school_id = $school_id AND team_id = " . gri('team_id', -1));
    $message = T_('Squad deleted');
    break;

  case 'edit':
    $result = mq("SELECT team_id, team_name FROM teams WHERE school_id = $school_id AND team_id = " . gri('team_id', -1));
    $edit_row = mysql_fetch_assoc($result);
    break;

  case 'edit2':
    $team_id = gri('team_id', -1);
    $team_name = gr('team_name');

    $result = mq('SELECT 1 FROM teams WHERE team_name = ' . ms($team_name) . " AND school_id = $school_id AND team_id != $team_id");

    if(mysql_num_rows($result)) {
      $message = T_('Unable to edit squad, this name is already used.');
      $result = mq("SELECT $team_id team_id, " . ms($team_name) . ' team_name');
      $edit_row = mysql_fetch_assoc($result);
      $action = 'edit';
    } else {
      mq('UPDATE teams SET team_name = ' . ms($team_name) . " WHERE team_id = $team_id");
      $message = T_('Squad edited');
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
<TITLE><?=T_('Squads'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>

<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_team.php" method="get" accept-charset="UTF-8">
<P>
<?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT> <INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<?endif;?>
<?if(!$school_id):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<DIV class="ui_body">
<DIV class="ui_menu">
<?ui_menu();?>
</DIV>
<DIV class="content">
<H1><?=T_('Squads')?></H1>
<DIV class="infobox">
</DIV>
<BR><BR>
<?if($edit_row):?>
<P><A HREF="admin_team.php?school_id=<?=$school_id?>"><?=T_('Cancel')?></A></P>

<FORM action="admin_team.php" method="post" accept-charset="UTF-8">
<P CLASS="rows">
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="team_id" value="<?=$edit_row['team_id']?>">
<LABEL><?=T_('Name')?><BR><INPUT type="text" name="team_name" maxlength=255 value="<?=es($edit_row['team_name'])?>"><BR>
<INPUT class="submit" type="submit" value="<?=$action == 'edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<?else:?>
<?$result = mq("SELECT team_id, team_name FROM teams WHERE school_id = $school_id ORDER BY team_name");?>

<TABLE CLASS="list list_<?=$align_start?>">
<THEAD>
<TR>
  <TH><?=T_('Name')?></TH>
  <TH></TH>
  <TH></TH>
</TR>
</THEAD>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=es($row['team_name'])?></TD>
    <TD><A HREF="admin_team.php?action=edit&amp;team_id=<?=$row['team_id']?>&amp;school_id=<?=$school_id?>"><?=T_('Edit Squad Info')?></A></TD>
    <TD><A HREF="admin_team.php?action=delete&amp;team_id=<?=$row['team_id']?>&amp;school_id=<?=$school_id?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete Squad')?></A></TD>
</TR>
<? endwhile; ?>
</TABLE>
<? endif; ?>
<BR style="clear: both;">
</DIV>
</DIV>
<? endif; ?>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
