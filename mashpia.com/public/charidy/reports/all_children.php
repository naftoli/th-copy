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

$totals = [];

$users = [];
$sql = "select u.user_id, u.user_serial, u.non_th_school, u.non_th_city, u.non_th_state, u.first, u.last, aa.admin_id, a.admin_email, s.* 
        from users u 
        join schools s using (school_id) 
        join admin_auths aa on aa.id = u.user_id 
        join admins a using (admin_id) 
        where u.user_registered > 0";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['user_id']] = $row;
    if (isset($totals[$row['school_name']]['registered'])) $totals[$row['school_name']]['registered']++;
    else $totals[$row['school_name']]['registered'] = 1;
}

$sql = "select u.user_id, u.user_serial, u.non_th_school, u.non_th_city, u.non_th_state, u.first, u.last, tc.parent_id, a.admin_email, s.* 
        from users u 
        join schools s using (school_id) 
        join th_chidon tc using (user_id) 
        join admin_auths aa on aa.admin_id = tc.parent_id 
        join admins a using (admin_id) 
        where tc.year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['user_id']] = $row;
    if (in_array($row['school_id'], [61, 269])) {
        if (isset($totals[$row['non_th_school']]['chidon'])) $totals[$row['non_th_school']]['chidon']++;
        else $totals[$row['non_th_school']]['chidon'] = 1;
    } else {
        if (isset($totals[$row['school_name']]['chidon'])) $totals[$row['school_name']]['chidon']++;
        else $totals[$row['school_name']]['chidon'] = 1;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>All Children / School Report</title>
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
            <th>User ID</th>
            <th>User Serial</th>
            <th>Parent ID</th>
            <th>Parent Email</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>School Name</th>
            <th>City</th>
            <th>State</th>
            <th>Country</th>
        </tr>
        <?php
        foreach ($users as $user_id => $user) {
            $parent = isset($user['admin_id']) ? $user['admin_id'] : $user['parent_id'];
            echo "<tr><td>" . $user_id . "</td><td>" . $user['user_serial'] . "</td><td>" . $parent . "</td><td>" .
                $user['admin_email'] . "</td><td>" . $user['first'] . "</td><td>" . $user['last'] . "</td><td>";
            if (in_array($user['school_id'], [61, 269])) {
                echo $user['non_th_school'] . "</td><td>" . $user['non_th_city'] . "</td><td>" . $user['non_th_state'] .
                    "</td><td></td></tr>";
            } else {
                echo $user['school_name'] . "</td><td>" . $user['school_city'] . "</td><td>" . $user['school_state'] .
                    "</td><td>" . $user['school_country'] . "</td></tr>";
            }
        }
        ?>
    </table>
    <br /><br />
    <p>Totals</p>
    <table>
        <tr>
            <th>School Name</th>
            <th>Number of registered Chayolim</th>
            <th>Number of chayolim signed up to Chidon</th>
        </tr>
        <?php
        foreach ($totals as $school => $more) {
            $registered = isset($more['registered']) ? $more['registered'] : 0;
            $chidon = isset($more['chidon']) ? $more['chidon'] : 0;
            echo "<tr><td>" . $school . "</td><td>" . $registered . "</td><td>" . $chidon . "</td></tr>";
        }
        ?>
    </table>
</body>
</html>