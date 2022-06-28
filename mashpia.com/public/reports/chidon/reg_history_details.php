<?php
ini_set('display_errors',1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$cur_year = GlobalSettings::getChidonYear();
$start_yr = 5777;

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true );
$schools = $as->getSchools();

// get class_id
$class_id = $_GET['id'];

// figure out which years kids were enrolled into for this school
$info = [];
$grades = [];
$serials = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT tc.year, u.*, c.*  
    FROM th_chidon tc 
    JOIN users u USING (user_id) 
    JOIN classes c ON c.class_id = u.class_id 
    WHERE u.class_id = :class_id 
    AND tc.reg_date > 0 
    ORDER BY class_grade, class_sub
");
$stmt->execute([':class_id' => $class_id]);
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    $grades[$row['class_id']] = $grade;
    $name = $row['first'] . ' ' . $row['last'];
    $year = $row['year'];
    $info[$row['user_id']][$name][] = $year;
    $serials[$row['user_id']] = $row['user_serial'];
}
//echo "<pre>"; print_r( $info ); echo "</pre>";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset=""utf8" />
        <title>Chidon History Report</title>
        <style>
            tr, th, td {
                font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
                font-size: 14px;
                padding: 10px;
                border: 1px solid lightgrey;
            }
        </style>
    </head>
    <body>
        <h1>Chidon History Report</h1>
        <h2>Grade <?= $grades[$class_id] ?></h2>
        <table>
            <tr>
                <th>Serial Number</th>
                <th>Student</th>
                <?php
                $totals = []; // initialize totals per year
                for ($i = $start_yr; $i <= $cur_year; $i++) {
                    echo "<th>" . $i . "</th>";
                    $totals[$i] = 0;
                }
                ?>
            </tr>
            <?php
            foreach ($info as $user_id => $more) {
                foreach ($more as $name => $other) {
                    echo "<tr><td>" . $name . "</td><td>" . $serials[$user_id] . "</td>";
                    for ($i = $start_yr; $i <= $cur_year; $i++) {
                        echo "<td>";
                        if (array_search($i, $other) !== false) {
                            echo "&#10003;";
                            $totals[$i]++;
                        }
                        echo "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "<tr><th></th><th>Totals:</th>";
            for ($i = $start_yr; $i <= $cur_year; $i++) echo "<th>" . $totals[$i] . "</th>";
            echo "</tr>";
            ?>
        </table>
    </body>
</html>
