<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);

$admin_auth = array('school');
require('../header.php');

require_once '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

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
            font-size: 14px;
            padding: 5px;
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
require_once '../class.bpSummary.php';
foreach ($campaigns as $year => $more) {
    foreach ($more as $id => $campaign) {
        $bps = new BpSummary( $id, 'user' );
        foreach ($users as $user_id => $info) {
            $learned = $bps->getSummary($user_id);
            if ($learned == '') $learned = 0;
            $results[$info['school_id']][$user_id][$year][$campaign] = $learned;
        }
    }
}

$totals = [];
foreach ($results as $school => $more) {
    echo "<h2>" . $schools[$school] . "</h2>";
    ?>
    <table width="75%">
        <tr>
            <th>Grade</th>
            <th>Chayol</th>
            <th colspan="7">תניא בעל פה <br />Lines Learned</th>
            <th colspan="7">משניות בעל פה <br />Lines Learned</th>
        </tr>
        <tr>
            <th colspan="2"></th>
            <?php
            for ($i = 5754; $i <= $year; $i++) {
                echo "<th>$i</th>";
            }
            ?>
        </tr>
        <?php
        foreach ($more as $user_id => $info) {
            $name = $users[$user_id]['first'] . ' ' . $users[$user_id]['last'];
            $grade = $users[$user_id]['class_grade'] . (empty($users[$user_id]['class_sub']) ? '' : '-' . $users[$user_id]['class_sub']);
            echo "<tr><td>" . $grade . "</td><td>" . $name . '</td>';
            for ($i = 5754; $i <= $year; $i++) {
                echo "<td>" . (isset($info[$i]['tanya']) ? $info[$i]['tanya'] : '') . "</td>";
                // update totals
                if (isset($totals[$school][$i]['tanya'])) $totals[$school][$i]['tanya'] += $info[$i]['tanya'];
                else $totals[$school][$i]['tanya'] = $info[$i]['tanya'];
            }
            for ($i = 5754; $i <= $year; $i++) {
                echo "<td>" . (isset($info[$i]['mishna']) ? $info[$i]['tanya'] : '') . "</td>";
                // update totals
                if (isset($totals[$school][$i]['mishna'])) $totals[$school][$i]['mishna'] += $info[$i]['mishna'];
                else $totals[$school][$i]['mishna'] = $info[$i]['mishna'];
            }
            echo "</tr>";
        }
        echo "<tr><th colspan='2'>Total:</th>";
        for ($i = 5754; $i <= $year; $i++) {
            echo "<th>" . $totals[$school][$i]['tanya'] . "</th>";
        }
        for ($i = 5754; $i <= $year; $i++) {
            echo "<th>" . $totals[$school][$i]['mishna'] . "</th>";
        }
        echo "</tr>";
        ?>
    </table>
    <div class="page-break"></div>
    <?php
}
echo "<p></p><h2>Totals</h2>";
echo "<table><tr><th>School</th>";
for ($i = 5754; $i <= $year; $i++) {
    echo "<th>Total Tanya $i</th>";
}
for ($i = 5754; $i <= $year; $i++) {
    echo "<th>Total Mishna $i</th>";
}
echo "</tr>";

foreach ($totals as $school => $more) {
    echo "<tr><td>" . $schools[$school] . "</td>";
    for ($i = 5754; $i <= $year; $i++) {
        echo "<td>" . $more[$i]['tanya'] . "</td>";
    }
    for ($i = 5754; $i <= $year; $i++) {
        echo "<td>" . $more[$i]['mishna'] . "</td>";
    }
}
echo "</table>";
?>
</body>
</html>