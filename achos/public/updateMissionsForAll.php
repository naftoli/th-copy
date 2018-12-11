<?
require_once 'db.php';

include("classes/user.php");
include("classes/user_track.php");
include("classes/school_class.php");
include 'class.taskExceptions.php';
include("classes/date_tasks_mission.php");
include("classes/daily_task.php");
include("classes/weekly_task.php");
include("classes/shabbos_task.php");
include("classes/no_label_task.php");
include("classes/task.php");
include("classes/date_tasks_mark.php");

require_once 'class.parshos.php';
$p = new Parshos;
$parshos = $p->getParshos();
	
require_once 'class.missionMarks.php';
require_once("classes/medal_updater.php");
$medal_updater = new medal_updater();

$users = array();
$sql = "select user_id from users";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row['user_id'];
}

foreach ($parshos as $parsha) {
	if ($parsha['start'] > 2456634) break;
	foreach ($users as $user_id) {	    	 
		$mm = new MissionMarks($user_id, 0, $parsha['start'], $parsha['end']);
		$mm->checkMissionCompletion();
		    $medal_updater->update_medal_two($user_id);
		}
	}
}
?>