<?
echo "<pre>";
print_r($_POST);
echo "</pre>";

require '../db.php';
$user = mysql_real_escape_string($_POST['user']);
$year = mysql_real_escape_string($_POST['year']);

//get school and grade
$sql = "select school_id, class_id from users where user_id = " . $user;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$school = $row['school_id'];
$grade = $row['class_id'];

$mesechtos = array();
foreach ($_POST['mesechto'] as $id => $val) {
	$mesechtos[] = mysql_real_escape_string($id);
}

$mSedorim = array();
$sql = "select mesechto_id, seder_id from mesechtos where mesechto_id in (" . implode(',', $mesechtos) . ")";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$mSedorim[$row['mesechto_id']] = $row['seder_id'];
}

//first delete what's already been there
$sql = "delete from mishna_assigned 
		where he_year = " . $year . " 
		and user_id = " . $user;
@mysql_query($sql);

foreach ($mesechtos as $mesechto) {
	$sql = "insert into mishna_assigned   
			set seder_id = " . $mSedorim[$mesechto] . ", 
			mesechto_id = " . $mesechto . ",  
			school_id = " . $school . ", 
			class_id = " . $grade . ", 
			he_year = " . $year . ",  
			user_id = " . $user;
	//echo $sql;
	@mysql_query( $sql );
}

header("Location: mbp.html?id=" . $user);
exit;
?>