<?
require '../db.php';
require '../class.mishnaPoints.php';

$user = mysql_real_escape_string($_POST['user']);
$mesechto = mysql_real_escape_string($_POST['mesechto']);
$perek = mysql_real_escape_string($_POST['perek']);
$checked = $_POST['checked'];
$ref = $mesechto . ":" . $perek;

$qrys = array();
if ($checked) {
	$qrys[] =  "insert ignore into mishna_at_once 
				set user_id = " . $user . ", 
				mesechto_id = " . $mesechto . ", 
				perek = " . $perek;
	
	try {
		if ($perek == 0) {
			$mp = new MishnaPoints($user, 'm_points');
			$type = 'm_points';
		} else {
			$mp = new MishnaPoints($user, 'p_points');
			$type = 'p_points';
		}
		$ppl = $mp->calculatePPL();
	} catch (Exception $e) {
		//$error = $e->getMessage();
		echo 2;
		exit;
	}
	
	$points = $ppl * $lines;
	$qrys[] =  "insert into bp_points 
				set user_id = " . $user . ", 
				bp_type_id = 1, 
				points = " . $points . ", 
				type = '" . $type . "', 
				ref = '" . $ref . "'";
} else {
	$qrys[] =  "delete from mishna_at_once 
				where user_id = " . $user . " 
				and mesechto_id = " . $mesechto . " 
				and perek = " . $perek;
	
	try {
		if ($perek == 0) {
			$mp = new MishnaPoints($user, 'm_points');
			$type = 'm_points';
		} else {
			$mp = new MishnaPoints($user, 'p_points');
			$type = 'p_points';
		}
		$ppl = $mp->calculatePPL();
	} catch (Exception $e) {
		//$error = $e->getMessage();
		echo 2;
		exit;
	}
	
	$points = $ppl * $lines;
	$qrys[] =  "delete from bp_points 
				where user_id = " . $user . " 
				and bp_type_id = 1 
				and points = " . $points . " 
				and type = '" . $type . "' 
				and ref = '" . $ref . "'";
}

mysql_query("set autocommit=0");
mysql_query("begin");
foreach ($qrys as $qry) {
	if (!mysql_query($qry)) {
		mysql_query("rollback");
		mysql_query("set autocommit=1");
		echo 1;
		exit;
	}
}
mysql_query("commit");
mysql_query("set autocommit=1");
echo 0;
?>