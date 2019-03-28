<?
$admin_auth = array('school'); 
require('header.php');

$schools = array();
$sql = "select * from chidon_schools where year = 5776 order by gender, school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[$row['chidon_schools_id']] = array(
		'gender' => $row['gender'], 
		'name' => $row['school_name']);
}

$total = array();
$reg = array();
foreach ($schools as $id => $info) {
	$sql = "select * from chidon_reg where chidon_schools_id = " . $id . " order by grade";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$mark1 = $row['mark1'];
		$mark2 = $row['mark2'];
		$mark3 = $row['mark3'];
		$bonus = $row['bonus'];
		
		$avg = 0;
		if ($mark3 > 0) {
			$avg = round(($mark1 + $mark2 + $mark3 + $bonus) / 3);
		} else {
			$avg = round(($mark1 + $mark2 + $bonus) / 2);
		}
		
		if ($avg >= 65) {
			$total[$info['gender']][$row['grade']][] = $row;
			$reg[$info['gender']][$row['grade']][$info['name']][] = $row;
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Registration Report 5776</title>
        <style type='text/css'>
            body, table {
                font-size: 12px;
                font-family: 'Arial', 'Verdana';
            }
            th, td {
                padding: 3px 10px;
            }
            td {
            	vertical-align: top;
            }
        </style>
    </HEAD>

    <BODY>
        <h1>Chidon Participants Report 5776</h1>
        
        <? foreach ($total as $gender => $info) : ?>
        	<h2><?=ucfirst($gender)?></h2>
	  		<table>
	  			<tr>
	  				<th>Grade</th>
	  				<th>Total Participants</th>
	  			</tr>
	  			<?
	  			$gtotal = 0;
	  			foreach ($info as $grade => $participants) {
	  				$num = count($participants);
					$gtotal += $num;
	  				echo "<tr><td>" . $grade . "</td><td>" . $num . "</td></tr>";
	  			}
	  			echo "<tr><td><b>Total</b></td><td><b>" . $gtotal . "</b></td></tr>";
	  			?>
	  		</table>
	  	<? endforeach; ?>
	  	
	  	<hr />
	  	
	  	<? foreach ($reg as $gender => $info) : ?>
        	<h2><?=ucfirst($gender)?></h2>
	  		<table>
	  			<tr>
	  				<th>Grade</th>
	  				<th>School</th>
	  				<th>Total Participants</th>
	  			</tr>
	  			<?
	  			foreach ($info as $grade => $other) {
	  				foreach ($other as $school => $participants) {
	  					echo "<tr><td>" . $grade . "</td><td>" . $school . "</td><td>" . count($participants) . "</td></tr>";
					}
	  			}
	  			?>
	  		</table>
	  	<? endforeach; ?>
    </body>
</html>