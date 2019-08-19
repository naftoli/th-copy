<?
ini_set('display_errors',1);
require '../../db.php';
require 'vars.php';

$info = array();
$sql = "select * from th_chidon_staff where gender = 'boys' and year = " . $year . " order by first_name, last_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[] = $row;
}
//echo "<pre>"; print_r( $info ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			body {
				font-family: Arial, Helvetica, sans-serif;
				font-size: 14px;
			}
			caption {
				font-size: 16px;
				font-weight: bold;
				border-bottom: 1px solid grey;
			}
		</style>
	</head>
	
	<body>	
    <table>
      <tr>
				<th>Staff ID</th>
        <th>Name</th>
        <th>Username</th>
        <th>Password</th>
      </tr>
      <?php 
      foreach ( $info as $staff ) {
        echo "<tr><td>" . $staff['staff_id'] . "</td><td>" . ($staff['first_name'] . ' ' . $staff['last_name']) . "</td><td>" . $staff['username'] . "</td><td>shabbaton</td></tr>";
      }
      ?>
      </table>
	</body>
</html>