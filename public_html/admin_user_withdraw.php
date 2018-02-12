<? 
$admin_auth = array('school'); 

require('header.php'); 
require_once('admin_ui.php');

$ui_type = 'school';

check_id_access();

$school_id = gri('school_id', -1);
$search_user_serial = gr('search_user_serial');
$search_first = gr('search_first');
$search_last = gr('search_last');
$search_class_id = gri('search_class_id', -1);

if ($vouchers = gra('vouchers')) 
{
	if (mysql_result(mq("SELECT GET_LOCK('withdraw', 30)"),0) != 1) trigger_error('could not get lock', E_USER_ERROR);
	
	foreach($vouchers as $user_id => $data) 
	{
		$user_id = intval($user_id);
		
		$beginning_of_hebrew_year = beginning_of_hebrew_year();
		$sql = "SELECT count(*) AS vouchers_printed_this_year FROM user_withdraw WHERE user_id=" . $user_id . " AND jul_print_date >= " . $beginning_of_hebrew_year;
		$query = mysql_query($sql);
		$row = mysql_fetch_assoc($query);
		$vouchers_printed_this_year = $row['vouchers_printed_this_year'];
		
		$user_points = mysql_result(mq(totalMarks("WHERE user_id = $user_id AND mark_date >= " . chaiElul())), 0);
		$used_points = mysql_result(mq("SELECT IFNULL(SUM(points), 0) points_total FROM user_withdraw WHERE user_id = $user_id"), 0);
		
		for ($i = 0; $i < min(count($data), floor(($user_points-$used_points)/50)); $i++) 
		{
			$beginning_of_hebrew_year = beginning_of_hebrew_year();
			$sql = "SELECT count(*) AS vouchers_printed_this_year FROM user_withdraw WHERE user_id=" . $user_id . " AND jul_print_date >= " . $beginning_of_hebrew_year;
			$query = mysql_query($sql);
			$row = mysql_fetch_assoc($query);
			$vouchers_printed_this_year = $row['vouchers_printed_this_year'];
		
			if ($vouchers_printed_this_year <= 64)
			{
				$count = 0;
				do {
					if ($count++ > 100000) 
						trigger_error('could not get ID', E_USER_ERROR);
						
					$id = mysql_result(mq('SELECT FLOOR(RAND() * 999999999)'),0);
				} while(mysql_result(mq("SELECT COUNT(*) FROM user_withdraw WHERE code_id = $id"),0) != 0);
				
				mq("INSERT INTO user_withdraw (user_id, code_id, points, scan_date) SELECT user_id, $id code_id, 50, NOW() FROM users WHERE user_id = $user_id AND school_id = $school_id");
			}
		}
	}
	
	$message = T_('Vouchers withdrawn and cashed.');
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
   
<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_("Users' Vouchers"), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<SCRIPT type="text/javascript">
			function setCheckboxes(form, nameRegex, value) 
			{
				var pattern = new RegExp(nameRegex);

				for(var i = 0; i < form.elements.length; i++) 
				{
					if(pattern.test(form.elements[i].name) && form.elements[i].type == 'checkbox') 
					{
						form.elements[i].checked = (value == -1 ? !form.elements[i].checked : value);
					}
				}
			}
		</SCRIPT>
		
	</HEAD>
	
	<BODY>
	
		<?include('admin_header.php');?>
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
		<DIV class="body">
		
			<DIV class="sub_menu">
				<?if(!empty($message)):?><H2><?=$message?></H2><?endif;?>
			</DIV>
			
			<H1><?=T_('Base Management')?></H1>
			
			<?if($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1):?>
			<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
			<FORM action="admin_user_withdraw.php" method="get" accept-charset="UTF-8">
				<P>
					<LABEL><?=T_('Select Institution')?>: <SELECT name="school_id">
					<?while($school_row = mysql_fetch_assoc($school_result)):?>
<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
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
<DIV class="infobox">
<P><?=T_('Step One: Print a report for each teacher, so that they know how many packs of pictures to give out to each child.')?></P>
<P><?=T_('Step Two: Press select all on the withdraw and cash button.')?></P>
<P><?=T_('Step Three: Press withdraw and cash (at the bottom of the page).')?></P>
<P><?=T_('Step Four: Give the report and pictures to the teacher to give out to the children.')?></P>
</DIV>
<DIV class="infobox2">
<FORM action="admin_user_withdraw.php" method="get" accept-charset="UTF-8">
<P>
<B><?=T_('Search by')?>:</B><BR>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<LABEL style="white-space: nowrap;"><?=T_('Serial #')?>: <INPUT type="text" name="search_user_serial" value="<?=es($search_user_serial)?>"></LABEL>
<LABEL style="white-space: nowrap;"><?=T_('First name')?>: <INPUT type="text" name="search_first" value="<?=es($search_first)?>"></LABEL>
<LABEL style="white-space: nowrap;"><?=T_('Last name')?>: <INPUT type="text" name="search_last" value="<?=es($search_last)?>"></LABEL>
<?$class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub");?>
<LABEL style="white-space: nowrap;"><?=T_('Platoon')?>: <SELECT name="search_class_id">
<OPTION value="-1">&lt;<?=T_('All')?>&gt;
<?while($class_row = mysql_fetch_assoc($class_result)):?>
<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $search_class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
<?endwhile;?>
</SELECT></LABEL>
</P>
<P>
<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
</P>
</FORM>
</DIV>
<BR><BR>
<FORM action="admin_user_withdraw.php" method="post" accept-charset="UTF-8" name="edit">
<? 
/*
$sql = "SELECT class_grade, class_sub, user_id, username, first, last, user_start_date, add_on_two, user_serial, 
	COUNT(user_withdraw.user_id) vouchers, 
	COUNT(scan_date) cashed_vouchers, 
	SUM(points) used_points 
	FROM users 
	LEFT JOIN classes USING (class_id, school_id) 
	LEFT JOIN user_withdraw USING (user_Id) 
	WHERE school_id = $school_id" . ($search_first !== '' ? ' 
	AND first LIKE ' . ms("$search_first%") : '') . ($search_user_serial !== '' ? ' 
	AND user_serial = ' . intval($search_user_serial) : '') . ($search_last !== '' ? ' 
	AND last LIKE ' . ms("$search_last%") : '') . ($search_class_id != -1 ? " 
	AND class_id = $search_class_id" : '') . ' 
	GROUP BY user_id ORDER BY class_grade, class_sub, last, first';
echo $sql;
exit;
*/
$result = mq("SELECT class_grade, class_sub, user_id, username, first, last, user_start_date, add_on_two, user_serial, COUNT(user_withdraw.user_id) vouchers, COUNT(scan_date) cashed_vouchers, SUM(points) used_points FROM users LEFT JOIN classes USING (class_id, school_id) LEFT JOIN user_withdraw USING (user_Id) WHERE school_id = $school_id" . ($search_first !== '' ? ' AND first LIKE ' . ms("$search_first%") : '') . ($search_user_serial !== '' ? ' AND user_serial = ' . intval($search_user_serial) : '') . ($search_last !== '' ? ' AND last LIKE ' . ms("$search_last%") : '') . ($search_class_id != -1 ? " AND class_id = $search_class_id" : '') . ' GROUP BY user_id ORDER BY class_grade, class_sub, last, first'); 
?>
<TABLE CLASS="list list_<?=$align_start?>">
<THEAD>
<TR>
  <TH rowspan="2"><?=T_('Platoon')?></TH>
  <TH rowspan="2">
    <?=T_('Name')?><BR>
    <?=T_('Serial #')?>
  </TH>
  <TH rowspan="2"><?=sprintf(T_('Miles %s'), chaiElulYear())?></TH>
  <TH colspan="3" style="text-align: center;"><?=T_('Existing Vouchers')?></TH>
  <TH rowspan="2"><?=T_('Available Vouchers')?></TH>
  <TH rowspan="2">
    <?=T_('Withdraw Vouchers')?><BR>
    <A HREF="#" onClick="setCheckboxes(document.forms['edit'], 'vouchers\\[\\d+\\]\\[\\]', 1); return false;"><?=T_('Select All')?></A><BR>
    <A HREF="#" onClick="setCheckboxes(document.forms['edit'], 'vouchers\\[\\d+\\]\\[\\]', 0); return false;"><?=T_('Select None')?></A><BR>
    <A HREF="#" onClick="setCheckboxes(document.forms['edit'], 'vouchers\\[\\d+\\]\\[\\]', -1); return false;"><?=T_('Toggle Selections')?></A>
  </TH>
</TR>
<TR>
<TH style="padding: 15px 4px;"><?=T_('Total')?></TH>
<TH style="padding: 15px 4px;"><?=T_('Cashed')?></TH>
<TH style="padding: 15px 4px;"><?=T_('Not Cashed')?></TH>
</TR>
</THEAD>
<? while($row = mysql_fetch_assoc($result)): ?>
<? 
$user_points = mysql_result(mq(totalMarks("WHERE user_id = {$row['user_id']} AND mark_date >= " . chaiElul())), 0); 
?>

<?
	if ($row['user_id'] == 4205)
	{
		echo "<input type='hidden' name='USER POINTS' value='" . $user_points . "'>\n";
		echo "<input type='hidden' name='USED POINTS' value='" . $row['used_points'] . "'>\n";
		$user_vouchers = floor(($user_points-$row['used_points']) / 50);
		echo "<input type='hidden' name='VOUCHERS' value='" . $user_vouchers . "'>\n";
	}
?>

<TR>
  <TD><?=es($row['class_grade'])?>-<?=es($row['class_sub'])?></TD>
  <TD>
    <A href="admin_user.php?action=edit&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$school_id?>"><?=es("{$row['first']} {$row['last']}")?></A><BR>
    <?=$row['user_serial']?>
  </TD>
  <TD><?=floatval($user_points)?></TD>
  <TD><A href="admin_withdraw.php?school_id=<?=$school_id?>&amp;search_user_serial=<?=$row['user_serial']?>&amp;type=b&amp;search=Go"><?=$row['vouchers']?></A></TD>
  <TD><A href="admin_withdraw.php?school_id=<?=$school_id?>&amp;search_user_serial=<?=$row['user_serial']?>&amp;type=ps&amp;search=Go"><?=$row['cashed_vouchers']?></A></TD>
  <TD><A href="admin_withdraw.php?school_id=<?=$school_id?>&amp;search_user_serial=<?=$row['user_serial']?>&amp;type=p&amp;search=Go"><?=$row['vouchers']-$row['cashed_vouchers']?></A></TD>
  <?
	$show = true;
	//$sql2 = "select mark_date from date_tasks_marks where user_id = " . $row['user_id'] . " order by mark_date";
	//$result2 = mysql_query($sql2);
	//$row2 = mysql_fetch_assoc($result2);
	$date = $row['user_start_date'];
	if ($date >= 2455448 || is_null($date)) {
		if ($row['add_on_two'] == 0) {
			$show = false;
		}
	}
	if ($show) {  
  ?>
  
		<TD>
			<? 
			$total = $row['vouchers'];
			$available = 0;
			if ($total < 64) {
				$points = floor(($user_points-$row['used_points'])/50);
				$num = 64 - $total;
				if ($points <= $num)
					$available = $points;
				else 
					$available = $num;
				echo $available;				 
			}
			?>
		</TD>
  
		<TD>
			<? for ($i = 0; $i < $available; $i++): ?>
			<INPUT type="checkbox" name="vouchers[<?=$row['user_id']?>][]" value="1">
			<? endfor; ?>
		</TD>
		
  <? } else { ?>
  <td>0</td><td>&nbsp;</td>
  <? } ?>
</TR>
<? endwhile; ?>
</TABLE>
<P>
<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
<INPUT type="hidden" name="search_first" value="<?=$search_first?>">
<INPUT type="hidden" name="search_last" value="<?=$search_last?>">
<INPUT type="hidden" name="search_user_serial" value="<?=$search_user_serial?>">
<INPUT type="hidden" name="search_class_id" value="<?=$search_class_id?>">
<INPUT type="submit" class="submit" value="<?=es(T_('Withdraw & Cash'))?>">
</P>
</FORM>
<BR style="clear: both;">
</DIV>
</DIV>
			<? endif; ?>
			</DIV>
			
		</DIV>
		
		<? include('admin_footer.php'); ?>
		
	</BODY>
	
</HTML>
