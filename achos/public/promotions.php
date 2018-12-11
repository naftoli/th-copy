<?
$admin_auth = array('school'); 
require('header.php');
?>
<html>
    <head>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <style type='text/css'>
        table {
            font-size: 12px;
        }
        th, td {
            padding: 3px 10px;
            border: 1px solid black;
        }
    </style>
    <body>
<?
require_once 'class.report.php';
$r = new Report();
$dates = $r->getReportDates();

$users = array();
$sql = "
    SELECT school_name, r.rank_name, COUNT( user_id ) AS total
	FROM rank_marks rm
	JOIN ranks r
	USING ( rank_ord ) 
	JOIN users u
	USING ( user_id ) 
	JOIN schools s 
	USING ( school_id ) 
	WHERE u.user_registered >0
	AND rm.date_promoted >= " . $dates['start'] . " 
	AND rm.date_promoted <= " . $dates['end'] . " 
	AND r.rank_ord != 1 
	GROUP BY school_id, r.rank_ord 
	ORDER BY school_name     
";
$result = mysql_query($sql) or die( $sql . "<br />" . mysql_error() );
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['school_name']][$row['rank_name']] = $row['total'];
}

//get rank names
$ranks = array();
$sql = "select * from ranks where rank_ord != 1";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
	$ranks[] = $row['rank_name'];
}

echo "<table><tr><th>School</th>";
foreach( $ranks as $rank ) {
	echo "<th>" . $rank . "</th>";
}
echo "</tr>";

foreach ( $users as $school => $info ) {
	echo "<tr><td>" . $school . "</td>"; 
	foreach( $ranks as $rank ) {
		$str = "";
		if ( isset( $info[$rank] ) ) {
			$num = $info[$rank];
			if ( $num > 1 )
				$str = $num . ' ' . $rank . 's';
			else {
				$str = $num . ' ' . $rank;
			} 
		}
		echo "<td>" . $str . "</td>";
	}
	echo "</tr>";
}
echo "</table>";
?>
	</body>
</html>