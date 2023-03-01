<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "no permission.";
    exit;
}

$year = 5783;

$track_info = [
    1   => 'yesod',
    2   => 'yediah',
    3   => 'havonah',
    4   => 'iyun'
];

$tracks = [];
$sql = "select * from th_chidon_info where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $tracks[$row['user_id']] = $row['highest_track'];
}

$info = [];
$sql = "select * from th_chidon_finals where year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[$row['user_id']] = $row;
}
$qrys = [];
foreach ($info as $user_id => $row) {
    $allowed = true;
    for ($i = 1; $i <= 4; $i++) {
        $level = 'level_' . $i;
        if ($allowed) {
            $track = $tracks[$user_id];
            $key = array_search($track, $track_info);
            if (!$key || $level > $key) $allowed = false;
        }
        if (!$allowed && $row[$level] > 0) {
            $qrys[] = "update th_chidon_finals set " . $level . " = 0 where user_id = " . $user_id . " and year = " . $year;
        }
    }
}

echo "<pre>"; print_r($qrys); echo "</pre>";