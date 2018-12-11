<? 
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php'); 
assure_id_school('school_id'); //this will only allow the school logged in to see thier school -cool

$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$user_id = gri('user_id', -1);
$subject = gri('subject_id', -1);

if (isset($_GET['medals_filter'])) 
	$medals_filter = $_GET['medals_filter'];
else
	$medals_filter = 0;

 //parse the values from check box 'date_reveived' and update the db with date
 $box = gra('date_received');        
 foreach($box as $edit_user_id => $value) {  
            while (list ($subject_id,$val) = @each ($value)) { 
                    while (list ($k,$medal_ord) = @each ($val)) { 
                     //echo "$user_id,$subject_id,$medal_ord". "<br>";
					
					$query1 = sprintf("update medal_marks JOIN users USING (user_id) set date_received=now() where (user_id='%s' and subject_id='%s' and medal_ord='%s')" . ($school_id != '-1' ? " AND school_id = $school_id " : '') ,					
					  mysql_real_escape_string($edit_user_id),
                      mysql_real_escape_string($subject_id),
					  mysql_real_escape_string($medal_ord));                    
					
					mq($query1) ; 
			}
          } 
       }
     
   // parse rank check box and update 'rank marks'
   
     $rankbox = gra('rankdate_book_received');        
        foreach($rankbox as $edit_user_id => $value) {  
               while (list ($rank_ord,$val) = @each ($value)) { 
                    $query2= sprintf("update rank_marks JOIN users USING (user_id) set date_book_received=now() where user_id='%s' and rank_ord='%s'" .($school_id != '-1' ? " AND school_id = $school_id " : ''),
					 mysql_real_escape_string($edit_user_id),
					 mysql_real_escape_string($val));
					
				mq($query2); 
//echo $query2;				
              } 
           }
// parse rank cards check box  update rank cards date

     $rankcards = gra('rank_card_received');        
        foreach($rankcards as $edit_user_id => $value) {  
               while (list ($rank_ord,$val) = @each ($value)) { 
                    $query3= sprintf("update rank_marks JOIN users USING (user_id) set date_card_received=now() where user_id='%s' and rank_ord='%s'" .($school_id != '-1' ? " AND school_id = $school_id " : ''),
					 mysql_real_escape_string($edit_user_id),
					 mysql_real_escape_string($val));
					
				mq($query3); 
//echo $query2;				
              } 
           }




		   
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<HTML DIR="<?=$dir?>">
<HEAD>
<TITLE><?=T_("Soldier's Medal and Rank Report").' - '.T_('Tzivos Hashem Management System')?></TITLE>
<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>


<!-- Begin
function checkAll(field)
{
for (i = 0; i < field.length; i++)
	field[i].checked = true ;
}

function uncheckAll(field)
{
for (i = 0; i < field.length; i++)
	field[i].checked = false ;
}
//  End -->
</script>


</HEAD>
<BODY>
<?include('admin_header.php');?>
<DIV CLASS="body">

<H1><?=T_("Soldier's Medal and Rank Report")?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>

<? $school_result = mq('SELECT school_id, school_name FROM schools' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name'); ?>


<FORM  name="myform" action="admin_received_stats.php" method="get" accept-charset="UTF-8">
<P>
<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">

<?while($school_row = mysql_fetch_assoc($school_result)):?>
   <OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row[   'school_name'])?></OPTION>
<?endwhile;?>

  </SELECT></LABEL>
    <INPUT class="submit" type="submit" value="<?=T_('Go')?>">
  </P>
  </FORM>

<?endif;?>

<?if($school_id == -1):?>
<?=T_('Please select an Institution.')?>
<?else:?>

<DIV>
<? $class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub"); ?>

