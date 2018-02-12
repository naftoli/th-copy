<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html>
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Registration Report</title>
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            td {
            	vertical-align: top;
            	text-align: center;
            }
            .newPage {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <h1>Chidon Registration Report</h1>
        
		<table>
			<tr>
				<th>School</th>
				<th>Location</th>
				<th>Grade(s)</th>
				<th>Date Registered</th>
			</tr>
		<?
		$sql = "select * from chidon_new";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			echo "<tr><td>" . $row['school'] . "</td><td>" . $row['location'] . "</td><td>" . $row['grades'] . 
				"</td><td>" . $row['date'] . "</td></tr>";
		}
		?>
		</table>
    </body>
</html>