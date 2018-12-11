<?
require '../db.php';

$user = mysql_real_escape_string($_POST['user']);
$mesechtos = $_POST['mesechtos'];

$sql = "select school_id, class_id from users where user_id = " . $user;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$school = $row['school_id'];
$grade = $row['class_id'];

$qrys = array();
$qrys[] = "delete from mishna_assigned where user_id = " . $user;
foreach ($mesechtos as $m) {
	$info = mysql_real_escape_string($m);
	$arr = explode(':', $info);
	$seder = $arr[0];
	$mesechto = $arr[1];
	$qrys[] = "insert ignore into mishna_assigned 
				set user_id = " . $user . ", 
				school_id = " . $school . ", 
				class_id = " . $grade . ", 
				seder_id = " . $seder . ", 
				mesechto_id = " . $mesechto;
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