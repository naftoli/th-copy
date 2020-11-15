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

function getRaffleHistory( $type, $user_id ) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
    $year = GlobalSettings::getCurrentYear();

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
            ORDER BY end_date";
    $result = mysql_query($sql);
    while ( $row = mysql_fetch_assoc($result) ) {
        // check if user won
        $won = false;
        $past = $row['date_ran'] > 0 ? true : false;
        $winnerSql = "SELECT * FROM raffle_winners WHERE raffle_id = " . $row['raffle_id'] . " AND user_id = " . $user_id;
        $winnerRes = mysql_query($winnerSql);
        if ( mysql_num_rows($winnerRes) ) $won = true;
        // find out which days were marked
        $days = [];
        $start = intval($row['start_date']);
        $end = $start + 6;
        for ( $i = $start; $i <= $end; $i++ ) {
            $days[] = [
                'completed' => checkTasks( $user_id, $i, $i ),
                'past'      => $past
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

function getDailyTaskInfo( $user_id, $type ) {
    $raffle = getRaffleInfo($type);

    $start = $raffle['start'];
    $end = $raffle['end'];
    $heMonths = ['','תשרי','חשון','כסלו','טבת','שבט','אדר','אדר ב','ניסן','אייר','סיון','תמוז','אב','אלול'];
    $months = ['', 'Tishrei', 'Cheshvon', 'Kislev', 'Teves', 'Shevat', 'Adar', 'Adar 2', 'Nissan', 'Iyar', 'Sivan', 'Tamuz', 'Av', 'Elul'];

    $startHe = explode('/', jdtojewish($start));
    $endHe = explode('/', jdtojewish($end));
    $run_date = $raffle['run_date'];

    $origin = new DateTime();
    $target = new DateTime($run_date);
    $interval = $origin->diff($target);
    $diff = $interval->format('a');

    $total = checkTasks($user_id, $raffle['start'], $raffle['end']);

    $info = [];
    while ($start++ <= $end) {
        $past = $start < unixtojd() ? true : false;
        $heDate = explode('/', jdtojewish($start));
        $heMonth = $months[$heDate[0]];
        $info[$heMonth][] = [
            'completed' => checkTasks($user_id, $start, $start),
            'past'      => $past
        ];
    }
    $result = json_encode([
        'raffleNumber'  => $raffle['raffle_id'],
        'startMonth'    => $heMonths[$startHe[0]],
        'endMonth'      => $heMonths[$endHe[0]],
        'year'          => $raffle['year'],
        'daysTillDrawing'   => $diff,
        'daysCompleted' => $total,
        'months'        => $info
    ]);
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
    while ($row = mysql_fetch_assoc($result_raffles)) {
        $raffles[] = $row['raffle_id'];
        if (empty($row['date_ran'])) break;
    }

    foreach ($raffles as $raffle_id) {
        $prize = getPrizeInfo($raffle_id);
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
        while ($row = mysql_fetch_assoc($result_raffles)) {
            $gender = '';
            if ($row['gender'] == 'M') {
                $gender = 'boys';
            } else if ($row['gender'] == 'F') {
                $gender = 'girls';
            }
            $raffleInfo[$raffle_id] = [
                $gender => [
                    'name' => $row['first'] . ' ' . $row['last'],
                    'grade' => $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']),
                    'rank' => $ranks[$row['rank_ord']],
                    'school' => $row['school_name']
                ]
            ];
        }

        $result[$raffle_id] = [
            'prize' => [
                'name' => $prize['name'],
                'img' => $prize['pic'],
                'thumb' => $prize['thumb']
            ],
            'year' => $year,
            'raffles' => $raffleInfo
        ];
    }
    return $result;
}