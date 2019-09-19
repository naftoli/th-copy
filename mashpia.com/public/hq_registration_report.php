<?php
$admin_auth = ['school'];
require 'header.php';
require_once 'class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

if ( $admin_user['auth'] != 'super' ) {
    echo "No permission to access this page.";
    exit;
}

$info = [];
$sql = "select * from registration_charges 
        join users u using (user_id) 
        join classes c on u.class_id = c.class_id 
        where year = " . $year;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $info[] = $row;
    if ( isset( $totals[$row['type']] ) ) $totals[$row['type']] += $row['amount'];
    else $totals[$row['type']] = $row['amount'];
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
            caption {
				border-bottom: 1px solid black;
			}
		</style>
	</head>
	
	<body>
        <h1>Registration Report <?= $year ?></h1>
        
        <table>
            <caption>Summary</caption>
            <tr>
                <th>Type</th>
                <th>Total Amount</th>
            </tr>
            <?php
            $grandTotal = 0;
            foreach ( $totals as $type => $amount ) {
                if ( $type == 'shipping' ) continue;
                echo "<tr><td>" . $type . "</td><td>$" . number_format( $amount ) . "</td></tr>";
                $grandTotal += $amount;
            }
            echo "<tr><th>Total:</th><th>$" . number_format( $grandTotal ) . "</th></tr>";
            ?>
        </table>
        <table>
            <tr>
                <th>School</th>
                <th>Grade</th>
                <th>Student</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
            <?
            foreach ( $info as $row ) {
                if ( $row['type'] == 'shipping' ) continue;
                $grade = $row['class_grade'] . (empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub']);
                echo "<tr><td>" . $schools[$row['school_id']] . "</td><td> =" . $grade . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . 
                    $row['type'] . "</td><td>" . $row['amount'] . "</td><td>" . $row['date'] . "</td></tr>";
            }
            ?>
        </table>
	</body>
</html>