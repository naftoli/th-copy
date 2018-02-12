<?
require_once '../db.php';
$school = mysql_real_escape_string($_GET['id']);

$sql = "select c.class_id, class_grade, class_sub from classes c 
		join users using (class_id) 
		where class_era = 0 and c.school_id = " . $school . " order by class_grade, class_sub";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $grades[$row['class_id']] = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
}

foreach ($grades as $class_id => $grade) {
	$sql = "select * from mishna_ppl 
			where school_id = $school 
			and class_id = $class_id 
			and user_id is null";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$points = $row['points'];
		$mPoints = $row['m_points'];
		$pPoints = $row['p_points'];
		$sPoints = $row['s_points'];
		$shasPoints = $row['shas_points'];
	} else {
		$sql = "select * from mishna_ppl 
			where school_id = $school 
			and class_id is null 
			and user_id is null";
		$result = mysql_query($sql);
		if (mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			$points = $row['points'];
			$mPoints = $row['m_points'];
			$pPoints = $row['p_points'];
			$sPoints = $row['s_points'];
			$shasPoints = $row['shas_points'];
		} else {
			$points = 0;
			$mPoints = 0;
			$pPoints = 0;
			$sPoints = 0;
			$shasPoints = 0;
		}
	} 
	$info[$class_id][$grade] = array('reg' => $points, 
									'perek' => $pPoints, 
									'mesechto' => $mPoints, 
									'seder'	=> $sPoints, 
									'shas' => $shasPoints);
}

echo json_encode($info);
?>