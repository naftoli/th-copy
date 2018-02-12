<?
require 'db.php';

$columns = array();
$sql = "show columns from reservations";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	if ($row['Field'] == 'description') break;
	$columns[] = $row['Field'];
}

$res_number = 1; //Yud Aleph Nissan 5776
$reservations = array();
$sql = "select * from reservations where $res_number = 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$reservations[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			table {
				font-family: 'Arial';
			}
			th, td {
				padding: 5px;
				font-size: 12px;
			}
		</style>
	</head>
	
	<body>
		<table>
			<caption>Yud Aleph Nissan Rally Reservations 5776</caption>
			<tr>
				<?
				foreach ($columns as $column) {
					echo "<th>" . ucwords($column) . "</th>";
				}
				?>
			</tr>
			<?
			foreach ($reservations as $reservation) {
				echo "<tr>";
				foreach ($columns as $column) {
					echo "<td>" . $reservation[$column] . "</td>";
				}
				echo "</tr>";
			}
			?>
		</table>
	</body>
</html>