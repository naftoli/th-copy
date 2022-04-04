<?php
$admin_auth = array('school');
require_once ( __DIR__ . '/../../header.php' );

require_once ( __DIR__ . '/../../class.globalSettings.php' );
$year = GlobalSettings::getChidonYear();

require_once ( __DIR__ . '/../../class.adminSchools.php' );
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], true, true ); // needed for including chidon only schools
$schools = $as->getSchools();

$prizes = [];
$sql = "select * from chidon_credit_prizes where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[$row['credits']] = $row;
}

$recruits = [];
$userInfo = [];
$sql = "select th_chidon_id, tc.user_id, recruited_by, u.first, u.last, u.user_serial, u.gender, s.school_name, c.class_grade, c.class_sub 
        from th_chidon tc
        join users u on u.user_id = tc.recruited_by or u.user_serial = tc.recruited_by
        join schools s on u.school_id = s.school_id
        join classes c on u.class_id = c.class_id 
        where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $recruits[$row['user_serial']][] = $row['user_id'];
    $userInfo[$row['user_serial']] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Chidon Credit Prizes Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/admin_styles.css" rel="stylesheet" type="text/css" />
    <style>
        tr, th, td {
            font-family: Arial;
            font-size: 14px;
            padding: 10px;
        }
    </style>
</head>
<body>
    <?php include( __DIR__ . '/../../admin_header.php'); ?>
    <h1>Chidon Credit Prizes Report</h1>
    <table>
        <tr>
            <th>Student</th>
            <th>Grade</th>
            <th>School</th>
            <th>Gender</th>
            <th>Serial Number</th>
            <th>Amount of children recruited</th>
            <th>User ID's of kids recruited</th>
            <th>Total Credits Earned</th>
            <th>Prize(s) Earned</th>
        </tr>
        <?php
        foreach ($recruits as $serialNum => $recruited) {
            $info = $userInfo[$serialNum];
            $numRecruited = count($recruited);
            $grade = $info['class_grade'] . (empty($info['class_sub']) ? '' : '-' . $info['class_sub']);
            echo "<tr><td>" . ($info['first'] . ' ' . $info['last']) . "</td><td>" . $grade . "</td><td>" . $info['school_name'] .
                "</td><td>" . $info['gender'] . "</td><td>" . $serialNum . "</td><td>" . $numRecruited . "</td><td>";
            foreach ($recruited as $user_id) {
                echo $user_id . ', ';
            }
            echo "</td><td>" . $numRecruited . "</td><td>";
            for ($i = 1; $i <= $numRecruited; $i++) {
                echo $prizes[$i]['prize'] . ', ';
            }
            echo "</td></tr>";
        }
        ?>
    </table>
</body>
</html>