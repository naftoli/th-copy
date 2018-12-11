<?php
include ("db.php");

$function_name = $_GET['function_name'];
$parameters = $_GET['parameters'];
$parameters = explode(",", $parameters);

echo $function_name($parameters);

function get_user_missions($parameters)
{
	include("classes/subject.php");
	include("classes/medal.php");
	include("classes/subject_user.php");
	
	$school_id = $parameters[0];
	$class_id = $parameters[1];
	$end_date = $parameters[2];
	
	$start_date = unixtojd() - 21;

	$subjects = array();
	
	$sql = "SELECT * FROM school_subjects JOIN subjects USING (subject_id) WHERE school_id=" . $school_id . " ORDER BY subject_id";
	$query = mysql_query($sql);	
	while ($row = mysql_fetch_assoc($query)) 
	{	
		$subject = new subject($row);
		$subject->set_show_subject(false);
		$subject->get_subject_medals();
		$subject->get_subject_users($school_id, $start_date, $end_date);
		array_push($subjects, $subject);
	}
	
	$medal_totals = array();
	
	// ********** SHOW ONLY ********** //
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
							
							$key = $subject->subject_name . ":" . $subject_medal->medal_name;
							
							if (!array_key_exists($key, $medal_totals))
							{
								$medal_totals[$key] = 1;
							}
							else
							{
								$medal_totals[$key] = $medal_totals[$key] + 1;
							}
							
						}
						
					}
					
				}
				
			}
			
		}
		
	}
	
	$count = 0;
	foreach ($subjects as $subject) 
	{
		
		if ($subject->show_subject == true) 
		{
			echo "<span style='color:red'>" . $subject->subject_name . "</span><br />";

			foreach ($subject->subject_users as $subject_user) 
			{
				
				if ($subject_user->show_user_subject == true)
				{
					$count++;
					echo $count . ") " . $subject_user->user_id . " " . $subject_user->first . " " . $subject_user->last . " MEDAL:" . $subject_user->medal_name . "<br />";
				}
				
			}
			
		}
		
	}
	

	foreach ($medal_totals as $key => $value)
	{
		echo $key . " " . $value . "<br />";
	}
	
	//foreach ($medal_totals as $medal_total)
	//{
	//	var_dump($medal_total);
	//}
	// ********** SHOW ONLY ********** //
}

function get_school_classes_options($parameters)
{
	$school_id = $parameters[0];
	
	$sql = "SELECT * FROM classes WHERE school_id=" . $school_id;
	$query = mysql_query($sql);
	
	$return_string = "<option value='0'>All</option>";
	while ($row = mysql_fetch_assoc($query)) {	
		$return_string = $return_string . "<option value='" . $row['class_id'] . "'>" . $row['class_grade'] . "-" . $row['class_sub'] . " " . $row['class_name'] . "</option>";
	}
	
	return json_encode($return_string);	
}

function get_school_classes($parameters) {
	$school_id = $parameters[0];
	
	$sql = "SELECT * FROM classes WHERE school_id=" . $school_id;
	$query = mysql_query($sql);
	
	$return_string = "<div class='class_list select_box'>";
	$return_string = $return_string . "<a class='prev button'>";
	$return_string = $return_string . "<span class='icon'></span>";
	$return_string = $return_string . "<span class='label'>Previous Platoon</span>";
	$return_string = $return_string . "</a>";
	$return_string = $return_string . "<select name='class_id'>";
	$return_string = $return_string . "<option>Choose a Platoon</option>";
	$return_string = $return_string . "<option value='0'>All Platoons</option>";
	
	while ($row = mysql_fetch_assoc($query)) {	
		$return_string = $return_string . "<option value='" . $row['class_id'] . "'>" . $row['class_grade'] . "-" . $row['class_sub'] . "</option>";
	}
	
	$return_string = $return_string . "</select>";
	$return_string = $return_string . "<a class='next button'>";
	$return_string = $return_string . "<span class='icon'></span>";
	$return_string = $return_string . "<span class='label'>Next Platoon</span>";
	$return_string = $return_string . "</a>";
	$return_string = $return_string . "</div>";
	
	return json_encode($return_string);	
}

function get_first_user_by_school_and_class($parameters) {
	$school_id = $parameters[0];
	
	$user_id = 0;
		
	$sql = "SELECT u.user_id, u.class_id ";
	$sql = $sql . "FROM users AS u ";
	$sql = $sql . "LEFT JOIN classes AS c USING (school_id, class_id) ";
	$sql = $sql . "WHERE u.school_id=" . $school_id . " ";
	$sql = $sql . "AND u.user_registered IS NOT NULL ";
	$sql = $sql . "ORDER BY c.class_grade, c.class_sub, u.last, u.first ";
	$sql = $sql . "LIMIT 1";
	
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	
	if ($row) {
		$user_id = $row['user_id'];
		$class_id = $row['class_id'];
		$element = compact('user_id', 'class_id');
		return json_encode($element);	
	}
	else {
		return json_encode("0");	
	}
		
}

function get_user_by_class($parameters) {
	$school_id = $parameters[0];
	$class_id = $parameters[1];
	
	$user_id = 0;
	
	$sql = "SELECT u.user_id ";
	$sql = $sql . "FROM users AS u ";
	$sql = $sql . "LEFT JOIN classes AS c USING (school_id, class_id) ";
	$sql = $sql . "WHERE u.school_id=" . $school_id . " ";
	$sql = $sql . "AND u.class_id=" . $class_id . " ";
	$sql = $sql . "AND u.user_registered IS NOT NULL ";
	$sql = $sql . "ORDER BY c.class_grade, c.class_sub, u.last, u.first ";
	$sql = $sql . "LIMIT 1";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);
	
	if ($row)
		$user_id = $row['user_id'];
		
	return json_encode($user_id);
}

function get_class_students($parameters) {
	$school_id = $parameters[0];
	$class_id = $parameters[1];
	
	$users = array();
	$sql = "SELECT u.last, u.first, c.class_grade, c.class_sub FROM users AS u LEFT JOIN classes AS c USING (school_id, class_id) WHERE u.school_id=" . $school_id . " AND user_registered IS NOT NULL AND u.class_id=" . $class_id . " ORDER BY c.class_grade, c.class_sub,last, first"; 
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$last = $row["last"];
		$first = $row["first"];
		$class_grade = $row["class_grade"];
		$class_sub = $row["class_sub"];
		
		$user = compact('last', 'first', 'class_grade', 'class_sub');
		array_push($users, $user);
	}
	return json_encode($users);
}
?>