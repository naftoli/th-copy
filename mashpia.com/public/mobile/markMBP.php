<?
require '../db.php';
require '../class.mishnaPoints.php';

$user = mysql_real_escape_string($_POST['user']);
$info = mysql_real_escape_string($_POST['info']);
$checked = mysql_real_escape_string($_POST['checked']);

$arrInfo = explode(':', $info);
$mesechto = $arrInfo[0];
$perek = $arrInfo[1];
$mishna = $arrInfo[2];
$lines = $arrInfo[3];

$qrys = array();
if ($checked) {
	$qrys[] =  "insert ignore into mishna_learned  
				set mesechto_id = " . $mesechto . ", 
				perek = " . $perek . ",  
				mishna = " . $mishna . ",  
				lines_learned = " . $lines . ", 
				user_id = " . $user;
	
	try {
		$mp = new MishnaPoints($user, 'points');
		$ppl = $mp->calculatePPL();
	} catch (Exception $e) {
		//$error = $e->getMessage();
		echo 2;
		exit;
	}
	
	$points = $ppl * $lines;
	$qrys[] =  "insert ignore into bp_points 
				set user_id = " . $user . ", 
				bp_type_id = 1, 
				points = " . $points . ", 
				type = 'points'";
} else {
	$qrys[] =  "delete from mishna_learned  
				where mesechto_id = " . $mesechto . "  
				and perek = " . $perek . " 
				and mishna = " . $mishna . " 
				and lines_learned = " . $lines . " 
				and user_id = " . $user;
	$qrys[] =  "delete from bp_points 
				where user_id = " . $user . " 
				and bp_type_id = 1 
				and points = " . $points . " 
				and type = 'points'";
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
