<?php
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');

assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_("Rank Report"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">

<H1><?=T_("Rank Report")?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<P><?=T_('Click on a name to "drill-down" for more detail.')?></P>

<P><?=T_('Note: This counts ALL ranks received! Not just the highest rank received.')?></P>

<?
if($school_id == -1) {
  $header = T_('Schools');
  $names = mq("SELECT school_name name, school_id id FROM schools " . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name');
  $where = 'school_id';
  $up = NULL;
  $down = '?school_id';
} elseif($class_id == -1) {
  $header = mysql_fetch_assoc(mq("SELECT school_name FROM schools WHERE school_id = $school_id"));
  if(!$header) trigger_error_client('school not found', E_USER_ERROR);
  $header = $header['school_name'];
  $names = mq("SELECT CONCAT(class_grade, '-', class_sub) name, class_id id FROM classes JOIN schools USING (school_id) WHERE school_id = $school_id ORDER BY class_grade, class_sub");
  $where = "school_id = $school_id AND class_id";
  $up = '';
  $down = "?school_id=$school_id&amp;class_id";
} else {
  $header = mysql_fetch_assoc(mq("SELECT school_name, class_grade, class_sub FROM schools JOIN classes USING (school_id) WHERE school_id = $school_id AND class_id = $class_id"));
  if(!$header) trigger_error_client('class not found', E_USER_ERROR);
  $header = $header['school_name'] . ' ' . $header['class_grade'] . '-' . $header['class_sub'];
  $names = mq("SELECT CONCAT(IF(first_he = '', first, first_he), ' ', IF(last_he = '', last, last_he)) name, user_id id FROM users JOIN schools USING (school_id) JOIN classes USING (school_id, class_id) WHERE school_id = $school_id AND class_id = $class_id ORDER BY last_he, first_he, last, first");
  $where = "school_id = $school_id AND class_id = $class_id AND user_id";
  $up = "?school_id=$school_id";
  $down = NULL;
}
?>

<? $ranks = mq("SELECT rank_ord, rank_name, rank_color, COUNT(*) num FROM ranks LEFT JOIN rank_marks USING (rank_ord) WHERE rank_ord <= (SELECT MAX(rank_ord) FROM rank_marks) GROUP BY rank_ord, rank_name, rank_color ORDER BY rank_ord"); ?>
<TABLE class="pretty_grid">
<CAPTION><?=es($header)?> <?=isset($up) ? "<BR><A HREF='admin_rank_report.php$up'>" . T_('Back') . '</A>' : ''?></CAPTION>
<THEAD>
<TR>
  <TH></TH>
  <? while($rank = mysql_fetch_assoc($ranks)): ?>
    <TH <?=!empty($rank['rank_color']) ? 'style="color: ' . es($rank['rank_color']) . ';"' : ''?>><?=$rank['rank_name']?></TH>
  <? endwhile; ?>
  <TH></TH>
</TR>
</THEAD>
<TFOOT>
<? if($admin_user['auth'] == 'super' && $school_id == -1): ?>
<? mysql_data_seek($ranks, 0); ?>
<TR>
  <TH><?=T_('Total')?></TH>
  <? while($rank = mysql_fetch_assoc($ranks)): ?>
    <TH style="text-align: <?=$align_end?>; <?=!empty($rank['rank_color']) ? 'color: ' . es($rank['rank_color']) . ';' : ''?>"><?=$rank['num']?></TH>
  <? endwhile; ?>
  <TH><?=T_('Total')?></TH>
</TR>
<? endif; ?>
<? mysql_data_seek($ranks, 0); ?>
<TR>
  <TH></TH>
  <? while($rank = mysql_fetch_assoc($ranks)): ?>
    <TH <?=!empty($rank['rank_color']) ? 'style="color: ' . es($rank['rank_color']) . ';"' : ''?>><?=$rank['rank_name']?></TH>
  <? endwhile; ?>
  <TH></TH>
</TR>
</TFOOT>
<TBODY>
<? while($name = mysql_fetch_assoc($names)): ?>
<TR>
<TH style="text-align: <?=$align_start?>"><?=isset($down) ? "<A HREF='admin_rank_report.php$down={$name['id']}'>" : ''?><?=es($name['name'])?><?=isset($down) ? '</A>' : ''?></TH>
<? $data = mysql_fetch_column(mq("SELECT rank_ord, COUNT(*) num, MIN(date_promoted) date_promoted FROM rank_marks JOIN users USING (user_id) WHERE $where = {$name['id']} GROUP BY rank_ord")); ?>
<? mysql_data_seek($ranks, 0); ?>
  <? while($rank = mysql_fetch_assoc($ranks)): ?>
    <TD style="text-align: <?=$align_end?>"><?if(isset($data[$rank['rank_ord']])):?>
      <?if(isset($down)):?>
        <?=$data[$rank['rank_ord']]['num']?>
      <?else:?>
      &#10004;<BR>
      <?=dateToHebrew($data[$rank['rank_ord']]['date_promoted'])?>
      <?endif;?>
    <?endif;?></TD>
  <? endwhile; ?>
<TH style="text-align: <?=$align_start?>"><?=isset($down) ? "<A HREF='admin_rank_report.php$down={$name['id']}'>" : ''?><?=es($name['name'])?><?=isset($down) ? '</A>' : ''?></TH>
</TR>
<? endwhile; ?>
</TBODY>
</TABLE>
<BR><BR>

</DIV>
<? include('admin_footer.php'); ?>
</BODY>
</HTML>
