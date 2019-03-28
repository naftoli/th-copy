<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	</HEAD>
	<style>
		table {
			font-size: 10px;
			border-top: 1px solid black;
			border-right: 1px solid black;
		}
		th, td {
			border-left: 1px solid black;
			border-bottom: 1px solid black;
			text-align: center;
		}
	</style>
	<BODY>	
<? 
include("../db.php");
$prizes = array();
$sql = "select prize_id, prize_name from auction_prizes ap join prizes_auction using (prize_id) where auction_id = 37 order by prize_id";
$res = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $res ) ) {
	$prizes[$row['prize_name']] = $row['prize_id'];
}

$user_prizes = array();
foreach( $prizes as $prize ) {
	$sql = "SELECT pa.prize_name, s.school_name, COUNT( aup.user_id ) AS total
			FROM auction_user_prizes aup
			JOIN prizes_auction pa
			USING ( prize_id ) 
			JOIN users u
			USING ( user_id ) 
			JOIN schools s ON ( u.school_id = s.school_id ) 
			WHERE aup.auction_id = 37
			AND aup.prize_id = $prize 
			AND aup.user_id not in (
				SELECT user_id from auction_winners where auction_id > 32
			)
			GROUP BY s.school_id
			ORDER BY s.school_name";
	//echo $sql . "<br />";		
	$res = mysql_query( $sql );
	while ( $row = mysql_fetch_assoc( $res ) ) { 
		$user_prizes[$row['school_name']][$row['prize_name']] = $row['total'];
	}
}

echo "<pre>";
//print_r( $user_prizes );
echo "</pre>";

$totals = array();
echo "<table>";
echo "<tr><th>School</th>";
foreach( $prizes as $name => $id ) {
	echo "<th>" . $name . "</th>";
	$totals[$id] = 0;
}
echo "</tr>";
foreach( $user_prizes as $school => $info ) {
	echo "<tr><td>" . $school . "</td>"; 
	foreach( $prizes as $name => $id ) {
		echo "<td>" . (isset( $info[$name] ) ? $info[$name] : '&nbsp;') . "</td>";
		if ( isset( $info[$name] ) ) {
			$totals[$id] += $info[$name];
		}
	}
	echo "</tr>";
}
echo "<tr><td><b>Totals</b></td>";
foreach ( $prizes as $id ) {
	echo "<td><b>" . $totals[$id] . "</b></td>";
}
echo "</tr>";
echo "</table>";

echo "<pre>";
//print_r( $totals );
asort( $totals );
//print_r( $totals );
echo "</pre>";
?>
	</body>
</html>