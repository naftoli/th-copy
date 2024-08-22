<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    echo 'You are not authorized to view this page.';
    exit;
}

// get all platoons from all schools
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

// get current year
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$platoons = [];
$stmt = $MASHPIA_DB->prepare("SELECT * FROM classes WHERE school_id = :school_id AND class_era = 0 ORDER BY school_id, class_grade, class_sub");
foreach ($schools as $school_id => $school_name) {
    $stmt->execute(['school_id' => $school_id]);
    $classes = $stmt->fetchAll();
    foreach ($classes as $class) {
        $platoons[$school_id][] = $class;
    }
}
?>
<DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Platoon Report</title>
    <style>
        th, td {
            border-bottom: 1px solid black;
            padding: 10px;
            font-family: "Arial", sans-serif;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>Platoon Report</h1>
    <table>
        <tr>
            <th>School</th>
            <th>Grade</th>
            <th>Sub</th>
            <th>Updated for <?= $year ?></th>
        </tr>
        <?php
        foreach ($platoons as $school_id => $grades) {
            foreach ($grades as $grade) {
                echo '<tr>';
                echo '<td>' . $schools[$school_id] . '</td>';
                echo '<td>' . $grade['class_grade'] . '</td>';
                echo '<td>' . $grade['class_sub'] . '</td>';
                echo '<td>' . (intval($grade['updated']) ? 'Yes' : 'No') . '</td>';
                echo '</tr>';
            }

        }
        ?>
    </table>
</body>
</html>