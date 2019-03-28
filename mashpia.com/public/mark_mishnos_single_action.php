<?
echo "<pre>"; print_r($_POST); echo "</pre>"; exit;
$admin_auth = array('school', 'user');
require_once 'header.php';
require_once 'class.mishnaPoints.php';

$school = mysql_real_escape_string( $_POST['school'] );
$grade = mysql_real_escape_string( $_POST['grade'] );
$user = mysql_real_escape_string( $_POST['student'] );
$mesechto = mysql_real_escape_string( $_POST['mesechto'] );

$mesechtoAtOnce = false;
if (array_key_exists('mesecthoAtOnce', $_POST)) {
	$mesechtoAtOnce = true;
}

$perekAtOnce = array();
foreach ($_POST as $k => $v) {
	if (!empty($v) && strpos($k, ':') !== false) {
		//mishna info
		$arrInfo = explode(':', $k);
		$perek[] = $arrInfo[0];
		$mishna[] = $arrInfo[1];
		$lines[] = $v;
	} else if ($v == 'on' && strpos($k, '|') !== false) {
		$arrPerek = explode('|', $k);
		$perekAtOnce[] = $arrPerek[1];
	}
}

$qrys = array();
$numRows = count($mishna);
for ($i = 0; $i < $numRows; $i++) {
	//check if we are updating or adding
	$sql = "select * from mishna_learned  
			where user_id = $user 
			and mesechto_id = $mesechto 
			and perek = $perek[$i]  
			and mishna = $mishna[$i]";
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		//check if the lines learned amount changed or not
		$row = mysql_fetch_assoc($result);
		$lines_learned = $row['lines_learned'];
		if ($lines[$i] > $lines_learned) {
			$amount = $lines[$i] - $lines_learned;
		} else {
			continue;
		}
		$qrys[] =  "update mishna_learned  
					set lines_learned = $lines[$i] 
					where mesechto_id = $mesechto 
					and user_id = $user 
					and perek = $perek[$i] 
					and mishna = $mishna[$i]";
	} else {
		$qrys[] =  "insert into mishna_learned  
					set mesechto_id = $mesechto, 
					perek = $perek[$i], 
					mishna = $mishna[$i], 
					lines_learned = $lines[$i], 
					user_id = $user";
		$amount = $lines[$i];
	}
	/*
	$mp = new MishnaPoints( $user, 'points' );
	try {
		$ppl = $mp->calculatePPL();
		$points = $ppl * $amount;
		//give points to student
		$qrys[] =  "insert into bp_points 
					set user_id = " . $user . ", 
					bp_type_id = 1, 
					points = " . $points . ", 
					type = 'points'";
	} catch (Exception $e) {
		$msg = $e->getMessage();
		header("Location: mark_mishnos_single.php?msg=" . urlencode($msg));
		exit;
	}
	*/
}

foreach ($perekAtOnce as $perek) {
	//$ref = $mesechto . ":" . $perek;
	$qrys[] =  "insert ignore into mishna_at_once 
				set user_id = " . $user . ", 
				mesechto_id = " . $mesechto . ", 
				perek = " . $perek;
	/*
	$mp = new MishnaPoints( $user, 'p_points' );
	$ppl = $mp->calculatePPL();
	$qrys[] =  "insert into bp_points 
				set user_id = " . $user . ", 
				bp_type_id = 1, 
				points = " . $points . ", 
				type = 'p_points', 
				ref = '" . $ref . "'";
	*/
}
if ($mesechtoAtOnce) {
	//$ref = $mesechto . ":0";
	$qrys[] =  "insert ignore into mishna_at_once 
				set user_id = " . $user . ", 
				mesechto_id = " . $mesechto . ", 
				perek = 0";
	/*
	$mp = new MishnaPoints( $user, 'm_points' );
	$ppl = $mp->calculatePPL();
	$qrys[] =  "insert into bp_points 
				set user_id = " . $user . ", 
				bp_type_id = 1, 
				points = " . $points . ", 
				type = 'm_points', 
				ref = '" . $ref . "'";
	*/
}

//echo "<pre>"; print_r($qrys); echo "</pre>";

$success = true;
mysql_query("set autocommit=0");
mysql_query("begin");

foreach ($qrys as $qry) {
	if (!mysql_query($qry)) {
		$success = false;
		break;
	}
}

if ($success) {
	mysql_query("commit");
	mysql_query("set autocommit=1");
	
	MishnaSummary::updateSummary( $user );
	header("Location: mark_mishnos_single.php?msg=y&school=$school&grade=$grade");
	exit;
} else {
	mysql_query("rollback");
	mysql_query("set autocommit=1");
	
	$msg = "There was an error saving to database. Please try again.";
	header("Location: mark_mishnos_single.php?msg=" . urlencode($msg));
	exit;
}
?>