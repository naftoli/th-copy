<?php
//ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonRegYear();

$info = [];
$sql = "
    SELECT 
        u.user_id, 
        u.school_id,
        first,
        last,
        gender,
        user_serial,
        reg_date,
        tc.book,
        c.class_grade,
        c.class_sub
    FROM
        th_chidon tc  
            JOIN
        users u USING (user_id)
            JOIN
        classes c ON c.class_id = u.class_id
    WHERE
        tc.year = $year 
            AND u.school_id IN (" . implode(',', array_keys($schools)) . ")
    ORDER BY school_name , class_grade , class_sub , last , first
    ";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['gender']][$schools[$row['school_id']]][] = $row;
}
// sort schools alphabetically
foreach ($info as $gender => $other) {
    ksort($info[$gender]);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf8" />
        <title>Study Guide / Yahadus Book Shipping Report</title>
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
                padding: 10px;
                border-bottom: 1px solid grey;
            }
        </style>
    </head>
    <body>
    <h1>Study Guide / Yahadus Book Shipping Report <?= $year ?></h1>
    <?php
    foreach ($info as $gender => $other) :
        if ($gender == 'M') $gender = 'Boys';
        else if ($gender == 'F') $gender = 'Girls';
        echo "<h2>" . $gender . "</h2>";
        ?>
        <table>
            <tr>
                <th>Serial Number</th>
                <th>School</th>
                <th>Grade / Class</th>
                <th>Student</th>
                <th>Study Guide Number</th>
                <th>Book Number</th>
                <th>Registered</th>
            </tr>
            <?php
            $schoolTotals = [];
            $schoolBookTotals = [];
            for ($i = 1; $i <= 5; $i++) {
                $totals[$i] = 0;
                $bookTotals[$i] = 0;
            }
            foreach ($other as $school => $more) {
                foreach ($more as $row) {
                    // check if child bought book
                    $sql = "select * from registration_charges where type = 'yahadus' and year = " . $year . " and user_id = " . $row['user_id'];
                    $result = mysql_query($sql);
                    $bookPurchased = false;
                    if (mysql_num_rows($result) > 0) $bookPurchased = true;

                    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
                    echo "<tr><td>" . $row['user_serial'] . "</td><td>" . $school . "</td><td>" . $grade . "</td><td>" .
                        $row['first'] . ' ' . $row['last'] . "</td><td>" . $row['book'] . "</td><td>";
                    if ($bookPurchased) echo $row['book'];
                    echo "</td><td>" . $row['reg_date'] . "</td></tr>";

                    $totals[$row['book']]++;
                    if (isset($schoolTotals[$school][$row['book']])) $schoolTotals[$school][$row['book']]++;
                    else $schoolTotals[$school][$row['book']] = 1;

                    if ($bookPurchased) {
                        $bookTotals[$row['book']]++;
                        if (isset($schoolBookTotals[$school][$row['book']])) $schoolBookTotals[$school][$row['book']]++;
                        else $schoolBookTotals[$school][$row['book']] = 1;
                    }
                }
            }
            ?>
        </table>
        <h2><?= $gender ?> Study Guide Totals</h2>
        <table>
            <tr>
                <?php
                for ($i = 1; $i < 6; $i++) {
                    echo "<th>Study Guide " . $i . "</th>";
                }
                ?>
            </tr>
            <?php
            echo "<tr>";
            for ($i = 1; $i < 6; $i++) {
                echo "<td>" . $totals[$i] . "</td>";
            }
            echo "</tr>";
            ?>
        </table>
        <h2><?= $gender ?> Study Guide School Totals</h2>
        <table>
            <tr>
                <th>School</th>
                <?php
                for ($i = 1; $i < 6; $i++) {
                    echo "<th>Study Guide $i</th>";
                }
                ?>
            </tr>
            <?php
            foreach ($schoolTotals as $school => $other) {
                echo "<tr><td>" . $school . "</td>";
                for ($i = 1; $i < 6; $i++) {
                    echo "<td>" . $schoolTotals[$school][$i] . "</td>";
                }
                echo "</tr>";
            }
            ?>
        </table>
        <h2><?= $gender ?> Book Totals</h2>
        <table>
            <tr>
                <?php
                for ($i = 1; $i < 6; $i++) {
                    echo "<th>Book " . $i . "</th>";
                }
                ?>
            </tr>
            <?php
            echo "<tr>";
            for ($i = 1; $i < 6; $i++) {
                echo "<td>" . $bookTotals[$i] . "</td>";
            }
            echo "</tr>";
            ?>
        </table>
        <h2><?= $gender ?> Book School Totals</h2>
        <table>
            <tr>
                <th>School</th>
                <?php
                for ($i = 1; $i < 6; $i++) {
                    echo "<th>Book $i</th>";
                }
                ?>
            </tr>
            <?php
            foreach ($schoolBookTotals as $school => $other) {
                echo "<tr><td>" . $school . "</td>";
                for ($i = 1; $i < 6; $i++) {
                    echo "<td>" . $schoolBookTotals[$school][$i] . "</td>";
                }
                echo "</tr>";
            }
            ?>
        </table>
    <?php endforeach; ?>
    </body>
</html>

