<? $admin_auth = array('school','user'); ?>
<? require('header.php'); ?>
<?
require_once('calendar.php');
require_once('file_save.php');
require_once('card_printer.php');

$lines=5;
$cols=2;

$auth_mode = check_id_access();
$school_id = gri('school_id', -1);
$user_id = gri('user_id', -1);

if($auth_mode == 'user') {
  check_school_setting($user_id, 'home_school');
  $lines = 1;
  $cols = 1;
}

$tanya = gri('tanya');
$subject_id = gri('subject_id', -1);
$medal_stage = gri('medal_stage');

if(!is_null($medal_stage)) $medal_stage = max(1, min($medal_stage, 4));

$points = grf('points', 50);

$fb = gr('fb', 'fb');
$copies = min(max(gri('copies', 1), 1), 100);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_('Print Tanya Achievement Cards'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<LINK href="card_printer.css" rel="stylesheet" type="text/css">
<STYLE type="text/css">
.fronts, .backs {
  margin: auto;
}

<?=$auth_mode == 'user' ? '' : '.fronts, '?>.backs {
  page-break-after: always;
}

.fronts td, .backs td {
  border: 1px dashed black;
  vertical-align: middle;
  height: 2.125in;
}

.fronts td td, .backs td td {
  width: auto;
  height: auto;
  border: none;
}

@media print {
  .fronts td, .backs td {
    border: none;
  }

  hr {
    display: none;
  }
}
</STYLE>
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">
<DIV class="body_header_left">
<A HREF="admin.php"><?=T_('Home page')?></A>
</DIV>
<DIV class="body_header_right">
<A HREF="logout.php"><?=T_('Logout')?></A>
</DIV>
<DIV class="left_menu"><?include('admin_inc.php');?></DIV>
<DIV class="noprint">
<H1><?=T_('Print Achievement Cards')?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<?if($auth_mode != 'user'):?>
<H2>Printing Instructions</H2>
<P style="font-size: 150%;">
File<?=$next_arr?>Page Set up<BR>
<BR>
Portrait<BR>
Scale 95 (make sure that shrink to page is NOT checked off)<BR>
<BR>
Margins: Top: 0.3<BR>
Left, Right, Bottom: 0.0<BR>
<BR>
All headers and footers: Blank<BR>
<BR>
Print on green perforated paper
</P>
<HR>

<FORM action="admin_tanya_cards.php" method="get" accept-charset="UTF-8">
<H2><?=T_('Print Tanya achievement cards')?></H2>
<P>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>

<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<?endif;?>
<LABEL><?=T_('Tanya was learned for')?>: <SELECT name="tanya">
<OPTION value="1" <?=$tanya == 1 ? 'SELECTED' : ''?>>1 <?=T_('day')?>, 0.5 <?=T_('miles')?>
<OPTION value="2" <?=$tanya == 2 ? 'SELECTED' : ''?>>2 <?=T_('days')?>, 1.0 <?=T_('miles')?>
<OPTION value="3" <?=$tanya == 3 ? 'SELECTED' : ''?>>3 <?=T_('days')?>, 1.5 <?=T_('miles')?>
<OPTION value="4" <?=$tanya == 4 ? 'SELECTED' : ''?>>4 <?=T_('days')?>, 2.0 <?=T_('miles')?>
<OPTION value="5" <?=$tanya == 5 ? 'SELECTED' : ''?>>5 <?=T_('days')?>, 2.5 <?=T_('miles')?>
<OPTION value="6" <?=$tanya == 6 ? 'SELECTED' : ''?>>6 <?=T_('days')?>, 3.0 <?=T_('miles')?>
<OPTION value="7" <?=$tanya == 7 ? 'SELECTED' : ''?>>7 <?=T_('days')?>, 7.0 <?=T_('miles')?> (<?=T_('Includes bonus')?>)
</SELECT></LABEL><BR>
<LABEL><?=T_('Page type')?>: <SELECT name="fb">
<OPTION value="fb" <?=$fb == 'fb' ? 'selected' : ''?>><?=T_('Fronts and backs')?>
<OPTION value="f" <?=$fb == 'f' ? 'selected' : ''?>><?=T_('Fronts only')?>
<OPTION value="b" <?=$fb == 'b' ? 'selected' : ''?>><?=T_('Backs only')?>
</SELECT></LABEL><BR>
<LABEL><?=T_('# of sheets (10 cards per sheet)')?>: <SELECT name="copies">
<?for($i=1; $i<=100; $i += $i<20 ? 1 : 5):?>
<OPTION value="<?=$i?>" <?=$copies == $i ? 'selected' : ''?>><?=$i?>
<?endfor;?>
</SELECT></LABEL><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<FORM action="admin_tanya_cards.php" method="get" accept-charset="UTF-8">
<H2><?=T_('Print Tanya release cards')?></H2>
<P>
<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>

<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL><BR>
<?endif;?>
<LABEL><?=T_('Medal stage/progress')?>: <SELECT name="medal_stage">
<OPTION value="1" <?=$medal_stage == 1 ? 'SELECTED' : ''?>>25%
<OPTION value="2" <?=$medal_stage == 2 ? 'SELECTED' : ''?>>50%
<OPTION value="3" <?=$medal_stage == 3 ? 'SELECTED' : ''?>>75%
<OPTION value="4" <?=$medal_stage == 4 ? 'SELECTED' : ''?>>100%
</SELECT></LABEL><BR>
<LABEL><?=T_('Page type')?>: <SELECT name="fb">
<OPTION value="fb" <?=$fb == 'fb' ? 'selected' : ''?>><?=T_('Fronts and backs')?>
<OPTION value="f" <?=$fb == 'f' ? 'selected' : ''?>><?=T_('Fronts only')?>
<OPTION value="b" <?=$fb == 'b' ? 'selected' : ''?>><?=T_('Backs only')?>
</SELECT></LABEL><BR>
<LABEL><?=T_('# of sheets (10 cards per sheet)')?>: <SELECT name="copies">
<?for($i=1; $i<=100; $i += $i<20 ? 1 : 5):?>
<OPTION value="<?=$i?>" <?=$copies == $i ? 'selected' : ''?>><?=$i?>
<?endfor;?>
</SELECT></LABEL><BR>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
<HR>
<?endif;?>
</DIV>
<?
if(!is_null($tanya)) { // tanya point cards
  $sql = "SELECT subject_name, subject_id, subject_image_id, school_id, school_name, school_city, school_state, school_logo_id, school_number FROM subjects JOIN schools WHERE school_id = $school_id AND subject_type = 'Tanya'";
  $table = 'points_codes';
  $names = 'points';
  $values = $tanya == 7 ? 7 : $tanya * 0.5;
  $points = $tanya * 0.5;
  $bonus = $tanya == 7 ? 3.5 : 0;
  $expires = unixtojd() + 180;
  $prefix = '1'; //1 prefix for point cards
  $description = sprintf(T_("Learned Tanya for %s days"), $tanya);
  $left_circle = $tanya;
  $right_circle = $tanya;
  $series = '';
} elseif(!is_null($medal_stage)) { // release cards
  $sql = "SELECT subject_name, subject_id, subject_image_id, school_id, school_name, school_city, school_state, school_logo_id, school_number FROM subjects JOIN schools WHERE school_id = $school_id AND subject_type = 'Tanya'";
  $table = 'tanya_medal_cards';
  $names = 'medal_stage';
  $values = $medal_stage * 25;
  $points = 25;
  $bonus = '';
  $expires = unixtojd() + 180;
  $prefix = '7'; //7 prefix for tanya release cards
  $description = T_("Checkpoint");
  $left_circle = $medal_stage;
  $right_circle = $medal_stage;
  $series = '';
} else {
  $sql = 'SELECT NULL FROM dual WHERE 1=2'; //designed to return no rows
}

$result = mq($sql);
$row = mysql_fetch_assoc($result);
if(mysql_num_rows($result) > 1) {
  $result_back = mq($sql);
  $row_back = mysql_fetch_assoc($result_back);
} else {
  $row_back = $row;
}
?>
<?if(mysql_num_rows($result)):?>
<P class="noprint" style="text-align: center;"><INPUT type="button" value="<?=T_('Print')?>" onClick="print();"></P>
<? if($auth_mode == 'user'): ?>
<FORM action="admin.php#grant" method="post" accept-charset="UTF-8">
<P class="noprint" style="text-align: center;">
<INPUT type="submit" value="<?=T_('Grant Card to Soldier')?>"> (<?=T_("Store card in Soldier's inbox")?>)
<INPUT type="hidden" name="user_id" value="<?=$user_id?>">
</P>
<? endif; ?>
<?
if(mysql_result(mq("SELECT GET_LOCK('$table', 30)"),0) != 1) trigger_error('could not get lock', E_USER_ERROR);
for($copy = 0; $copy < $copies*ceil(mysql_num_rows($result)/($lines*$cols)); $copy++):
?>
<? if($fb == 'fb' || $fb == 'f'): ?>
<TABLE class="fronts">
<? for($line=0; $line<$lines; $line++): ?>
<TR>
<? for($col=0; $col<$cols; $col++): ?>
<TD>
<?
if($row) {

if(isset($mission_number) || isset($mission_number_series)) {
  $points = floatval($row['points']);
  $bonus = $is_bonus ? $row['points_bonus'] : 0;
  $description = $row['mission_name'];
  $left_circle = $row['mission_number'];
  $right_circle = $row['mission_number'];
  $series = '';
  $values = "{$row['mission_number']}, $is_bonus";
} elseif(!is_null($tanya) || !is_null($medal_stage)) {
  $subject_id = $row['subject_id'];
}

echo display_card_front($expires, $row['school_number'], $row['school_name'], $row['school_city'], $row['school_state'], $row['school_logo_id']);

}
?>
</TD>
<? if(mysql_num_rows($result) > 1) $row = mysql_fetch_assoc($result); ?>
<? endfor; ?>
</TR>
<? endfor; ?>
</TABLE>
<?
if(mysql_num_rows($result) > 1) {
  if(!$row) {
    mysql_data_seek($result, 0);
    $row = mysql_fetch_assoc($result);
  }
}
?>
<? endif; ?>
<HR>
<? if($fb == 'fb' || $fb == 'b'): ?>
<TABLE class="backs">
<? for($line=0; $line<$lines; $line++): ?>
<TR>
<? for($col=0; $col<$cols; $col++): ?>
<TD>
<? if($row_back): ?>
<?
if(isset($mission_number) || isset($mission_number_series)) {
  $points = floatval($row_back['points']);
  $bonus = $is_bonus ? $row_back['points_bonus'] : 0;
  $description = $row_back['mission_name'];
  $left_circle = $row_back['mission_number'];
  $right_circle = $row_back['mission_number'];
  $series = '';
  $values = "{$row_back['mission_number']}, $is_bonus";
} elseif(!is_null($tanya) || !is_null($medal_stage)) {
  $subject_id = $row_back['subject_id'];
}

$count = 0;
do {
  if($count++ > 100000) trigger_error('could not get ID', E_USER_ERROR);
  $id = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
} while(mysql_result(mq("SELECT COUNT(*) FROM $table WHERE code_id = $id"),0) != 0);

mq("INSERT INTO $table (code_id, school_id, subject_id, $names, expiration_date) VALUES ($id, $school_id, $subject_id, $values, FROM_DAYS($expires-1721060))");

$id = $prefix . str_pad($id, 19, '0', STR_PAD_LEFT);

echo display_card_back($id, $points, $bonus, $left_circle, $right_circle, $description, $row_back['subject_name'], $row_back['subject_image_id'], $series);

?>
<? if($auth_mode == 'user'): ?>
<INPUT type="hidden" name="code[]" value="<?=$id?>">
<? endif; ?>
<? endif; ?>
</TD>
<? if(mysql_num_rows($result) > 1) $row_back = mysql_fetch_assoc($result_back); ?>
<? endfor; ?>
</TR>
<? endfor; ?>
</TABLE>
<?
if(mysql_num_rows($result) > 1) {
  if(!$row_back) {
    mysql_data_seek($result_back, 0);
    $row_back = mysql_fetch_assoc($result_back);
  }
}
?>
<HR>
<? endif; ?>
<? endfor; ?>
<? mq("SELECT RELEASE_LOCK('$table')"); ?>
<?endif;?>
<? if($auth_mode == 'user'): ?>
</FORM>
<? endif; ?>
</DIV>
<DIV class="noprint"><? include('admin_footer.php'); ?></DIV>
</BODY>
</HTML>
