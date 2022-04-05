<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.adminSchools.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/class.globalSettings.php";

$year = GlobalSettings::getChidonYear();
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$items = [];
$sql = "select * from chidon_missing_items where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $items[$row['user_id']] = json_decode($row['items']);
}

$users = [];
$user_ids = array_keys($items);
$sql = "select * from users u 
        join schools s using (school_id) 
        join classes c on c.class_id = u.class_id 
        where u.user_id in (" . implode(',', $user_ids) . ") 
        order by s.school_id, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['user_id']] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Full Chidon Report</title>
    <link href="../../../admin_styles.css" rel="stylesheet" type="text/css">
</head>
<body>
    <?php include('../../../admin_header.php'); ?>
    <h1>Chidon Missing Items Report</h1>
    <table>
        <tr>
            <th>Serial Number</th>
            <th>School</th>
            <th>Grade</th>
            <th>Student</th>
            <th>Missing Items</th>
        </tr>
        <?php
        foreach ($users as $id => $user) {
            $details = $items[$id];
            $grade = $user['class_grade'] . ($user['class_sub'] ? '-' . $user['class_sub'] : '');
            echo "<tr><td>" . $user['user_serial'] . "</td><td>" . $schools[$user['school_id']] . "</td><td>" . $grade .
                "</td><td>" . ($user['first'] . ' ' . $user['last']) . "</td><td>";
            foreach ($items as $item) {
                echo "Description: " . $item->desc . "<br />";
                echo "Value: " . $item->value . "<br /><br />";
            }
        }
        ?>
    </table>
</body>
</html>