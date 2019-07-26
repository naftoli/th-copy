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
					border: none;
					padding: 5x;
				}
			}
		</style>
	</head>
	<body>
<?
// $levels = array(
// 	array(
// 		87,
// 		92,
// 		220,
// 		412,
// 		204,
// 		84,
// 		147,
// 		191,
// 		221,
// 		196,
// 		209,
// 		208,
// 		211,
// 		192,
// 		203,
// 		193
// 	),
// 	array(
// 		218,
// 		77,
// 		222,
// 		28,
// 		167,
// 		157,
// 		165,
// 		397,
// 		351,
// 		154,
// 		79,
// 		219
// 	),
// 	array(
// 		308,
// 		323,
// 		69,
// 		78,
// 		301,
// 		75,
// 		140,
// 		169,
// 		357,
// 		356,
// 		357,
// 		301,
// 		403,
// 		405,
// 		403,
// 		147,
// 		326
// 	),
// 	array(
// 		150,
// 		199,
// 		249,
// 		206,
// 		322,
// 		214,
// 		197,
// 		332,
// 		205,
// 		259,
// 		200,
// 		155,
// 		348,
// 		404,
// 		388,
// 		215,
// 		283,
// 		321,
// 		217,
// 		253,
// 		255,
// 		285,
// 		257,
// 		306,
// 		153,
// 		198,
// 		201,
// 		346
// 	),
// 	array(
// 		420,
// 		225,
// 		400,
// 		399,
// 		414,
// 		415,
// 		416,
// 		417,
// 		402,
// 		401,
// 		350,
// 		267,
// 		406,
// 		325
// 	),
// 	array(
// 		398,
// 		273,
// 		224,
// 		243,
// 		390,
// 		66,
// 		413,
// 		367,
// 		282,
// 		262,
// 		250,
// 		298,
// 		280,
// 		268,
// 		396,
// 		240,
// 		366,
// 		244,
// 		261,
// 		236,
// 		252,
// 		289,
// 		242,
// 		286,
// 		264,
// 		276,
// 		248,
// 		288,
// 		391,
// 		260,
// 		364,
// 		331,
// 		277,
// 		235,
// 		258,
// 		239,
// 		297,
// 		265,
// 		278,
// 		362,
// 		232,
// 		251,
// 		245,
// 		234,
// 		230,
// 		302,
// 		256,
// 		254,
// 		377,
// 		340,
// 		376,
// 		293,
// 		294,
// 		342,
// 		336,
// 		319,
// 		327,
// 		317,
// 		295,
// 		303,
// 		386,
// 		373,
// 		385,
// 		339,
// 		341,
// 		344,
// 		371,
// 		343,
// 		369,
// 		304,
// 		305,
// 		315,
// 		292,
// 		318,
// 		133,
// 		320,
// 		310,
// 		307,
// 		314,
// 		337,
// 		237,
// 		290,
// 		270,
// 		274,
// 		275,
// 		238,
// 		359,
// 		361,
// 		324,
// 		15,
// 		410
// 	),
// 	array(177),
// 	array(128),
// 	array(175),
// 	array(394),
// 	array(370),
// 	array(380),
// 	array(387),
// 	array(179),
// 	array(379),
// 	array(182),
// 	array(395),
// 	array(9),
// 	array(309),
// 	array(381),
// 	array(384),
// 	array(409),
// 	array(408),
// 	array(6),
// 	array(407),
// 	array(421),
// 	array(279),
// 	array(372)
// );

require_once '../db.php';
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
        where aw.auction_id = 80 order by ";
if (isset($_GET['order']) && $_GET['order'] == 2) {
	$sql .= "p.prize_name, ";
}
$sql .= "s.school_name, c.class_sub, c.class_grade, u.last, u.first";

$result = mysql_query( $sql );
?>
<p>
	Order by: <a href="winners.php?order=1">School</a> | <a href="winners.php?order=2">Prize</a>
</p>
<?
$i = 0;
echo "<table>";
echo "<tr><th>&nbsp;</th><th>Prize ID</th><th>Prize</th><th>Student ID</th><th>Name</th><th>School</th><th>Grade</th></tr>";
while ( $row = mysql_fetch_assoc( $result ) ) {
	// find level
	$level = '';
	foreach ($levels as $k => $v) {
		$index = array_search($row['prize_id'], $levels[$k]);
		if ($index !== false) {
			$level = $k;
			break;
		}
	}
	echo "<tr><td>" . ++$i . "</td><td>" . $row['prize_id'] . "</td><td>" .
		$row['prize_name'] . "</td><td>" . $row['user_id'] . "</td><td>" . 
		$row['first'] . ' ' . $row['last'] . "</td><td>" . $row['school_name'] . "</td><td>" . 
		$row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']) . "</td></tr>";
}
echo "</table>";
?>
	</body>
</html>