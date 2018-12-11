<? $admin_auth = array('school');
require('header.php'); 
require_once('file_save.php');
require_once('calendar.php');

assure_id_school('school_id');
$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$mission_number = array_filter(gra('mission_number', array()), 'is_numeric');
$mission_numbers = implode(',', $mission_number);

$wwt_date = array_filter(gra('wwtcdate', array()), 'is_numeric');
$wwtdates = implode(',', $wwt_date);

 $logorow = mysql_fetch_assoc(mq("SELECT school_name,school_logo_id FROM schools WHERE school_id = $school_id"));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_("Mission Report"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">

<H1><?=T_("Mission Report Stats")?></H1><DIV><?=!is_null($logorow['school_logo_id']) ? linkImgFile($logorow['school_logo_id'], NULL, '150') : ''?></DIV>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
<div class="noprint">
<P><?=T_('Click on a name to "drill-down" for more detail.')?></P>
<P>Select reports to view below</P>
<form action="admin_mission_report.php" method="post" accept-charset="UTF-8">

<table border="0"> 
	<tr>
		<td valign="top">
<table id="hakhel">
<? 
$mysubjects = mq('SELECT DISTINCT subject_id, subject_name FROM subjects JOIN date_tasks_missions USING (subject_id) WHERE mission_number IS not NULL');
 while($srow = mysql_fetch_assoc($mysubjects)): 
 $mymissions = mq("SELECT mission_number, MAX(end_date) end_date FROM date_tasks_missions WHERE subject_id = {$srow['subject_id']} AND mission_number IS NOT NULL GROUP BY mission_number ORDER BY mission_number"); 
?>

 <tr>
   <td colspan="8" align="center" bgcolor="white">
   <INPUT type="hidden" name="school_id" value="<?=$school_id?>">
   <INPUT type="hidden" name="class_id" value="<?=$class_id?>">
     <LABEL>Hakhel</LABEL>
     </td>
	</tr> 
  
	 <tr>
	 <?$counter=0?>
  <? while($row = mysql_fetch_assoc($mymissions)): ?>
  <? $counter++;  ?>
  
    <td>
  <LABEL><INPUT type="checkbox" name="mission_number[]" value="<?=$row['mission_number']?>" <?=in_array($row['mission_number'], $mission_number) ? 'CHECKED' : '' ?>><?=es($row['mission_number'])?></LABEL>
     </td>
	<?if ($counter==8)
	{ $counter=0;
	echo "</tr><tr>";
	}?> 
  <? endwhile; ?>
  </tr>
  <!--p-->
  <? endwhile; ?>
  </table>
  </td>
<td>&nbsp;&nbsp;&nbsp;&nbsp;<td>  
	
	<td id="wwwt" valign="top">
	<table>
<? 
$mysubjects = mq('SELECT DISTINCT subject_id, subject_name FROM subjects JOIN date_tasks_missions USING (subject_id) WHERE mission_number IS NULL');	
while($srow = mysql_fetch_assoc($mysubjects)):
$dates = mq("SELECT start_date, MAX(end_date) end_date FROM date_tasks_missions WHERE subject_id = {$srow['subject_id']} AND mission_number IS NULL AND start_date <= " . unixtojd() . " GROUP BY start_date ORDER BY start_date"); ?> 
 	<tr>
	  <td colspan="8" align="center" bgcolor="white">
     <LABEL>WWTC</LABEL>
     </td>
	</tr>
   
   <TR>
    <?$counter=0?>
    <? while($sdates = mysql_fetch_assoc($dates)): ?>
     <?$counter++;?>
	 <td>
     <LABEL><INPUT type="checkbox" name="wwtcdate[]" value="<?=$sdates['start_date']?>"<?//=in_array($sdates['start_date'], $wwtdates) ? 'CHECKED' : '' ?>> <?=dateToHebrew($sdates['start_date'])?></LABEL>
	  	 </td>
    	 	<?if ($counter==3)
	{ $counter=0;
	echo "</tr><tr>";
	}?>
	 
	 <?endwhile;?>
 
 	 
   <?endwhile;?>
</tr>   
  </table>

		
	</td>
	</tr>
	
	<tr><td colspan="4"><INPUT class="submit" type="submit" value="<?=T_('Go')?>"></td></tr>
	
</table>
</FORM>
</div>

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

$subjects = mq('SELECT DISTINCT subject_id, subject_name FROM subjects JOIN date_tasks_missions USING (subject_id) WHERE mission_number IS NOT NULL');
?>
<? while($subject = mysql_fetch_assoc($subjects)): ?>
<? if(!empty($mission_numbers))
   {
   $show_hakhel=1;
$missions = mq("SELECT mission_number, MAX(end_date) end_date FROM date_tasks_missions WHERE subject_id = {$subject['subject_id']} AND mission_number IS NOT NULL and mission_number in($mission_numbers) GROUP BY mission_number ORDER BY mission_number");
}else{
$show_hakhel=-1;
$missions = mq("SELECT mission_number, MAX(end_date) end_date FROM date_tasks_missions WHERE subject_id = {$subject['subject_id']} AND mission_number IS NOT NULL and mission_number in(1.1) GROUP BY mission_number ORDER BY mission_number");

}
 ?>
 <?if ($show_hakhel==1)
 { ?>
<TABLE class="pretty_grid">
<CAPTION><?=es($subject['subject_name'] . ' - ' . $header)?> <?=isset($up) ? "<BR><A HREF='admin_mission_report.php$up'>" . T_('Back') . '</A>' : ''?></CAPTION>
<THEAD>
<TR>
  <TH></TH>
  <? while($mission = mysql_fetch_assoc($missions)): ?>
    <TH><?=$mission['mission_number']?></TH>
  <? endwhile; ?>
  <!--TH--><!--/TH-->
</TR>
</THEAD>
<TBODY>
<? while($name = mysql_fetch_assoc($names)): ?>
<TR>
<TH style="text-align: <?=$align_start?>"><?=isset($down) ? "<A HREF='admin_mission_report.php$down={$name['id']}'>" : ''?><?=es($name['name'])?><?=isset($down) ? '</A>' : ''?></TH>
<? $data = mysql_fetch_column(mq("SELECT mission_number, COUNT(DISTINCT user_id) num FROM date_tasks_mission_marks JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN users USING (user_id) WHERE mission_number IS NOT NULL AND $where = {$name['id']} GROUP BY mission_number")); ?>
<? mysql_data_seek($missions, 0); ?>
  <? while($mission = mysql_fetch_assoc($missions)): ?>
    <TD style="text-align: <?=$align_end?>"><?if(isset($data[$mission['mission_number']])):?>
      <?if(isset($down)):?>
        <?=$data[$mission['mission_number']]?><BR>
        <?$num = mysql_result(mq("SELECT COUNT(*) FROM users WHERE $where = {$name['id']} AND user_start_date <= {$mission['end_date']}"), 0)?>
        <?=$num ? number_format(100*$data[$mission['mission_number']]/$num, 1) . '%' : '-'?>
      <?else:?>
      &#10004;
      <?endif;?>
    <?endif;?></TD>
  <? endwhile; // add th below here?>

</TR>
<? endwhile; ?>
</TBODY>
</TABLE>
<?}?>
<? endwhile; ?>

<? $subjects = mq('SELECT DISTINCT subject_id, subject_name FROM subjects JOIN date_tasks_missions USING (subject_id) WHERE mission_number IS NULL'); ?>
<? while($subject = mysql_fetch_assoc($subjects)): ?>
<? if(!empty($wwtdates))
  {
 $show_wwtc=1;
$dates = mq("SELECT start_date, MAX(end_date) end_date FROM date_tasks_missions WHERE subject_id = {$subject['subject_id']} AND  start_date in($wwtdates) and mission_number IS NULL AND start_date <= " . unixtojd() . " GROUP BY start_date ORDER BY start_date"); 
}else{
$show_wwtc=-1;
$dates = mq("SELECT start_date, MAX(end_date) end_date FROM date_tasks_missions WHERE subject_id = {$subject['subject_id']} AND  start_date in(2454975) and mission_number IS NULL AND start_date <= " . unixtojd() . " GROUP BY start_date ORDER BY start_date");
}

?>
<? $labels = mysql_fetch_column_tuple(mq("SELECT DISTINCT start_date, label_id, label_name FROM date_tasks_missions JOIN date_tasks USING (date_tasks_mission_id) JOIN labels USING (label_id) WHERE subject_id = {$subject['subject_id']} AND mission_number IS NULL ORDER BY mandatory_qty DESC, start_date, label_name")); ?>

<? if ($show_wwtc==1)
{  ?>
<TABLE class="pretty_grid">
<CAPTION><?=es($subject['subject_name'] . ' - ' . $header)?> <?=isset($up) ? "<BR><A HREF='admin_mission_report.php$up'>" . T_('Back') . '</A>' : ''?></CAPTION>
<THEAD>
<TR>
  <!-- TH --><!-- /TH -->
  <? while($date = mysql_fetch_assoc($dates)): ?>
    <TH <?=isset($labels[$date['start_date']]) ? 'colspan="' . (count($labels[$date['start_date']])+1) . '"' : ''?>><?=dateToHebrew($date['start_date'])?></TH>
  <? endwhile; ?>
  <TH></TH>
</TR>
<? mysql_data_seek($dates, 0); ?>
<TR>
  <TH></TH>
  <? while($date = mysql_fetch_assoc($dates)): ?>
    <TH><?=T_('Did Mission')?></TH>
    <? if(isset($labels[$date['start_date']])) foreach($labels[$date['start_date']] as $label_id => $label_name): ?>
      <TH><?=$label_name?></TH>
    <? endforeach; ?>
  <? endwhile; ?>
  <!--TH--><!--/TH-->
</TR>
</THEAD>
<? @mysql_data_seek($names, 0); ?>
<TBODY>
<? while($name = mysql_fetch_assoc($names)): ?>
<? @mysql_data_seek($dates, 0); ?>
<? $data = mysql_fetch_column(mq("SELECT start_date, COUNT(DISTINCT user_id) num FROM date_tasks_mission_marks JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN users USING (user_id) WHERE mission_number IS NULL AND $where = {$name['id']} GROUP BY start_date")); ?>
<? $data_label = mysql_fetch_column_tuple(mq("SELECT start_date, label_id, COUNT(DISTINCT user_id) num, SUM(mark_quantity) total FROM date_tasks_marks JOIN date_tasks USING (date_task_id) JOIN date_tasks_missions USING (date_tasks_mission_id) JOIN users USING (user_id) WHERE mission_number IS NULL AND $where = {$name['id']} GROUP BY start_date, label_id")); ?>
<TR>
  <TH style="text-align: <?=$align_start?>"><?=isset($down) ? "<A HREF='admin_mission_report.php$down={$name['id']}'>" : ''?><?=es($name['name'])?><?=isset($down) ? '</A>' : ''?></TH>
  <? while($date = mysql_fetch_assoc($dates)): ?>
    <TD style="text-align: <?=$align_end?>"><?if(isset($data[$date['start_date']])):?>
      <?if(isset($down)):?>
        <?=$data[$date['start_date']]?><BR>
        <?$num = mysql_result(mq("SELECT COUNT(*) FROM users WHERE $where = {$name['id']} AND user_start_date <= {$date['end_date']}"), 0)?>
        <?=$num ? number_format(100*$data[$date['start_date']]/$num, 1) . '%' : '-'?>
      <?else:?>
      &#10004;
      <?endif;?>
    <?endif;?></TD>
    <? if(isset($labels[$date['start_date']])) foreach($labels[$date['start_date']] as $label_id => $label_name): ?>
      <TD style="text-align: <?=$align_end?>"><?if(isset($data_label[$date['start_date']][$label_id])):?>
        <?if(isset($down)):?>
          <?=$data_label[$date['start_date']][$label_id]['num'] ? '#&nbsp;' . $data_label[$date['start_date']][$label_id]['num'] : ''?><BR>
          <?=$data_label[$date['start_date']][$label_id]['total'] ? T_('Tot:') . '&nbsp;' . $data_label[$date['start_date']][$label_id]['total'] : ''?>
        <?else:?>
          <?=$data_label[$date['start_date']][$label_id]['total'] ? $data_label[$date['start_date']][$label_id]['total'] : ($data_label[$date['start_date']][$label_id]['num'] ? '&#10004;' : '')?>
        <?endif;?>
      <?endif;?></TD>
    <? endforeach; ?>
  <? endwhile; // add the th below here for link on both sides?>

  </TR>
<? endwhile; ?>
</TBODY>
</TABLE>
<?}?>
<? endwhile; ?>

</DIV>

<? include('admin_footer.php'); ?>
</BODY>
</HTML>
