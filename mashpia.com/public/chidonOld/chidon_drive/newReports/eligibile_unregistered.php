<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
$year = GlobalSettings::getChidonRegYear();

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

function getRegInfo() {
    global $reg, $year, $users, $schools, $admin_user;

    $sqlReg = "select u.user_id, u.user_serial, u.first, u.last, u.school_id, u.class_id, c.class_grade, tc.th_chidon_id, 
                tc.paid, tc.parent_id, tc.test_type, tc.reward_type, s.school_name, c.class_grade, c.class_sub
            from users u 
            join th_chidon tc using (user_id)  
            join schools s on s.school_id = u.school_id 
            join classes c on c.class_id = u.class_id 
            where paid is null  
            and tc.year = " . $year;
    if ($admin_user['auth'] != 'super') $sqlReg .= " and u.school_id in (" . implode(',', array_keys($schools)) . ")";
    $sqlReg .= " order by s.school_name, c.class_grade, c.class_sub, last, first";
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

function getAdminInfo() {
    global $reg, $admins;

    foreach ($reg as $row) {
        $admin_id = $row['parent_id'];
        $sql = "select first, last, admin_email, admin_phone_mobile, admin_phone_work, admin_phone_home from admins 
                where admin_id = " . $admin_id;
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $admins[$admin_id] = $row;
    }
}

$reg = [];
$users = [];
$tracks = [];
$admins = [];

getRegInfo();
getTracks();
getAdminInfo();
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
        <th></th>
        <th>School</th>
        <th>Class</th>
        <th>Parent ID</th>
        <th>User ID</th>
        <th>Serial Number</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Grade</th>
        <th>Highest Track Passed</th>
        <th>Parent Name</th>
        <th>Parent Email</th>
        <th>Parent Phone</th>
    </tr>
    <?php
    $i = 1;
    foreach ($reg as $row) {
        $track = $tracks[$row['user_id']];
        if ($track == 'none') continue;
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        echo "<tr><td>" . $i++ . "</td><td>" . $row['school_name'] . "</td><td>" . $grade . "</td><td>" . $row['parent_id'] .
            "</td><td>" . $row['user_id'] . "</td><td>" . $row['user_serial'] .  "</td><td>" . $row['first'] . "</td><td>" .
            $row['last'] . "</td><td>" . $row['class_grade'] . "</td><td>" . $track . "</td>";
        $adminInfo = $admins[$row['parent_id']];
        $phone = $adminInfo['admin_phone_mobile'] ?? '';
        $phone .= $adminInfo['admin_phone_work'] ? $phone == '' ? $adminInfo['admin_phone_work'] : ("<br />" . $adminInfo['admin_phone_work']) : '';
        $phone .= $adminInfo['admin_phone_home'] ? $phone == '' ? $adminInfo['admin_phone_home'] : ("<br />" . $adminInfo['admin_phone_home']) : '';
        echo "<td>" . ($adminInfo['first'] . ' ' . $adminInfo['last']) . "</td><td>" . $adminInfo['admin_email'] . "</td><td>" .
            $phone . "</td></tr>";
    }
    ?>
</table>
</body>
</html>
