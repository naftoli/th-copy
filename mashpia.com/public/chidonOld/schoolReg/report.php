<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

$year = GlobalSettings::getChidonYear();
$info = [];
$sql = "select s.school_name, tcs.* from schools s join th_chidon_schools tcs using (school_id) where tcs.year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['school_name']] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Shabbaton School Report</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 5px;
        }
    </style>
</head>
<body>
    <table>
        <caption>Shabbaton School Report</caption>
        <tr>
            <th>School</th>
            <th>Option</th>
            <th>Goggles To Rent</th>
            <th>Goggles To Buy</th>
        </tr>
        <?php
        foreach ($info as $school => $row) {
            echo "<tr><td>" . $school . "</td><td>Option " . $row['option'] . "</td><td>" . $row['goggles_rent'] .
                "</td><td>" . $row['goggles_buy'] . "</td></tr>";
        }
        ?>
    </table>
</body>
</html>
