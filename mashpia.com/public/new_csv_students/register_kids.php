<? 
include("../db.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	<BODY>
<?
$added = 0;
$not_added = array();

$serials = array(
772271,
772265,
772270,
772269,
772283,
772278,
772266,
772274,
772261,
7741852,
7741853,
772262,
7741851,
7741848,
7741849,
7741844,
7741850,
772275);
	
foreach ($serials as $serial) {	
	$sql = "select user_start_date from users where user_serial = $serial";
	$res = mysql_query($sql);
	$row = mysql_fetch_row($res);
	$date = $row[0];
	
	if ($date > 0 ) $need_date = false;
	else $need_date = true;
	
	if ($need_date) {
		$sql = "update users
			set user_registered = now(),
			user_registration_fee = '60.00', 
			school_type_id = 2, 
			user_start_date = 2455448  
			where user_serial = $serial";
	} else {
		$sql = "update users
			set user_registered = now(),
			user_registration_fee = '60.00', 
			school_type_id = 2 
			where user_serial = $serial";
	}
	//echo ++$added . ": " . $sql . "<br />";
		
	$query = mysql_query($sql) or die($sql . "<br />" . mysql_error());
		
	if ($query) 
			$added++;
	else {
		$not_added[] = $id;
		echo "*** ERROR->" . $sql . "<br />";
	}
	
}

echo "Edited:" . $added . "<br />";
var_dump($not_added);

?>
	</BODY>
</HTML>
