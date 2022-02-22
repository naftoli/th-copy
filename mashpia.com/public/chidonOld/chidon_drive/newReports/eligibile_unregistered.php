<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$year = GlobalSettings::getChidonRegYear();

function getRegInfo() {
    global $reg, $year, $users;

    $sqlReg = "select u.user_id, u.user_serial, u.first, u.last, u.school_id, u.class_id, c.class_grade, tc.th_chidon_id, 
                tc.paid, tc.date_paid, tc.payment_request, tc.parent_id, tc.khk_trip, tc.test_type, tc.reward_type, tc.to_fundraise_5782    
            from users u 
            join th_chidon tc using (user_id)  
            join classes c on c.class_id = u.class_id 
            where paid is null  
            and tc.year = " . $year . "
            order by date_paid, last, first";
    $resReg = mysql_query($sqlReg);
    while ($rowReg = mysql_fetch_assoc($resReg)) {
        $reg[] = $rowReg;
        $users[] = [
            'user_id'   => $rowReg['user_id'],
            'class_id'  => $rowReg['class_id'],
            'school_id' => $rowReg['school_id'],
            'test_type' => $rowReg['test_type'],
            'reward_type'   => $rowReg['reward_type'],
            'th_chidon_id'  => $rowReg['th_chidon_id']
        ];
    }
}

function getTracks() {
    global $tracks, $users;

    $ct = new ChidonTests();
    $types = $ct->getTypes();

    foreach ($users as $user) {
        $info = $ct->getHighestTrackPassed($user);
        $highest = $info['highest_track'];
        $reward_type = $user['reward_type'];
        if ($reward_type !== 'highest track passed') {
            if ($highest == '') $highest = $reward_type;
            else {
                $key1 = array_search($highest, array_keys($types));
                $key2 = array_search($reward_type, array_keys($types));
                if ($key2 > $key1) $highest = $reward_type;
            }
        }
        $tracks[$user['user_id']] = isset($types[$highest]) ? $types[$highest] : 'none';
    }
}

$reg = [];
$users = [];
$tracks = [];

getRegInfo();
getTracks();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <title>Chidon Report</title>
    <style>
        tr, th, td {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 12px;
            padding: 5px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
<h1>Chidon Report</h1>
<table>
    <caption>Unegistered Report</caption>
    <tr>
        <th>Parent ID</th>
        <th>User ID</th>
        <th>Serial Number</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Grade</th>
        <th>Highest Track Passed</th>
    </tr>
    <?php
    foreach ($reg as $row) {
        echo "<tr><td>" . $row['parent_id'] . "</td><td>" . $row['user_id'] . "</td><td>" . $row['user_serial'] .
            "</td><td>" . $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $row['class_grade'] . "</td><td>" .
            $tracks[$row['user_id']] . "</td></tr>";
    }
    ?>
</table>
</body>
</html>
