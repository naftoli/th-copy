<?
require_once 'db.php';
$function = $_GET['function'];
echo $function();

function updateMission() {
	$val = alterDB('update');
	return $val;
}

function deleteMission() {
	$val = alterDB('delete');
	return $val;
}

function alterDB($type) {
	$user_id = $_GET['user_id'];
	$mission = $_GET['mission'];
	
	//check if it's one mission or many missions
	if (isOneMission($mission)) {
		//check if updating or deleting
		switch ($type) {
			case 'update':
				$sql2 = "insert into user_sefer_hamitzvos values(null, $user_id, $mission, now())";
				break;
			case 'delete':
				$sql2 = "delete from user_sefer_hamitzvos where user_id = $user_id and mission = $mission";
				break;
		}
		if (mysql_query($sql2)) {
			return "update performed";
		}
		else {
			return "update not performed";
		}
		break;
	}	
	else {
		//get first and last missions to update/delete
		$arr = extractMissions($mission);
		$first = $arr[0];
		$last = $arr[1];
		$success = 0;
		
		if ($type == 'update') {
			for ($i = $first; $i <= $last; $i++) {
				$sql2 = "insert into user_sefer_hamitzvos values(null, $user_id, $i, now())";
				if (mysql_query($sql2)) {
					$success++;
				}
			}
		}
		else if ($type == 'delete') {
			for ($i = $first; $i <= $last; $i++) {
				$sql2 = "delete from user_sefer_hamitzvos where user_id = $user_id and mission = $i";
				if (mysql_query($sql2)) {
					$success++;
				}
			}
		}
		
		if ($success == $last) {
			return "update performed";
		}
		else {
			return "update not performed";
		}
	}
}

function isOneMission($mission) {
	if (strstr($mission, '-'))
		return false;
	else
		return true;
}

function extractMissions($mission) {
	$pos = strpos($mission, '-');
	$first = substr($mission, 0, $pos);
	$last = substr($mission, $pos+1);
	$arr = array($first, $last);
	return $arr;
}
?>