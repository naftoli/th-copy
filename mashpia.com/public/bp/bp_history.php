<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);

$admin_auth = array('school');
require('../header.php');

require_once '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
$start = 5775;

$types = ['tanya', 'mishna'];

$sql = "select * from line_campaigns order by year";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $campaigns[$row['year']][$row['id']] = strtolower( $row['type'] );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

<HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Last Year's birthday present to the Rebbe</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            font-size: 12px;
            padding: 5px;
        }
        tr {
            border-bottom: 1px solid grey;
        }
    </style>
</head>

<body>
<? include('../admin_header.php'); ?>
<h1 class="no-print">Last Year's birthday present to the Rebbe</h1>

<?php
require_once '../class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$users = [];
foreach ($schools as $id => $school) {
    $sql = "select * from users u 
            join classes c using (class_id) 
            where u.school_id = $id 
            and u.user_registered > 0 
            and c.class_era = 0 
            order by class_grade, class_sub, last, first";
    $result = mysql_query( $sql );
    if (mysql_num_rows( $result ) > 0) {
        while ($row = mysql_fetch_assoc( $result )) {
            $users[$row['user_id']] = $row;
        }
    }
}

$results = [];
$sql = "SELECT 
            bus.*, s.school_id, l.type, l.year
        FROM
            bp_user_summary bus
                JOIN
            users u USING (user_id)
                JOIN
            schools s USING (school_id)
                JOIN
            line_campaigns l ON l.id = bus.campaign_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $learned = $row['num_lines'];
    if ($learned == '') $learned = 0;
    $results[$row['school_id']][$row['user_id']][$row['year']][strtolower($row['type'])] = $learned;
}
//echo "<pre>"; print_r($results); echo "</pre>"; exit;

$totals = [];
foreach ($results as $school => $more) {
    echo "<h2>" . $schools[$school] . "</h2>";
    ?>
    <table width="75%">
        <thead>
            <tr>
                <th>Grade</th>
                <th>Chayol</th>
                <th colspan="8" style="text-align: center">תניא בעל פה <br />Lines Learned</th>
                <th colspan="8" style="text-align: center">משניות בעל פה <br />Lines Learned</th>
            </tr>
            <tr>
                <th colspan="2"></th>
                <?php
                foreach ($types as $type) {
                    for ($i = $start; $i <= $year; $i++) {
                        echo "<th>$i</th>";
                    }
                }
                ?>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach ($more as $user_id => $info) {
            if (! isset($users[$user_id])) continue;
            $name = $users[$user_id]['first'] . ' ' . $users[$user_id]['last'];
            $grade = $users[$user_id]['class_grade'] . (empty($users[$user_id]['class_sub']) ? '' : '-' . $users[$user_id]['class_sub']);
            echo "<tr><td>" . $grade . "</td><td>" . $name . '</td>';
            foreach ($types as $type) {
                for ($i = $start; $i <= $year; $i++) {
                    echo "<td>" . (isset($info[$i][$type]) ? $info[$i][$type] : '') . "</td>";
                    // update totals
                    if (isset($totals[$school][$i][$type])) $totals[$school][$i][$type] += $info[$i][$type];
                    else $totals[$school][$i][$type] = $info[$i][$type];
                }
            }
            echo "</tr>";
        }
        echo "<tr><th colspan='2'>Total:</th>";
        foreach ($types as $type) {
            for ($i = $start; $i <= $year; $i++) {
                echo "<th>" . $totals[$school][$i][$type] . "</th>";
            }
        }
        echo "</tr>";
        ?>
        </tbody>
    </table>
    <div class="page-break"></div>
    <?php
}
echo "<p></p><h2>Totals</h2>";
echo "<table><tr><th>School</th>";
foreach ($types as $type) {
    for ($i = $start; $i <= $year; $i++) {
        echo "<th>Total " . strtoupper($type) . " $i</th>";
    }
}
echo "</tr>";

foreach ($totals as $school => $more) {
    echo "<tr><td>" . $schools[$school] . "</td>";
    foreach ($types as $type) {
        for ($i = $start; $i <= $year; $i++) {
            echo "<td>" . $more[$i][$type] . "</td>";
        }
    }
}
echo "</table>";
?>
</body>
</html>