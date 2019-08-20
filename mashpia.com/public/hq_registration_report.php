<?php
require 'db.php';
require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$info = [];
$sql = "select * from registration_charges 
        join users using (user_id) 
        where year = " . $year;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
	$info[] = $row;
}

$schools = [];
$sql = "select * from schools";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $schools[$row['school_id']] = $row['school_name'];
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Registration Report</title>
		<style>
			table {
				padding-bottom: 20px;
			}
			th, td {
				padding: 5px 10px;
				font-size: 14px;
                font-family: Arial;
			}
		</style>
	</head>
	
	<body>
		<h1>Registration Report</h1>
		        
        <table>
            <tr>
                <th>School</th>
                <th>Student</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
            <?
            foreach ( $info as $row ) {
                echo "<tr><td>" . $schools[$row['school_id']] . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . $row['type'] . "</td><td>" . $row['amount'] . 
                    "</td><td>" . $row['date'] . "</td></tr>";
            }
            ?>
        </table>
	</body>
</html>