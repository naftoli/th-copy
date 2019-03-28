<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
		join classes c on (c.class_id = u.class_id) 
		join prizes_auction pa using (prize_id) 
		where aw.auction_id = 37 
		and pa.prize_points >= 72 
		order by s.school_name, pa.prize_name, u.last, u.first";
$result = mysql_query( $sql );
$prizes = array();
while ( $row = mysql_fetch_assoc( $result ) ) {
	$school = $row['school_name'];
	$grade = empty( $row['class_sub'] ) ? $row['class_grade'] : $row['class_grade'] . "-" . $row['class_sub'];
	$name = $row['first'] . " " . $row['last'];
	$prizes[$school][$row['prize_name']] = $name;
}

?>
		<table>
			<tr>
				<th>School</th>
				<th>Prize</th>
				<th>Student</th>
			</tr>
			<?
			foreach( $prizes as $school => $info ) {
				foreach( $info as $prize => $name ) {
					echo "<tr><td>" . $school . "</td><td>" . $prize . "</td><td>" . $name . "</td></tr>";
				}
			}
			?>
		</table>
	</body>
</html>