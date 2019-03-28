<?
$school_id = $_POST['school_id'];
$year = $_POST['year'];
$newAmount = $_POST['val'];

require_once '../db.php';
$sql = "update registration_brochures 
		set brochures = " . $newAmount . " 
		where year = " . $year . " 
		and school_id = " . $school_id;
mysql_query( $sql );		
?>