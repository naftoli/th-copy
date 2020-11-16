<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No permission.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$cur_year = GlobalSettings::getChidonYear();

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true );
$schools = $as->getSchools();

// figure out which years kids were enrolled into
$info = [];
$stmt = $MASHPIA_DB->query("
    SELECT tc.year, u.user_id, s.school_name  
    FROM th_chidon tc 
    JOIN users u USING (user_id) 
    JOIN schools s ON s.school_id = u.school_id 
    ORDER BY school_name
");
$rows = $stmt->fetchAll();
foreach ($rows as $row) {
    $school = $row['school_name'];
    $year = $row['year'];
    if (isset($info[$school][$year])) $info[$school][$year]++;
    else $info[$school][$year] = 1;
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
        <table>
            <tr>
                <th>School</th>
                <?php
                $totals = []; // initialize totals per year
                for ($i = 5777; $i <= $cur_year; $i++) {
                    echo "<th>" . $i . "</th>";
                    $totals[$i] = 0;
                }
                ?>
            </tr>
            <?php
            foreach ($info as $school => $values) {
                echo "<tr><td><a href='reg_history_school.php?id=" . array_search($school, $schools) . "'>" . $school . "</a></td>";
                for ($i = 5777; $i <= $cur_year; $i++) {
                    echo "<td>";
                    if (isset($info[$school][$i])) {
                        echo $info[$school][$i];
                        $totals[$i] += $info[$school][$i];
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
            echo "<tr><th align='right'>Totals:</th>";
            for ($i = 5777; $i <= $cur_year; $i++) echo "<th>" . $totals[$i] . "</th>";
            echo "</tr>";
            ?>
        </table>
    </body>
</html>
