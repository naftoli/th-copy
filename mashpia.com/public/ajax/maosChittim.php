<?
require_once '../db.php';

$user = isset($_POST['user']) ? $_POST['user'] : 0;
$school = isset($_POST['school']) ? $_POST['school'] : 0;
$val = isset($_POST['val']) ? $_POST['val'] : null;
$type = $_POST['type'];
$action = $_POST['action'];

if ($action == 'set') {
	if ($user) {
		$sql = "select * from maos_chitim where user_id = $user and year = 5774";
	} else if ($school) {
		$sql = "select * from maos_chitim where school_id = $school and year = 5774";
	}
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$id = $row['id'];
		$sql = "update maos_chitim set $type = $val where id = $id";
	} else {
		$sql = "insert into maos_chitim 
				set user_id = $user, 
				school_id = $school, 
				$type = $val, 
				year = 5774";
	}
	if (mysql_query($sql)) {
		echo 1;
	} else {
		echo 0;
	}
} else if ($action == 'get') {
	if ($user) {
		$sql = "select * from maos_chitim where user_id = $user and year = 5774";
	} else if ($school) {
		$sql = "select * from maos_chitim where school_id = $school and year = 5774";
	}
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		echo $row["$type"];
	} else {
		echo 0;
	}
}
?>