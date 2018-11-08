<? 
//dates in this report do not seem to be working properly - use at own risk
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');
$ui_type = 'reports';
require_once('admin_ui.php');

if (!isset($_SESSION)) 
	session_start();

include("admin_schools.php");

if (isset($_SESSION["school_id"])) 
	$school_id = $_SESSION["school_id"];
else 
	$school_id = 0;

if ($school_id == 0) 
	$school_id = gri('school_id', -1);

$class_id = gri('class_id', -1);
$user_id = gri('user_id', -1);
$report_type = gr('report_type');

switch	($report_type) 
{
	case 'rank':
		$title = T_("Base Rank Report");
    break;

	case 'rank_class':
		$title = T_("Soldier's Rank Report");
    break;

	case 'points':
		$title = T_("Base Mileage Report");
    break;

	case 'points_class':
		$title = T_("Soldier's Mileage Report");
    break;

	default:
		$title = T_("Soldier's Statistics Report");
    break;
}

$default_subjects = gr('subjects'); //army, base, or all, default default is none
$subject_id = array_filter(gra('subject_id', array()), 'is_numeric');
$order_by = gr('order_by', 'cn');
$cols = gra('cols', array_fill_keys(array('p', 's', 'm', 'l', 'e', 'c', 'a', 'b', 't', 'd', 'y', 'u', 'w', 'r', 'n'), true));
$cols = array_merge(array_fill_keys(array('p', 's', 'm', 'l', 'e', 'c', 'a', 'b', 't', 'd', 'y', 'u', 'w', 'r', 'n'), false), $cols);
$registered_only = gri('registered_only', 0);

//////////$start_date = gri('start_date', 2455350);
$start = null;
$end = null;
$d = unixtojd();
$day = date("N");
switch ($day) {
	case 1: //Monday
		$start = $d - 8;
		$end = $d - 2;
		break;
	case 2: //Tuesday
		$start = $d - 9;
		$end = $d - 3;
		break;
	case 3:
		
}
$start_date = gri('start_date', $d - 7);
///////////$end_date = gri('end_date', $d);
$end_date = gri('end_date', $d);

if ($end_date && $start_date > $end_date) 
{ 
	$temp = $start_date;
	$start_date = $end_date;
	$end_date = $temp;
	unset($temp);
}

if ($start_date == 0) 
	$start_date = null;
	
if ($end_date == 0) 
	$end_date = null;

if (!$start_date) 
	$date_message = sprintf(T_('as of %s'), dateToHebrew(!$end_date ? unixtojd() : $end_date));
else
	$date_message = sprintf(T_('from %s to %s'), dateToHebrew($start_date), dateToHebrew(!$end_date ? unixtojd() : $end_date));
	
