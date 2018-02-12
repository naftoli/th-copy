<?php
include ("db.php");

include("classes/spb_user.php");
include("classes/user_track.php");
include("classes/medal.php");
include("classes/subject.php");

$school_id = $_GET['school_id'];
$class_id = $_GET['class_id'];
$end_date = $_GET['end_date'];

$start_date = unixtojd() - 21;

// ********** Get all the users for the school ********** //
$users = array();
$sql = "SELECT * ";
$sql = $sql . "FROM users AS u ";
$sql = $sql . "JOIN classes AS c USING (class_id) ";
$sql = $sql . "WHERE u.school_id=" . $school_id . " ";
if ($class_id > 0)
	$sql = $sql . "AND u.class_id=" . $class_id . " ";	
$sql = $sql . "ORDER BY c.class_grade, c.class_sub, u.first, u.last ";
$query = mysql_query($sql);
while ($row = mysql_fetch_assoc($query)) 
{	
	$user = new spb_user($row);
	$user->set_show_user(false);
	$user->get_user_tracks($start_date, $end_date);		
	array_push($users, $user);
}
// ********** Get all the users for the school ********** //

$counter = 0;

// ********** Loop through each student ********** //
foreach ($users as $user) 
{
		echo "<br />" . $user->class_grade . "-" . $user->class_sub . "<br />";
		echo "<span style='color:red'>" . $user->last . ", " . $user->first . "</span><br />";
		
		// ********** Loop throug each students subjects ********** //
		foreach ($user->user_tracks as $user_track) 
		{
			echo "<span style='color:darkblue'>" . $user_track->subject_name . "</span><br />";
					
			// The number of missions that a student can achieve before the end date of a report for a given subject //
			$possible_missions = $user_track->possible_missions;
			
			// ********** Loop through all the medals for a subject ********** //
			foreach ($user_track->medals as $medal) 		
			{
				$missions_required = $missions_required + $medal->missions_required;
				//echo $medal->medal_name . "<br />";
				$medal->set_board();
				echo $medal->board . "<br />";
				//echo $medal->medal_name . "<br />";
				//echo "4) MEDAL:" . $medal->medal_name . " REQUIRED MISSIONS:" . $missions_required . "<br />";
				
			}
			// ********** Loop through all the medals for a subject ********** //
			//if no medals show white sticker board
			if ($user_track->noMedals) {
				echo "White<br />";
			}
		}
		// ********** Loop throug each students subjects ********** //
}
// ********** Loop throug each student ********** //

// ********** POSSIBLE MEDALS BY SUBJECT/STUDENTS ********** //
foreach ($users as $user) 
{
		
	if ($user->show_user == true) 
	{
?>
		<table>
			<tr>
				<td>
					<span style="color:red"><?=$user->last . ", " . $user->first;?></span>
				</td>
				
				<td style="width:250px;">
						<? if ($subject_user->class_sub == "") : ?>
						<span style="color:darkblue;"><?=$subject_user->class_grade;?></span>&nbsp;&nbsp;&nbsp;
						<? else : ?>
						<span style="color:darkblue;"><?=$subject_user->class_grade . "-" . $subject_user->class_sub;?></span>&nbsp;&nbsp;&nbsp;						
						<? endif; ?>
				</td>
			</tr>
<?
		foreach ($user->user_tracks as $user_track) 
		{
				
			if ($user_track->show_user_track == true)
			{
?>
				<tr>
					<td>
						<span style="color:darkblue;"><?=$user_track->subject_name . " POSSIBLE:" . $user_track->possible_missions;?></span>&nbsp;&nbsp;&nbsp;
					</td>
					
					<?
					
					echo "<table>";
					foreach ($user_track->medals as $medal) 
					{
					
						if ($medal->show_medal == true)
						{
							echo "<tr><td>" . $medal->medal_name . " " . $medal->missions_required . "</td></tr>";
						}
					}
					echo "</table>";
					
					?>
				</tr>
<?
			}
				
		}
		
			
?>
		</table>
		<br />
<?
			
	} // if ($user->show_user == true) 	
}
// ********** POSSIBLE MEDALS BY SUBJECT/STUDENTS ********** //

// ********** SUMMARY ********** //
?>
