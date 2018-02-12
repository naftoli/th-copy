<?php
require 'class.points.php';

class TestPoints
{
    public static function test( $user_id ) {
        $p = new Points( $user_id );
        echo "Total Points: " . $p->getTotalPoints() . "<br />";
        echo "This year's total Points: " . $p->getTotalThisYear() . "<br />";
        echo "Auction Points: " . $p->getAuctionPoints( 2457629 ) . "<br />";
        echo "Store Points: " . $p->getStorePoints() . "<br />";
    }
}