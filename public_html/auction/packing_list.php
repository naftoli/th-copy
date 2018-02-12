<html>
	<head>
		<meta charset='UTF-8' />
		<style>
			table {
				font-size: 12px;
			}
			th, td {
				border: 1px solid black;
				padding: 3px 10px;
			}
			.page-break {
				page-break-after: always;
			}
			@media print {
				.no-print {
					display: none;
				}
			}
		</style>
	</head>
	<body>
		<div align="center">
			<input type="button" value="Print" onclick="window.print()" class="no-print" />
		</div>
<?
require_once '../db.php';

$sql = "select * from auction_winners aw 
		join users u using (user_id) 
		join schools s on (u.school_id = s.school_id) 
		join prizes_auction pa using (prize_id) 
		where aw.auction_id = 37 
		order by s.school_name, pa.prize_name, u.last, u.first";
$result = mysql_query( $sql );
$prizes = array();
while ( $row = mysql_fetch_assoc( $result ) ) {
	if ( isset( $prizes[$row['school_name']][$row['prize_name']] ) ) {
		$prizes[$row['school_name']][$row['prize_name']]++;
	} else {
		$prizes[$row['school_name']][$row['prize_name']] = 1;
	}
}

foreach( $prizes as $school => $info ) {
	echo "<table>";
	echo "<tr><th colspan='2'>$school</th></tr>";
	echo "<tr><th>Prize</th><th>Qty</th></tr>"; 
	foreach( $info as $prize => $qty ) {
		echo "<tr><td>" . $prize . "</td><td>" . $qty . "</td></tr>";
	}
	echo "</table>";
	echo "<div class='page-break'></div>";
}
?>
	</body>
</html>