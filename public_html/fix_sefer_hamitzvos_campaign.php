<?php
require 'db.php';

$users = array();
$sql = "select * from medal_marks where subject_id = 21";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[$row['user_id']][] = $row;
}

// need to find all extra medals given and change the subject_id to 106
// then need to change sefer hamitzvos medals_subjects to be 15, 20, 25, etc.
// then need to update medal_updater to remove "continue" clause in updater loop for subject_id 21

$new = array(
    1   =>  15,
    2   =>  35,
    3   =>  60,
    4   =>  90,
    5   =>  125,
    6   =>  165,
    7   =>  210,
    8   =>  260,
    9   =>  315,
    10  =>  375
);

$correctMedals = array();
$sql = "select user_id, count(*) as total from date_tasks_mission_marks where subject_id = 21 group by user_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $num = $row['total'];
    foreach ($new as $medal => $total) {
        if ($total > $num) {
            $correctMedals[$row['user_id']] = --$medal;
            break;
        }
    }
}
echo "<pre>";
//print_r($correctMedals);

$extra = array();
foreach ($correctMedals as $user => $medal) {
    $sql = "select * from medal_marks where user_id = " . $user . " and subject_id = 21 and medal_ord > " . $medal;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $extra[$user][] = $row['medal_ord'];
    }
}
//print_r($extra);

foreach ($extra as $user => $changes) {
    foreach ($changes as $medal) {
        $sql = "update medal_marks set subject_id = 106 where subject_id = 21 and user_id = " . $user . " and medal_ord = " . $medal;
        //echo $sql . "<br />";
        mysql_query($sql);
    }
}
echo "done.";