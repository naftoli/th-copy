<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);

$admin_auth = array('school');
require('../header.php');

require_once '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
$start = 5775;

$types = ['tanya', 'mishna'];

$sql = "select * from line_campaigns where year != $year order by year";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $campaigns[$row['year']][$row['id']] = strtolower( $row['type'] );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

<HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Bal Peh Class Pledge for Rebbe</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        @font-face {
          font-family: Exo;
          src: url("/fonts/Exo/static/Exo-Regular.ttf")
        }

        @font-face {
          font-family: Hebrew;
          src: url("/fonts/FbTkuma-Regular.otf")
        }

        h2 {
            font-family: Exo;
        }

        table {
            margin-left: auto;
            margin-right: auto;
            font-family: Exo;
        }
        tr, th, td {
            font-size: 12px;
            padding: 5px;
            border: 1px solid grey;
        }
        img {
            width: 750px;
        }
        img.imgFooter {
            margin-top: 20px;
        }
        .ches {
            border-top: 2px solid black;
            border-right: 2px solid black;
            border-left: 2px solid black;
        }
        button {
            padding: 8px;
            font-size: 16px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
<? include('../admin_header.php'); ?>
<h1 class="no-print">Bal Peh Class Pledge for Rebbe</h1>
<button class="no-print" onclick="window.print()">Print</button>
<br />
<br />
<?php
require_once '../class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$users = [];
foreach ($schools as $id => $school) {
    $sql = "select * from users u 
            join classes c on u.class_id = c.class_id
            where u.school_id = $id 
            order by class_grade, class_sub, last, first";
    $result = mysql_query( $sql );
    if (mysql_num_rows( $result ) > 0) {
        while ($row = mysql_fetch_assoc( $result )) {
            $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
            $users[$id][$grade][$row['user_id']] = $row;
        }
    }
}

$results = [];
$sql = "SELECT 
            bus.*, s.school_id, l.type, l.year, c.class_grade, c.class_sub 
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
        ORDER BY 
            s.school_id, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $learned = $row['num_lines'];
    if ($learned == '') $learned = 0;
    $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
    $results[$row['school_id']][$grade][$row['user_id']][$row['year']][strtolower($row['type'])] = $learned;
}
//echo "<pre>"; print_r($results); echo "</pre>"; exit;

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

$totals = [];
foreach ($users as $school => $grades) {
    foreach ($grades as $grade => $other) {
        echo "<img src='JPGs/TBP Pledge Card Class 5783 Header.jpg' class='imgHeader' />";
        echo "<h2>" . $schools[$school] . ' - ' . $grade . "</h2>";

        if (strpos($grade, '-') !== false) {
            $gradeInfo = explode('-', $grade);
            $gradeOnly = $gradeInfo[0];
        } else {
            $gradeOnly = $grade;
        }

        // make sure grade is one of the options in the array
        if (! in_array($gradeOnly, $all_grades)) continue;
        ?>
        <table width="75%">
            <thead>
            <tr>
                <th class="ches">Chayol</th>
                <th class="ches" colspan="8" style="text-align: center; font-family: Hebrew;">תניא בעל פה <br />Lines Learned</th>
                <th class="ches" colspan="8" style="text-align: center; font-family: Hebrew;">משניות בעל פה <br />Lines Learned</th>
            </tr>
            <tr>
                <th style="border-left: 2px solid black; border-right: 2px solid black; border-bottom: 2px solid black;"></th>
                <?php
                foreach ($types as $type) {
                    for ($i = $start; $i < $year; $i++) {
                        if ($i == ($year - 1)) {
                            echo "<th style='border-right: 2px solid black; border-bottom: 2px solid black;'>$i</th>";
                        } else {
                            echo "<th style='border-bottom: 2px solid black;'>$i</th>";
                        }
                    }
                }
                ?>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($other as $user_id => $info) {
                $bpInfo = isset($results[$school][$grade][$user_id]) ? $results[$school][$grade][$user_id] : [];
                $name = $info['first'] . ' ' . $info['last'];
                echo "<tr><td style='border-left: 2px solid black; border-right: 2px solid black;'>" . $name . '</td>';
                foreach ($types as $type) {
                    for ($i = $start; $i < $year; $i++) {
                        $amount = '';
                        if ($start >= $startYr[$gradeOnly] && isset($bpInfo[$i][$type])) $amount = $bpInfo[$i][$type];
                        if ($i == ($year - 1)) {
                            echo "<td class='cell' style='border-right: 2px solid black;'>" . $amount . "</td>";
                        } else {
                            echo "<td class='cell'>" . $amount . "</td>";
                        }
                        // update totals
                        if ($amount > 0) {
                            if (isset($totals[$school][$grade][$i][$type])) $totals[$school][$grade][$i][$type] += $amount;
                            else $totals[$school][$grade][$i][$type] = $amount;
                        }
                    }
                }
                echo "</tr>";
            }
            echo "<tr><th style='border: 2px solid black;'>Total:</th>";
            foreach ($types as $type) {
                for ($i = $start; $i < $year; $i++) {
                    if ($i == ($year - 1)) {
                        echo "<th style='border-bottom: 2px solid black; border-right: 2px solid black; border-top: 2px solid black;'>" . ($totals[$school][$grade][$i][$type] ?? 0) . "</th>";
                    } else {
                        echo "<th style='border-bottom: 2px solid black; border-top: 2px solid black;'>" . ($totals[$school][$grade][$i][$type] ?? 0) . "</th>";
                    }
                }
            }
            echo "</tr>";
            ?>
            </tbody>
        </table>
        <img src="JPGs/TBP Pledge Card Class 5783 Footer.jpg" class="imgFooter" />
        <div style="page-break-after: always"></div>
        <?php
    }
}
?>
</body>
</html>