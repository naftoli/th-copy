<?
require_once '../db.php';
$mesechto = mysql_real_escape_string( $_POST['mesechto'] );
$user = isset( $_POST['user'] ) ? mysql_real_escape_string( $_POST['user'] ) : 0;

require_once '../class.mishnaInfo.php';
$perokim = MishnaInfo::getPerokim( $mesechto );

if (isset($_POST['getLearned']) && $_POST['getLearned']) {
	foreach ($perokim as $perek) {
		$mishnos[$perek] = MishnaInfo::getMishnos( $mesechto, $perek );
		$learned[$perek] = MishnaInfo::getLearned( $mesechto, $perek, $user );
	}
	echo json_encode( array($mishnos, $learned) );
} else {
	foreach ($perokim as $perek) {
		$mishnos[$perek] = MishnaInfo::getMishnos( $mesechto, $perek );
	}
	echo json_encode( $mishnos );
}
?>