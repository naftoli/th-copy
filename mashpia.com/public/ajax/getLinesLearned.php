<?
require_once '../db.php';

$school_id = $_POST['id'];
$class_id = isset($_POST['grade']) ? $_POST['grade'] : 0;
$user_id = isset($_POST['user']) ? $_POST['user'] : 0;
$campaign = $_POST['campaign'];
$type = $_POST['type'];
$lines = isset($_POST['lines']) && !empty($_POST['lines']) ? (int)$_POST['lines'] : 0;

if ($type == 'update') {
	$sql = select();
	//echo $sql;
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		update();
	} else {
		insert();
	}
} else if ($type == 'select') {
	$sql = select();
	//echo $sql;
	if ($result = mysql_query($sql)) {
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			echo $row['lines_learned'];
		} else {
			echo 0;
		}
	} else {
		echo 0;
	}
}
	
function update() {
	global $school_id, $user_id, $campaign, $lines;
	if ($lines == 0) {
		$sql = "delete from lines_learned where school_id = $school_id and user_id = $user_id and campaign_id = $campaign";
	} else {
		$sql = "update lines_learned set lines_learned = $lines where school_id = $school_id and user_id = $user_id and campaign_id = $campaign";
	}
	if (mysql_query($sql)) {
		echo 1;
	} else {
		echo 0;
	}
}
function select() {
	global $school_id, $user_id, $campaign, $lines;
	$sql = "select * from lines_learned where school_id = $school_id and user_id = $user_id and campaign_id = $campaign"; 
	return $sql;
}
function insert() {
	global $school_id, $user_id, $campaign, $lines;
	$sql = "insert into lines_learned values(null, $campaign, $school_id, $lines, $user_id)";
	//echo $sql;
	if (mysql_query($sql)) {
		echo 1;
	} else {
		echo 0;
	}
}
?>