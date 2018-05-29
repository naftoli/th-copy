<?php
ini_set('display_errors',1);
// give out 1 ticket for 5 prizes to all children who do not have any tickets in the auction but have points
require '../db.php';
//require '../class.points.php';

$auction_id = 79;

function getChildren() {
    $users = array();
    global $auction_id;
    $sql = "select user_id from users 
            where user_registered > 0 
            and school_id > 0 
            and class_id > 0 
            and user_id not in (
                select user_id from auction_user_prizes 
                where auction_id = " . $auction_id . " 
                group by user_id 
            )";
    $result = mysql_query( $sql );
    while ($row = mysql_fetch_assoc( $result )) {
        $user_id = $row['user_id'];
        // find out how many auction points are available
        //$p = new Points( $user_id );
        //$points = $p->getAuctionPoints( 2458007 );
        $sqlPoints = "select SUM(mark_points) points FROM date_tasks_marks WHERE user_id = $user_id and mark_date >= 2458007";
        $resultPoints = mysql_query( $sqlPoints );
        if (mysql_num_rows( $resultPoints ) > 0) {
            $rowPoints = mysql_fetch_assoc( $resultPoints );
            $points = floor($rowPoints['points']);
        } else {
            $points = 0;
        }
        $users[$user_id] = $points;
    }
    return $users;
}

function giveTickets( $children ) {
    global $auction_id;
    $prizes = array(425,431,439,440,445);
    foreach( $children as $user_id => $points) {
        if ($points >= 50) {
            foreach( $prizes as $prize_id ) {
                // insert ticket
                $sql = "insert into auction_user_prizes 
                        set user_id = " . $user_id . ", 
                        prize_id = " . $prize_id . ", 
                        auction_id = " . $auction_id . ", 
                        quantity = 1";
                //echo $sql . "<br />";
                mysql_query( $sql ) or die( mysql_error() );
                $points -= 50; // each prize is worth 50 points
                if ($points < 50) break; // make sure we still have enough points for next prize
            }
        } 
    }
    echo "Done.";
}

$children = getChildren();
//echo "<pre>"; print_r( $children ); echo "</pre>"; exit;
giveTickets( $children );