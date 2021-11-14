<?php
ini_set('display_errors', 1);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonYear();

$info = [];
foreach ($schools as $id => $school_name) {
    $sql = "select u.first, u.last, u.user_serial, u.gender, c.class_grade, c.class_sub 
            from users u 
            join classes c using (class_id) 
            join th_chidon tc using (user_id) 
            where tc.year = $year 
            and tc.reg_date > 0 
            and tc.school_id = $id 
            order by class_grade, class_sub, last, first";
//    echo $sql;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $info[$id][$grade][] = $row;
    }
}
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Shipping Report for Test Prizes</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 14px;
            padding: 10px;
            border-bottom: 1px solid grey;
        }
        caption {
            font-size: 20px;
            border-bottom: 1px solid grey;
        }
        @media only print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <h1 class="no-print">Shipping Report for Test Prizes <?= $year ?></h1>
    <?php
    $prizes = [
        1 => 'Drawstring Bag',
        2 => 'Infinity Cube',
        3 => 'Tzedakah Pouch',
        4 => 'Water Bottle'
    ];
    // colors for the first and last prize; first the boys color then the girls color
    $colors = [
        1 => ['blue', 'pink'],
        4 => ['navy', 'burgandy']
    ];
    if (isset($_POST['submit'])) {
        $index = $_POST['num'];
        echo "<h2>Prize: " . $prizes[$index] . "</h2>";

        if (in_array($index, [1, 4])) {
            $grandTotals['blue'] = 0;
            $grandTotals['pink'] = 0;
            foreach ($info as $school_id => $grades) {
                $schoolTotal['blue'] = 0;
                $schoolTotal['pink'] = 0;
                echo "<h3>" . $schools[$school_id] . "</h3>";
                foreach ($grades as $grade => $rows) {
                    $totals['blue'] = 0;
                    $totals['pink'] = 0;
                    ?>
                    <table>
                        <caption><?= $schools[$school_id] . ' Grade: ' . $grade ?></caption>
                        <tr>
                            <th>Serial Number</th>
                            <th>Student</th>
                            <th>Gender</th>
                            <th>Prize Color</th>
                        </tr>
                        <?php
                        foreach ($rows as $row) {
                            echo "<tr><td>" . $row['user_serial'] . "</td><td>" . ($row['first'] . ' ' . $row['last']) .
                                "</td><td>" . $row['gender'] . "</td><td>";
                            if ($row['gender'] == 'M') {
                                echo ucwords($colors[$index][0]);
                                $totals['blue']++;
                                $schoolTotal['blue']++;
                                $grandTotals['blue']++;
                            }
                            else if ($row['gender'] == 'F') {
                                echo ucwords($colors[$index][1]);
                                $totals['pink']++;
                                $schoolTotal['pink']++;
                                $grandTotals['pink']++;
                            }
                            echo "</td></tr>";
                        }
                    echo "</table><br />";
                    echo "Total " . ucwords($colors[$index][1]) . " " . ucwords($prizes[$index]) . ": " . $totals['blue'];
                    echo "<br />Total " . ucwords($colors[$index][0]) . " " . ucwords($prizes[$index]) . ": " . $totals['pink'];
                    echo "<br /><br />";
                }
                echo "<br /><hr /><br />";
                echo "Total " . ucwords($colors[$index][0]) . " " . ucwords($prizes[$index]) . " for school: " . $schoolTotal['blue'];
                echo "<br />Total " . ucwords($colors[$index][1]) . " " . ucwords($prizes[$index]) . " for school: " . $schoolTotal['pink'];
                echo "<p></p><div style='page-break-after: always'></div>";
            }
            if ($admin_user['auth'] == 'super') {
                echo "Grand Total " . ucwords($colors[$index][0]) . " " . ucwords($prizes[$index]) . ": " . $grandTotals['blue'];
                echo "<br />Grand Total " . ucwords($colors[$index][1]) . " " . ucwords($prizes[$index]) . ": " . $grandTotals['pink'];
            }
        } else {
            $grandTotals = 0;
            foreach ($info as $school_id => $grades) {
                $schoolTotal = 0;
                echo "<h3>" . $schools[$school_id] . "</h3>";
                foreach ($grades as $grade => $rows) {
                    $totals = 0;
                    ?>
                    <table>
                        <caption><?= $schools[$school_id] . ' Grade: ' . $grade ?></caption>
                        <tr>
                            <th>Serial Number</th>
                            <th>Student</th>
                        </tr>
                        <?php
                        foreach ($rows as $row) {
                            echo "<tr><td>" . $row['user_serial'] . "</td><td>" . ($row['first'] . ' ' . $row['last']) . "</td></tr>";
                            $totals++;
                            $schoolTotal++;
                            $grandTotals++;
                        }
                    echo "</table><br />";
                    echo "Total " . ucwords($prizes[$index]) . ": " . $totals;
                    echo "<br /><br />";
                }
                echo "Total " . ucwords($prizes[$index]) . " for school: " . $schoolTotal;
                echo "<hr /><div style='page-break-after: always'></div>";
            }
            if ($admin_user['auth'] == 'super') {
                echo "Grand Total " . ucwords($prizes[$index]) . ": " . $grandTotals;
            }
        }
    } else {
        ?>
        <form action="" method="post">
            Please choose which test you would like to have the report for:<br /><br />
            <select name="num">
                <?php
                for ($i = 1; $i <= 4; $i++) {
                    echo "<option value='$i'>Test #$i</option>";
                }
                ?>
            </select><br /><br />
            <input type="submit" name="submit" value="Generate Report" />
        </form>
        <?php
    }
    ?>
</body>
</html>
