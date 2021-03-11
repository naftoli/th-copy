<?php
//ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$info = [];
$sql = "select * from chidon_user_prizes cup 
        join users u using (user_id) 
        join chidon_prizes cp using (prize_id)
        join schools s using (school_id) 
        join classes c using (class_id) 
        where s.school_id in (" . implode(',', array_keys($schools)) . ")  
        group by cup.user_id 
        order by school_name, class_grade, class_sub, last, first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['school_id']][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Prizes Report</title>
    <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body>
<?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
<h1>Prizes Report</h1>
<?php
foreach ($info as $school_id => $prizes) {
    echo "<h2>" . $schools[$school_id] . "</h2>";
    ?>
    <table>
        <tr>
            <th>Grade</th>
            <th>Student</th>
            <th>Prize Name</th>
            <th>Size</th>
            <th>Color</th>
        </tr>
    <?php
    foreach ($prizes as $prize) {
        $grade = $prize['class_grade'] . (empty($prize['class_sub']) ? '' : '-' . $prize['class_sub']);
        echo "<tr><td>" . $grade . "</td><td>" . $prize['first'] . ' ' . $prize['last'] . "</td><td>" .
            $prize['prize_name'] . "</td><td>" . $prize['size'] . "</td><td>" . $prize['color'] . "</td></tr>";
    }
}
?>
</table>
</body>
</html>