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
$school_id = isset( $_GET['school_id'] ) ? $_GET['school_id'] : 0;

$sql = "select * from auction_winners aw 
		join users u using (user_id) 
		join schools s on (u.school_id = s.school_id) 
		join classes c on (c.class_id = u.class_id) 
		join prizes_auction pa using (prize_id) 
		where aw.auction_id = 37 ";
if ( $school_id > 0 ) {
	if ( $school_id != 19 ) {
		$sql .= "and u.school_id = $school_id ";
	} else {
		$sql .= "and u.school_id in (19, 42) ";
	}
}
	
$sql .= "order by s.school_name, pa.prize_name, u.last, u.first";
$result = mysql_query( $sql );
$prizes = array();
while ( $row = mysql_fetch_assoc( $result ) ) {
	$school = $row['school_name'];
	$grade = empty( $row['class_sub'] ) ? $row['class_grade'] : $row['class_grade'] . "-" . $row['class_sub'];
	$name = $row['first'] . " " . $row['last'] . ":" . $row['user_id'];
	$prizes[$school][$grade][$name] = $row['prize_name'];
}
/*
echo "<pre>";
print_r( $prizes );
echo "</pre>";
*/
//sort array by class
foreach( $prizes as $school => $info ) {
	ksort( $prizes[$school] );
}

foreach( $prizes as $school => $info ) {
	echo "<table>";
	echo "<tr><th colspan='4'>$school</th></tr>";
	echo "<tr><th>Grade</th><th>Name</th><th>User ID</th><th>Prize</th></tr>"; 
	foreach( $info as $grade => $students ) { 
		foreach( $students as $name => $prize ) {
			$userInfo = explode(':', $name);
			$name = $userInfo[0];
			$id = $userInfo[1];
			echo "<tr><td>" . $grade . "</td><td>" . $name . "</td><td>" . $id . "</td><td>" . $prize . "</td></tr>";
 		}
	}
	echo "</table>";
	echo "<div class='page-break'></div>";
}
?>
	</body>
</html>