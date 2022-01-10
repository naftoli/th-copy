<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);

$admin_auth = array('school');
require('../header.php');

require_once '../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
$start = 5775;

$types = ['tanya', 'mishna'];

$sql = "select * from line_campaigns where year != 5782 order by year";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $campaigns[$row['year']][$row['id']] = strtolower( $row['type'] );
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

<HEAD>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>History of Bal Peh for Rebbe</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            font-size: 12px;
            padding: 5px;
            border: 1px solid grey;
        }
        button {
            padding: 6px;
            font-size: 16px;
        }
    </style>
</head>

<body>
<? include('../admin_header.php'); ?>
<h1 class="no-print">History of Bal Peh for Rebbe</h1>

<button id="save">Save</button>
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
            line_campaigns l ON l.id = bus.campaign_id 
                JOIN 
            classes c on c.class_id = u.class_id 
        ORDER BY 
            s.school_id, c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $learned = $row['num_lines'];
    if ($learned == '') $learned = 0;
    $results[$row['school_id']][$row['user_id']][$row['year']][strtolower($row['type'])] = $learned;
}
//echo "<pre>"; print_r($results); echo "</pre>"; exit;

$totals = [];
foreach ($results as $school => $more) {
    if (! isset($schools[$school])) continue;
    echo "<h2>" . $schools[$school] . "</h2>";
    ?>
    <table width="75%">
        <thead>
        <tr>
            <th>Grade</th>
            <th>Chayol</th>
            <th colspan="7" style="text-align: center">תניא בעל פה <br />Lines Learned</th>
            <th colspan="7" style="text-align: center">משניות בעל פה <br />Lines Learned</th>
        </tr>
        <tr>
            <th colspan="2"></th>
            <?php
            foreach ($types as $type) {
                for ($i = $start; $i < $year; $i++) {
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
                    echo "<td class='cell' id='$i:$user_id:$type' contenteditable='true'>" . (isset($info[$i][$type]) ? $info[$i][$type] : '') . "</td>";
                    // update totals
                    if (isset($info[$i][$type])) {
                        if (isset($totals[$school][$i][$type])) $totals[$school][$i][$type] += $info[$i][$type];
                        else $totals[$school][$i][$type] = $info[$i][$type];
                    }
                }
            }
            echo "</tr>";
        }
        echo "<tr><th colspan='2'>Total:</th>";
        foreach ($types as $type) {
            for ($i = $start; $i <= $year; $i++) {
                echo "<th>" . ($totals[$school][$i][$type] ?? 0) . "</th>";
            }
        }
        echo "</tr>";
        ?>
        </tbody>
    </table>
    <div style="page-break-after: always"></div>
    <?php
}
?>
</body>
<script>
    let updates = []
    $(".cell").blur( function () {
        let value = $(this).text()
        let info = $(this).attr('id').split(':')
        let toUpdate = {
            year: info[0],
            user: info[1],
            type: info[2],
            lines: value
        }
        updates.push(toUpdate)
    })

    $("#save").click( function () {
        if (!updates.length) {
            alert("Nothing to save.");
            return;
        }
        $.post('saveLines.php', { info: updates }, function( result ) {
            const res = JSON.parse(result)
            if (res.success) alert("Saved.")
            else alert('Not Saved.')
        })
    })
</script>
</html>