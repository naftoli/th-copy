<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	<style type="text/css">
		th, td {
			padding: 5px;
			font-size: 12px;
		}
	</style>
	<BODY>
	
<? 
include("../db.php");

$filter = array(
	array(87,92,220,412,204,84,147,191,221,196,209,208,211,192,203,193),
	array(218,77,222,28,167,157,165,397,351,154,79,219),
	array(323,308,69,75,140,169,78,356,357,301,163,405,403,147,326)
);

$sql = "
SELECT u.user_id, u.first, u.last, s.school_id, s.school_name, s.school_number, c.class_grade, c.class_sub, pa.prize_id, pa.prize_name, pa.prize_number, aup.quantity
FROM `auction_user_prizes` aup
JOIN users u
USING ( user_id )
JOIN classes c ON ( u.class_id = c.class_id )
JOIN schools s ON ( s.school_id = u.school_id )
JOIN prizes_auction pa
USING ( prize_id )
WHERE auction_id = 70 ";
if (isset($_GET['filter'])) {
	$index = $_GET['filter'];
	$sql .= " and aup.prize_id in (" . implode(',',$filter[$index]) . ") ";
}
$sql .= "
ORDER BY s.school_name, pa.prize_name, c.class_grade, c.class_sub, u.last, u.first";
//echo $sql;
$result = mysql_query($sql);
?>
	<table>
		<tr>
			<th>User ID</th>
			<th>Name</th>
			<th>School ID</th>
			<th>School</th>
			<th>Grade</th>
			<th>Prize ID</th>
			<th>Prize</th>
			<th>Quantity</th>
			<th>Prize Number</th>
		</tr>
		<?
		while ($row = mysql_fetch_array($result)) {
			$grade = $row['class_sub'] == '' ? $row['class_grade'] : $row['class_grade'] . "-" . $row['class_sub'];
			$name = $row['first'] . " " . $row['last'];
			echo "<tr><td>" . $row['user_id'] . "</td><td>" . $name . "</td><td>" . $row['school_id'] . "</td><td>" . 
				$row['school_name'] . "</td><td>" . $grade . "</td><td>" . $row['prize_id'] . "</td><td>" . 
				$row['prize_name'] . "</td><td>" . $row['quantity'] . "</td><td>" . $row['prize_number'] . "</td></tr>";
		}
		?>
	</table>
	</BODY>
</HTML>