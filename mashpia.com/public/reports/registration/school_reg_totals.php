<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

$sql = "SELECT 
            s.school_name, COUNT(*) AS registered
        FROM
            users u
                JOIN
            schools s USING (school_id)
        WHERE
            u.user_registered > 0 
        GROUP BY school_id";
$result = mysql_query($sql);
if (!$result) {
    die('Invalid query: ' . mysql_error());
}

$total = 0;
$info = [];
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['school_name']] = $row['registered'];
    $total += $row['registered'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>School Registration Totals</title>
    <style>
      tr, th, td {
        font-family: "Arial", sans-serif;
        font-size: 14px;
        padding: 10px;
        border-bottom: 1px solid #f2f2f2;
      }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>School Registration Totals</h1>
    <table>
        <tr>
            <th>School</th>
            <th>Registered</th>
        </tr>
        <?php foreach ($info as $school => $registered) { ?>
            <tr>
                <td><?php echo $school; ?></td>
                <td><?php echo $registered; ?></td>
            </tr>
        <?php } ?>
        <tr>
            <th>Total</th>
            <th><?php echo $total; ?></th>
        </tr>
    </table>
</body>
</html>