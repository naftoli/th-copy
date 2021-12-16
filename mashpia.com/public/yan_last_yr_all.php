<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);

$admin_auth = array('school'); 
require('header.php');

require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$sql = "select * from line_campaigns where year = " . --$year;
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
	$campaigns[$row['id']] = strtolower( $row['type'] );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Last Year's birthday present to the Rebbe</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style>
            tr, th, td {
                font-size: 14px;
                padding: 5px;
            }
        </style>
    </head>
    
    <body>
    	<? include('admin_header.php'); ?>
        <h1 class="no-print">Last Year's birthday present to the Rebbe</h1>

        <?php
        require_once 'class.adminSchools.php';
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
        require_once 'class.bpSummary.php';
        foreach ($campaigns as $id => $campaign) {
            $bps = new BpSummary( $id, 'user' );
            foreach ($users as $user_id => $info) {
                $learned = $bps->getSummary( $user_id );
                if ($learned == '') $learned = 0;
                $results[$info['school_id']][$user_id][$campaign] = $learned;
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
                    <th>תניא בעל פה <br />Lines Learned</th>
                    <th>משניות בעל פה <br />Lines Learned</th>
                </tr>
                <?php
                foreach ($more as $user_id => $info) { 
                    $name = $users[$user_id]['first'] . ' ' . $users[$user_id]['last'];
                    $grade = $users[$user_id]['class_grade'] . (empty($users[$user_id]['class_sub']) ? '' : '-' . $users[$user_id]['class_sub']);
                    echo "<tr><td>" . $grade . "</td><td>" . $name . '</td><td>' . $info['tanya'] . "</td><td>" .
                    $info['mishna'] . "</td></tr>";
                    if (isset($totals[$school]['tanya'])) $totals[$school]['tanya'] += $info['tanya'];
                    else $totals[$school]['tanya'] = $info['tanya'];
                    if (isset($totals[$school]['mishna'])) $totals[$school]['mishna'] += $info['mishna'];
                    else $totals[$school]['mishna'] = $info['mishna'];
                }
                echo "<tr><th colspan='2'>Total:</th><th>" . $totals[$school]['tanya'] . "</th><th>" . $totals[$school]['mishna'] . "</th></tr>";
                ?>
            </table>
            <div class="page-break"></div>
            <?php
        }
        echo "<p></p>";
        echo "<table><caption>Totals</caption><tr><th>School</th><th>Total Tanya Lines</th><th>Total Mishna Lines</th></tr>";
        foreach ($totals as $school => $types) {
            echo "<tr><td>" . $schools[$school] . "</td>";
            echo "<td>" . $types['tanya'] . "</td>";
            echo "<td>" . $types['mishna'] . "</td></tr>";
        }
        echo "</table>";
        ?>
    </body>
</html>