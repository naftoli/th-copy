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
			@media print {
				th, td {
					font-size: 12px;
				}
				.noPrint {
					display: none;
				}
			}
		</style>
	</head>
	<body>
<?
require_once '../db.php';
$auction_id = 79;
/*
$sql = "select s.school_name, u.user_id, u.first, u.last, p.prize_name, c.class_grade, c.class_sub  
        from auction_winners aw 
        join users u using (user_id) 
        join schools s on (u.school_id = s.school_id) 
        join prizes_auction p using (prize_id) 
        join classes c on c.class_id = u.class_id 
        where aw.auction_id = (
        	select auction_id from auctions where auction_ran = 1 order by auction_id desc limit 1
        )";
*/
$sql = "select s.school_name, u.user_id, u.first, u.last, p.prize_id, p.prize_name, c.class_grade, c.class_sub  
        from auction_winners aw 
        join users u using (user_id) 
        left join schools s on (u.school_id = s.school_id) 
        join prizes_auction p using (prize_id) 
        left join classes c on c.class_id = u.class_id 
        where aw.auction_id = " . $auction_id . " order by ";
$sql .= "s.school_name, c.class_sub, c.class_grade, u.last, u.first";

$result = mysql_query( $sql );
?>
<p>
	<input type="button" class='noPrint' value="Print" onclick="window.print()" />
</p>
<?
$i = 0;
$sName = '';
echo "<table>";
echo "<tr><th>&nbsp;</th><th>Prize</th><th>Name</th><th>School</th><th>Grade</th><th>Packed</th><th>Received</th></tr>";
while ( $row = mysql_fetch_assoc( $result ) ) {
	$school = $row['school_name'];
	if ($i == 0) $sName = $school;
	else if ($i && $school != $sName) {
        $i = 0;
		$sName = $school;
		echo "</table>";
		echo "<div style='page-break-after: always'></div>";
		echo "<table><tr><th>&nbsp;</th><th>Prize</th><th>Name</th><th>School</th><th>Grade</th><th>Packed</th><th>Received</th></tr>";
	}
	echo "<tr><td>" . ++$i . "</td><td>" . $row['prize_name'] . "</td><td>" . 
		$row['first'] . ' ' . $row['last'] . "</td><td>" . $row['school_name'] . "</td><td>" . 
		$row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']) . "</td><td></td><td></td></tr>";
	if ($i % 23 == 0) {
		echo "</table>";
		echo "<div style='page-break-after: always'></div>";
		echo "<table><tr><th>&nbsp;</th><th>Prize</th><th>Name</th><th>School</th><th>Grade</th><th>Packed</th><th>Received</th></tr>";
	}
}
echo "</table>";
?>
	</body>
</html>