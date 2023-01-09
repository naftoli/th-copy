<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);

$admin_auth = array('school');
require('../header.php');

require_once '../class.globalSettings.php';
$cur_year = GlobalSettings::getCurrentYear();

require '../class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

$lines_learned = [];
$sql = "SELECT 
            bus.*, s.school_id, l.type, l.year
        FROM
            bp_user_summary bus
                JOIN
            users u USING (user_id)
                JOIN
            schools s USING (school_id)
                JOIN
            line_campaigns l ON l.id = bus.campaign_id 
                JOIN 
            classes c on c.class_id = u.class_id 
        WHERE 
            l.year != $cur_year 
        ORDER BY 
            s.school_id, c.class_grade, c.class_sub, u.last, u.first ";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $learned = $row['num_lines'];
    if ($learned == '') $learned = 0;
    $lines_learned[$row['school_id']][$row['user_id']][$row['year']][strtolower($row['type'])] = $learned;
}
//echo "<pre>"; print_r($lines_learned); echo "</pre>";

$users = [];
foreach ($schools as $school_id => $school_name) {
    $sql = "select u.user_id, u.first, u.last, c.class_grade, c.class_sub, s.school_name 
            from users u 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id 
            where u.school_id = $school_id 
            order by c.class_grade, c.class_sub, u.last, u.first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $name = $row['first'] . ' ' . $row['last'];
        $users[$school_id][$grade][$row['user_id']] = $name;
    }
}
?>
<!DOCTYPE html>
<HTML>

<HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Bal Peh Pledge Cards</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            font-size: 12px;
            padding: 10px;
            border: 2px solid black;
        }
        .card {
            width: 8in;
            line-height: 1.3;
            text-align: center;
        }
        .card table {
            margin-left: auto;
            margin-right: auto;
            width: 75%;
            margin-top: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }

        img {
            width: 750px;
        }
        img.imgHeader {
            margin-bottom: 30px;
            margin-top: 8%;
        }
        img.imgFooter {
            margin-top: 20px;
        }
        button {
            padding: 8px;
            font-size: 16px;
        }
    </style>
</head>

<body>
<? include('../admin_header.php'); ?>
<h1 class="no-print">Bal Peh Pledge Cards</h1>
<button class="no-print" onclick="window.print()">Print</button>
<?php
$all_grades = ['Pre1a', '1', '2', '3', '4', '5', '6', '7', '8'];
$startYr = [
    'Pre1a' => 5783,
    '1'   => 5782,
    '2'   => 5781,
    '3'   => 5780,
    '4'   => 5779,
    '5'   => 5778,
    '6'   => 5777,
    '5'   => 5776,
    '6'   => 5775,
    '7'   => 5774,
    '8'   => 5773
];

foreach ($users as $school_id => $grades) {
    foreach ($grades as $grade => $more) {
        foreach ($more as $user_id => $name) {
//        if (! isset($users[$school_id][$user_id])) continue;
//        $grade = $users[$school_id][$user_id]['grade'];
//        $name = $users[$school_id][$user_id]['name'];

            $bpInfo = isset($lines_learned[$school_id][$user_id]) ? $lines_learned[$school_id][$user_id] : [];
            ksort($bpInfo);

            $highestTanya = 0;
            $highestMishna = 0;
            foreach ($bpInfo as $year => $lines) {
                $tanya = isset($lines['tanya']) ? $lines['tanya'] : 0;
                if ($tanya > $highestTanya) $highestTanya = $tanya;

                $mishna = isset($lines['mishna']) ? $lines['mishna'] : 0;
                if ($mishna > $highestMishna) $highestMishna = $mishna;
            }

            if (strpos($grade, '-') !== false) {
                $gradeInfo = explode('-', $grade);
                $gradeOnly = $gradeInfo[0];
            } else {
                $gradeOnly = $grade;
            }

            echo "<div class='card'>";
            echo "<img src='MishnaHeader.jpg' class='imgHeader' />";
            echo "<b>School:</b> " . $schools[$school_id] . "<br />";
            echo "<b>Grade:</b> " . $grade . "<br />";
            echo "<b>Student:</b> " . $name . "<br />";
            echo "<br />";
            if ($gradeOnly != 'Pre1a') {
                echo "<b>Highest amount of lines of Tanya:</b> " . $highestTanya . "<br />";
                echo "<b>Highest amount of lines of Mishna:</b> " . $highestMishna . "<br /><br />";
                //        echo "Pledge for the Rebbe's 120th Birthday: Tanya _______ Mishna _______<br /><br />";

                // figure out which grade child started at
                $start = $startYr[$gradeOnly];
                echo "<table><thead><tr><th>Grade / Year</th><th>Total Tanya Lines</th><th>Total Mishna Lines</th></tr></thead><tbody>";
                foreach ($bpInfo as $year => $lines) {
                    $tanya = isset($lines['tanya']) ? $lines['tanya'] : 0;
                    $mishna = isset($lines['mishna']) ? $lines['mishna'] : 0;

                    if ($year < $start) continue;
                    $max = array_search($gradeOnly, $all_grades);
                    $diff = $cur_year - $year;
                    $history = $all_grades[$max - $diff];
                    echo "<tr><td>Grade " . $history . " (" . $year . ")</td><td>" . $tanya . "</td><td>" . $mishna . "</td></tr>";
                }
                echo "</tbody></table>";
                echo "<p></p>";
            }
            echo "<img src='MishnaFooter.jpg' class='imgFooter' /></div>";
            echo "<div style='page-break-after: always'></div>";
        }
    }
}
?>