<?
require_once 'raffle.class.php';
$auction_id = 39;
$prize_id = 15;
$school_id = 84;
$raffle = new Raffle( $auction_id, $prize_id );
$raffle->setSchoolsSpecificPrize( array( $school_id ) );
$raffle->setChildren( $school_id );
$raffle->setSpecificSchoolWinner( $school_id );
?>