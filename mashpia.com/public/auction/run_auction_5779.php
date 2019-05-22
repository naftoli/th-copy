<?php
ini_set('display_errors',1);
require_once '../db.php';
$auction_id = 80;

// get list of prizes
$prizes = [];
$sql = "select * from auction_prizes where auction_id = " . $auction_id;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $prizes[] = $row['prize_id'];
}

// for each prize get list of children; for each ticket add user to array
$tickets = [];
$sql = "SELECT prize_id, user_id, quantity FROM mashpiadb.auction_user_prizes where auction_id = " . $auction_id;
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
  $prize_id = $row['prize_id'];
  $user_id = $row['user_id'];
  $quantity = $row['quantity'];
  if ( $prize_id && $quantity ) {
    while ( $quantity ) {
      $tickets[$prize_id][] = $user_id;
      $quantity--;
    }
  }
}

// for each prize randomly choose a winner
$winners = [];
foreach ( $prizes as $prize_id ) {
  $totalTickets = count( $tickets[$prize_id] );
  $winners[$prize_id] = $tickets[$prize_id][mt_rand( 0, $totalTickets - 1 )];
}

// save to db
echo "<pre>";
print_r( $winners );
echo "</pre>";