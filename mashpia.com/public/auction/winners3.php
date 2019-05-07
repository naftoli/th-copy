<html>
	<head>
		<meta charset='UTF-8' />
		<style>
			table {
				font-size: 12px;
				font-family: Arial;
			}
			th, td {
				border: 1px solid black;
				padding: 3px 10px;
			}
			@media print {
				th, td {
					border: none;
					padding: 5x;
				}
			}
		</style>
	</head>
	<body>
<?
require_once '../db.php';
$sqlAuction = "select auction_id from auctions where auction_ran = 0 order by auction_id desc limit 1";
$resultAuction = mysql_query( $sqlAuction );
$rowAuction = mysql_fetch_assoc( $resultAuction );
$auction_id = $rowAuction['auction_id'];
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
        where aw.auction_id = " . $auction_id . " 
		and u.school_id != 82 
		order by ";
if (isset($_GET['order']) && $_GET['order'] == 2) {
	$sql .= "p.prize_name, ";
}
$sql .= "s.school_name, c.class_sub, c.class_grade, u.last, u.first";

$result = mysql_query( $sql );
?>
<p>
	Order by: <a href="winners3.php?order=1">School</a> | <a href="winners3.php?order=2">Prize</a>
</p>
<?
$i = 0;
echo "<table>";
echo "<tr><th>&nbsp;</th><th>Prize ID</th><th>User ID</th><th>Prize</th><th>Name</th><th>School</th><th>Grade</th></tr>";
while ( $row = mysql_fetch_assoc( $result ) ) {
	// find level
	echo "<tr><td>" . ++$i . "</td><td>" . $row['prize_id'] . "</td><td>" . $row['user_id'] . "</td><td>" .
		$row['prize_name'] . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . $row['school_name'] . "</td><td>" . 
		$row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']) . "</td></tr>";
}
echo "</table>";
?>
	</body>
</html>