<?php
ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonRegYear();

$info = [];
$sql = "
    SELECT 
        u.school_id, school_name, first, last, user_serial, amount, date, tc.book 
    FROM
        registration_charges rc
            JOIN
        users u USING (user_id)
            JOIN
        schools s ON s.school_id = u.school_id 
            JOIN 
        th_chidon tc USING (user_id) 
    WHERE
        year = 5782 AND type = 'yahadus'
            AND u.school_id IN (" . implode(',', array_keys($schools)) . ")
    ORDER BY school_name , last , first
    ";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Yahadus Book Purchases</title>
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
                padding: 10px;
                border-bottom: 1px solid grey;
            }
        </style>
    </head>
    <body>
    <h1>Yahadus Book Purchases <?= $year ?></h1>
    <table>
        <tr>
            <th>Serial Number</th>
            <th>School</th>
            <th>Student</th>
            <th>Book Number</th>
            <th>Amount Paid</th>
            <th>Date Purchased</th>
        </tr>
        <?php
        foreach ($info as $row) {
            echo "<tr><td>" . $row['user_serial'] . "</td><td>" . $row['school_name'] . "</td><td>" .
                $row['first'] . ' ' . $row['last'] . "</td><td>" . $row['book'] . "</td><td>" . $row['amount'] .
                "</td><td>" . $row['date'] . "</td></tr>";
        }
        ?>
    </table>
    </body>
</html>

