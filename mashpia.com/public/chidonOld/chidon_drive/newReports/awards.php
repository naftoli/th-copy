<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../../header.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$types = ['Certificate', 'Plaque', 'Stage Plaque', 'Medal'];

$khk = [];
$awards = [];
$sql = "select * from th_chidon tc 
        join users u using (user_id) 
        join classes c on c.class_id = u.class_id 
        where year = " . $year . " 
        and (khk_plaque = 1 
        or award_type in (" . implode(',', $types) . ") 
        order by u.school_id, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if (intval($row['khk_plaque'])) $khk[$row['school_id']][$row['gender']][] = $row;
    else $awards[$row['school_id']][$row['gender']][$row['award_type']][] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Chidon Awards</title>
    <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
            border-bottom: 1px solid grey;
        }
        .warning {
            background-color: yellow;
        }
    </style>
</head>
<>
    <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); ?>
    <h1>Chidon Awards</h1>
    <?php
    foreach ($awards as $school_id => $more) {
        foreach ($more as $gender => $info) {
            if ($gender == 'M') $gender = "Boys";
            else if ($gender == 'F') $gender = 'Girls';
            echo "<h2>" . $schools[$school_id] . ' - ' . $gender . "</h2>";
            ?>
            <table>
                <tr>
                    <th>Grade</th>
                    <th>Student</th>
                </tr>
                <?php
                foreach ($types as $type) {
                    foreach ($info[$type] as $row) {
                        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                        echo "<tr><td>" . $grade . "</td><td>" . $row['first'] . ' ' . $row['last'] . "</td></tr>";
                    }
                }
            echo "</table>";
        }
    }
    ?>
</body>
</html>