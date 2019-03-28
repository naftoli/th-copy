<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	<BODY>
	
<? 
include("../db.php");

$new_students = fopen("users.csv", "r");

$row_num = 0;
$school_id = 54;
//$child_type_id = 1;	
$added = 0;

$contents = stream_get_contents($new_students);
$arrRows = preg_split("/[\n\r]+/", $contents);
foreach ($arrRows as $strLine) {
	$data =explode(",", $strLine);
	$row_num++;
	if ($row_num > 0) {	
		$serial = $data[0];
		if ($serial == '') break;
		/*
		$sql = "select user_start_date from users where user_serial = $serial and user_start_date > 0";
		$res = mysql_query($sql);
		$num = mysql_num_rows($res);
		
		if ($num > 0) $need_date = false;
		else $need_date = true;
		
		if ($need_date) {
			$sql = "update users
				set user_registered = now(),
				user_registration_fee = '50.00', 
				add_on_one = 1, 
				shirt_size = '$size', 
				user_start_date = 2455448  
				where user_serial = $serial";
		} else {
			$sql = "update users
				set user_registered = now(),
				user_registration_fee = '50.00', 
				school_type_id = $school_type_id, 
				add_on_one = 1, 
				shirt_size = '$size' 
				where user_serial = $serial";
		}
		*/
		$sql = "update users 
				set add_on_two = 0, 
				user_start_date = 2455448 
				where user_serial = $serial";
		//echo ++$added . ": " . $sql . "<br />";
			
		$query = mysql_query($sql);	
		if ($query) 
				$added++;
		else {
			$not_added[] = $id;
		}
		
	}
}

echo "Edited:" . $added . "<br />";
var_dump($not_added);

?>
	</BODY>
</HTML>
