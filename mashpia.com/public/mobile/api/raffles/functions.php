<?php
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
        $info['name'] = $row['name'];
    }
    return $info;
}

function getPrizeInfo( $raffleID ) {
    $info = [];
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
    return $info;
}

function checkTasks( $user_id, $start, $end ) {
    $grid_id = 13012;
    $sql = "select count(distinct mark_date) as total from date_tasks_marks dtm
            join date_tasks dt using (date_task_id) 
            where dtm.user_id = " . $user_id . " 
            and dt.grid_id = " . $grid_id . " 
            and dtm.mark_date >= " . $start . " 
            and dtm.mark_date <= " . $end;
    $result = mysql_query($sql);
    return mysql_fetch_assoc($result)['total'];
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
            ORDER BY end_date desc";
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
        while ( $end-- >= $start ) { // check all days in week
            $days[] = [
                'completed' => checkTasks( $user_id, $end, $end ) > 0 ? true : false,
                'past'      => $row['date_ran'] > 0 ? true : false
            ];
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
    $heMonths = ['','תשרי','חשון','כסלו','טבת','שבט','אדר','אדר ב','ניסן','אייר','סיון','תמוז','אב','אלול'];
    $months = ['', 'Tishrei', 'Cheshvon', 'Kislev', 'Teves', 'Shevat', 'Adar', 'Adar 2', 'Nissan', 'Iyar', 'Sivan', 'Tamuz', 'Av', 'Elul'];

    // get raffles
    $raffles = [];
    if ($type == 'monthly') {
        $todayHe = explode('/', jdtojewish( unixtojd() ));
        $sql_raffles = "select * from raffles 
                        where type = '" . $type . "' 
                        and year = " . $todayHe[2] . "  
                        order by run_date desc";
    } else {
        $today = unixtojd();
        $sql_raffles = "SELECT * FROM raffles
			WHERE type = '" . $type . "'
			AND start_date <= " . $today . "
            AND end_date >= " . $today;
    }
    $result_raffles = mysql_query($sql_raffles);
    while ($row = mysql_fetch_assoc($result_raffles)) {
        $raffles[] = $row;
    }

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
        $total = checkTasks($user_id, $start, $end);

        $info = [];
        while ($end-- > $start) {
            $past = $end < unixtojd() ? true : false;
            $heDate = explode('/', jdtojewish($end));
            $heMonth = $months[$heDate[0]];
            $info[$heMonth][] = [
                'completed' => checkTasks($user_id, $end, $end) > 0 ? true : false,
                'past' => $past
            ];
        }
        $result[] = [
            'raffleNumber'  => $idx + 1,
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
        $prize = getPrizeInfo($raffle_id);
        if (!empty($prize)) {
            $raffleInfo = [];
            $sql_raffles = "
                SELECT 
                    u.first,
                    u.last,
                    u.gender,
                    s.school_name,
                    MAX(rank_ord) AS rank,
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
                            'rank' => $ranks[$row['rank']],
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
                    'thumb' => $prize['thumb']
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