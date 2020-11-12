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