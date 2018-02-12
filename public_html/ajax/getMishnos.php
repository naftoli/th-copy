<?
require_once '../db.php';
$school = mysql_real_escape_string( $_POST['school'] );
$grade = mysql_real_escape_string( $_POST['grade'] );
$user = isset( $_POST['user'] ) && $_POST['user'] > 0 ? mysql_real_escape_string( $_POST['user'] ) : 0;

$info = array();
require_once '../class.mishnaInfo.php';

if ($_POST['byList'] === '1') {
	$mesechtos = MishnaInfo::getMesechtosByList();
	$assigned = MishnaInfo::getAssignedAll( $school, $grade, $user );
	foreach ($mesechtos as $id => $mesechto) {
		if (in_array($id, $assigned)) { 
			$info[$id][$mesechto] = 1;
		} else {
			$info[$id][$mesechto] = 0;
		}
	}
} else {
	$sedorim = MishnaInfo::getSedorim();
	foreach ($sedorim as $id => $seder) {
		$mesechtos[$seder] = MishnaInfo::getMesechtos( $id );
		$assigned[$seder] = MishnaInfo::getAssigned( $id, $school, $grade, $user );
	}
	foreach ($mesechtos as $seder => $other) {
		foreach ($other as $id => $mesechto) {
			if (in_array($id, $assigned[$seder])) { 
				$info[$seder][$id][$mesechto] = 1;
			} else {
				$info[$seder][$id][$mesechto] = 0;
			}
		}
	}
}
echo json_encode($info);
?>