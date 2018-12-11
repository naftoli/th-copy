<?
$grades = array();
$school = $_GET['id'];

require_once '../db.php';
$sql = "select class_id, class_grade, class_sub from classes where class_era = 0 and school_id = " . $school . " order by class_grade, class_sub";
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
    $grades[$row['class_id']] = $row['class_grade'] . '-' . $row['class_sub'];
}

echo json_encode($grades);
?>