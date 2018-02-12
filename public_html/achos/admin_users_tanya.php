<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'programs';
require_once('admin_ui.php');
$subject_id = 27;
assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$mode = 'pledge';
$action = gr('action');

if(!empty($action)) switch($action) {
  case 'save':    
    foreach(gra('tanya') as $user_id => $data) {
        $user_id = intval($user_id);
        $tanya_lines = intval($data['tanya_lines']);
        $mishna_lines = intval($data['mishna_lines']);    
        $sql = "INSERT INTO tanya_users (user_id, tanya_lines, mishna_lines) 
                SELECT user_id, $tanya_lines tanya_lines, $mishna_lines mishna_lines 
                FROM users 
                WHERE user_id = $user_id 
                AND school_id = $school_id" . 
                ($class_id != -1 ? " AND class_id = $class_id" : '') . " 
                ON DUPLICATE KEY UPDATE tanya_lines = $tanya_lines, mishna_lines = $mishna_lines";
        mq($sql);
    }
    break;

  default:
    user_error('unknown action', E_USER_ERROR);
    break;
}

$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");
$edit_result = mq("SELECT users.user_id, users.first, users.last, users.username, class_grade, class_sub, tanya_lines, mishna_lines FROM users LEFT JOIN tanya_users USING (user_id) LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '') . ' ORDER BY class_grade, class_sub, users.last, users.first, users.username');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_("Tanya Lines Learned"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
<DIV class="body">
<DIV class="sub_menu">
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
</DIV>
<H1><?=T_('Tanya Lines Learned')?></H1>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
<FORM action="admin_users_tanya.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>

<?endif;?>
<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>
<DIV class="ui_body">
<DIV class="ui_menu">
<?ui_menu();?>
</DIV>
<DIV class="content">
<FORM action="admin_users_tanya.php" method="get" accept-charset="UTF-8">
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<LABEL><?=T_('Show only Platoon')?>: <SELECT name="class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?while($class_row = mysql_fetch_assoc($class_result)):?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<!--</DIV>-->

<?if($edit_result): ?>
<DIV class="infobox">
    Please enter lines of Tanya and lines of Mishna learned in honor of Yud Alef Nissan Ayin Gimmel.<br />
</div>
<FORM action="admin_users_tanya.php" method="post" accept-charset="UTF-8" name="user_tracks">
<INPUT type="hidden" name="action" value="save">
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="class_id" value="<?=$class_id?>">
<TABLE class="list list_<?=$align_start?>" id="tanya_user">
<THEAD>
<TR>
  <TH><?=T_('Soldier')?></TH>
  <TH><?=T_('Tanya Lines')?></TH>
  <TH><?=T_('Mishna Lines')?></TH>
</TR>
</THEAD>
<? $toggle = 0; ?>
<?while($row = mysql_fetch_assoc($edit_result)):?>
<TR class="<?=($toggle ^= 1) ? 'odd' : 'even'?>">
  <TD><?=$row['class_grade'] . '-' . $row['class_sub'] . ': ' . $row['first'] . ' ' . $row['last']?></TD>
  <TD style="white-space: nowrap;"><INPUT type="text" name="tanya[<?=$row['user_id']?>][tanya_lines]" value="<?=is_null($row['tanya_lines']) ? '' : $row['tanya_lines']?>" maxlength="9" size="5"></TD>
  <TD style="white-space: nowrap;"><INPUT type="text" name="tanya[<?=$row['user_id']?>][mishna_lines]" value="<?=is_null($row['mishna_lines']) ? '' : $row['mishna_lines']?>" maxlength="9" size="5"></TD>
</TR>
<?endwhile;?>
</TABLE>
<br />
<P>
<INPUT type="submit" value="<?=T_('Save')?>">
<INPUT type="reset" value="<?=T_('Undo Changes')?>">
</P>
</DIV>
</FORM>
<? endif; ?>
<? endif; ?>
<BR style="clear: both;">
</DIV>
</DIV>
</DIV>
</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
