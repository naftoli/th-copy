<?php
include ("db.php");

include("classes/upm_subject3.php");
include("classes/upm_medal3.php");
include("classes/upm_subject_user3.php");

$school_id = $_GET['school_id'];
$class_id = $_GET['class_id'];
//$start_date = unixtojd();
//$end_date = 2456159; //elul ayin beis
//$start_date = unixtojd() - 21;

$start_date = 2455832;
$end_date = 2456159;

// ********** Get all the subjects for the school ********** //
$subjects = array();
$sql = "SELECT * FROM school_subjects JOIN subjects USING (subject_id) WHERE school_id=" . $school_id . " ORDER BY subject_id";
$query = mysql_query($sql);	
while ($row = mysql_fetch_assoc($query)) 
{	
	$subject = new upm_subject($row);
	$subject->get_subject_medals();
	$subject->get_subject_users($school_id, $class_id, $start_date, $end_date);
	array_push($subjects, $subject);
}
// ********** Get all the subjects for the school ********** //

$medal_totals = array();
foreach ($subjects as $subject) 
{
		
	if (count($subject->subject_users) > 0)
	{			
			
		foreach ($subject->subject_users as $subject_user) 
		{			
			$possible_missions = $subject_user->possible_missions;							
				
			foreach ($subject->subject_medals as $subject_medal) 
			{				
				$missions_required = $subject_medal->missions_required;
			
				//if ($subject_user->user_id == 8256 && $subject->subject_id == 1)
				//{
				//	echo "user: " . $subject_user->first . " " . $subject_user->last . " subject: " . $subject->subject_name . 
				//	" medal: " . $subject_medal->medal_name . " missions_required:" . $missions_required . 
				//	" possible_missions:" . $possible_missions . "<br />";
				//}
				
				if ($possible_missions < $missions_required) 
				{
					break;
				}
				else
				{
					$subject_user->set_medal_awarded($subject->subject_id, $subject_user->user_id, $subject_medal->medal_ord);
						
					if ($subject_user->medal_awarded == false) 
					{						
						$subject->set_show_subject(true);
						$subject_user->set_show_user_subject(true);
						$subject_user->set_medal_name($subject_medal->medal_name);
						$subject_user->add_possible_medal($subject_medal->medal_name);
						
						$key = $subject->subject_name . ":" . $subject_medal->medal_name;
							
					
						//if ($subject_user->user_id == 8256 && $subject->subject_id == 1)
						//{
						//	echo "MEDAL NAME:" . $subject_user->medal_name . "<br />";
						//}
						
						if (!array_key_exists($key, $medal_totals))
						{
							//if ($subject_user->user_id == 8256 && $subject->subject_id == 1)
							//{
							//	echo "DOES NOT EXIST<br />";
							//}
							
							$medal_totals[$key] = 1;
						}
						else
						{
							//if ($subject_user->user_id == 8256 && $subject->subject_id == 1)
							//{
							//	echo "EXISTS<br />";
							//}
							
							$medal_totals[$key] = $medal_totals[$key] + 1;
						}
							
					} // if ($subject_user->medal_awarded == false) 	
					
				} // if ($possible_missions < $missions_required) 
				
			} // foreach ($subject->subject_medals as $subject_medal) 
			
		} // foreach ($subject->subject_users as $subject_user) 
			
	} //if (count($subject->subject_users) > 0)
	
} // foreach ($subjects as $subject) //


// ********** POSSIBLE MEDALS BY SUBJECT/STUDENTS ********** //
foreach ($subjects as $subject) 
{
		
	if ($subject->show_subject == true) 
	{
?>
		<table>
			<tr>
				<td>
					<span style="color:red"><?=$subject->subject_name;?></span>
				</td>
			</tr>
<?

		foreach ($subject->subject_users as $subject_user) 
		{
				
			if ($subject_user->show_user_subject == true)
			{
			
				foreach ($subject_user->possible_medals as $possible_medal) 
				{
?>
				<tr>
					<td style="width:250px;">
						<span style="color:darkblue;"><?=$subject_user->first . " " . $subject_user->last;?></span>&nbsp;&nbsp;&nbsp;
					</td>
					
					<td style="width:250px;">
						<? if ($subject_user->class_sub == "") : ?>
						<span style="color:darkblue;"><?=$subject_user->class_grade;?></span>&nbsp;&nbsp;&nbsp;
						<? else : ?>
						<span style="color:darkblue;"><?=$subject_user->class_grade . "-" . $subject_user->class_sub;?></span>&nbsp;&nbsp;&nbsp;						
						<? endif; ?>
					</td>
					
					<td style="width:100px;">
						<span style="color:blue"><?=$possible_medal;?></span>
					</td>
				</tr>
<?
				}
				
			}
				
		}
			
?>
		</table>
		<br />
<?
			
	} // if ($subject->show_subject == true) 	
}
// ********** POSSIBLE MEDALS BY SUBJECT/STUDENTS ********** //


// ********** SUMMARY ********** //
if (count($medal_totals) > 0)
{

	?>
	<br />
	<table>
		<tr>
			<th><span style="color:green;">SUMMARY</span></th>
		</tr>
	<?
	
	$previous_subject = "";
	$counter = 0;
	foreach ($medal_totals as $key => $value)
	{
		$info = explode(":", $key);
		$subject = $info[0];

		if ($previous_subject != $subject) 
		{
			if ($counter > 0)
			{
	?>	
		<tr><td>&nbsp;</td></tr>
	<?
			}
	?>		
		<tr><td colspan="3"><span style="color:red;"><?=$subject;?></span></td></tr>
	<?		
		}
		
	?>	
		<tr>
			<td>
				<span style="color:darkblue;">
					<?=$info[1];?>
				</span>
			</td>
			<td>
				<span style="color:blue;">
					<?=$value;?>
				</span>
			</td>
		</tr>
	<?
	
		$previous_subject = $subject;
		$counter++;
	}
	
	?>
	</table>
	<?
}
// ********** SUMMARY ********** //
?>
