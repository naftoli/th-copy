<?
$school = $_GET['id'];
$email = isset($_GET['email']) ? $_GET['email'] : 0;
$extra = isset($_GET['extra']) ? $_GET['extra'] : false;

require_once '../db.php';
$sql = "SELECT u.user_id, u.last, u.first, u.email, c.class_grade, c.class_sub";
if($extra == 'parent_tasks') $sql .= ", u.allow_parent_tasks, u.print_parent_tasks";
$sql .=  " FROM users u"
		." JOIN classes c using (class_id)" 
		." WHERE u.school_id = $school"
		." AND u.user_registered > 0"
		." AND c.class_era = 0 "
		." ORDER BY u.last, u.first;";
//echo $sql;
$result = mysql_query($sql);

$users = [];
while ($row = mysql_fetch_assoc($result)) {
	$user = $row['first'] . ' ' . $row['last'];
	if($extra == 'parent_tasks') {
		$users[] = ["user_id" => $row['user_id'],
					"name" => $user,
					"allow_parent_tasks" => $row['allow_parent_tasks'] == 1,
					"print_parent_tasks" => $row['print_parent_tasks'] == 1];
	} else if ($email) {
		$users[$row['class_grade']][$row['class_sub']][$row['user_id']] = $user . ':' . $row['email'];
	} else {
    	$users[$row['class_grade']][$row['class_sub']][$row['user_id']] = $user;
	}
}

if($extra == 'parent_tasks'){
	echo json_encode($users);
	die();
}

$order = array('Pre-school 1','Pre-school 2','Pre-school 3','Pre1a','1','2','3','4','5','6','7','8','9','10','11','12');
$newArr = array();
foreach ($order as $val) {
	if (isset($users[$val])) {
		$newArr[$val] = $users[$val];
	}
}

echo json_encode($newArr);
?>