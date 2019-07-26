<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style>
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
		where aw.auction_id = 80   
		order by pa.prize_name, u.last, u.first";
$result = mysql_query( $sql );
$prizes = array();
while ( $row = mysql_fetch_assoc( $result ) ) {
	$school = $row['school_name'];
	$grade = empty( $row['class_sub'] ) ? $row['class_grade'] : $row['class_grade'] . "-" . $row['class_sub'];
	$name = $row['first'][0] . " " . $row['last'];
	$prizes[$row['prize_name']][$school][] = $name;
}
/*
echo "<pre>";
print_r( $prizes );
echo "</pre>";
*/
//sort array by prizes
ksort( $prizes );

foreach( $prizes as $prize => $names ) {
	echo "<b>" . strtoupper( $prize ) . "</b><br />"; 
	foreach( $names as $school => $info ) {
		echo "<b>" . $school . "</b><br />";
		foreach( $info as $name ) {
			echo $name . "<br />";
		}
	}
	echo "<br />";
}

?>
	</body>
</html>