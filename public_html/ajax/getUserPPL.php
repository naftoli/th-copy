<?
require_once '../db.php';
$users = array();
$school = mysql_real_escape_string($_GET['id']);

$sql = "select u.user_id, u.last, u.first, u.email, c.class_id, c.class_grade, c.class_sub  
		from users u 
		join classes c using (class_id) 
		where u.school_id = $school 
		and u.user_registered > 0 
		and c.class_era = 0  
		order by u.last, u.first";
//echo $sql;
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result)) {
	$user = $row['first'] . ' ' . $row['last'];
    $users[$row['class_grade']][$row['class_sub']][$row['class_id']][$row['user_id']] = $user;
}

$order = array('Pre-school 1','Pre-school 2','Pre-school 3','Pre1a','1','2','3','4','5','6','7','8','9','10','11','12');
$newArr = array();
foreach ($order as $val) {
	if (isset($users[$val])) {
		$newArr[$val] = $users[$val];
	}
}

$userPPL = array();
foreach ($newArr as $grade => $info) {
	foreach ($info as $sub => $other) {
		foreach ($other as $class_id => $users) {
			foreach ($users as $user_id => $user) {
				$sql = "select * from mishna_ppl 
						where school_id = $school 
						and class_id = $class_id 
						and user_id = $user_id";
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
				}
				$class = $grade . (empty($sub) ? '' : '-' . $sub);
				$userPPL[$class_id][$class][$user_id][$user] = array('reg' => $points, 
																	'perek' => $pPoints, 
																	'mesechto' => $mPoints, 
																	'seder' => $sPoints, 
																	'shas' => $shasPoints);
			}
		}
	}
}
//echo "<pre>"; print_r($info); echo "</pre>"; exit;
echo json_encode($userPPL);
?>