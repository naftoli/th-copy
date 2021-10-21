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
    <title>Drawstring Bag Shipping Report</title>
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
    <h1 class="no-print">Drawstring Bag Shipping Report <?= $year ?></h1>
    <?php
    $grandTotals['blue'] = 0;
    $grandTotals['pink'] = 0;
    foreach ($info as $school_id => $grades) {
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
                    <th>Bag Color</th>
                </tr>
                <?php
                foreach ($rows as $row) {
                    echo "<tr><td>" . $row['user_serial'] . "</td><td>" . ($row['first'] . ' ' . $row['last']) .
                        "</td><td>" . $row['gender'] . "</td><td>";
                    if ($row['gender'] == 'M') {
                        echo "Blue";
                        $totals['blue']++;
                        $grandTotals['blue']++;
                    }
                    else if ($row['gender'] == 'F') {
                        echo "Pink";
                        $totals['pink']++;
                        $grandTotals['pink']++;
                    }
                    echo "</td></tr>";
                }
            echo "</table><br />";
            echo "Total Blue Bags: " . $totals['blue'];
            echo "<br />Total Pink Bags: " . $totals['pink'];
            echo "<p></p><div style='page-break-after: always'></div>";
        }
    }
    if ($admin_user['auth'] == 'super') {
        echo "Grand Total Blue Bags: " . $grandTotals['blue'];
        echo "<br />Grand Total Pink Bags: " . $grandTotals['pink'];
    }
    ?>
</body>
</html>