//echo "<input type='hidden' name='START DATE' value='" . $start_date . "'>\n";	
//echo "<input type='hidden' name='END DATE' value='" . $end_date . "'>\n";	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE>
			<?=$title, ' - ', T_('Tzivos Hashem Management System')?>
		</TITLE>
		
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		
		<SCRIPT type="text/javascript" src="icalendar.js">
		</SCRIPT>
		
		<STYLE type="text/css">
			dl 
			{
				float: <?=$align_start?>;
				margin-<?=$align_end?>: 3em;
			}

			dt 
			{
				font-weight: bold;
			}

			.class_row td, .class_row th 
			{
				background-color: #d8e8fe;
			}
		</STYLE>
		
		<SCRIPT type="text/javascript">
			function setChildCheckboxes(el, value) 
			{
				var els = el.getElementsByTagName('input');

				for (var i = 0; i < els.length; i++) 
				{
					if(els[i].type == 'checkbox') 
					{
						els[i].checked = (value == -1 ? !els[i].checked : value);
					}
				}
			}
		</SCRIPT>
	</HEAD>
	
	<BODY>
		
		<? include('admin_header.php'); ?>
		
		
		<DIV class="ui_<?=$ui_type?> <?=$align_start?>">
		
			<DIV class="body">
			
				<DIV class="sub_menu">
					<? if (!empty($message)) : ?>
						<H2><?=$message?></H2>
					<?endif;?>				
				</DIV>
				
				<!-- ********** SCHOOLS DROP DOWN ********** -->
				<DIV class="noprint">
				
					<H1>
						<?=T_('Reports')?>
					</H1>
					
					
					
					<? if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) : ?>
						<? $school_result = mq('SELECT school_id, school_name, inst_name FROM schools JOIN institutions USING (inst_id)' . ($admin_user['auth'] != 'super' ? ' WHERE school_id IN (' . implode(',', $admin_user['auths']['school']) . ')' : '') . ' ORDER BY inst_name, school_name'); ?>
						<FORM action="admin_stats.php" method="get" accept-charset="UTF-8">
							<P>
								<LABEL>
									<?=T_('Select Institution')?>: 
									<SELECT name="school_id">
										<?while($school_row = mysql_fetch_assoc($school_result)):?>
										<OPTION value="<?=$school_row['school_id']?>" <?=$school_row['school_id'] == $school_id ? 'SELECTED' : ''?>><?=es($school_row['inst_name'])?> - <?=es($school_row['school_name'])?></OPTION>
										<?endwhile;?>
									</SELECT>
								</LABEL>
								<INPUT class="submit" type="submit" value="<?=T_('Go')?>">
							</P>
						</FORM>
					<?endif;?>
					
				</DIV>
				<!-- ********** SCHOOLS DROP DOWN ********** -->
				
			
				<? if($school_id == -1) : ?>
					<?=T_('Please select an Institution.')?>
				<? else: ?>
				
					<DIV class="ui_body">
					
						<DIV class="ui_menu">
							<?ui_menu();?>
						</DIV>
						
						<DIV class="content">
						
							<H2 class="noprint">
								<?=$title?>
							</H2>
							
							<DIV class="infobox noprint">
							
								<OL>
								
									<? if ($report_type != 'rank') : ?>									
									<LI><?=T_('Choose a Platoon or Select ALL<!--, you can also select an individual child-->.')?>
									<? endif; ?>
									
									<? if($report_type == '' || $report_type == 'points' || $report_type == 'points_class') : ?>
									<LI><?=T_('Select which campaigns you would like to view.')?>
									<? endif; ?>
									
									<? if($report_type == '') : ?>
									<LI><?=T_('Choose what you would like view in the report.')?>
									<LI><?=T_('Choose how you would like your report to be sorted.')?>
									<LI><?=T_('Select a start and end date of when you want you report to be from.<BR>Please note: The date will automatically be set for the past week.')?>
									<?endif;?>
									
									<LI><?=T_('Chose if you would like to include all children or only registered Chayolim.')?>
									<LI><?=T_('Press GO.')?>
									<LI><?=T_('Press Print after the report loads.')?>
									<LI><?=T_('If you would like to print a portrait report. After you press print, Click on properties, then advanced, then change the orientation from landscape to portrait.')?>
								</OL>
								
							</DIV>
							
							<DIV style="float: <?=$align_start?>; width: 100%;">
							
								<DIV class="infobox2 noprint">
								
									<FORM action="admin_stats.php" method="get" accept-charset="UTF-8">
									
										<? if($report_type == 'rank') : ?>										
										<DIV style="display: none;">
										<? endif; ?>
										
										<P>
											<? $class_result = mq("SELECT class_id, class_grade, class_sub FROM classes WHERE school_id = $school_id ORDER BY class_grade, class_sub"); ?>
											<INPUT type="hidden" name="school_id" value="<?=$school_id?>">
											<?=T_('Choose Platoon')?>: 
											<SELECT name="class_id">
												<OPTION value="-1">&lt;<?=T_('All')?>&gt;
												<?while($class_row = mysql_fetch_assoc($class_result)):?>
												<OPTION value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'SELECTED' : ''?>><?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?></OPTION>
												<?endwhile;?>
											</SELECT>
											<BR>
										</P>
										
										<? if($report_type == 'rank') : ?>
										</DIV>
										<?endif;?>
										
										<? if (!($report_type == '' || $report_type == 'points' || $report_type == 'points_class')):?>
											<DIV style="display: none;">
										<? endif; ?>
										
