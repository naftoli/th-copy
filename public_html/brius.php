<?
require 'db.php';

$info = array();
$sql = "select medal_name, count(*) as total from medal_marks 
		join medals using (medal_ord) 
		where subject_id = 100 
		group by medal_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Brius Haguf Medals Totals</title>
		<meta charset="UTF-8" />
		<style>
			caption {
				width: 150px;
			}
			th, td {
				font-size: 12px;
				padding: 5px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<caption>Brius Haguf Totals</caption>
			<tr>
				<th>Medal</th>
				<th>Total</th>
			</tr>
			<?
			$total = 0;
			foreach ($info as $row) {
				$total += $row['total'];
				echo "<tr><td>" . $row['medal_name'] . "</td><td>" . $row['total'] . "</td></tr>";
			}
			echo "<tr><td>Grand Total:</td><td>" . $total . "</td></tr>";
			?>
		</table>
	</body>
</html>