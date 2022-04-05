<?php
function getUsers() {
    global $schools;

    $info = [];
    $sql = "select * from users u 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id 
            where c.class_grade in (4,5,6,7,8)";
    if (! empty($schools)) $sql .= " and u.school_id in (" . implode(',', array_keys($schools)) . ")";
    $sql .= " order by u.school_id, class_grade, class_sub, last, first";
//    echo $sql . "<br />";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$row['school_id']][] = $row;
    }
    return $info;
}

function getChidonUsers() {
    global $year, $schools;

    $users = [];
    $sql = "select * from th_chidon where year = " . $year . " and date_paid > 0";
    if (! empty($schools)) $sql .= " and school_id in (" . implode(',', array_keys($schools)) . ")";
//    echo $sql . "<br />";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $users[$row['user_id']] = $row;
    }
    return $users;
}

function getChidonUsersLastYr() {
    global $year, $schools;

    $lastYr = $year - 1;
    $users = [];
    $sql = "select u.first, u.last, u.user_id, u.user_serial, u.gender, tc.* 
            from users u 
            join th_chidon tc using (user_id) 
            where tc.year = " . $lastYr;
    if (! empty($schools)) $sql .= " and u.school_id in (" . implode(',', array_keys($schools)) . ")";
//    echo $sql . "<br />";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $users[$row['user_id']] = $row;
    }
    return $users;
}

function getRecruitmentPrizes() {
    global $year;

    $prizes = [];
    $sql = "select * from chidon_credit_prizes where year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$row['credits']] = $row;
    }
    return $prizes;
}

function getRecruitments() {
    global $year;

    $recruits = [];
    $sql = "select recruited_by, count(*) as total from th_chidon where year = " . $year . " group by recruited_by";
//    echo $sql . "<br />";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $recruits[$row['recruited_by']] = $row['total'];
    }
    return $recruits;
}

function getSurpriseGifts() {
    global $year;

    $gifts = [];
    $lastYr = $year - 1;
    $sql = "select user_id from th_chidon where year = " . $lastYr . " and surprise_gift = 1";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $gifts[] = $row['user_id'];
    }
    return $gifts;
}

function getPrizes() {
    global $year;

    $prizes = [];
    $sql = "SELECT 
                *
            FROM
                chidon_user_prizes cup
                    JOIN
                chidon_prizes cp USING (prize_id)
            WHERE
                cup.year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$row['user_id']][] = $row;
    }
    return $prizes;
}

function getGift($user, $chidonInfo) {
    $gift = '';
    switch ($user['gender']) {
        case 'M':
            $gift = 'Yarmulka - Size: ' . $chidonInfo['yarmulka'];
            break;
        case 'F':
            $gift = 'Chidon Necklace';
            break;
    }
    return $gift;
}

function getAwards() {
    global $year;

    // qry to get all kids that should get any award
    $info = [];
    $sql = "
        SELECT
            s.school_name, u.user_id, u.class_id, u.school_id, u.user_serial, u.first_he, u.last_he, u.gender, 
            c.class_grade, c.class_sub, tci.highest_track, tcf.*
        FROM 
            users u 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id 
            join th_chidon tc using (user_id) 
            join th_chidon_info tci on tc.year = tci.year and tc.user_id = tci.user_id
            join th_chidon_finals tcf on tc.year = tcf.year and tc.user_id = tcf.user_id
        WHERE
            tc.year = $year
            and tc.date_paid > 0";
    //echo $sql . "<br />"; exit;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[$row['user_id']] = $row;
    }

    foreach ($info as &$row) {
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
        $highest_track = $row['highest_track'];

        // find out what award should be
        $row['award'] = '';
        $key = array_search($highest_track, $tracks);
        if ($key !== false) {
            $score = 0;
            // go down from key to find where the child is holding
            for ($i = $key; $i > 0; $i--) {
                $level = 'level_' . $i;
                if ($row[$level]) {
                    $score += $row[$level];
                }
            }
            for ($i = 1; $i <= $key; $i++) {
                $divide_by = $finals[$tracks[$i]];
                $final_score = number_format(($score / $divide_by) * 100, 2);
                if ($final_score >= $needed[$tracks[$i]]) {
                    $row['award'] = $tracks[$i];
                }
            }
        }
    }
    return $info;
}

function getCelebrationItems() {
    global $year;

    $items = [];
    $sql = "select * from extra_purchases p 
            left join purchase_addresses pa using (purchase_id) 
            where p.year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $items[$row['admin_id']][] = $row;
    }
}

