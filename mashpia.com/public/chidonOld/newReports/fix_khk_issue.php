<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$ct = new ChidonTests();
$types = $ct->getTypes();

$users = [];
$sql = "select u.user_id, u.class_id, u.school_id, tc.th_chidon_id, tc.reward_type  
        from th_chidon tc 
        join users u using (user_id) 
        where tc.year = 5782 and tc.khk_trip = 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row;
}

$remove = [];
foreach ($users as $row) {
    $child = [
        'user_id'   => $row['user_id'],
        'class_id'  => $row['class_id'],
        'school_id' => $row['school_id'],
        'th_chidon_id'  => $row['th_chidon_id'],
        'reward_type'   => $row['reward_type']
    ];
    $highestTrack = $ct->getHighestTrackPassed($child)['highest_track'];
    $rewardType = $child['reward_type'];
    if ($rewardType != 'highest track passed') {
        $indexes = array_keys($types);
        $key1 = array_search($highestTrack, $indexes);
        $key2 = array_search($rewardType, $indexes);
        if ($key1 && $key2) $highestTrack = $key1 >= $key2 ? $highestTrack : $rewardType;
        else if ($key2) $highestTrack = $rewardType;
    }
    $track = ucwords($types[$highestTrack]);
    if (! ($track == 'Havonah' || $track == 'Iyun')) {
        $remove[] = $child['user_id'];
    }
}

echo "<pre>"; print_r($remove); echo "</pre>";
foreach ($remove as $user_id) {
    $sql = "update th_chidon set khk_trip = 0 where year = 5782 and user_id = " . $user_id;
    mysql_query($sql);
}
echo "done.";