<?php
function get_auctions( $greg_start, $greg_end, $school_id = false ){
    $query = mysql_query(
        " SELECT user_id, auction_id, prize_id, quantity, prize_name, auction_name, users.school_id, "
        ." shipped, shipment_id, shipments.name as shipment_name "
        ." FROM auction_winners JOIN auctions USING (auction_id) JOIN prizes_auction USING (prize_id) "
        ." LEFT JOIN shipments USING (shipment_id)"
        ." JOIN users using ( user_id ) WHERE auction_ran = 1 "
        ." AND auction_run_date >= '$greg_start' AND auction_run_date <= '$greg_end' "
        .( $school_id ? " AND users.school_id = '$school_id' " : "" )
    );
    $result = [];
    while( $row = mysql_fetch_assoc( $query ) ){
        $ajax = "auction:".$row['user_id'].":".$row['auction_id'].":".$row['prize_id'];
        $result[$row['school_id']][$row['user_id']][] = [
            'shipped' => $row['shipped'],
            'item' => $row['prize_name']." (".$row['auction_name']." Auction)",
            'ajax' => $ajax,
            'shipment' => $row['shipment_name'] ? $row['shipment_name'] : "N/A",
            'shipment_id' => $row['shipment_id']
        ];
    }
    // echo "<pre>";
    // print_r( $result ); die();
    return $result;
}

function mark_auction( $shipped, $user_id, $auction_id, $prize_id ){
    $shipped = $shipped ? "1" : "0";
    $query = mysql_query(
         " UPDATE auction_winners SET shipped='$shipped', shipment_id = NULL WHERE user_id = '$user_id' "
        ." AND auction_id = '$auction_id' AND prize_id='$prize_id' "
    );
    return !!$query;
}