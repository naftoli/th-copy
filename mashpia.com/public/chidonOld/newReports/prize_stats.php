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

$prizes = [];
$sql = "SELECT 
            cp.prize_id, cp.prize_name, COUNT(*) as total
        FROM
            chidon_prizes cp
                JOIN
            chidon_user_prizes cup USING (prize_id)
        WHERE
            cp.year = $year
        GROUP BY cp.prize_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[$row['prize_id']] = $row;
}

$user_prizes = [];
$sql = "SELECT 
            cup.user_id, cup.prize_id, tc.th_chidon_id, tc.school_id
        FROM
            chidon_user_prizes cup
                JOIN
            th_chidon tc USING (user_id, year)
        WHERE
            cup.year = $year
        ORDER BY prize_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $user_prizes[$row['prize_id']][] = $row;
}

$needed = [
    1 => 50,
    2 => 60,
    3 => 65,
    4 => 70
];
$passed = [];
for ($num = 1; $num <= 4; $num++) {
    foreach ($user_prizes as $id => $more) {
        $passed[$num][$id] = 0;
        foreach ($more as $prize) {
            if (isset($marks[$prize['school_id']][$prize['th_chidon_id']][$num])) {
                $mark = $marks[$prize['school_id']][$prize['th_chidon_id']][$num]['maven'];
                if ($mark >= $needed[$num]) $passed[$num][$id]++;
            }
        }
    }
}
echo "<pre>"; print_r($passed); echo "</pre>";
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
            foreach ($prizes as $id => $prize) {
                echo "<tr><td>" . $id . "</td><td>" . $prize['prize_name'] . "</td><td>" . $prize['total'] . "</td>";
                for ($i = 1; $i <= 4; $i++) {
                    echo "<td>";
                    if (isset($passed[$num][$id])) echo $passed[$num][$id];
                    echo "</td>";
                }
                echo "</tr>";
            }
            ?>
        </table>
    </body>
</html>