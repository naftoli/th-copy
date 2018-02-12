<?
$admin_auth = array('school');
require_once 'header.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Medals Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <style>
        	table {
                font-size: 11px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Medals Report</h1>
        <?
        $medals = array();
		$users = array();
        $sql = "select u.user_id, u.first, u.last, s.school_name, su.subject_name, m.medal_name 
        		from medal_marks mm 
        		join users u using (user_id) 
        		join schools s using (school_id) 
        		join subjects su using (subject_id) 
        		join medals m using (medal_ord) 
				where (date_shipped > '2013-08-30' 
				or date_received > '2013-08-30') 
				order by m.medal_ord, su.subject_name, s.school_name";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$users[$row['user_id']] = $row['first'] . ' ' . $row['last'];
			$medals[$row['medal_name']][$row['subject_name']][$row['school_name']][] = $row['user_id'];
		}
		//echo "<pre>"; print_r($medals); echo "</pre>";
        ?>
        <table>
        	<tr>
        		<th>Medal</th>
        		<th>Subject</th>
        		<th>School</th>
        		<th>Total</th>
        	</tr>
        	<?
        	$totals = array();
        	foreach ($medals as $medal => $info) {
        		foreach ($info as $subject => $arr) {
        			foreach ($arr as $school => $users) {
        				$total = count($users);
        				echo "<tr><td>" . $medal . "</td><td>" . $subject . "</td><td>" . 
        					$school . "</td><td>" . $total . "</td></tr>";
        				if (!isset($totals[$medal][$subject])) {
        					$totals[$medal][$subject] = $total;
        				} else {
        					$totals[$medal][$subject] += $total;
        				}
        			}
        		}
        	}
        	?>
        </table>
        <h2>Grand Totals</h2>
        <table>
        	<tr>
        		<th>Medal</th>
        		<th>Subject</th>
        		<th>Grand Total</th>
        	</tr>
        	<?
        	foreach ($totals as $medal => $info) {
        		foreach ($info as $subject => $total) {
        			echo "<tr><td>" . $medal . "</td><td>" . $subject . "</td><td>" . $total . "</td></tr>";
        		}
        	}
        	?>
        </table>
    </body>
</html>        