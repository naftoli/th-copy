<?php
ini_set('display_errors', 1);
require 'db.php';

$info = array();
$sql = "select * from transactions where trans_date > '2016-08-17 16:21:44'";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
		<title>Detailed Registration Report</title>
		<style>
			th, td {
				padding: 5px 10px;
				font-size: 12px;
			}
		</style>
    </head>
    
    <body>
        <table>
            <tr>
                <th>Date</th>
                <th>Admin</th>
                <th>Address</th>
                <th>Users Registered</th>
                <th>Paid for Registration</th>
                <th>Paid for Shipping</th>
                <th>Total Paid</th>
            </tr>
            <?php
            foreach ($info as $row) {
                $admin = array();
                $aSql = "select * from admins where admin_id = " . $row['admin_id'];
                $aResult = mysql_query($aSql);
                $admin = mysql_fetch_assoc($aResult);
                
                $users = array();
                $uSql = "select user_id, first, last from users where user_id in (" . $row['users_registered'] . ")";
                $uResult = mysql_query($uSql);
                while ($uRow = mysql_fetch_assoc($uResult)) {
                    $users[$uRow['user_id']] = $uRow['first'] . ' ' . $uRow['last'];
                }
                echo "<tr><td>" . $row['trans_date'] . "</td><td>" . $admin['first'] . ' ' . $admin['last'] . "</td><td>" .
                    $admin['admin_address1'] . "<br />" . $admin['admin_city'] . " " . $admin['admin_state'] . " " . $admin['admin_postal'] . "<br />" . $admin['admin_country'] .
                    "</td><td>";
                foreach ($users as $user) {
                    echo $user . "<br />";
                }
                echo "</td><td>" . $row['reg_amount'] . "</td><td>" . $row['ship_amount'] . "</td><td>" . $row['amount'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>
        