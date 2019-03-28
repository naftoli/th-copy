<?
require '../../db.php';

$sql = array();
$new_students = fopen("ot.csv", "r");
$contents = stream_get_contents($new_students);
$arrRows = preg_split("/[\n\r]+/", $contents);
foreach ($arrRows as $strLine) {
	$data =explode(",", $strLine);
	$id = $data[0];
	$fname = ucwords(strtolower($data[1]));
	$lname = ucwords(strtolower($data[2]));
	$family = ucwords(strtolower($data[3]));
	$address = ucwords(strtolower($data[4]));
	$between = $data[5];
	$phone = $data[6];
	$emerg = $data[7];	
	$sql[] = "update chidon_reg 
			set name = '" . $fname . "', 
			last_name = '" . $lname . "', 
			family = '" . $family . "',
			address = '" . $address . "', 
			between_streets = '" . $between . "', 
			phone = '" . $phone . "', 
			emergency = '" . $emerg . "'  
			where chidon_reg_id = " . $id;
}

foreach ($sql as $qry) {
	mysql_query($qry) or die($qry . "<br />" . mysql_error());
}
?>