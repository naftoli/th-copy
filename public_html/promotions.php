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
$r = new Report(true);
$dates = $r->getReportDates();
$heDates = $r->getHeReportDates();

//if ($dates['start'] == 2456689) $dates['start'] = 2456650;

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
//echo $sql;
$result = mysql_query($sql) or die( $sql . "<br />" . mysql_error() );
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['school_name']][$row['rank_name']] = $row['total'];
}

echo "<pre>";
//print_r($users);
echo "</pre>";

$schools = array();
$sql = "select school_name from schools where school_id not in (82) and school_era is null order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $school = $row['school_name'];
    if (!key_exists($school, $users))
        $schools[] = $row['school_name'];
}

$ranks = array(
    'Sergeant'          =>  'S', 
    'Sergeant Major'    =>  'SM', 
    'Second Lieutenant' =>  'SL', 
    'First Lieutenant'  =>  'FL', 
    'Captain'           =>  'CP', 
    'Major'             =>  'M', 
    'Colonel'           =>  'CL', 
    'General'           =>  'G', 
    '1* General'        =>  '1*G', 
    '2* General'        =>  '2*G', 
    '3* General'        =>  '3*G', 
    '4* General'        =>  '4*G', 
    '5* General'        =>  '5*G'
);

echo "<br /><p align='left'>This report includes chayolim who were promoted from " . $heDates['start_he'] . " to " . $heDates['end_he'] . "</p>";

echo "<table><tr><th>School</th>";
foreach( $ranks as $rank ) {
	echo "<th>" . $rank . "</th>";
}
echo "</tr>";

foreach ( $users as $school => $info ) {
	echo "<tr><td>" . $school . "</td>"; 
	foreach( $ranks as $rank => $abbr ) {
		$str = "";
		if ( isset( $info[$rank] ) ) {
			$num = $info[$rank];
        	$str = $num . ' ' . $abbr;	
		}
		echo "<td>" . $str . "</td>";
	}
	echo "</tr>";
}
foreach ($schools as $school) {
    echo "<tr><td>" . $school . "</td>";
    foreach ($ranks as $rank) {
        echo "<td></td>";
    }
    echo "</tr>";
}
echo "</table>";
?>
	</body>
</html>