<? //$user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, last, first, username"); ?>
<? $user_result = mq("
SELECT 
    class_grade, 
    class_sub, 
    user_id, 
    username, 
    first, 
    last 
FROM 
    users, 
    classes 
WHERE 
	users.school_id = $school_id 
	AND 
	users.school_id=classes.school_id  
    AND 
	users.class_id=classes.class_id " .  
    ($class_id != -1 ? " AND users.class_id = $class_id" : '') . " 
ORDER BY 
	class_grade, class_sub, last, first, username"); ?>

<? $subjects_result = mq("SELECT subject_name, subject_id FROM subjects");?>

<FORM action="admin_received_stats.php" method="get" accept-charset="UTF-8">
<P>
<? if(!empty($admin_user)): ?>
   <INPUT type="hidden" name="school_id" value="<?=$school_id?>">
   <BR>
<!--<INPUT type="hidden" name="subject_id" value="<?=$subject?>">
    --><?=T_('Choose Subject')?>: <SELECT name="subject_id">
    <OPTION value="-1">&lt;<?=T_('All')?>&gt;

<?while($subject_row = mysql_fetch_assoc($subjects_result)):?>
      <OPTION value="<?=$subject_row['subject_id']?>" <?=$subject_row['subject_id'] == $subject ? 'SELECTED' : ''?>><?=es($subject_row['subject_name'])?></OPTION>
<?endwhile;?>

</SELECT><BR>
    <?=T_('Choose Platoon')?>: <SELECT name="class_id">
    <OPTION value="-1">&lt;<?=T_('All')?>&gt;

<?while($class_row = mysql_fetch_assoc($class_result)):?>
      <OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row[       'class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>

</SELECT>

<BR>
<?=T_('Choose Soldier')?> <SELECT name="user_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<? while($user_row = mysql_fetch_assoc($user_result)): ?>
  <OPTION value="<?=$user_row['user_id']?>" <?=$user_row['user_id'] == $user_id ? 'SELECTED' : ''?>><?=$class_id == -1 &&    $user_row['class_grade'] != '' ? es($user_row['class_grade'] . '-' . $user_row['class_sub']) . ': ' : ''?><?=es($user_row['last'])?>, <?=es($user_row['first'])?> (<?=es($user_row['username'])?>)</OPTION>
<?endwhile;?>

</SELECT>
<BR>
<?$from_awarded = gri('from_awarded', unixtojd(mktime(0,0,0,date("m"),date("d")-7,date("Y"))));?>
<?$to_awarded = gri('to_awarded', unixtojd(mktime(0,0,0,date("m"),date("d"),date("Y"))));?>
<LABEL><?=T_('Medals Awarded Between')?>: <INPUT type="text" name="from_awarded_disp" READONLY value="<?=es(dateToHebrew($from_awarded))?>" onClick="getDate(this.form, 'from_awarded', true);"/></LABEL>
<INPUT type="hidden" name="from_awarded" value="<?=$from_awarded?>"> &nbsp;
<LABEL><?=T_('And')?>: &nbsp;<INPUT type="text" name="to_awarded_disp" READONLY value="<?=es(dateToHebrew($to_awarded))?>" onClick="getDate(this.form, 'to_awarded', true);"/></LABEL>
<INPUT type="hidden" name="to_awarded" value="<?=$to_awarded?>">

<BR>

		<LABEL>Medals Filter</LABEL>
		<SELECT NAME="medals_filter">
			<OPTION <? if ($medals_filter == 0) echo " selected='selected' "; ?> VALUE="0">ALL</OPTION>
			<OPTION <? if ($medals_filter == 1) echo " selected='selected' "; ?> VALUE="1">Awarded But Not Received</OPTION>
			<OPTION <? if ($medals_filter == 2) echo " selected='selected' "; ?> VALUE="2">Awarded And Received</OPTION>
		</SELECT>

<BR>

<?
$from_promoted = gri('from_promoted', unixtojd(mktime(0,0,0,date("m"),date("d")-7,date("Y"))));
$to_promoted = gri('to_promoted', unixtojd(mktime(0,0,0,date("m"),date("d"),date("Y"))));
?>
<LABEL><?=T_('Promoted Between')?>: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT type="text" name="from_promoted_disp" READONLY value="<?=es(dateToHebrew($from_promoted))?>" onClick="getDate(this.form, 'from_promoted', true);"/></LABEL>
<INPUT type="hidden" name="from_promoted" value="<?=$from_promoted?>">&nbsp;
<LABEL><?=T_('And')?>: &nbsp;<INPUT type="text" name="to_promoted_disp" READONLY value="<?=es(dateToHebrew($to_promoted))?>" onClick="getDate(this.form, 'to_promoted', true);"/></LABEL>
<INPUT type="hidden" name="to_promoted" value="<?=$to_promoted?>">

<BR>
<BR><INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</form>
  <br>
  <hr>
<?endif;?>

<?php
?>

<?php 17.10. 09
//I comment the next row because it prevent the working for options ALL!
?>
<?//if(($class_id != -1) ||  ($user_id != -1)): // show only if platoon or user is selected?>

<? 
/*
$users = mq("
    SELECT 
        users.user_id,
        first,
        last,
        school_id,
        class_id 
    FROM users 
    JOIN medal_marks ON users.user_id=medal_marks.user_id 
    JOIN rank_marks ON users.user_id=rank_marks.user_id 
    WHERE 
        school_id = $school_id" . 
        ($class_id!=''?" class_id = $class_id ":'') . 
        ($user_id!=''?" AND users.user_id=$user_id ":'') . 
        
        ($from_awarded!=0?" AND date_awarded>=$from_awarded ":'') . 
        ($to_awarded!=0?" AND date_awarded<=$to_awarded ":'') . 
        
        ($from_promoted!=0?" AND date_promoted>=$from_promoted ":'') . 
        ($to_promoted!=0?" AND date_promoted<=$to_promoted ":'') . 
    "GROUP BY 
        users.user_id 
    ORDER BY 
        last, first, username"); 
*/
/*
switch ($_GET['filter']) {
	case 'marked':
		$users = mq("
			SELECT 
				user_id,
				first,
				last,
				school_id,
				class_id 
			FROM users 
			join medal_marks using ($user_id) 
			WHERE school_id = $school_id" . 
				($class_id!=-1?" AND class_id=$class_id ":' ') . 
				($user_id!=-1?" AND user_id=$user_id ":' ') . 
			" GROUP BY user_id ORDER BY last, first, username"); 
		break;
	case 'unmarked':
		$users = mq("
			SELECT 
				user_id,
				first,
				last,
				school_id,
				class_id 
			FROM users 
			WHERE school_id = $school_id" . 
				($class_id!=-1?" AND class_id=$class_id ":' ') . 
				($user_id!=-1?" AND user_id=$user_id ":' ') . 
			" GROUP BY user_id ORDER BY last, first, username"); 
		break;
	default:
		$users = mq("
			SELECT 
				user_id,
				first,
				last,
				school_id,
				class_id 
			FROM users 
			WHERE school_id = $school_id" . 
				($class_id!=-1?" AND class_id=$class_id ":' ') . 
				($user_id!=-1?" AND user_id=$user_id ":' ') . 
			" GROUP BY user_id ORDER BY last, first, username"); 
		break;
}
*/
$users = mq("
			SELECT 
				user_id,
				first,
				last,
				u.school_id,
				u.class_id, 
				class_grade, 
				class_sub 
			FROM users as u 
			JOIN classes as c on (u.class_id = c.class_id) 
			WHERE u.school_id = $school_id" . 
				($class_id!=-1?" AND u.class_id=$class_id ":' ') . 
				($user_id!=-1?" AND user_id=$user_id ":' ') . 
			" GROUP BY user_id ORDER BY class_grade, class_sub, last, first, username"); 
?>
<? //check if sql $users finds a record if not then sorry son no medals yet  
$numrows=mysql_num_rows($users);
if($numrows <1) {
echo "No user found";
	}else{ ?>
<DIV class="infobox" style="text-align:center;">
	Soldier's and Medal Rank Report Grid
</DIV>


		<FORM action="admin_received_stats.php" method="post" accept-charset="UTF-8" name="y">    
		
			<TABLE class="pretty_grid" style="font-size:12px;">

				<thead>
				
					<tr>
						<th><?=T_('Soldier')?></th>
						<th colspan="1"><?=T_('Grade')?></th>
						<th colspan="1"><?=T_('Subject')?></th>
						<th colspan="1"><?=T_('Medal')?> </th>
						<th colspan="1"><?=T_('Date Earned')?></th>
						<th colspan="1"><?=T_('Medal Received')?></th>
						<th colspan="1"><?=T_('Rank')?></th>
						<th colspan="1"><?=T_('Date Promoted')?></th>
						<th colspan="1"><?=T_('Rank Book Received')?></th>
						<th colspan="1"><?=T_('Rank Card Received')?></th> 	  
					</tr>
					
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><?=T_(" Select when medal received")?></td>
						<td></td>
						<td></td>
						<td><?=T_(" Select when rank book received")?></td>
						<td><?=T_(" Select when rank card received")?></td>
					</tr>
					
				</thead>
 
				<TBODY>

					<? while($user_row = mysql_fetch_assoc($users)): $userid=$user_row['user_id'] ?>

					<tr>
						<th style="white-space:nowrap; text-align:left"><?=es($user_row['last']) . "  - " . es($user_row['first']) ?></th>
							<td><?=$user_row['class_grade'] . "-" . $user_row['class_sub'];?></td>
							<td><INPUT type="hidden" name="school_id" value="<?=$user_row['school_id']?>"></td>
							<td><INPUT type="hidden" name="class_id" value="<?=$user_row['class_id']?>"></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>	
						</tr>
    
 <? 
 
 if (isset($_GET['subject_id'])) 
	$subject_id = $_GET['subject_id'];
 else
	$subject_id = 0;
	
$sql = "SELECT date_awarded, date_received, medal_name, subject_name, medal_marks.subject_id, medal_marks.user_id, medal_marks.medal_ord ";
$sql .= "FROM medal_marks ";
$sql .= "JOIN medals USING (medal_ord) ";
$sql .= "JOIN users USING (user_id) ";
$sql .= "INNER JOIN subjects ON (subjects.subject_id=medal_marks.subject_id) ";
$sql .= "WHERE user_id=" .  $userid . " ";
if ($subject_id > 0) 
	$sql .= "AND medal_marks.subject_id=" . $subject_id . " ";
if ($from_awarded != 0) 
	$sql .= "AND date_awarded >=" . $from_awarded . " ";
if ($to_awarded != 0)	
	$sql .= "AND date_awarded <=" . $to_awarded . " ";
if ($medals_filter == 1)
	$sql .= "AND date_received IS NULL ";
elseif ($medals_filter == 2)
	$sql .= "AND date_received IS NOT NULL ";
$sql .= "ORDER BY medal_ord";

$info = mq($sql);

/*
 $info = mq(
 "select 
     date_awarded,
     date_received,
     medal_name,
     subject_name,
     medal_marks.subject_id,
     medal_marks.user_id,
     medal_marks.medal_ord 
 FROM medal_marks 
 JOIN medals USING (medal_ord) 
 JOIN users USING (user_id) 
 inner join subjects ON subjects.subject_id=medal_marks.subject_id 
 WHERE user_id = $userid".
 ($subject!=-1?" AND medal_marks.subject_id=$subject ":'').
 ($from_awarded!=0?" AND date_awarded>=$from_awarded ":'').
 ($to_awarded!=0?" AND date_awarded<=$to_awarded ":'')."
 ORDER BY medal_ord"); 
*/
    
  while($row = mysql_fetch_assoc($info)): 
   
 ?>

			<tr>
				<th></th>
				<td></td>
				<td><?=es($row['subject_name']) ?></td>
				<td><?=es($row['medal_name']) ?></td>
				<td style="white-space:nowrap"><?=dateToHebrew($row['date_awarded']) ?></td>
				<TD style="text-align:center">
					<? if (is_null($row['date_received'])) :?>
						<LABEL>
							<INPUT type="checkbox" name="date_received[<?=$userid?>][<?=$row['subject_id']?>][]" value="<?=$row['medal_ord']?>">
						</LABEL>
					<? else: ?>
						<?=es($row['date_received'])?>
					<? endif; ?>
				</TD>
				<td></td>
				<td></td>
				<td></td>
				<td></td>	
			</tr>

 <? 
 endwhile;// end while for medal info 
 
 // add the ranks here 
  
   $rank_info = mq("SELECT user_id, rank_ord, rank_name, rank_color,date_promoted,date_book_received,date_card_received FROM rank_marks JOIN ranks USING (rank_ord) JOIN users USING (user_id) where user_id=$userid".($from_promoted!=0?" AND date_promoted>=$from_promoted ":'').($to_promoted!=0?" AND date_promoted<=$to_promoted ":''));
   while($rank_row = mysql_fetch_assoc($rank_info)):  
 ?>

<tr>
   <th></th>
   <td></td>
    <td></td>
    <td></td>
    <td></td>
    <TD></TD>
    <td <?=!empty($rank_row['rank_color']) ? 'style="color: ' . $rank_row['rank_color'] . ';"' : ''?>><?=es($rank_row['rank_name']) ?></td>
    <td><?=dateToHebrew($rank_row['date_promoted']) ?></td>
    <TD style="text-align:center"><?if(is_null($rank_row['date_book_received'])):?><LABEL><INPUT type="checkbox" name="rankdate_book_received[<?=$userid?>][]" value="<?=$rank_row['rank_ord']?>"></LABEL><?else:?><?=$rank_row['date_book_received']?><?endif;?></TD>
    <TD style="text-align:center"><?if(is_null($rank_row['date_card_received'])):?><LABEL><INPUT type="checkbox" name="rank_card_received[<?=$userid?>][]" value="<?=$rank_row['rank_ord']?>"></LABEL><?else:?><?=$rank_row['date_card_received']?><?endif;?></TD>	
</tr>
        
 <? endwhile; ?>  
 <? endwhile; ?>
  
</TBODY>
</TABLE>
<div>
<INPUT type="submit" value="<?=T_('Save')?>">
<INPUT type="reset" value="<?=T_('Undo Changes')?>">

</div>
<?}?>
 <? endif; ?>
 <? //endif; ?>
 </form>

 </div>
</DIV>
<DIV class="noprint">
<? include('admin_footer.php'); ?>
</DIV>
</BODY>
</HTML>
