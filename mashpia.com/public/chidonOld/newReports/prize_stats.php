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

if (isset($_POST['submit'])) {
    $marks = [];
    foreach ($schools as $id => $name) {
        $t->setStudents($id);
        $t->setScores();
        $t->calculateMarks();
        $marks[$id] = $t->getMarks();
    }

    $prizes = [];
    $sql = "SELECT 
                cp.prize_id, cp.prize_name, cp.size, cp.color, COUNT(*) as total
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
                cup.user_id, cup.prize_id, tc.th_chidon_id, tc.school_id, tc.date_paid 
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

    $needed = intval($_POST['needed']);
    $testNum = intval($_POST['test_num']);
    $passed = [];
    $passedAndRegistered = [];
    foreach ($user_prizes as $id => $more) {
        $passed[$testNum][$id] = 0;
        $passedAndRegistered[$testNum][$id] = 0;
        foreach ($more as $prize) {
            if (isset($marks[$prize['school_id']][$prize['th_chidon_id']][$testNum])) {
                $mark = $marks[$prize['school_id']][$prize['th_chidon_id']][$testNum]['pro'];
                if ($mark >= $needed) {
                    $passed[$testNum][$id]++;
                    if ($prize['date_paid'] > 0) $passedAndRegistered[$testNum][$id]++;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Prize Numbers</title>
        <style>
            th, td {
                border-bottom: 1px solid grey;
                font-size: 12px;
                padding: 10px;
                font-family: Arial, Helvetica, sans-serif;
            }
        </style>
    </head>
    <body>
    <?php if (isset($_POST['submit'])) : ?>
        <h1>Prize Numbers</h1>
        <table>
            <tr>
                <th>Prize ID</th>
                <th>Prize</th>
                <th>Color</th>
                <th>Size</th>
                <th>Number of prizes chosen at Registration</th>
                <th><?= $needed ?>%+ after Test# <?= $testNum ?></th>
                <th><?= $needed ?>%+ after Test# <?= $testNum ?> and Registered for Experience</th>
            </tr>
            <?php
            foreach ($prizes as $id => $prize) {
                echo "<tr><td>" . $id . "</td><td>" . $prize['prize_name'] . "</td><td>" . $prize['color'] . "</td><td>" .
                    $prize['size'] . "</td><td>" . $prize['total'] . "</td><td>";
                if (isset($passed[$testNum][$id])) echo $passed[$testNum][$id];
                echo "</td><td>";
                if (isset($passedAndRegistered[$testNum][$id])) echo $passedAndRegistered[$testNum][$id];
                echo "</td></tr>";
            }
            ?>
        </table>
    <?php else: ?>
        <form action="prize_stats.php" method="post">
            <p>Please enter the following in order to generate the report:</p>
            <p style="line-height: 1.6">
                Test Number:
                <select name="test_num">
                    <?php
                    for ($i = 1; $i <= 4; $i++) {
                        echo "<option value='" . $i . "'>" . $i . "</option>";
                    }
                    ?>
                </select><br />
                Percentage Needed: <input type="number" name="needed" style="width: 50px;" /><br /><br />
                <input type="submit" name="submit" />
            </p>
        </form>
    <?php endif; ?>
    </body>
</html>