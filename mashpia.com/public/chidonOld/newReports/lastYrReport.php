<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear() - 1;

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // add chidon schools
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$ct = new ChidonTests();

$types = $ct->getTypes();
$info = [];
$sql = "select th.*, u.first, u.last, s.school_name, c.class_grade, c.class_sub from th_chidon th 
        join users u using (user_id) 
        join schools s on s.school_id = u.school_id 
        join classes c on c.class_id = u.class_id 
        where th.year = " . $year . "
        order by s.school_name, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Last Yr Report</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            font-size: 14px;
            padding: 5px;
            border: 1px solid grey;
        }
        td:not(.type) {
            vertical-align: top;
        }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Last Yr Report</h1>
<body>
    <table>
        <tr>
            <th>School</th>
            <th>Class</th>
            <th>Student</th>
            <th>Test Type</th>
        </tr>
        <?php
        foreach ($types as $type => $value) {
            $totals[$type] = 0;
        }
        foreach ($info as $row) {
            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
            echo "<tr><td>" . $row['school_name'] . "</td><td>" . $grade . "</td><td>" . $row['first'] . ' ' . $row['last'] .
                "</td><td>" . $row['test_type'] . "</td></tr>";
            $totals[$row['test_type']]++;
        }
        ?>
    </table>
    <br /><br />
    <table>
        <caption>Totals</caption>
        <tr>
            <th>Test Type</th>
            <th>Total</th>
        </tr>
        <?php
        foreach ($types as $type => $value) {
            echo "<tr><td>" . $type . "</td><td>" . $totals[$type] . "</td></tr>";
        }
        ?>
    </table>
</body>
</html>