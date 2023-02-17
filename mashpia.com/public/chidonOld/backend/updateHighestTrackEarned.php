<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

if ($admin_user['auth'] !== 'super') {
    echo "No Permission";
    exit;
}

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

$year = GlobalSettings::getChidonYear();

$info = [];
foreach ($schools as $school_id => $name) {
    $sql = "select u.user_id, u.school_id, u.class_id, u.user_serial, u.first, u.last, s.school_name, c.class_grade,    
                c.class_sub, tc.date_paid, tc.khk_trip, tc.th_chidon_id, tc.test_type, tc.reward_type  
            from users u 
            join schools s using (school_id) 
            join classes c on u.class_id = c.class_id 
            join th_chidon tc using (user_id) 
            where tc.year = " . $year . " and u.school_id = " . $school_id . " 
            order by school_name, date_paid, class_grade, class_sub, last, first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[] = $row;
    }
}

$qrys = [];
$test = new ChidonTests();
$tracks = $test->getTypes();

foreach ($info as $row) {
    $highestTrack = $test->getHighestTrackPassed($row)['highest_track'];
    $rewardType = $row['reward_type'];
    if ($rewardType != 'highest track passed') {
        if ($highestTrack == '') $highestTrack = $rewardType;
        else {
            $indexes = array_keys($tracks);
            $key1 = array_search($highestTrack, $indexes);
            $key2 = array_search($rewardType, $indexes);
            if ($key2 > $key1) $highestTrack = $rewardType;
        }
    }

    $track = $highestTrack ? $tracks[$highestTrack] : 'none';
    if ($track !== 'none') {
        $qry = "insert ignore into th_chidon_info 
                set year = $year, 
                user_id = " . $row['user_id'] . ", 
                highest_track = '" . strtolower($track) . "' 
                on duplicate key update highest_track = '" . strtolower($track) . "'";
        $qrys[] = $qry;
    }
}

mysql_query('set autocommit=0');
mysql_query('begin');
$success = true;
foreach ($qrys as $qry) {
    if (! mysql_query($qry)) {
        $success = false;
        echo mysql_error() . "<br />" . $qry;
        break;
    }
}
if ($success) mysql_query("commit");
else mysql_query("rollback");
mysql_query('set autocommit=1');
echo "done";