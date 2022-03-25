<?php
ini_set('display_errors',1);
$admin_auth = array('school');
require('../../header.php');

require_once '../../class.adminSchools.php';
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

require_once '../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once 'functions.php';

$prizes = getUserPrizes();
$marks = getMarks();
$final_marks = getFinalMarks();

$tracks = [
    1   => 'yesod',
    2   => 'yediah',
    3   => 'havonah',
    4   => 'iyun'
];

$sheets = [];
foreach ($schools as $school_id => $school) {
    foreach (['boys', 'girls'] as $gender) {
        $children = getChildren($school_id, $gender);
        $sheets[] = createSpreadSheet($children);
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

<HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chidon Video Report</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <style type='text/css'>
        table {
            font-size: 14px;
        }
        tr, th, td {
            padding: 5px 10px;
            border-bottom: 1px grey solid;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</HEAD>

<BODY>
<? include('../../admin_header.php'); ?>
<h1>Chidon Video Report</h1>
<table>
    <tr>
        <th>Chayol Name</th>
        <th>Chayol Picture</th>
        <th>Grade</th>
        <th>School Name</th>
        <th>School Locations</th>
        <th>School Logo</th>
        <th>Award</th>
        <th>Trip</th>
        <th>Prize 1</th>
        <th>Prize 2</th>
        <th>Prize 3</th>
        <th>Prize 4</th>
        <th>Prize 5</th>
        <th>Prize 6</th>
        <th>Number of Prizes</th>
    </tr>
    <?php
    foreach ($sheets as $sheet) {
        foreach ($sheet as $idx => $row) {
            if ($idx == 0) continue;
            echo "<tr>";
            for ($i = 1; $i <= 15; $i++) {
                echo "<td>";
                if ($i == 7) {
                    if ($row[$i]) echo $tracks[$row[$i]];
                    else echo '';
                } else if ($i == 8) {
                    if ($row[$i] == 1) echo 'regular';
                    else if ($row[$i] == 2) echo 'khk';
                } else {
                    echo $row[$i];
                }
                echo "</td>";
            }
            echo "</tr>";
        }
    }
    ?>
</table>
</body>
</html>