<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

// get info for report: serial number, name, school, class, avg per track, highest track passed, highest eligible reward,
// track registered for
$info = [];
foreach ($schools as $school_id => $name) {
    $sql = "select u.user_id, u.school_id, u.class_id, u.user_serial, u.first, u.last, s.school_name, c.class_grade,    
                c.class_sub, tc.date_paid, tc.khk_trip, tc.th_chidon_id, tc.test_type, tc.reward_type, tci.highest_track  
            from users u 
            join schools s using (school_id) 
            join classes c on u.class_id = c.class_id 
            join th_chidon tc using (user_id) 
            left join th_chidon_info tci on tc.year = tci.year and tc.user_id = tci.user_id 
            where tc.year = " . $year . " and u.school_id = " . $school_id . " 
            order by school_name, date_paid, class_grade, class_sub, last, first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[] = $row;
    }
}
// echo "<pre>"; print_r($info); echo "</pre>"; exit;
$endDates = [
    1 => 2459894,
    2 => 2459932,
    3 => 2459975
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Eligibility / Registered Report</title>
    <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
            padding: 6px;
            font-size: 12px;
            border-bottom: 1px solid grey;
        }
    </style>
</head>
<body>
    <?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
    <h1>Eligibility / Registered Report</h1>
    <table>
        <tr>
            <th>School</th>
            <th>Class</th>
            <th>Serial Number</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Track Eligible for</th>
            <th>Eligible Rewards</th>
        </tr>
        <?php
        $test = new ChidonTests();
        $rewards = [
            'Yesod'     => 'Sweater, Gifts & Yesod Final',
            'Yediah'    => 'Sweater, Gifts, Prizes, & Yediah Final',
            'Havonah'   => 'Sweater, Gifts, Trip, & Havonah Final',
            'Iyun'      => 'Sweater, Gifts, Trip & Iyun Final',
            'Khk Trip'  => 'Sweater, Gifts, Trip & '
        ];
        $i = 0;
        foreach ($info as $row) {
            $track = ucwords($row['highest_track']);
            if (empty($track)) {
                // find out which test number to pass to the highest track function
                $today = unixtojd();
                foreach ($endDates as $num => $date) {
                    if ($date >= $today) break;
                }
                $ct = new ChidonTests();
                $highest = $ct->getHighestTrackPassed($row, $num)['highest_track'];
                $types = $ct->getTypes();
                if (! empty($highest)) $track = $types[$highest];
            }
            $highestTrack = $track;
            if (intval($row['class_grade']) === 8 && (in_array(strtolower($highestTrack), ['havonah', 'iyun']))) $highestTrack = 'Khk Trip';
            $reward = empty($highestTrack) ? 'none' : $rewards[$highestTrack];
            if ($highestTrack == 'Khk Trip') $reward .= ucwords($track) . ' Final';
            $school = $schools[$row['school_id']];
            $class = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
            $paid = $row['date_paid'] > 0 ? 'yes' : 'no';
            $khk = $row['khk_trip'] > 0 ? 'yes' : '';
            echo "<tr><td>" . $school . "</td><td>" . $class . "</td><td>" . $row['user_serial'] .  "</td><td>" .
                $row['first'] . "</td><td>" . $row['last'] . "</td><td>" . $highestTrack . "</td><td>" . $reward . "</td></tr>";
        }
        ?>
    </table>
</body>
</html>
