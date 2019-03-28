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

$sql = "
SELECT pa.prize_id, pa.prize_name, pa.prize_number 
from auction_prizes ap
join prizes_auction pa using (prize_id) 
WHERE ap.auction_id = 70 
ORDER BY pa.prize_name";
$result = mysql_query($sql);
?>
	<table>
		<tr>
			<th>Prize ID</th>
			<th>Prize</th>
			<th>Quantity</th>
			<th>Prize Number</th>
		</tr>
		<?
		while ($row = mysql_fetch_array($result)) {
			echo "<tr><td>" . $row['prize_id'] . "</td><td>" . $row['prize_name'] . "</td><td>" . $row['quantity'] .
                "</td><td>" . $row['prize_number'] . "</td></tr>";
		}
		?>
	</table>
	</BODY>
</HTML>