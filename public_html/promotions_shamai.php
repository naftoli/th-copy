<?
$admin_auth = array('school'); 
require('header.php');
$previous = false;
if (isset($_GET['previous'])) {
	$previous = true;
}
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
            padding: 5px 10px;
            border: 1px solid black;
			vertical-align: top;
			white-space: nowrap;
        }
    </style>
    <body>
<?
require_once 'class.report.php';
$r = new Report($previous);
$dates = $r->getReportDates();
$heDates = $r->getHeReportDates();

/*
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
* 
*/

$schools = array();
$sql = "select school_id, school_name from schools where school_id != 82 and shamai_ord > 0 order by shamai_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['school_id']] = $row['school_name'];
}

$users = array();
foreach ($schools as $id => $name) {
	if (in_array($id, array(61,81,110,176,269))) {
		$types = array('M','F');
		foreach ($types as $type) {
			$sql = "select r.rank_ord, u.first, u.last  
					FROM rank_marks rm
					JOIN ranks r
					USING ( rank_ord ) 
					JOIN users u
					USING ( user_id ) 
					JOIN schools s 
					USING ( school_id ) 
					WHERE u.user_registered >0 
					and u.school_id = $id 
					and u.gender = '$type' 
					AND rm.date_promoted >= " . $dates['start'] . " 
					AND rm.date_promoted <= " . $dates['end'] . " 
					AND r.rank_ord != 1 
					ORDER BY rank_ord, u.last, u.first";
			$result = mysql_query($sql);
			if (mysql_num_rows($result)) {
				while ($row = mysql_fetch_assoc($result)) {
					$users[$id][$name][$type][$row['rank_ord']][] = $row['first'] . ' ' . $row['last'];
				}
			} else {
				$users[$id][$name][$type][][] = '';
			}
		}
	} else {
		$sql = "select r.rank_ord, u.first, u.last  
				FROM rank_marks rm
				JOIN ranks r
				USING ( rank_ord ) 
				JOIN users u
				USING ( user_id ) 
				JOIN schools s 
				USING ( school_id ) 
				WHERE u.user_registered >0 
				and u.school_id = $id 
				AND rm.date_promoted >= " . $dates['start'] . " 
				AND rm.date_promoted <= " . $dates['end'] . " 
				AND r.rank_ord != 1 
				ORDER BY rank_ord, u.last, u.first";
		echo "<input type='hidden' name='sql' value='" . $sql . "' />";
		$result = mysql_query($sql);
		if (mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$users[$id][$name]['all'][$row['rank_ord']][] = $row['first'] . ' ' . $row['last'];
			}
		} else {
			$users[$id][$name]['all'][][] = '';
		}
	}
}
//echo "<pre>"; print_r($users); echo "</pre>";

$ranks = array(
    2 => 'S', 
    3 => 'SM', 
    4 => 'SL', 
    5 => 'FL', 
    6 => 'CP', 
    7 => 'M', 
    8 => 'CL', 
    9 => 'G', 
    10 => '1*G', 
    11 => '2*G', 
    12 => '3*G', 
    13 => '4*G', 
    14 => '5*G'
);

$ranksExpanded = array(
	9	=> 'General',
	10	=> 'One Star General',
	11	=> 'Two Star General',
	12	=> 'Three Star General',
	13	=> 'Four Star General',
	14	=> 'Five Star General'
);

echo "<br /><p align='left'>This report includes chayolim who were promoted from " . $heDates['start_he'] . " to " . $heDates['end_he'] . "</p>";

echo "<table><tr><th></th><th>School ID</th><th>School</th>";
foreach( $ranks as $rank ) {
	echo "<th>" . $rank . "</th>";
}
echo "<th>Rank</th><th>List of Generals</th>";
echo "</tr>";

//$schoolsSaved = array();
$i = 1;
foreach ( $users as $id => $rest ) {
    foreach ( $rest as $school => $other ) {
		//$schoolsSaved[$i] = array_search( $school, $schools );
        foreach ( $other as $type => $info ) {
			$listOfGenerals = array();
            echo "<tr><td>" . $i++ . "</td><td>" . $id . "</td><td>";
            if ($type == 'M' && $school == 'Bais Chaya Mushka IA') echo 'Oholei Menachem Postville IA';
            else echo $school;
            if ($type != 'all') {
                if ($type == 'M') echo " Boys";
                else if ($type == 'F') echo " Girls";
            }
            echo "</td>";
            foreach( $ranks as $rank => $abbr ) {
                $str = "";
                if ( isset( $info[$rank] ) ) {
                    if ($rank < 9) {
                        $num = count($info[$rank]);
                        $str = $num;
                    } else {
                        foreach ($info[$rank] as $name) {
                            $str .= $name . "<br />";
							$listOfGenerals[] = array($rank, $name);
                        }
                    }	
                }
                echo "<td>" . $str . "</td>";
            }
			echo "<td>";
			$j = 1;
			foreach ($listOfGenerals as $gen) {
				if ($j == 16) {
					break;
				}
				echo $ranksExpanded[$gen[0]] . "<br />";
				$j++;
			}
			for (; $j < 16; $j++) {
				echo "abcdefg<br />";
			}
			echo "</td><td>";
			$j = 1;
			foreach ($listOfGenerals as $gen) {
				if ($j == 16) {
					break;
				}
				echo $gen[1] . "<br />";
				$j++;
			}
			for (; $j < 16; $j++) {
				echo "abcdefg<br />";
			}
            echo "</td>";
			$num = count($listOfGenerals);
			if ($num > 15) {
				echo "<td>";
				for ($k = 15; $k < $num; $k++) {
					echo $ranksExpanded[$listOfGenerals[$k][0]] . "<br />";
				}
				echo "</td><td>";
				for ($k = 15; $k < $num; $k++) {
					echo $listOfGenerals[$k][1] . "<br />";
				}
				echo "</td>";
			}
			echo "</tr>";
        }
    }
}
echo "</table>";
//echo "<pre>"; print_r( $schoolsSaved ); echo "</pre>";
?>
	</body>
</html>