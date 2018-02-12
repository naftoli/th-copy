<?
ini_set('display_errors', TRUE);
$admin_auth = array('school'); 
require('header.php');

require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$info = array();
foreach ($schools as $id => $school) { 
	$sql = "select * from schools where school_id = " . $id . " and conf_pushka_users = 1";
	$result = mysql_query( $sql );
	if (mysql_num_rows($result) == 0) continue;
	$row = mysql_fetch_assoc( $result );
	$info[$id]['school'] = $row;

	$names = array();
	
	$sql = "select teacher_hname from classes 
			where school_id = $id 
			and class_era = 0";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		if (!empty($row['teacher_hname'])) {
			$names[] = $row['teacher_hname'];
		}
	}
	
	$sql = "select * from users 		
			where school_id = " . $id . " 
			and user_registered > 0 
			and class_id is not null";
	$result = mysql_query( $sql );
	while ($row = mysql_fetch_assoc($result)) {
		if (!empty($row['he_name'])) {
			$names[] = $row['he_name'];
		}
	}

	$info[$id]['names'] = $names;
}
//echo "<pre>"; print_r( $info ); echo "</pre>"; exit;
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Pushka Names Report</title>
		<style>
			@font-face {
			    font-family: heb;
			    src: url('fonts/Adobe Hebrew Regular.otf');
			}
			.label {
				background-color: black;
				color: white;
				text-align: center;
				width: 7.4cm;
				height: 1.3cm;
				line-height: 1.1;
				padding-top: 6px;
				page-break-inside: avoid;
			}
			.heading {
				font-family: heb;
				font-size: 15px;
			}
			.name {
				font-size: 20px;
			}
			.outer {
				padding: 5px;
				background-color: white;
				float: left;
			}
		</style>
	</head>
	
	<body>
		<?
		foreach ($info as $school => $names) {
			echo "Order #: " . $info[$school]['school']['school_number'] . "<br />";
			echo $schools[$school] . "<br />";
			$schoolInfo = $names['school'];
			$address = $schoolInfo['school_address1'] . "<br />" . 
				(empty($schoolInfo['school_address2']) ? '' : $schoolInfo['school_address2'] . "<br />");
			$address .= $schoolInfo['school_city'] . ", " . $schoolInfo['school_state'] . "  " . 
				$schoolInfo['school_postal'] . "<br />" . $schoolInfo['school_country'] . "<br />";
			$total = count($names['names']);
			if (!empty($schoolInfo['he_name_principal'])) $total++;
			if (!empty($schoolInfo['he_name_p2'])) $total++;
			echo "Total Number of Pushkas: " . $total . "<br />";
			
			if (!empty($schoolInfo['he_name_principal'])) {
				echo "<div class='outer'><div class='label'><span class='heading'>לה׳ הארץ ומלואה</span>
					<br /><span class='name'>" . $schoolInfo['he_name_principal'] . "</span></div></div>";
			}
			if (!empty($schoolInfo['he_name_p2'])) {
				echo "<div class='outer'><div class='label'><span class='heading'>לה׳ הארץ ומלואה</span>
					<br /><span class='name'>" . $schoolInfo['he_name_p2'] . "</span></div></div>";
			}
			
			foreach ($names['names'] as $name) {
				echo "<div class='outer'><div class='label'><span class='heading'>לה׳ הארץ ומלואה</span>
					<br /><span class='name'>" . $name . "</span></div></div>";
			}
			echo "<div style='clear: both; page-break-after: always'></div><br /><br />";
		}
		?>
	</body>
</html>