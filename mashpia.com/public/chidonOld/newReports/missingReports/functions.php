<?php
function getUsers() {
    global $schools;

    $info = [];
    $sql = "select * from users u 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id ";
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
    $sql = "select * from th_chidon tc 
            join schools s using (school_id) 
            join th_chidon_info tci using (user_id, year)
            where tc.year = " . $year . " and tc.date_paid > 0";
    if (! empty($schools)) $sql .= " and school_id in (" . implode(',', array_keys($schools)) . ")";
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

function getRecruitmentPrizesById() {
    global $year;

    $prizes = [];
    $sql = "select * from chidon_credit_prizes where year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$row['chidon_credit_prize_id']] = $row;
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
function getChidonPrizes() {
    global $year;

    $prizes = [];
    $sql = "select * from chidon_prizes where year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$row['prize_id']] = $row;
    }
    return $prizes;
}

function getGift($user) {
    $gift = '';
    switch ($user['gender']) {
        case 'M':
            $gift = 'Yarmulka - Size: ' . $user['yarmulka'];
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
            // get marks
            for ($i = 1; $i <= $key; $i++) {
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

    // find out which child it should be assigned to
    $children = [];
    foreach ($items as $admin_id => $more) {
        $sql = "SELECT 
                    user_id
                FROM
                    th_chidon tc
                        JOIN
                    users u USING (user_id)
                        JOIN
                    classes c USING (class_id)
                WHERE
                    tc.year = $year AND tc.parent_id = $admin_id
                        AND tc.date_paid > 0
                ORDER BY c.class_grade DESC
                LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            $row = mysql_fetch_assoc($result);
            $children[$row['user_id']] = $more;
        }
    }
    return $children;
}

function getMissingItems($user_id) {
    global $year;
    $sql = "select * from chidon_missing_items where user_id = " . $user_id . " and year = " . $year;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        // make sure there's no characters that can trip up json_decode
        $json = preg_replace('/[[:cntrl:]]/', '', $row['items']);
        return json_decode($json);
    }
    return [];
}

function getAllMissingItems() {
    global $year;

    $sql = "select * from chidon_missing_items where year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        // make sure there's no characters that can trip up json_decode
        $json = preg_replace('/[[:cntrl:]]/', '', $row['items']);
        $items[$row['user_id']] = json_decode($json);
    }
    return $items;
}

function getMissingUsers($items) {
    global $year;

    $users = [];
    $user_ids = array_keys($items);
    $sql = "select * from users u 
            join schools s using (school_id) 
            join classes c on c.class_id = u.class_id 
            left join th_chidon tc using (user_id)
            where u.user_id in (" . implode(',', $user_ids) . ") 
            and (tc.year is null or tc.year = $year or (tc.year = ($year - 1) and tc.surprise_gift = 1)) 
            order by s.school_id, c.class_grade, c.class_sub, u.last, u.first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $users[$row['user_id']] = $row;
    }
    return $users;
}

function parseItem($item, $user) {
    global $recruitmentPrizes;

    $desc = '';
    switch ($item->desc) {
        case 'recruitment_prize':
            foreach ($recruitmentPrizes as $prize) {
                if ($item->value == $prize['chidon_credit_prize_id']) {
                    $desc = "Recruitment Prize: " . $prize['prize'];
                    break;
                }
            }
            break;
        case 'surprise_gift':
            $desc = "Surprise Gift: Chavat Book";
            break;
        case 'chidon_gift':
            $gift = getGift($user);
            $desc = "Gift: " . $gift;
            break;
        case 'chidon_prize':
            $prize = getChidonPrize($item->value);
            $desc = "Prize: " . $prize['name'];
            if ($prize['size']) $desc .= " " . $prize['size'];
            if ($prize['color']) $desc .= " " . $prize['color'];
            break;
        case 'award':
            $desc = "Award: " . $item->value;
            break;
        case 'celeb_item':
            $celeb_item = getCelebItem($item->value);
            if ($celeb_item['name'] == 'sweater') $desc = ucwords($celeb_item['type'] . " " . $celeb_item['name'] . " " . $celeb_item['size']);
            else $desc = "Celebration Box";
            break;
        case 'rebbe_pic_5781':
            $desc = "Rebbe Picture 5781";
            break;
        case 'comments':
            $desc = "Comment: " . str_replace(['u0027', 'u0022'], ["'", "\""], $item->value);
            break;
    }
    return $desc;
}

