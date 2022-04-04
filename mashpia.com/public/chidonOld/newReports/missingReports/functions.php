<?php
function getUsers() {
    global $schools;

    $info = [];
    $sql = "select * from users u 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id 
            where c.class_grade in (4,5,6,7,8)";
    if (! empty($schools)) $sql .= " and s.school_id in (" . implode(',', array_keys($schools)) . ")";
    $sql .= " order by school_id, class_grade, class_sub, last, first";
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
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $users[$row['user_id']] = $row;
    }
    return $users;
}

function getRecruitmentPrizes() {
    $prizes = [];
    $sql = "select * from chidon_credit_prizes";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$row['credits']] = $row['prize'];
    }
    return $prizes;
}

function getRecruitments() {
    global $year;

    $recruits = [];
    $sql = "select recruited_by, count(*) as total from th_chidon where year = " . $year;
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

function getGift($user) {
    $gift = '';
    if ($user['date_paid'] > 0) {
        switch ($user['gender']) {
            case 'M':
                $gift = 'Yarmulka - Size: ' . $user['yarmulka'];
                break;
            case 'F':
                $gift = 'Chidon Necklace';
                break;
        }
    }
    return $gift;
}




