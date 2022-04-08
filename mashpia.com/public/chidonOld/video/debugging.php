<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

function getFinalMarks() {
    $final_marks = [];
    $sql = "select * from th_chidon_finals where year = 5782";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $final_marks[$row['user_id']] = $row;
    }
    return $final_marks;
}

function getAward($child) {
    $final_marks = getFinalMarks();

    $tracks = [
        1   => 'yesod',
        2   => 'yediah',
        3   => 'havonah',
        4   => 'iyun'
    ];
    $finals = [
        'yesod'     => 20,
        'yediah'    => 40,
        'havonah'   => 60,
        'iyun'      => 80
    ];
    $needed = [
        'yesod'     => 60,
        'yediah'    => 70,
        'havonah'   => 80,
        'iyun'      => 90
    ];
    $awards = [
        'yesod'     => 'certificate',
        'yediah'    => 'plaque',
        'havonah'   => 'medal / plaque',
        'iyun'      => 'trophy / medal / plaque'
    ];

    $highest_track = $child['highest_track'];
    echo "Child: " . $child['user_id'] . "<br />";
    echo "Highest Track: " . $highest_track . "<br />";
    // find out if award is same as before final or not
    $award = false;
    $key = array_search($highest_track, $tracks);
    if ($key !== false) {
        // go down from key to find where the child is holding
        if (isset($final_marks[$child['user_id']])) {
            $row = $final_marks[$child['user_id']];
            $score = 0;
            for ($i = 1; $i <= $key; $i++) {
                $level = 'level_' . $i;
                if ($row[$level]) {
                    $score += $row[$level];
                }
            }
            echo "Score: " . $score . "<br />";
            for ($i = 1; $i <= $key; $i++) {
                $divide_by = $finals[$tracks[$i]];
                echo "Divide By: " . $divide_by . "<br />";
                $final_score = number_format(($score / $divide_by) * 100, 2);
                echo "Final Score: " . $final_score . "<br />";
                echo "Needed: " . $needed[$tracks[$i]] . "<br /><br />";
                if ($final_score >= $needed[$tracks[$i]]) {
                    $award = $tracks[$i];
                }
            }
        }
    }
    if ($award) return array_search($award, $tracks);
    else return '';
}

$users = [7763230, 7754010, 7757183, 7756107, 7772704];
foreach ($users as $user) {
    $sql = "select user_id, highest_track from users u 
            join th_chidon_info using (user_id) 
            where user_serial = " . $user;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $award = getAward($row);
    echo "Award for " . $user . ": " . $award . "<br />";
}