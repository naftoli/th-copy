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
        table {
            margin-left: auto;
            margin-right: auto;
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

$totals = [];
foreach ($users as $school => $grades) {
    foreach ($grades as $grade => $other) {
        echo "<img src='classPledgeHeader.jpg' class='imgHeader' />";
        echo "<h2>" . $schools[$school] . ' - ' . $grade . "</h2>";
        ?>
        <table width="75%">
            <thead>
            <tr>
                <th class="ches">Chayol</th>
                <th class="ches" colspan="8" style="text-align: center">תניא בעל פה <br />Lines Learned</th>
                <th class="ches" colspan="8" style="text-align: center">משניות בעל פה <br />Lines Learned</th>
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
                    for ($i = $start; $i <= $year; $i++) {
                        if ($i == 5782) {
                            echo "<td class='cell' style='border-right: 2px solid black;'>" . (isset($bpInfo[$i][$type]) ? $bpInfo[$i][$type] : '') . "</td>";
                        } else {
                            echo "<td class='cell'>" . (isset($bpInfo[$i][$type]) ? $bpInfo[$i][$type] : '') . "</td>";
                        }
                        // update totals
                        if (isset($bpInfo[$i][$type])) {
                            if (isset($totals[$school][$grade][$i][$type])) $totals[$school][$grade][$i][$type] += $bpInfo[$i][$type];
                            else $totals[$school][$grade][$i][$type] = $bpInfo[$i][$type];
                        }
                    }
                }
                echo "</tr>";
            }
            echo "<tr><th style='border: 2px solid black;'>Total:</th>";
            foreach ($types as $type) {
                for ($i = $start; $i <= $year; $i++) {
                    if ($i == 5782) {
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
        <img src="classPledgeFooter.jpg" class="imgFooter" />
        <div style="page-break-after: always"></div>
        <?php
    }
}
?>
</body>
</html>