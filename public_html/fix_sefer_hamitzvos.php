<?
require 'db.php';

//correct number of missions needed per medal
$correct = array(
	1	=>	15,
	2	=>	20,
	3	=>	25,
	4	=>	30,
	5	=>	35,
	6	=>	40,
	7	=>	45,
	8	=>	50,
	9	=>	55,
	10	=>	60
);

//for each user
$users = array();
$sql = "select u.user_id from users u 
		join medal_marks mm using (user_id) 
		where mm.subject_id = 21 
		group by u.user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$users[] = $row['user_id'];
}

//find out how many missions user completed
$missions = array();
foreach ($users as $id) {
	$sql = "select count(dtmm.date_tasks_mission_id) as total from date_tasks_mission_marks dtmm 
			join users u using (user_id) 
			where dtmm.subject_id = 21 
			and u.user_id = " . $id;
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$missions[$id] = $row['total'];
	}		
}

//find out how many medals user has
$medals = array();
foreach ($users as $id) {
	$sql = "select max(medal_ord) as medal from medal_marks where subject_id = 21 and user_id = " . $id;
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$medals[$id] = $row['medal'];
	}
}

//determine how many should have
foreach ($users as $id) {
	$total = $missions[$id];
	$needs = 0;
	$shouldHave = 0;
	
	foreach ($correct as $k => $numMissions) {
		$needs += $numMissions;
		if ($total < $needs) {
			if ($k > 1) {
				$shouldHave = $k - 1;
			}
			break;
		}
	}
	
	echo "User ID: " . $id . "<br />";
	echo "Missions earned: " . $missions[$id] . "<br />";
	echo "Highest medal earned: " . $medals[$id] . "<br />";
	echo "Highest medal should have earned: " . $shouldHave . "<br /><br />";
}

//change extra medals to new campaign
?>