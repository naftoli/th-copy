<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

function getRaffleInfo( $type ) {
    $info = [];
    $today = unixtojd();
    $sql = "SELECT * FROM raffles
			WHERE type = '" . $type . "'
			AND start_date <= " . $today . "
            AND end_date >= " . $today;
    $result = mysql_query($sql);
    if ($row = mysql_fetch_assoc($result)) {
        $info['raffle_id'] = $row['raffle_id'];
        $info['daysLeft'] = $row['end_date'] - $today;
        $info['name'] = $row['name'];
        $info['year'] = $row['year'];
        $info['start'] = $row['start_date'];
        $info['end'] = $row['end_date'];
        $info['run_date'] = $row['run_date'];
        $info['days_of_tasks'] = $row['days_of_tasks'];
    } else {
        // get last raffle that is in system for this type
        $sql = "SELECT * FROM raffles
                WHERE type = '" . $type . "'
                AND start_date <= " . $today . " 
                order by start_date desc
                limit 1";
        $result = mysql_query($sql);
        if ($row = mysql_fetch_assoc($result)) {
            $info['raffle_id'] = $row['raffle_id'];
            $info['daysLeft'] = $row['end_date'] - $today;
            $info['name'] = $row['name'];
            $info['year'] = $row['year'];
            $info['start'] = $row['start_date'];
            $info['end'] = $row['end_date'];
            $info['run_date'] = $row['run_date'];
            $info['days_of_tasks'] = $row['days_of_tasks'];
        }
    }
    return $info;
}

function getPrizeInfo( $raffleID, $type = 'weekly' ) {
    $info = [];
    if ($type == 'weekly') {
        $sql = "SELECT name, picture, thumbnail
                FROM prizes p 
                JOIN raffle_prizes rp USING (prize_id) 
                WHERE rp.raffle_id = " . $raffleID;
        $result = mysql_query($sql);
        if ($row = mysql_fetch_assoc($result)) {
            $info['name'] = $row['name'];
            $info['pic'] = $row['picture'];
            $info['thumb'] = $row['thumb'];
        }
    } else if ($type == 'monthly') {
        $sql = "SELECT prize_name, prize_image_id
                FROM prizes_auction p 
                JOIN raffles_monthly rm USING (prize_id) 
                WHERE rm.raffle_id = " . $raffleID;
        $result = mysql_query($sql);
        if ($row = mysql_fetch_assoc($result)) {
            $info['name'] = $row['prize_name'];
            $info['pic'] = '/file_view.php?id=' . $row['prize_image_id'];
        }
    }
    return $info;
}

function checkTasks( $user_id, $start, $end, $type ) {
    $grid_id = 13012;
//    if ($type == 'weekly') $rollover = 2459167;
//    else if ($type == 'monthly') $rollover = 2459171;
//    else if ($type == 'yearly') $rollover = 2459171;

//    if ($start >= $rollover) { // simple calculation
        $sql = "SELECT COUNT(distinct mark_date) AS total FROM date_tasks_marks dtm
                JOIN date_tasks dt USING (date_task_id) 
                WHERE dtm.user_id = " . $user_id . " 
                AND dt.grid_id = " . $grid_id . " 
                AND dtm.mark_date >= " . $start . " 
                AND dtm.mark_date <= " . $end;
//        echo $sql . "<br />";
        $result = mysql_query($sql);
        return mysql_fetch_assoc($result)['total'];
//    } else {
//        // find all tasks marked in date_tasks_marks up to rollover date
//        // then find all tasks marked using grid id for after rollover date
//        // then add the two numbers together
//        $sql1 = "SELECT COUNT(distinct mark_date) AS total FROM date_tasks_marks dtm
//                JOIN date_tasks dt USING (date_task_id)
//                WHERE (daily_task = 1 OR (daily_task = 0 AND (dt.quantity IS NULL OR (dt.quantity IS NOT NULL AND dtm.done_qty >= dt.quantity))))
//                AND dtm.user_id = " . $user_id . "
//                AND dtm.mark_date < " . $rollover . "
//                AND dtm.mark_date >= " . $start;
//        $sql2 = "SELECT COUNT(distinct mark_date) AS total FROM date_tasks_marks dtm
//                JOIN date_tasks dt USING (date_task_id)
//                WHERE dtm.user_id = " . $user_id . "
//                AND dt.grid_id = " . $grid_id . "
//                AND dtm.mark_date >= " . $rollover . "
//                AND dtm.mark_date <= " . $end;
//        $result1 = mysql_query($sql1);
//        $result2 = mysql_query($sql2);
//        if ($user_id == 5455) {
//            echo $sql1;
//            echo "\n" . $sql2;
//            exit;
//        }
//        return intval(mysql_fetch_assoc($result1)['total']) + intval(mysql_fetch_assoc($result2)['total']);
//    }
}

