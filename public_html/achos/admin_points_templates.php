<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
assure_id_school('school_id');

$action = gr('action');
$edit_row = false;

if(!empty($action)) switch($action) {
  case 'add':
    $edit_row = mysql_fetch_assoc(mq("SELECT -1 points_codes_template_id, -1 school_id, -1 subject_id, 50 points, '' left_circle, '' right_circle, '' description, NULL series"));
    break;

  case 'add2':
    $school_id = gri('school_id', -1);
    if($school_id == -1) {
      if($admin_user['auth'] == 'super') {
       $school_id = 'NULL';
      } else {
        break;
      }
    }
    mq("INSERT INTO points_codes_templates SET school_id = $school_id, subject_id = " . gri('subject_id', -1) . ', description = ' . ms(gr('description')) . ', left_circle = ' . ms(gr('left_circle')) . ', right_circle = ' . ms(gr('right_circle')) . ', points = ' . grf('points') . ', series = ' . nullif_max(gr('series'), 255));
    $message = T_('Added Template');
    break;

  case 'edit':
    $edit_row = mysql_fetch_assoc(mq('SELECT points_codes_template_id, subject_id, points, left_circle, right_circle, description, series FROM points_codes_templates WHERE points_codes_template_id = ' . gri('points_codes_template_id', -1)));
    break;

  case 'edit2':
    $points_codes_template_id = gri('points_codes_template_id');
    mq("UPDATE points_codes_templates SET subject_id = " . gri('subject_id', -1) . ', description = ' . ms(gr('description')) . ', left_circle = ' . ms(gr('left_circle')) . ', right_circle = ' . ms(gr('right_circle')) . ', points = ' . grf('points') . ', series = ' . nullif_max(gr('series'), 255) . " WHERE points_codes_template_id = $points_codes_template_id" . ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : ''));
    $message = T_('Updated Template');
    break;

  case 'delete':
    $points_codes_template_id = gri('points_codes_template_id');
    mq("DELETE FROM points_codes_templates WHERE points_codes_template_id = $points_codes_template_id");
    $message = T_('Deleted Template');
    break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Achievement Card Templates'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<H1><?=T_('Achievement Card Templates')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<?if($edit_row):?>

<FORM action="admin_points_templates.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="action" value="<?=$action?>2">
<INPUT type="hidden" name="points_codes_template_id" value="<?=$edit_row['points_codes_template_id']?>">
<?if($action == 'add' && ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1)):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>

<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?if($admin_user['auth'] == 'super'):?><OPTION value="-1">&lt;<?=T_('All Schools')?>&gt;<?endif;?>
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>"><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<?endif;?>
<? $subject_result = mq('SELECT subject_id, subject_name, inst_name FROM subjects JOIN institutions USING (inst_id) WHERE'  . ($admin_user['auth'] != 'super' ? ' inst_id IN (' . implode(',', $admin_user['inst_ids']) . ') AND' : '') . ' subject_type = \'school_points\' ORDER BY inst_name, subject_name'); ?>
<LABEL><?=T_('Select Subject')?>:
<SELECT name="subject_id">
  <? while($row = mysql_fetch_assoc($subject_result)): ?>
    <OPTION VALUE="<?=$row['subject_id']?>" <?=$edit_row['subject_id'] == $row['subject_id'] ? 'SELECTED' : '' ?>><?=$admin_user['auth'] == 'super' ? es($row['inst_name']) . ' - ' : ''?><?=es($row['subject_name'])?></OPTION>
      <? endwhile; ?>
</SELECT></LABEL><BR>
<LABEL><?=T_('Points')?>: <INPUT type="text" name="points" maxlength="9" size="9" value="<?=$edit_row['points']?>" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseFloat('0'+this.value, 10), 999999.99));"></LABEL><BR>
<LABEL><?=T_('Left Circle')?>: <INPUT type="text" name="left_circle" value="<?=es($edit_row['left_circle'])?>" maxlength="1"></LABEL><BR>
<LABEL><?=T_('Right Circle')?>: <INPUT type="text" name="right_circle" value="<?=es($edit_row['right_circle'])?>" maxlength="1"></LABEL><BR>
<LABEL><?=T_('(Left) Description')?>: <INPUT type="text" name="description" value="<?=es($edit_row['description'])?>" maxlength="255"></LABEL><BR>
<LABEL><?=T_('Series')?>: <INPUT type="text" name="series" maxlength="3" size="3" value="<?=$edit_row['series']?>" onChange="if(this.value != '') this.value = Math.max(0, Math.min(parseInt('0'+this.value, 10), 255));"></LABEL><BR>
<INPUT type="submit" value="<?=$action=='edit' ? T_('Save') : T_('Add new')?>">
</P>
</FORM>

<A HREF="admin_points_templates.php"><?=T_('Cancel')?></A>
<?else:?>

<?$result = mq('SELECT points_codes_template_id, school_name, points, subject_name, school_inst.inst_name school_inst_name, subject_inst.inst_name subject_inst_name, left_circle, right_circle, description, series FROM points_codes_templates LEFT JOIN schools USING (school_id) LEFT JOIN subjects USING (subject_id) LEFT JOIN institutions subject_inst ON (subjects.inst_id = subject_inst.inst_id) LEFT JOIN institutions school_inst ON (subjects.inst_id = school_inst.inst_id) ' . ($admin_user['auth'] != 'super' ? ' WHERE (school_id IS NULL OR school_id IN (' . implode(',', $admin_user['auths']['school']) . '))' : '') . ' ORDER BY school_name, subject_name, left_circle, right_circle, description, points_codes_template_id');?>

<A HREF="admin_points_templates.php?action=add"><?=T_('Add new template')?></A>

<TABLE CLASS="list">
<TR>
  <TH><?=T_('School')?></TH>
  <TH><?=T_('Subject')?></TH>
  <TH><?=T_('Points')?></TH>
  <TH><?=T_('Left Circle')?></TH>
  <TH><?=T_('Right Circle')?></TH>
  <TH><?=T_('Description')?></TH>
  <TH><?=T_('Series')?></TH>
  <TH></TH>
  <TH></TH>
</TR>
<? while($row = mysql_fetch_assoc($result)): ?>
<TR>
    <TD><?=is_null($row['school_name']) ? '&lt;' . T_('All') . '&gt;' : es(($admin_user['auth'] == 'super' ? es($row['school_inst_name']) . ' - ' : '') . $row['school_name'])?></TD>
    <TD><?=floatval($row['points'])?></TD>
    <TD><?=$admin_user['auth'] == 'super' ? es($row['subject_inst_name']) . ' - ' : ''?><?=es($row['subject_name'])?></TD>
    <TD><?=es($row['left_circle'])?></TD>
    <TD><?=es($row['right_circle'])?></TD>
    <TD><?=es($row['description'])?></TD>
    <TD><?=is_null($row['series']) ? '' : $row['series']?></TD>
    <TD><?if(!is_null($row['school_name']) || $admin_user['auth'] == 'super'):?><A HREF="admin_points_templates.php?action=edit&amp;points_codes_template_id=<?=$row['points_codes_template_id']?>"><?=T_('Edit template')?></A><?endif;?></TD>
    <TD><?if(!is_null($row['school_name']) || $admin_user['auth'] == 'super'):?><A HREF="admin_points_templates.php?action=delete&amp;points_codes_template_id=<?=$row['points_codes_template_id']?>" onClick="return confirm('<?=es(T_('Are you sure?'))?>')"><?=T_('Delete template')?></A><?endif;?></TD>
</TR>
<? endwhile; ?>
</TABLE>
<?endif;?>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
