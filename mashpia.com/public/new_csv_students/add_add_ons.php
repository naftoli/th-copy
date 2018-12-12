<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	
	<BODY>
	
<? 
include("../db.php");

$new_students = fopen("BCM LA.csv", "r");

$row_num = 0;
$added = 0;

$contents = stream_get_contents($new_students);
$arrRows = preg_split("/[\n\r]+/", $contents);
foreach ($arrRows as $strLine) {
	$data =explode(",", $strLine);
	$row_num++;
	if ($row_num > 0) {	
		$size = $data[0];
		$serial = $data[1];
		
		$sql = "update users set shirt_size = '$size', add_on_one = 1 where user_serial = $serial";		
		
		//echo ++$added . ": " . $sql . "<br />";
			
		$query = mysql_query($sql);
			
		if ($query) 
				$added++;
		else {
			$not_added[] = $serial;
		}
		
	}
}

echo "Edited:" . $added . "<br />";
var_dump($not_added);

?>
	</BODY>
</HTML>
