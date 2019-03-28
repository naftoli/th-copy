<?php
require 'db.php';

$info = array();
$sql = "select * from transactions t
        join admins a using (admin_id) 
        where t.trans_date > '2016-07-31'
        order by t.trans_date desc";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <style>
            th, td {
                padding: 5px;
                font-size: 12px;
                vertical-align: top;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>Trans Date</th>
                <th>Amount Charged</th>
                <th>Description</th>
                <th>Admin ID</th>
                <th>Parent Name</th>
                <th>Parent Address</th>
                <th>Parent Contact Info</th>
                <th>Parent Email</th>
            </tr>
            <?php
            foreach ($info as $row) {
                $amount = intval($row['amount']);
                $desc = $row['description'];
                $users = explode(',', $row['users_registered']);
                if (
                    $amount == 45 && count($users) > 1 ||
                    $amount == 90 && count($users) > 2 ||
                    $amount == 135 && count($users) > 3                    
                ) {
                    echo "<tr><td>" . $row['trans_date'] . "</td><td>" . $amount . "</td><td>" . $desc . "</td><td>" .
                    $row['admin_id'] . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td><td>" . $row['admin_address1'] . "<br />" .
                    $row['admin_city'] . ", " . $row['admin_state'] . ' ' . $row['admin_postal'] . "</td><td>" .
                    ($row['admin_phone_work'] ? $row['admin_phone_work'] . "<br />" : '') .
                    ($row['admin_phone_home'] ? $row['admin_phone_home'] . "<br />" : '') . 
                    ($row['admin_phone_mobile'] ? $row['admin_phone_mobile'] . "<br />" : '') .
                    "</td><td>" . $row['admin_email'] . "</td></tr>";
                }
            }
            ?>
        </table>
    </body>
</html>