function getRaffleHistory( $type, $user_id ) {
    $todayHe = explode('/', jdtojewish( unixtojd() ));
    $year = $todayHe[2];

    $history = [];
    $end = unixtojd() + 6;
    $sql = "SELECT 
                r.raffle_id, r.name, r.date_ran, r.start_date, r.end_date, p.name as prize
            FROM
                raffles r
                    JOIN
                raffle_prizes rp USING (raffle_id)
                    JOIN
                prizes p USING (prize_id)
            WHERE
                type = '$type' AND year = $year
                    AND end_date <= $end
            ORDER BY start_date DESC";
    $result = mysql_query($sql);
    while ( $row = mysql_fetch_assoc($result) ) {
        // check if user won
        $won = false;
        $winnerSql = "SELECT * FROM raffle_winners WHERE raffle_id = " . $row['raffle_id'] . " AND user_id = " . $user_id;
        $winnerRes = mysql_query($winnerSql);
        if ( mysql_num_rows($winnerRes) ) $won = true;
        // find out which days were marked
        $days = [];
        $start = $row['start_date'];
        $end = $row['end_date'];
        while ( $start <= $end ) { // check all days in week
            $days[] = [
                'completed' => checkTasks($user_id, $start, $start, $type) > 0 ? true : false,
                'past'      => $start < unixtojd() ? true : false
            ];
            $start++;
        }
        $history[] = [
            'parsha'    => $row['name'],
            'prize'     => $row['prize'],
            'won'       => $won,
            'days'      => $days
        ];
    }
    return json_encode($history);
}

function getDailyTaskInfo( $user_id, $type ) {
    $result = [];
    $heMonths = ['','תשרי','חשון','כסלו','טבת','שבט','אדר','אדר','ניסן','אייר','סיון','תמוז','אב','אלול'];
    $months = ['', 'Tishrei', 'Cheshvon', 'Kislev', 'Teves', 'Shevat', 'Adar', 'Adar', 'Nissan', 'Iyar', 'Sivan', 'Tamuz', 'Av', 'Elul'];

    // get raffles
    $raffles = [];
    if ($type == 'weekly') {
        $today = unixtojd();
        $sql_raffles = "SELECT * FROM raffles
			WHERE type = '" . $type . "'
			AND start_date <= " . $today . "
            AND end_date >= " . $today;
    } else {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
        $year = GlobalSettings::getCurrentYear();
        $sql_raffles = "select * from raffles 
                        where type = '" . $type . "' 
                        and year = " . $year . " 
                        and start_date <= " . unixtojd() . "
                        order by run_date desc";
    }
    $result_raffles = mysql_query($sql_raffles);
    while ($row = mysql_fetch_assoc($result_raffles)) {
        $raffles[] = $row;
    }
    $totalRaffles = count($raffles);

    foreach ($raffles as $idx => $raffle) {
        $start = $raffle['start_date'];
        $end = $raffle['end_date'];
        $year = $raffle['year'];
        $run_date = $raffle['run_date'];

        $startHe = explode('/', jdtojewish($start));
        $endHe = explode('/', jdtojewish($end));

        $origin = new DateTime();
        $target = new DateTime($run_date);
        $interval = $origin->diff($target);
        $days = $interval->days;
        if ($origin > $target) $days = 0; // if we are past the run date set days to 0
        $total = checkTasks($user_id, $start, $end, $type);

        // for yearly date return hebrew date
        // not working; causing the json_encode function not to work
//        if ($type == 'yearly') {
//            $dateOnly = explode(' ', $run_date);
//            $dateInfo = explode('-', $dateOnly[0]);
//            $jd = gregoriantojd($dateInfo[1], $dateInfo[2], $dateInfo[0]);
//            $run_date = jdtojewish($jd, true, CAL_JEWISH_ADD_ALAFIM_GERESH + CAL_JEWISH_ADD_GERESHAYIM);
//        }

        // check for leap year
        $m = array(3, 6, 8, 11, 14, 17, 19);
        $meuberet = in_array(($year % 19), $m);

        $info = [];
        $order = [];
        while ($start <= $end) {
            $heDate = explode('/', jdtojewish($start));
            $month = $months[$heDate[0]];
            if ($meuberet && $month == 7) $month = 'Adar II';
            if (!in_array($month, $order)) $order[] = $month;

            $info[$month][] = [
                'completed' => checkTasks($user_id, $start, $start, $type) > 0 ? true : false,
                'past'      => $start <= unixtojd() ? true : false
            ];
            $start++;
        }
        $result[] = [
            'orderBy'       => $order,
            'raffleNumber'  => $totalRaffles--,
            'startMonth'    => $heMonths[$startHe[0]],
            'endMonth'      => $heMonths[$endHe[0]],
            'year'          => $year,
            'daysTillDrawing' => $days,
            'daysCompleted' => $total,
            'months'        => $info,
            'raffleDate'    => $run_date
        ];
    }
    return $result;
}

