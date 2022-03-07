<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo 'No Permission.';
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$users = [];
$sql = "select * from th_chidon tc 
        join users u using (user_id) 
        where tc.date_paid > 0 
        and u.school_id in (61, 269) 
        and tc.year = " . $year . " 
        order by non_th_state, non_th_city, non_th_school";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Chidon Children / School Report</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 8px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th>Parent ID</th>
            <th>User ID</th>
            <th>Serial Number</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Base</th>
            <th>School</th>
            <th>City</th>
            <th>State</th>
        </tr>
        <?php
        foreach ($users as $user) {
            echo "<tr><td>" . $user['parent_id'] . "</td><td>" . $user['user_id'] . "</td><td>" . $user['user_serial'] .
                "</td><td>" . $user['first'] . "</td><td>" . $user['last'] . "</td><td>";
            if ($user['school_id'] == 61) echo "MyShliach";
            else if ($user['school_id'] == 269) echo "Anash Kinder";
            echo "</td><td>" . $user['non_th_school'] . "</td><td>" . $user['non_th_city'] . "</td><td>" .
                $user['non_th_state'] . "</td></tr>";
        }
        ?>
    </table>
</body>
<body>