<?
include("db.php");

$school_id = $_GET['school_id'];
$report_id = $_GET['report_id'];

$class_id = 0;
if (isset($_GET['class_id']))
	$class_id = $_GET['class_id'];

$user = 0;
if (isset($_GET['user_id']))
	$user_id = $_GET['user_id'];

include("camps/includes/classes/user.php");
include("camps/includes/classes/user_track.php");
include("classes/date_tasks_mission.php");
include("classes/daily_task.php");
include("classes/weekly_task.php");
include("classes/shabbos_task.php");
include("classes/no_label_task.php");
include("classes/task.php");
include("classes/date_tasks_mark.php");

if ($user_id > 0) 
{
	$sql = "SELECT * FROM users WHERE user_id=" . $user_id;
}
else 
{
	if ($class_id > 0) 
	{
		$sql = "SELECT * FROM users WHERE school_id=" . $school_id . " AND class_id=" . $class_id . " and user_registered > 0";
	}
	else 
	{
		$sql = "SELECT * FROM users WHERE school_id=" . $school_id . " and user_registered > 0";
	}
}
		
$query = mysql_query($sql);

if ($user_id > 0) 
{	
	$row = mysql_fetch_assoc($query);
	$user = new user($row);
	$message = $message . $user->first . " " . $user->last;
	$student_count = 1;
	$user->get_school_class();
	$user->get_user_tracks(-1, $start_date, $end_date);	
}
else 
{
	while ($row = mysql_fetch_assoc($query)) 
	{
		$user = new user($row);
		//$user->get_school_class();
		//$user->get_user_tracks(-1, $start_date, $end_date);				
		//array_push($users, $user);
	}
	$student_count = count($users);
}
?>

<div class="noprint">

	<div class="module clearfix">
		<div class="list_expand">
			<ul>
				<li>
					<h3>
						<span class="icon"></span>Print Instructions
					</h3>
					
					<p style="display:none;">
						<img src="images/Print-Dialog-Small-2.jpg" align="right" />
						<img src="images/Print-Dialog-Small-1.jpg" align="right" />
						In your browser click 'File' then 'Page Setup...'
					</p>
					<p style="display:none;">Step 1: Set the Orientation to Portrait</p>
					<p style="display:none;">Step 2: Check 'Shrink to fit Page Width'</p>
					<p style="display:none;">Step 3: In Options check 'Print Background (colors & images)'</p>
					<p style="display:none;">Step 4: In the second tab set all Margins to 0.5 inches (All Sides)</p>
					<p style="display:none;">Step 5: Set all Headers & Footers to Blank</p>
					<p style="display:none;">Note: The browser will save these preferences for later use.</p>
				</li>
			</ul>
		</div>
	</div>

</div>