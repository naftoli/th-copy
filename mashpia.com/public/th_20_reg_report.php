<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html>
<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Gimmul Tammuz Shnas Ho'Esrim Rally Registration Report</title>
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
        <h1>Gimmul Tammuz Shnas Ho'Esrim Rally Registration Report</h1>
        
		<table>
			<tr>
				<th>Camp</th>
				<th>Number of Staff</th>
				<th>Number of Campers</th>
				<th>Date Registered</th>
			</tr>
		<?
		$sql = "select * from th_20_reg";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			echo "<tr><td>" . $row['camp'] . "</td><td>" . $row['number_staff'] . "</td><td>" . $row['number_campers'] . 
				"</td><td>" . $row['date'] . "</td></tr>";
		}
		?>
		</table>
    </body>
</html>