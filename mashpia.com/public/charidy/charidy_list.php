<?php
require '../db.php';
$year = 5776;
$info = array();
$sql = "select * from charidy where year = " . $year . " order by email";
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
            tr, th, td {
                padding: 5px;
                font-family: sans-serif;
                font-size: 12px;
            }
        </style>
    </head>
    <body>
        <table>
            <tr>
                <th>Charidy ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Address</th>
                <th>Donation</th>
                <th>With Matching Donation</th>
            </tr>
            <?php
            foreach ($info as $row) {
                echo "<tr><td>" . $row['charidy_id'] . "</td><td>" . $row['name'] . "</td><td>" . $row['email'] . "</td><td>" .
                    $row['address'] . "<br />" . $row['city'] . ", " . $row['state'] . " " . $row['zip'] . "</td><td>" .
                    $row['donation'] . "</td><td>" . $row['with_matching'] . "</td></tr>";
            }
            ?>
        </table>
    </body>
</html>