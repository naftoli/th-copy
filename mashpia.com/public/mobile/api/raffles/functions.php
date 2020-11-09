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