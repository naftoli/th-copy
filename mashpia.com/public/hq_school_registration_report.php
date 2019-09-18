<?php
$admin_auth = ['school'];
require 'header.php';
require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

if ( $admin_user['auth'] != 'super' ) {
    echo "No permission to access this page.";
    exit;
}

$total = 0;
$info = [];
$sql = "select * from school_registrations sr 
        join schools s using (school_id) 
        join admins a using (admin_id) 
        where year = " . $year . " 
        and test_school = 0 
        order by school_name";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
    $total += $row['amount_paid'];
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>School Registration Report</title>
		<style>
			table {
				padding-bottom: 20px;
			}
			th, td {
				padding: 5px 10px;
				font-size: 14px;
                font-family: Arial;
			}
            caption {
				border-bottom: 1px solid black;
			}
		</style>
	</head>
	
	<body>
        <h1>School Registration Report <?= $year ?></h1>
        
        <p>Total Amount Paid: <?= number_format( $total ) ?></p>
        <table>
            <tr>
                <th>School</th>
                <th>Registered By</th>
                <th>Amount Paid</th>
                <th>Date Paid</th>
                <th>Registration Fee</th>
                <th>Prev Balance</th>
            </tr>
            <?
            foreach ( $info as $row ) {
                echo "<tr><td>" . $row['school_name'] . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . $row['amount_paid'] . "</td><td>" . 
                    $row['date_paid'] . "</td><td>" . $row['fee'] . "</td><td>" . $row['balance'] . "</td></tr>";
            }
            ?>
        </table>
	</body>
</html>