<?
$grades = array();
$school = $_GET['id'];
$hasUsers = isset($_GET['hasUsers']) ? $_GET['hasUsers'] : 0;

$flat_array = isset( $_GET['flat'] ) ? $_GET['flat'] : 0;
$named_array = isset( $_GET['named'] ) ? $_GET['named'] : 0;
$extra = isset( $_GET['extra'] ) ? $_GET['extra'] : '';

require_once '../db.php';
if ($hasUsers) {
	$sql = "SELECT c.class_id, class_grade, class_sub";
	if($extra == 'parent_tasks') $sql .= ", allow_parent_tasks, print_parent_tasks";
	$sql .= " FROM classes c "
			//." JOIN users USING (class_id) " 
			." WHERE class_era = 0 AND c.school_id = " . $school . " ORDER BY class_grade, class_sub";
} else {
	$sql = "SELECT c.class_id, class_grade, class_sub";
	if($extra == 'parent_tasks') $sql .= ", allow_parent_tasks, print_parent_tasks";
	$sql .=  " FROM classes c "
			." WHERE class_era = 0 AND c.school_id = " . $school . " ORDER BY class_grade, class_sub";
}
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
	if($flat_array){ // structure is a 2d array like so: [[class_id, grade], [class_id, grade]]
		$grades[] = [$row['class_id'], $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub'])];
	} elseif($named_array) {
		$grade = ['class_id' => $row['class_id'], "class_name" => $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub'])];
		if($extra == 'parent_tasks') $grade["parent_tasks"] = ['allow' => $row['allow_parent_tasks'] == '1', 'print' => $row['print_parent_tasks'] == '1'];
		$grades[] = $grade;
	} else { // structure is a js object with each class_id pointing to the respective grade e.g. grades.1 = "test"
		$grades[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
	}
}
// make sure grades are ordered properly
if(!$named_array) asort( $grades );

echo json_encode($grades);
?>