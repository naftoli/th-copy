<?php
ini_set('display_errors', 1);
ini_set('error_display', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$t = new ChidonTests();

$marks = [];
foreach ($schools as $id => $name) {
    $t->setStudents($id);
    $t->setScores();
    $t->calculateMarks();
    $marks[$id] = $t->getMarks();
}
echo "<pre>"; print_r($marks); echo "</pre>"; exit;

$prizes = [];
$prizeInfo = [];
$sql = "SELECT 
            cp.prize_id,
            cp.prize_name,
            cup.user_id,
            cup.he_name,
            tc.th_chidon_id, 
            tc.school_id
        FROM
            chidon_prizes cp
                JOIN
            chidon_user_prizes cup USING (prize_id)
                JOIN
            th_chidon tc USING (user_id)
        WHERE
            cp.year = $year
        ORDER BY cp.prize_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[$row['prize_id']][] = $row;
    $prizeInfo[$row['prize_id']] = $row['prize_name'];
}

$info = [];
foreach ($prizes as $id => $more) {
    foreach ($more as $prize) {
        $mark = $marks[$prize['school_id']][$prize['th_chidon_id']];
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Prize Numbers</title>
        <style>
            tr, th, td {
                border: 1px solid grey;
                font-size: 12px;
                padding: 5px;
                font-family: Arial, Helvetica, sans-serif;
            }
        </style>
    </head>
    <body>
        <h1>Prize Numbers</h1>
        <table>
            <tr>
                <th>Prize ID</th>
                <th>Prize</th>
                <th>Number of prizes chosen at Registration</th>
                <th>50%+ after Test #1</th>
                <th>60%+ after Test #2</th>
                <th>65%+ after Test #3</th>
                <th>70%+ after Test #4</th>
            </tr>
            <?php

            ?>
        </table>
    </body>
</html>
