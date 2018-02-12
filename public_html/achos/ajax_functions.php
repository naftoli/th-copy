<?
require_once 'db.php';
$function = $_GET['function'];
echo $function();

function get_students(){
	$sql = "SELECT * ";
	$sql .= "FROM users ";
	$sql .= "WHERE school_id=" . $_GET['school_id'];
	$query = mysql_query($sql);
	
	$students = '<a class="prev button"><span class="icon"></span></a>';
	$students .= '<select id="user_id" name="user_id">';
	while ($user = mysql_fetch_assoc($query)){
		$students .= '<option value="' . $user['user_id'] . '">' . $user['first'] . ' ' . $user['last'] . '</option>';
	}
	$students .= '</select>';
	$students .= '<a class="next button"><span class="icon"></span></a>';
	
	return $students;
}

function get_classes(){
	$sql = "SELECT * ";
	$sql .= "FROM classes ";
	$sql .= "WHERE school_id=" . $_GET['school_id'];
	$sql .= " AND class_era = 0 ";
	$sql .= "ORDER BY class_grade, class_sub";
	$query = mysql_query($sql);
	
	
	$classes = '<a class="prev button"><span class="icon"></span></a>';
	$classes .= '<select name="calss_id" id="class_id">';
	while ($class = mysql_fetch_assoc($query)){
		if ($class['class_sub'] != '')
			$classes .= '<option value="' . $class['class_id'] . '">' . $class['class_grade'] . '' . $class['class_sub']  .'</option>';
		else
			$classes .= '<option value="' . $class['class_id'] . '">' . $class['class_grade']  .'</option>';
	}
	$classes .= '</select>';
	$classes .= '<a class="next button"><span class="icon"></span></a>';
	
	return $classes;
	
}

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