function getWinnersInfo( $type, $year ) {
    $result = [];
    // keep array of rank names
    $ranks = [];
    $sql_ranks = "SELECT * FROM ranks";
    $result_ranks = mysql_query($sql_ranks);
    while ($row = mysql_fetch_assoc($result_ranks)) {
        $ranks[$row['rank_ord']] = $row['rank_name'];
    }
    // find the raffles we need to show
    $raffles = [];
    $sql_raffles = "SELECT * FROM raffles WHERE type = '" . $type . "' AND year = " . $year;
    $result_raffles = mysql_query($sql_raffles);
    $i = 1;
    while ($row = mysql_fetch_assoc($result_raffles)) {
        $raffles[$i++] = $row['raffle_id'];
    }

    foreach ($raffles as $id => $raffle_id) {
        $prize = getPrizeInfo($raffle_id, $type);
        if (!empty($prize)) {
            $raffleInfo = [];
            $sql_raffles = "
                SELECT 
                    u.user_id, 
                    u.first,
                    u.last,
                    u.gender,
                    s.school_name,
                    c.class_grade,
                    c.class_sub
                FROM
                    raffle_winners rw
                        JOIN
                    users u USING (user_id)
                        JOIN
                    schools s USING (school_id)
                        JOIN
                    classes c ON c.class_id = u.class_id
                        JOIN
                    rank_marks USING (user_id)
                WHERE
                    raffle_id = " . $raffle_id;
            $result_raffles = mysql_query($sql_raffles);
            if (mysql_num_rows($result_raffles) > 0) {
                while ($row = mysql_fetch_assoc($result_raffles)) {
                    // get child's rank
                    $sql_rank = "select MAX(rank_ord) AS rank from rank_marks where user_id = " . $row['user_id'];
                    $res_rank = mysql_query($sql_rank);
                    $row_rank = mysql_fetch_assoc($res_rank);
                    $rank = $row_rank['rank'];
                    $gender = '';
                    if ($row['gender'] == 'M') {
                        $gender = 'boys';
                    } else if ($row['gender'] == 'F') {
                        $gender = 'girls';
                    }
                    $raffleInfo[$id] = [
                        $gender => [
                            'name' => $row['first'] . ' ' . $row['last'],
                            'grade' => $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']),
                            'rank' => $ranks[$rank],
                            'school' => $row['school_name']
                        ]
                    ];
                }
            } else {
                $raffleInfo[$id] = [];
            }

            $result[$id] = [
                'prize' => [
                    'name' => $prize['name'],
                    'img' => $prize['pic'],
                    'thumb' => isset($prize['thumb']) ? $prize['thumb'] : ''
                ],
                'year' => $year,
                'raffles' => $raffleInfo
            ];
        } else {
            $result[$id] = [];
        }
    }
    return $result;
}