function getItemDesc($item, $desc, $user_id = 0) {
    global $recruitmentPrizesById, $chidonPrizes;

    switch ($desc) {
        case 'recruitment_prize':
            $item = $recruitmentPrizesById[$item]['prize'];
            break;
        case 'surprise_gift':
            $item = "Surprise Gift: Chavat Book";
            break;
        case 'chidon_gift':
            if (strpos($item, 'F') !== false) $item = 'Chidon Necklace';
            else {
                $details = explode('-', $item);
                $item = 'Yarmulka Size ' . $details[1];
            }
            break;
        case 'chidon_prize':
            $prize = $chidonPrizes[$item];
            if ($user_id) {
                $he_name = getHeName($item, $user_id);
                $item = $prize['prize_name'] . ' ' . $prize['size'] . ' ' . $prize['color'];
                if (!empty($he_name)) $item .= ' - ' . $he_name;
            } else {
                $item = $prize['prize_name'] . ' ' . $prize['size'] . ' ' . $prize['color'];
            }
            break;
        case 'celeb_item':
            $celeb_item = getCelebItem($item);
            if ($celeb_item['name'] == 'sweater') $desc = ucwords($celeb_item['type'] . " " . $celeb_item['name'] . " " . $celeb_item['size']);
            else $desc = $celeb_item['name'];
            $item = $desc;
            break;
        case 'rebbe_pic_5781':
            $item = 'Rebbe Picture 5781';
            break;
    }
    return $item;
}

function getChidonPrize($id) {
    $sql = "select * from chidon_prizes where prize_id = " . $id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $prize = [
        'name'  => $row['prize_name'],
        'color' => $row['color'],
        'size'  => $row['size']
    ];
    return $prize;
}

function getCelebItem($id) {
    $sql = "select * from extra_purchases where purchase_id = " . $id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $item = [
        'name'  => $row['item'],
        'size'  => $row['size'],
        'type'  => $row['type_of_sweater']
    ];
    return $item;
}

function getItemsBySchool() {
    global $items, $users;

    $sorted = [];
    foreach ($items as $user_id => $details) {
        $user = $users[$user_id];
        $school_id = $user['school_id'];
        foreach ($details as $item) {
            if ($item->desc == 'comments') continue; // not showing it on packing list
            // only show celeb items being shipped to school
            if ($item->desc == 'celeb_item') {
                $id = $item->value;
                $sql = "select * from purchase_addresses where purchase_id = " . $id;
                $result = mysql_query($sql);
                if (mysql_num_rows($result) > 0) continue;
            }
            $sorted[$school_id][$item->desc][] = $item->value;
        }
    }
    return $sorted;
}

function getCelebItemsForParents() {
    global $items;

    $parentItems = [];
    foreach ($items as $details) {
        foreach ($details as $item) {
            if ($item->desc == 'celeb_item') {
                $sql = "select * from purchase_addresses pa 
                        join extra_purchases ep using (purchase_id) 
                        join admins a using (admin_id) 
                        where purchase_id = " . $item->value;
                $result = mysql_query($sql);
                $row = mysql_fetch_assoc($result);
                $parentItems[$row['admin_id']][] = $row;
            }
        }
    }
    return $parentItems;
}

function getItemSummary($schoolItems) {
    $info = [];
    foreach ($schoolItems as $desc => $details) {
        foreach ($details as $value) {
            if (isset($info[$desc][$value])) $info[$desc][$value]++;
            else $info[$desc][$value] = 1;
        }
    }
    return $info;
}

function getItemDetails($school_id) {
    global $items, $users;

    $details = [];
    foreach ($items as $user_id => $more) {
        $user = $users[$user_id];
        if ($school_id != $user['school_id']) continue;
        foreach ($more as $item) {
            if ($item->desc == 'comments') continue;
            $grade = $user['class_grade'] . (empty($user['class_sub']) ? '' : '-' . $user['class_sub']);
            $details[$grade][$user_id][] = getItemDesc($item->value, $item->desc, $user_id);
        }
    }
    return $details;
}

function getHeName($prize_id, $user_id) {
    global $userPrizes;

    $name = '';
    foreach ($userPrizes[$user_id] as $prize) {
        if ($prize['prize_id'] == $prize_id) {
            if (! empty($prize['he_name'])) {
                $name = $prize['he_name'];
                break;
            }
        }
    }
    return $name;
}