<? 
			$subjects = mq("SELECT subject_id, subject_name, subject_type FROM subjects JOIN schools USING (inst_id) JOIN school_subjects USING (subject_id, school_id) WHERE school_id = $school_id 
			AND subject_type != 'school_points' AND subject_id not in (15,27,91) ORDER BY 
			CASE subject_type
			  WHEN 'WWTC' THEN 0
			  WHEN 'Tanya' THEN 1
			  WHEN 'Hakhel' THEN 2
			  WHEN '' THEN 4
			  WHEN 'school_points' THEN 200
			  WHEN 'home_points' THEN 201
			  ELSE 3
			END
			, subject_type, subject_name"); 
?>
			
			<? $old_subject_type = ''; ?>
			
			<? while ($row = mysql_fetch_assoc($subjects)) : ?>
				<? if (!in_array($row['subject_id'], $subject_id) && ($default_subjects == 'all' || ($default_subjects == 'army' && $row['subject_type'] != 'home_points' && $row['subject_type'] != 'school_points') || ($default_subjects == 'base' && ($row['subject_type'] == 'home_points' || $row['subject_type'] == 'school_points')))) $subject_id[] = $row['subject_id']; ?>
				<? if($old_subject_type != 'army' && $row['subject_type'] != 'home_points' && $row['subject_type'] != 'school_points') { echo ($old_subject_type != '' ? '</DL>' : '') . '<DL><DT>' . T_('Army Wide Campaigns') . ':<BR><A href="#" onClick="setChildCheckboxes(this.parentNode.parentNode, -1); return false;">' . T_('Toggle Checkboxes') . '</A>'; $old_subject_type = 'army'; } ?>
				<? if($old_subject_type != 'base' && ($row['subject_type'] == 'home_points' || $row['subject_type'] == 'school_points')) { echo ($old_subject_type != '' ? '</DL>' : '') . '<DL><DT>' . T_('Base Campaigns') . ':<BR><A href="#" onClick="setChildCheckboxes(this.parentNode.parentNode, -1); return false;">' . T_('Toggle Checkboxes') . '</A>'; $old_subject_type = 'base'; } ?>
			  <DD><LABEL><INPUT type="checkbox" name="subject_id[]" value="<?=$row['subject_id']?>" <?=in_array($row['subject_id'], $subject_id) ? 'CHECKED' : ''?>><?=es($row['subject_name'])?></LABEL>
			<? endwhile; ?>
			
			<? if ($old_subject_type !== '') : ?>
			</DL>
			<?endif;?>
			
			<? if (!($report_type == '' || $report_type == 'points' || $report_type == 'points_class')) : ?>
			</DIV>
			<?endif;?>
			
			<? if(!($report_type == '')) :?>
			<DIV style="display: none;">
			<?endif;?>
			
												<DL>
													<DT><?=T_('Show Columns')?>:
													<!--<DD><LABEL><INPUT type="checkbox" name="cols[p]" value="1" <?=$cols['p'] ? '' : ''?>><?=T_('Miles')?></LABEL>-->
													<DD><LABEL><INPUT type="checkbox" name="cols[s]" value="1" <?=$cols['s'] ? 'CHECKED' : ''?>><?=T_('Missions')?></LABEL>
													<DD><LABEL><INPUT type="checkbox" name="cols[m]" value="1" <?=$cols['m'] ? '' : ''?>><?=T_('Medal')?></LABEL>
													<!--<DD><LABEL><INPUT type="checkbox" name="cols[l]" value="1" <?=$cols['l'] ? '' : ''?>><?=T_('Lines of Tanya')?></LABEL><BR>&nbsp;&nbsp; &nbsp; (<?=T_('If Tanya campaign is selected')?>)-->
													<DD><LABEL><INPUT type="checkbox" name="cols[a]" value="1" <?=$cols['a'] ? '' : ''?>><?=T_('Army Miles')?></LABEL> (<?=T_('within the dates')?>)
													<!--
													<DD><LABEL><INPUT type="checkbox" name="cols[b]" value="1" <?=$cols['b'] ? '' : ''?>><?=T_('Base Miles')?></LABEL> (<?=T_('within the dates')?>)
													<DD><LABEL><INPUT type="checkbox" name="cols[t]" value="1" <?=$cols['t'] ? '' : ''?>><?=T_('Total Miles')?></LABEL>
													<DD><LABEL><INPUT type="checkbox" name="cols[d]" value="1" <?=$cols['d'] ? '' : ''?>><?=T_('Miles within Dates')?></LABEL>
													<DD><LABEL><INPUT type="checkbox" name="cols[y]" value="1" <?=$cols['y'] ? '' : ''?>><?=sprintf(T_('Total %s Miles'), chaiElulYear())?></LABEL>
													<DD><LABEL><INPUT type="checkbox" name="cols[u]" value="1" <?=$cols['u'] ? '' : ''?>><?=T_('Auction Points')?></LABEL>
													-->
													<DD><LABEL><INPUT type="checkbox" name="cols[w]" value="1" <?=$cols['w'] ? '' : ''?>><?=T_('Picture Packs Earned')?></LABEL>
													<DD><LABEL><INPUT type="checkbox" name="cols[r]" value="1" <?=$cols['r'] ? '' : ''?>><?=T_('Rank')?></LABEL>
													<DD><LABEL><INPUT type="checkbox" name="cols[n]" value="1" <?=$cols['n'] ? '' : ''?>><?=T_('Second display of name')?></LABEL>
												</DL>
			
												<DL>
													<DT><?=T_('Sort by')?>:
													<DD><LABEL><INPUT type="radio" name="order_by" value="cn" <?=$order_by == 'cn' ? '' : ''?> checked='checked'><?=T_('Class, then Name')?></LABEL>
													<DD><LABEL><INPUT type="radio" name="order_by" value="cr" <?=$order_by == 'cr' ? '' : ''?>><?=T_('Class, then Rank')?></LABEL>
													<!--
													<DD><LABEL><INPUT type="radio" name="order_by" value="cp" <?=$order_by == 'cp' ? '' : ''?>><?=T_('Class, then Total Miles')?></LABEL>
													<DD><LABEL><INPUT type="radio" name="order_by" value=".y" <?=$order_by == 'cy' ? '' : ''?>><?=sprintf(T_('Class, then Total %s Miles'), chaiElulYear())?></LABEL>
													-->
													<DD><LABEL><INPUT type="radio" name="order_by" value=".n" <?=$order_by == '.n' ? '' : ''?>><?=T_('Name')?></LABEL>
													<DD><LABEL><INPUT type="radio" name="order_by" value=".r" <?=$order_by == '.r' ? '' : ''?>><?=T_('Rank')?></LABEL>
													<!--
													<DD><LABEL><INPUT type="radio" name="order_by" value=".p" <?=$order_by == '.p' ? '' : ''?>><?=T_('Total Miles')?></LABEL>
													<DD><LABEL><INPUT type="radio" name="order_by" value=".y" <?=$order_by == '.y' ? '' : ''?>><?=sprintf(T_('Total %s Miles'), chaiElulYear())?></LABEL>
													-->
												</DL>
			
												<P style="clear: both;">
												
												<BR>
												
												<!-- ********** START DATE ********** -->
												<LABEL>
													Start Date:
													<INPUT type="text" name="start_date_disp" READONLY value="<?=es(dateToHebrew($start_date))?>" onClick="getDate(this.form, 'start_date');">
												</LABEL>
				
												<BR>
												<INPUT type="hidden" name="start_date" value="<?=$start_date?>"> <?=T_('Optional. Only affects Miles (including Totals) and Missions! Does not affect Medal, Rank, or lines of Tanya.')?>
												<BR>
												<!-- ********** START DATE ********** -->
												
												<BR>
				
												<!-- ********** END DATE ********** -->
												<LABEL>
													<?=T_('End Date')?>:
													<INPUT type="text" name="end_date_disp" READONLY value="<?=es(dateToHebrew($end_date))?>" onClick="getDate(this.form, 'end_date');">
												</LABEL>
												
												<BR>
												<INPUT type="hidden" name="end_date" value="<?=$end_date?>"> 
												<?=T_('Optional. Only affects Miles (including Totals) and Missions! Does not affect Medal, Rank, or lines of Tanya.')?>
												<BR>
												<!-- ********** END DATE ********** -->
												
												
												
											</P>
			
											<? if (!($report_type == '')) : ?>
											</DIV>
											<?endif;?>
											
											<P style="clear: both;">
											
											<LABEL>
												<INPUT type="checkbox" name="registered_only" value="1" <?=$registered_only ? 'CHECKED' : ''?>> <?=T_('Registered Soldiers Only')?>
											</LABEL>
											<BR>
											
											<BR>
											
											<INPUT type="hidden" name="report_type" value="<?=$report_type?>">
											
											<INPUT class="submit" type="submit" name="view" value="<?=T_('Go')?>">
			
										</P>
										
									</FORM>
									
								</DIV>
								
							</DIV>
			<? if(gr('view')): ?>
			<? if($user_id == -1): ?>
			<?
			$subject_ids = implode(',', $subject_id);
			$subject_query = $subject_ids ? "AND subject_id IN ($subject_ids)" : '';

			$subjects = mq("SELECT subject_id, subject_name, subject_type FROM subjects JOIN schools USING (inst_id) WHERE school_id = $school_id $subject_query ORDER BY
			CASE subject_type
			  WHEN 'WWTC' THEN 0
			  WHEN 'Tanya' THEN 1
			  WHEN 'Hakhel' THEN 2
			  WHEN '' THEN 4
			  WHEN 'school_points' THEN 200
			  WHEN 'home_points' THEN 201
			  ELSE 3
			END
			, subject_type, subject_name");

			switch($order_by[0]) {
			  case '.':
				$order_by_query = '';
				$show_class = false;
				break;

			  case 'c':
			  default:
				$order_by_query = 'class_grade, class_sub, class_id, ';
				$show_class = true;
				break;
			}

			switch($order_by[1]) {
			  case 'n':
			  default:
				$order_by_query .= '';
				break;

			  case 'r':
				$order_by_query .= 'rank_ord DESC, ';
				break;

			  case 'p':
				$order_by_query .= 'mark_points DESC, ';
				break;

			  case 'y':
				$order_by_query .= 'mark_y_points DESC, ';
				break;
			}

			$date_query = datesToQuery('mark_date', $start_date, $end_date);
			$class_query2 = " school_id = $school_id" . ($class_id != -1 ? " AND class_id = $class_id" : '');
			$user_query = ($registered_only ? ' AND user_registered IS NOT NULL' : '');
			$class_query = ($class_id != -1 ? ' LEFT JOIN classes USING (school_id, class_id)' : '') . ' WHERE' . $class_query2 . $user_query;

			$users = mq("
			SELECT user_id, first, last, first_he, last_he, username, class_id, IFNULL(marks_i.mark_points, 0) mark_points, IFNULL(marks_d_i.mark_points, 0) mark_d_points, IFNULL(marks_y_i.mark_points, 0) mark_y_points, IFNULL(marks_a_i.mark_points, 0) mark_a_points, IFNULL(marks_b_i.mark_points, 0) mark_b_points, rank_ord, rank_name, rank_color
			FROM users
				 LEFT JOIN classes USING (class_id, school_id)
				 LEFT JOIN (" . totalMarks("JOIN users USING (user_id) $class_query", 'user_id') . ") marks_i USING (user_id)
				 LEFT JOIN (" . totalMarks("JOIN users USING (user_id) $class_query AND $date_query", 'user_id') . ") marks_d_i USING (user_id)
				 LEFT JOIN (" . totalMarks("JOIN users USING (user_id) $class_query AND mark_date >= " . beginning_of_hebrew_year() , 'user_id') . ") marks_y_i USING (user_id)
				 LEFT JOIN (" . totalMarks("JOIN users USING (user_id) JOIN subjects USING (subject_id) $class_query AND $date_query AND subject_type != 'home_points' AND subject_type != 'school_points'", 'user_id') . ") marks_a_i USING (user_id)
				 LEFT JOIN (" . totalMarks("JOIN users USING (user_id) JOIN subjects USING (subject_id) $class_query AND $date_query AND (subject_type = 'home_points' OR subject_type = 'school_points')", 'user_id') . ") marks_b_i USING (user_id)
				 LEFT JOIN (SELECT user_id, rank_ord, rank_name, rank_color FROM (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks JOIN users USING (user_id) $class_query GROUP BY user_id) rank JOIN ranks USING (rank_ord)) ranks_i USING (user_id)
			WHERE
			$class_query2
			$user_query
			ORDER BY $order_by_query last_he, first_he, last, first, username");

			$tanya = mysql_fetch_column(mq("SELECT user_id, IFNULL(lines_done - lines_offset, 0) lines_done, medal_ord, pledges, collected FROM users JOIN tanya_users USING (user_id) $class_query"));

			//per subject, per user
			
			if ($cols['s']) 
			{
				$sql = "SELECT user_id, subject_id, SUM(mission_count) AS missions "; 
				$sql = $sql . "FROM users ";
				$sql = $sql . "JOIN subjects ";
				$sql = $sql . "JOIN date_tasks_mission_marks USING (user_id, subject_id) ";
				$sql = $sql . $class_query . " ";
				$sql = $sql . $subject_query . " ";
				if ($start_date > 0)
				{
					$sql = $sql . "AND mark_date >= " . $start_date . " ";
					if ($end_date > 0)
						$sql = $sql . "AND mark_date <= " . $end_date . " ";
				}
				else
				{
					if ($end_date > 0)
						$sql = $sql . "AND end_date <= " . $end_date . " ";				
				}
				$sql = $sql . "GROUP BY user_id, subject_id";
				echo "<input type='hidden' name='MISSIONS' value='$sql'>";
				$missions = mysql_fetch_column_tuple(mq($sql));
			}
			
			if($cols['m']) $medals = mysql_fetch_column_tuple(mq("SELECT user_id, subject_id, medal_ord, medal_name FROM (SELECT MAX(medal_ord) medal_ord, user_id, subject_id FROM medal_marks GROUP BY user_id, subject_id) medal JOIN medals USING (medal_ord) JOIN users USING (user_id) $class_query"));
			$points = mysql_fetch_column_tuple(mq(totalMarks("JOIN users USING (user_id) $class_query $subject_query AND $date_query", 'user_id, subject_id')));

			if($show_class) {
			  $classes = mysql_fetch_column(mq("
			  SELECT classes.class_id, class_grade, class_sub, class_teacher, IFNULL(marks_i.mark_points, 0) mark_points, IFNULL(marks_d_i.mark_points, 0) mark_d_points, IFNULL(marks_y_i.mark_points, 0) mark_y_points, IFNULL(marks_a_i.mark_points, 0) mark_a_points, IFNULL(marks_b_i.mark_points, 0) mark_b_points
			  FROM (SELECT class_id, class_grade, class_sub, class_teacher FROM classes classes_inner WHERE $class_query2 UNION ALL (SELECT NULL class_id, NULL class_grade, NULL class_sub, NULL class_teacher)) classes
				   LEFT JOIN (" . totalMarks("JOIN users USING (user_id) $class_query", 'class_id') . ") marks_i ON (classes.class_id <=> marks_i.class_id)
				   LEFT JOIN (" . totalMarks("JOIN users USING (user_id) $class_query AND $date_query", 'class_id') . ") marks_d_i ON (classes.class_id <=> marks_d_i.class_id)
				   LEFT JOIN (" . totalMarks("JOIN users USING (user_id) $class_query AND mark_date >= " . beginning_of_hebrew_year() , 'class_id') . ") marks_y_i ON (classes.class_id <=> marks_y_i.class_id)
				   LEFT JOIN (" . totalMarks("JOIN users USING (user_id) JOIN subjects USING (subject_id) $class_query AND $date_query AND subject_type != 'home_points' AND subject_type != 'school_points'", 'class_id') . ") marks_a_i ON (classes.class_id <=> marks_a_i.class_id)
				   LEFT JOIN (" . totalMarks("JOIN users USING (user_id) JOIN subjects USING (subject_id) $class_query AND $date_query AND (subject_type = 'home_points' OR subject_type = 'school_points')", 'class_id') . ") marks_b_i ON (classes.class_id <=> marks_b_i.class_id)
			  ORDER BY classes.class_id"));

			  //per subject, per class
			  $tanya_class = mysql_fetch_column(mq("SELECT class_id, SUM(lines_done - lines_offset) lines_done, SUM(pledges) pledges, SUM(collected) collected FROM users JOIN tanya_users USING (user_id) $class_query GROUP BY class_id"));
			  
				if ($cols['s']) 
				{
					$sql = "SELECT class_id, subject_id, SUM(mission_count) AS missions ";
					$sql = $sql . "FROM users ";
					$sql = $sql . "JOIN subjects ";
					$sql = $sql . "JOIN date_tasks_mission_marks USING (user_id, subject_id) " . $class_query . " " . $subject_query . " ";
					$sql = $sql . "GROUP BY class_id, subject_id";
					if ($start_date > 0)
					{
						$sql = $sql . " AND mark_date >= " . $start_date . " ";
						if ($end_date > 0)
							$sql = $sql . " AND mark_date <= " . $end_date . " ";
					}
					else
					{
						if ($end_date > 0)
							$sql = $sql . " AND end_date <= " . $end_date . " ";				
					}
					
					$missions_class = mysql_fetch_column_tuple(mq($sql));					
				}
			  $points_class = mysql_fetch_column_tuple(mq(totalMarks("JOIN users USING (user_id) $class_query $subject_query AND $date_query", 'class_id, subject_id')));

			  $old_class_id = -1;
			}
			?>

			<P style="text-align: center;" class="noprint">
			<INPUT type="button" onClick="window.print();" value="<?=T_('Print')?>">
			</P>

			<TABLE class="pretty_grid">
				<THEAD>
					<TR>
						<TH colspan="0" style="font-size: 150%; border: none; background-color: white; padding-bottom: 1em;"><?=$title?> <?=es($date_message)?></TH>
					</TR>
					
					<TR>
						<TH colspan="<?=1+ ($cols['r'] ? 1 : 0)?>"></TH>
						<? while($row = mysql_fetch_assoc($subjects)): ?>
							<? $colspan = 0; ?>
							<? if($cols['p']) $colspan++; ?>
							<?
								switch($row['subject_type']) 
								{
									case 'Tanya':
										if($cols['l']) $colspan++;
										if($cols['e']) $colspan++;
										if($cols['c']) $colspan++;
										if($cols['m']) $colspan++;
									break;

									case 'WWTC':
									
									case 'Hakhel':
									
									case '':
										if($cols['s']) $colspan++;
										if($cols['m']) $colspan++;
									break;
								}
								
								if (!$colspan) $colspan = 1;
							?>

						<TH colspan="<?=$colspan?>"><?=es($row['subject_name'])?></TH>
						<? endwhile; ?>
						
						<? $i = ($cols['a'] ? 1 : 0) + ($cols['b'] ? 1 : 0) + ($cols['t'] ? 1 : 0) + ($cols['d'] ? 1 : 0) + ($cols['y'] ? 1 : 0) + ($cols['u'] ? 1 : 0) + ($cols['w'] ? 1 : 0) + ($cols['n'] ? 1 : 0); ?>
						
						<? if($i) : ?>
						<TH colspan="<?=$i?>"></TH>
						<? endif; ?>
					</TR>
					
					<? @mysql_data_seek($subjects, 0); ?>
					
					<?if($show_class): ob_start();?>
					<?else:?>
					<TR>
					<?endif;?>
					
					<?if($cols['r']):?>
						<TH><?=T_('Rank')?></TH>
					<?endif;?>
					
					<TH>
						<?=T_('Soldier')?>
					</TH>
					
					<? while ($row = mysql_fetch_assoc($subjects)) : ?>
					<? if($cols['p']):?>
						<TH><?=T_('Miles')?></TH>
					<? endif; ?>
					
				<?
					switch($row['subject_type']) 
					{
						case 'Tanya':
							if($cols['l']) echo '<TH>' . T_('Lines of Tanya') . "</TH>\n";
							if($cols['e']) echo '<TH>' . T_('Pledges') . "</TH>\n";
							if($cols['c']) echo '<TH>' . T_('Collected') . "</TH>\n";
							if($cols['m']) echo '<TH>' . T_('Medal') . "</TH>\n";
							if(!$cols['p'] && !$cols['l'] && !$cols['e'] && !$cols['c'] && !$cols['m']) echo '<TD>-</TD>';
						break;

						case 'WWTC':
						
						case 'Hakhel':
						
						case '':
							if($cols['s']) echo '<TH>' . T_('Missions') . "</TH>\n";
							if($cols['m']) echo '<TH>' . T_('Medal') . "</TH>\n";
							if(!$cols['p'] && !$cols['s'] && !$cols['m']) echo '<TD>-</TD>';
						break;

						default:
							if(!$cols['p']) echo '<TD>-</TD>';
						break;
					}
				?>
			  <? endwhile; ?>
			  <?if($cols['a']):?><TH><?=T_('Army Miles')?></TH><?endif;?>
			  <?if($cols['b']):?><TH><?=T_('Base Miles')?></TH><?endif;?>
			  <?if($cols['t']):?><TH><?=T_('Total Miles')?></TH><?endif;?>
			  <?if($cols['d']):?><TH><?=T_('Miles within Dates')?></TH><?endif;?>
			  <?if($cols['y']):?><TH><?=sprintf(T_('Total %s Miles'), chaiElulYear())?></TH><?endif;?>
			  <?if($cols['u']):?><TH><?=T_('Auction Points')?></TH><?endif;?>
			  <?if($cols['w']):?><TH><?=T_('Picture Packs Earned')?></TH><?endif;?>
			  <?if($cols['n']):?><TH><?=T_('Soldier')?></TH><?endif;?>
			<?if($show_class): $col_headers = ob_get_clean();?>
			<?else:?>
			</TR>
			<?endif;?>
			</THEAD>

			<?if(!$show_class):?><TBODY><?endif;?>
			<? while($row = mysql_fetch_assoc($users)): ?>
			<? if($show_class && $row['class_id'] != $old_class_id): ?>
			<? if($old_class_id != -1) echo '</TBODY>'; ?>
			<? $old_class_id = $row['class_id']; ?>
			<TBODY class="onepage_after">
			<TR class="class_row">
			<?=$col_headers?>
			</TR>
			<TR class="class_row">
			  <?if($cols['r']):?><TD></TD><?endif;?>
			  <TH><?=is_null($row['class_id']) ? T_('Not in a class') : es($classes[$row['class_id']]['class_grade']) . '-' . es($classes[$row['class_id']]['class_sub'])?><BR><?=es($classes[$row['class_id']]['class_teacher'])?></TH>
			  <? @mysql_data_seek($subjects, 0); ?>
			  <? while($subject = mysql_fetch_assoc($subjects)): ?>
				<?if($cols['p']):?><TD><?=isset($points_class[$row['class_id']][$subject['subject_id']]) && floatval($points_class[$row['class_id']][$subject['subject_id']]) ? floatval($points_class[$row['class_id']][$subject['subject_id']]) : ''?></TD><?endif;?>
				<?
				switch($subject['subject_type']) {
				  case 'Tanya':
					if(isset($tanya_class[$row['class_id']])) {
					  if($cols['l']) echo '<TD>', $tanya_class[$row['class_id']]['lines_done'] ? $tanya_class[$row['class_id']]['lines_done'] : '', "</TD>\n";
					  if($cols['e']) echo '<TD>', money_format('%n', $tanya_class[$row['class_id']]['pledges']), "</TD>\n";
					  if($cols['c']) echo '<TD>', money_format('%n', $tanya_class[$row['class_id']]['collected']), "</TD>\n";
					  if($cols['m']) echo "<TD></TD>\n";
					} else {
					  if($cols['l']) echo "<TD></TD>\n";
					  if($cols['e']) echo "<TD></TD>\n";
					  if($cols['c']) echo "<TD></TD>\n";
					  if($cols['m']) echo "<TD></TD>\n";
					}
					if(!$cols['p'] && !$cols['l'] && !$cols['e'] && !$cols['c'] && !$cols['m']) echo '<TD>-</TD>';
					break;

				  case 'WWTC':
				  case 'Hakhel':
				  case '':
					if($cols['s']) echo '<TD>', isset($missions_class[$row['class_id']][$subject['subject_id']]) ? round($missions_class[$row['class_id']][$subject['subject_id']]) : '', "</TD>\n";
					if($cols['m']) echo "<TD></TD>\n";
					if(!$cols['p'] && !$cols['s'] && !$cols['m']) echo '<TD>-</TD>';
					break;

				  default:
					if(!$cols['p']) echo '<TD>-</TD>';
					break;
				}
				?>
			  <? endwhile; ?>

			  <?if($cols['a']):?><TD><?=floatval($classes[$row['class_id']]['mark_a_points'])?></TD><?endif;?>
			  <?if($cols['b']):?><TD><?=floatval($classes[$row['class_id']]['mark_b_points'])?></TD><?endif;?>
			  <?if($cols['t']):?><TD><?=floatval($classes[$row['class_id']]['mark_points'])?></TD><?endif;?>
			  <?if($cols['d']):?><TD><?=floatval($classes[$row['class_id']]['mark_d_points'])?></TD><?endif;?>
			  <?if($cols['y']):?><TD><?=floatval($classes[$row['class_id']]['mark_y_points'])?></TD><?endif;?>
			  <?if($cols['u']):?><TD></TD><?endif;?>
			  <?if($cols['w']):?><TD><?=floor($classes[$row['class_id']]['mark_y_points']/50)?></TD><?endif;?>
			  <?if($cols['n']):?><TH><?=is_null($row['class_id']) ? T_('Not in a class') : es($classes[$row['class_id']]['class_grade']) . '-' . es($classes[$row['class_id']]['class_sub'])?><BR><?=es($classes[$row['class_id']]['class_teacher'])?></TH><?endif;?>
			</TR>
			<? endif;?>
			<TR>
			  <?if($cols['r']):?><TD class="rank_color" <?=!empty($row['rank_color']) ? 'style="color: ' . es($row['rank_color']) . ';"' : ''?>><?=$row['rank_name']?></TD><?endif;?>
			  <TH><A HREF="admin_stats.php?view=1&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>&amp;start_date=<?=$start_date?>&amp;end_date=<?=$end_date?>&amp;order_by=<?=$order_by?>&amp;registered_only=<?=$registered_only?>&amp;report_type=<?=$report_type?>&amp;<?=http_build_query(array('subject_id'=>$subject_id), null, '&amp;')?>&amp;cols=<?=http_build_query(array('cols'=>$cols), null, '&amp;')?>"><?=es($row['first_he'] ? $row['first_he'] : $row['first']) . ' ' . es($row['last_he'] ? $row['last_he'] : $row['last'])?></A></TH>
			  <? @mysql_data_seek($subjects, 0); ?>
			  <? while($subject = mysql_fetch_assoc($subjects)): ?>
				<?if($cols['p']):?><TD><?=isset($points[$row['user_id']][$subject['subject_id']]) && floatval($points[$row['user_id']][$subject['subject_id']]) ? floatval($points[$row['user_id']][$subject['subject_id']]) : ''?></TD><?endif;?>
				<?
				switch($subject['subject_type']) {
				  case 'Tanya':
					if(isset($tanya[$row['user_id']])) {
					  if($cols['l']) echo '<TD>', $tanya[$row['user_id']]['lines_done'] ? $tanya[$row['user_id']]['lines_done'] : '', "</TD>\n";
					  if($cols['e']) echo '<TD>', money_format('%n', $tanya[$row['user_id']]['pledges']), "</TD>\n";
					  if($cols['c']) echo '<TD>', money_format('%n', $tanya[$row['user_id']]['collected']), "</TD>\n";
					  if($cols['m']) echo '<TD>', isset($medals[$row['user_id']][$subject['subject_id']]) ? es($medals[$row['user_id']][$subject['subject_id']]['medal_name']) : '', "</TD>\n";
					} else {
					  if($cols['l']) echo "<TD></TD>\n";
					  if($cols['e']) echo "<TD></TD>\n";
					  if($cols['c']) echo "<TD></TD>\n";
					  if($cols['m']) echo "<TD></TD>\n";
					}
					if(!$cols['p'] && !$cols['l'] && !$cols['e'] && !$cols['c'] && !$cols['m']) echo '<TD>-</TD>';
					break;

				  case 'WWTC':
				  case 'Hakhel':
				  case '':
					if($cols['s']) echo '<TD>', isset($missions[$row['user_id']][$subject['subject_id']]) ? round($missions[$row['user_id']][$subject['subject_id']]) : '', "</TD>\n";
					if($cols['m']) echo '<TD>', isset($medals[$row['user_id']][$subject['subject_id']]) ? es($medals[$row['user_id']][$subject['subject_id']]['medal_name']) : '', "</TD>\n";
					if(!$cols['p'] && !$cols['s'] && !$cols['m']) echo '<TD>-</TD>';
					break;

				  default:
					if(!$cols['p']) echo '<TD>-</TD>';
					break;
				}
				?>
			  <? endwhile; ?>
			  <?if($cols['a']):?><TD><?=floatval($row['mark_a_points'])?></TD><?endif;?>
			  <?if($cols['b']):?><TD><?=floatval($row['mark_b_points'])?></TD><?endif;?>
			  <?if($cols['t']):?><TD><?=floatval($row['mark_points'])?></TD><?endif;?>
			  <?if($cols['d']):?><TD><?=floatval($row['mark_d_points'])?></TD><?endif;?>
			  <?if($cols['y']):?><TD><?=floatval($row['mark_y_points'])?></TD><?endif;?>
			  <?if($cols['u']):?><TD><?$auction_points = auctionPoints($row['user_id']);?><?=$auction_points['cur']?></TD><?endif;?>
			  <?if($cols['w']):?><TD><?=floor($row['mark_y_points']/50)?></TD><?endif;?>
			  <?if($cols['n']):?><TH><A HREF="admin_stats.php?view=1&amp;user_id=<?=$row['user_id']?>&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>&amp;start_date=<?=$start_date?>&amp;end_date=<?=$end_date?>&amp;order_by=<?=$order_by?>&amp;registered_only=<?=$registered_only?>&amp;report_type=<?=$report_type?>&amp;<?=http_build_query(array('subject_id'=>$subject_id), null, '&amp;')?>&amp;cols=<?=http_build_query(array('cols'=>$cols), null, '&amp;')?>"><?=es($row['first_he'] ? $row['first_he'] : $row['first']) . ' ' . es($row['last_he'] ? $row['last_he'] : $row['last'])?></A></TH><?endif;?>
			</TR>
			<? endwhile; ?>

			</TBODY>
			</TABLE>
			<? else: //user_id == -1 ?>
			<A HREF="admin_stats.php?view=1&amp;school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>&amp;start_date=<?=$start_date?>&amp;end_date=<?=$end_date?>&amp;order_by=<?=$order_by?>&amp;registered_only=<?=$registered_only?>&amp;report_type=<?=$report_type?>&amp;<?=http_build_query(array('subject_id'=>$subject_id), null, '&amp;')?>&amp;cols=<?=http_build_query(array('cols'=>$cols), null, '&amp;')?>"><?=T_('Back')?></A>
			<? $user = mysql_fetch_assoc(mq("SELECT user_id, first, first_he, last, last_he, user_serial, username, school_name, class_grade, class_sub FROM users LEFT JOIN schools USING (school_id) LEFT JOIN classes USING (class_id, school_id) WHERE user_id = $user_id AND school_id = $school_id")); ?>
			<H2><?='#', $user['user_serial'], ' ', es($user['first_he'] ? $user['first_he'] : $user['first']), ' ', es($user['last_he'] ? $user['last_he'] : $user['last'])?></H2>
			<H3><?=es($user['school_name']), ' ', es($user['class_grade']), '-', es($user['class_sub'])?></H3>
			<? $result = userStatement($user_id, $start_date, $end_date, implode(',', $subject_id), 'ASC'); ?>

			<? $running_balance = 0; ?>
			<TABLE class="pretty_grid">
			<? if(mysql_num_rows($result)): ?>
			<TR>
			  <TH><?=T_('Posting Date')?></TH>
			  <TH><?=T_('Subject')?></TH>
			  <TH><?=T_('Description')?></TH>
			  <TH><?=T_('Miles Earned')?></TH>
			  <TH><?=T_('Balance')?></TH>
			</TR>
			<? while($row = mysql_fetch_assoc($result)): ?>
			<? $running_balance += $row['points']; ?>
			<TR>
			  <TD><?=dateToHebrew($row['mark_date'])?></TD>
			  <TD><?=es($row['subject_name'])?><BR><?=es($row['name'])?></TD>
			  <TD><?=es($row['description'])?></TD>
			  <TD style="text-align: right;"><?=floatval($row['points']) ? number_format($row['points'], 2) : '-'?></TD>
			  <TD style="text-align: right;"><?=number_format($running_balance, 2)?></TD>
			</TR>
			<? endwhile; ?>
			<? else: ?>
			<TR><TD><?=T_('No transactions for the time period selected.')?></TD></TR>
			<? endif; ?>
			</TABLE>

			<? endif; //user_id == -1 ?>
			<? endif; //gr(view) ?>
			<BR style="clear: both;">
			</DIV>
			</DIV>
			<? endif; ?>
			</DIV>
			</DIV>
			
		<DIV class="noprint">	
			<? include('admin_footer.php'); ?>
		</DIV>
		
	</BODY>
	
</HTML>
