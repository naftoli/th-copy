<? $admin_auth = array('school'); 
 require('header.php'); 
 require_once('calendar.php'); 
 assure_id_school('school_id'); //this will only allow the school logged in to see thier school -cool

$school_id = gri('school_id', -1);
$class_id = gri('class_id', -1);
$user_id = gri('user_id', -1);


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
<TITLE><?=T_("Soldier's Medal and Rank Report"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
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
<DIV class="body_header_left">
<A HREF="admin.php"><?=T_('Home page')?></A>
</DIV>
<DIV class="body_header_right">
<A HREF="logout.php"><?=T_('Logout')?></A>
</DIV>
<DIV class="left_menu"><?include('admin_inc.php'); // this displays the top left menu?></DIV>

<H1><?=T_("Soldier's Medal and Rank Report")?></H1>
<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>

<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>

<? $school_result = mq('SELECT school_id, school_name FROM schools' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY school_name'); ?>


<FORM  name="myform" action="y.php" method="get" accept-charset="UTF-8">
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

<? $user_result = mq("SELECT class_grade, class_sub, user_id, username, first, last FROM users LEFT JOIN classes USING (school_id, class_id) WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . " ORDER BY class_grade, class_sub, last, first, username"); ?>

<FORM action="y.php" method="get" accept-charset="UTF-8">
<P>
<? if(!empty($admin_user)): ?>
   <INPUT type="hidden" name="school_id" value="<?=$school_id?>">
    <?=T_('Choose Platoon')?>: <SELECT name="class_id">
    <OPTION value="">&lt;<?=T_('All')?>&gt;

<?while($class_row = mysql_fetch_assoc($class_result)):?>
      <OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row[       'class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>

</SELECT><BR>
<?=T_('Choose Soldier')?> <SELECT name="user_id">
<OPTION value="">&lt;<?=T_('All')?>&gt;
<? while($user_row = mysql_fetch_assoc($user_result)): ?>
  <OPTION value="<?=$user_row['user_id']?>" <?=$user_row['user_id'] == $user_id ? 'SELECTED' : ''?>><?=$class_id == -1 &&    $user_row['class_grade'] != '' ? es($user_row['class_grade'] . '-' . $user_row['class_sub']) . ': ' : ''?><?=es($user_row['last'])?>, <?=es($user_row['first'])?> (<?=es($user_row['username'])?>)</OPTION>
<?endwhile;?>

</SELECT><BR><INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</form>
  <br>
  <hr>
<?endif;?>

<?if(($class_id != -1) ||  ($user_id != -1)): // show only if platoon or user is selected?>

<? $users = mq("SELECT user_id,first,last,school_id,class_id FROM users WHERE school_id = $school_id" .
($class_id != '' ? " AND class_id = $class_id " : '') . ($user_id != '' ? " AND user_id = $user_id " : '') . " GROUP BY  user_id ORDER BY last,first,username"); 

?>

<? //check if sql $users finds a record if not then sorry son no medals yet  
$numrows=mysql_num_rows($users);
if($numrows <1) {
echo "No user found";
	}else{ ?>

<?
$usersT = mq("SELECT user_id,first,last,school_id,class_id FROM users WHERE school_id = $school_id" .
($class_id != '' ? " AND class_id = $class_id " : '') . ($user_id != '' ? " AND user_id = $user_id " : '') . " GROUP BY  user_id ORDER BY last,first,username"); 

 while($user_row = mysql_fetch_assoc($usersT)): 
 $userid=$user_row['user_id'];

 $r = mq("select date_awarded,date_received,medal_name,subject_name,medal_marks.subject_id,medal_marks.user_id,medal_marks.medal_ord from medal_marks JOIN medals USING (medal_ord) JOIN users USING (user_id) inner join subjects ON subjects.subject_id=medal_marks.subject_id WHERE user_id = $userid ORDER BY medal_ord");
	 
$numrows=mysql_num_rows($r);
	echo "<br>total medals ".$numrows."<br>";
	for ($c=0;$c<$numrows;$c++)
	{
	echo $c." ";
	echo mysql_result($r,$c,2)."<br>";
	}  
   endwhile;
 
 ?>

   
   <FORM action="admin_received_stats.php" method="post" accept-charset="UTF-8" name="y">    
    <TABLE class="pretty_grid">

<thead>
<tr>
 <th><?=T_('Soldier')?></th>
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

<? while($user_row = mysql_fetch_assoc($users)): 
     $userid=$user_row['user_id'];
?>

<tr>
  <th style="white-space:nowrap; text-align:left"><?=es($user_row['last']) . "  - " . es($user_row['first']) ?></th>
    <td><INPUT type="hidden" name="school_id" value="<?=$user_row['school_id']?>"></td>
	<td><INPUT type="hidden" name="class_id" value="<?=$user_row['class_id']?>"></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>	
</tr>
    
 <? $info = mq("select date_awarded,date_received,medal_name,subject_name,medal_marks.subject_id,medal_marks.user_id,medal_marks.medal_ord from medal_marks JOIN medals USING (medal_ord) JOIN users USING (user_id) inner join subjects ON subjects.subject_id=medal_marks.subject_id WHERE user_id = $userid ORDER BY medal_ord");

 $rank_info = mq("SELECT user_id, rank_ord, rank_name, rank_color,date_promoted,date_book_received,date_card_received FROM rank_marks JOIN ranks USING (rank_ord) JOIN users USING (user_id) where user_id=$userid");
$numrows=mysql_num_rows($rank_info);

  $count=0;  
  while($row = mysql_fetch_assoc($info)): 
   
 ?>

<tr>
   <th></th>
    <td><?=es($row['subject_name']) ?></td>
    <td><?=es($row['medal_name']) ?></td>
    <td style="white-space:nowrap"><?=dateToHebrew($row['date_awarded']) ?></td>
    <TD style="text-align:center"><?if(is_null($row['date_received'])):?><LABEL><INPUT type="checkbox" name="date_received[<?=$userid?>][<?=$row['subject_id']?>][]" value="<?=$row['medal_ord']?>"></LABEL><?else:?><?=es($row['date_received'])?><?endif;?></TD>
    <td><? 
	if($count<$numrows)
	{
	echo mysql_result($rank_info,$count,2);
	}
	?>
	</td>
    <td></td>
    <td></td>
   <td></td>	
</tr>

 <?
$count++;
 
 endwhile;// end while for medal info 
 
 // add the ranks here 
  
     $rank_info = mq("SELECT user_id, rank_ord, rank_name, rank_color,date_promoted,date_book_received,date_card_received FROM rank_marks JOIN ranks USING (rank_ord) JOIN users USING (user_id) where user_id=$userid");
   while($rank_row = mysql_fetch_assoc($rank_info)):  
 ?>

<tr>
   <th></th>
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
 <? endif; ?>
 </form>

 </div>
</DIV>
<DIV class="noprint">
<? include('admin_footer.php'); ?>
</DIV>
</BODY>
</HTML>
