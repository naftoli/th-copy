<?php
ini_set('display_errors',1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$cur_year = GlobalSettings::getChidonRegYear();

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true );
$schools = $as->getSchools();

// get school id and make sure admin has permission to view
$school_id = isset($_GET['id']) ? $_GET['id'] : $admin_user['auths']['school'][0] ;
$ids = array_keys($schools);
if (!in_array($school_id, $ids)) {
    echo "No Permission.";
    exit;
}

// figure out which years kids were enrolled into for this school
$info = [];
$grades = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT tc.year, u.user_id, c.*  
    FROM th_chidon tc 
    JOIN users u USING (user_id) 
    JOIN classes c ON c.class_id = u.class_id 
    WHERE u.school_id = :school 
    ORDER BY class_grade, class_sub 
");
foreach ($schools as $school_id => $name) {
    $stmt->execute([':school' => $school_id]);
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $year = $row['year'];
        if (isset($info[$school_id][$grade][$year])) $info[$school_id][$grade][$year]++;
        else $info[$school_id][$grade][$year] = 1;
        $grades[$school_id][$row['class_id']] = $grade;
    }
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
        <?php foreach ($schools as $school_id => $name) : ?>
            <h2><?= $name ?></h2>
            <table>
                <tr>
                    <th>Grade</th>
                    <?php
                    $totals = []; // initialize totals per year
                    for ($i = 5777; $i <= $cur_year; $i++) {
                        echo "<th>" . $i . "</th>";
                        $totals[$i] = 0;
                    }
                    ?>
                </tr>
                <?php
                if (isset($info[$school_id])) {
                    foreach ($info[$school_id] as $grade => $values) {
                        echo "<tr><td><a href='reg_history_details.php?id=" . array_search($grade, $grades[$school_id]) . "'>" . $grade . "</a></td>";
                        for ($i = 5777; $i <= $cur_year; $i++) {
                            echo "<td>";
                            if (isset($info[$school_id][$grade][$i])) {
                                echo $info[$school_id][$grade][$i];
                                $totals[$i] += $info[$school_id][$grade][$i];
                            }
                            echo "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "<tr><th align='right'>Totals:</th>";
                    for ($i = 5777; $i <= $cur_year; $i++) echo "<th>" . $totals[$i] . "</th>";
                    echo "</tr>";
                }
                ?>
            </table>
        <?php endforeach; ?>
    </body>
</html>
