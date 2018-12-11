<?php
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');

$action = "";
$start_date = unixtojd() - 21;
$end_date = unixtojd();

if (isset($_POST['action'])) 
{
	include ("classes/spms_school.php");
	include ("classes/upm_subject.php");
	include("classes/upm_medal.php");
	include("classes/upm_subject_user.php");
	
	$action = $_POST["action"];
	$end_date = $_POST["end_date"];
	
	if (isset($_POST['registered'])) $registered = $_POST['registered'];
	else $registered = false;
	
	$schools = array();
	$sql = "SELECT school_id, school_name FROM schools WHERE school_era IS NULL ORDER BY school_name";
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) 
	{
		$school = new spms_school($row);
		$school->get_subjects($start_date, $end_date, $registered);
		array_push($schools, $school);
	}
	
	$medal_totals = array();
	foreach ($schools as $school)
	{

		foreach ($school->subjects as $subject)
		{
			
			foreach ($subject->subject_users as $subject_user) 
			{
			
				$possible_missions = $subject_user->possible_missions;
				
				foreach ($subject->subject_medals as $subject_medal) 
				{
					$missions_required = $subject_medal->missions_required;
					
					if ($possible_missions < $missions_required) 
					{
						break;
					}
					else
					{
						$subject_user->set_medal_awarded($subject->subject_id, $subject_user->user_id, $subject_medal->medal_ord);
							
						if ($subject_user->medal_awarded == false) 
						{						
							$school->set_display(true);							
							$subject->set_show_subject(true);
								
							$key = $school->school_name . ":" . $subject->subject_name . ":" . $subject_medal->medal_name;
								
							if (!array_key_exists($key, $medal_totals))
								$medal_totals[$key] = 1;
							else
								$medal_totals[$key] = $medal_totals[$key] + 1;
								
						}
					
					}
					
				}
						
			}
			
		}
	}
	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE><?=T_('Mission Report'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	
	<BODY>
		<?php include('admin_header.php'); ?>
		
		<div>
		
			<div class="body">

				<div class="sub_menu">
				</div>
			
				<div class="noprint">
				
					<H1>Reports</H1>
						
					<FORM action="schools_possible_medals_summary.php" method="post" accept-charset="UTF-8">
						<input type="hidden" name="action" value="report">
						
						<p><input type="checkbox" name="registered"<? if (isset($_POST['registered'])) echo " checked"; ?>>Only registered children<br />
							
							<label>
								End Date
								<input type="text" name="end_date_disp" id="end_date_disp" value="<?=es(dateToHebrew($end_date))?>" onClick="get_summary_date(this.form, 'end_date', true);">
							</label>
								
							<INPUT type="hidden" name="end_date" id="end_date" value="<?=$end_date?>"> <?=T_('Usually, last day of school or term.')?>
							
							<BR>
																							
						</p>
					
					</FORM>
					
				</div>
				
			</div>
			
		</div>
		
		<div id="report_div" name="report_div">
			<? if ($action != "") : ?>
			
			<br />
			<table>
				<tr>
					<th><span style="color:green;">SUMMARY</span></th>
				</tr>
			
			<? 
				$prev_school = "";
				$prev_subject = "";
				
				foreach ($medal_totals as $key => $value) 
				{
				
					$info = explode(":", $key);
					$school = $info[0];
					$subject = $info[1];				
					$medal = $info[2];
					
					if ($prev_school != $school) 
					{
						if ($prev_school != "")
							echo "<tr><td>&nbsp;</td></tr>";					
			?>
						<tr><td colspan="2"><span style="color:red;"><?=$school;?></span></td></tr>
			<?
					}
					
					if ($prev_subject != $subject) 
					{
			?>
						<tr><td colspan="2"><span style="color:darkblue;"><?=$subject;?></span></td></tr>
			<?
					}
					
			?>
			
					<tr><td colspan="2"><span style="color:blue;"><?=$medal;?> <?=$value;?></span></td></tr>

			<?
					$prev_school = $school;
					$prev_subject = $subject;
					
					//echo "SCHOOL:" . $school . " SUBJECT:" . $subject . " MEDAL:" . $medal . "<br />";
					//echo "NUMBER:" . $value . "<br />";
				}
			?>
			
			</table>
			
			<? endif; ?>
		</div>
		
	</BODY>
</HTML>
