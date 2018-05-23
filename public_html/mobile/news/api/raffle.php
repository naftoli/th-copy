<?php
include_once( dirname(__FILE__) . "/header.php" );
require_once( dirname(__FILE__) . "/../../../raffles/shared/classes/Raffle.php" );
require_once( dirname(__FILE__) . "/../../../raffles/shared/classes/Prize.php" );

use \raffles\weekly\Raffle as Raffle;

if ( $_SERVER['REQUEST_METHOD'] == "GET" ) {
    if ( isset( $_GET['raffle_id'] ) ) getRaffle( $_GET['raffle_id'] );
}

/**
 * getRaffle
 * 
 * GET /?raffle_id=<raffle_id>
 * 
 * 0 returns the latest raffle
 *
 * @param int/string $raffle_id
 * @return void
 */
function getRaffle( $raffle_id ){
    if ( $raffle_id == 0 ) {
        $raffle_query = mysql_query( 
            "SELECT * FROM raffles WHERE show_on_mobile = 1 ORDER BY date_ran DESC, type DESC LIMIT 1;"
        );
        $raffle = Raffle::loadFromRow( mysql_fetch_assoc( $raffle_query ) );
    } else {
        $raffle = Raffle::load( $raffle_id );
    }

    $raffle->get_winner_info( false, true, "name", false );
    $raffle->get_hebrew_dates();
    // $raffle->get_prizes();

    // get the next and previos ID's
    $raffle_ids_query = mysql_query(
        "SELECT raffle_id, name FROM raffles WHERE show_on_mobile = 1 ORDER BY date_ran DESC, type DESC"
    );
    // variables to keep track of where we are
    $previous_raffle = false;
    $found_raffle = false;
    $next_raffle = false;
    foreach ( fetch_results_assoc( $raffle_ids_query ) as $row ){
        if ( $found_raffle ) { // if the last row was the current raffle
            $previous_raffle = $row; break; // this must be the raffle before it
        // if this raffle is not the current raffle and we have not seen the current raffle yet
        } else if ( $raffle->raffle_id !== $row['raffle_id'] ) {
            $next_raffle = $row; // then this must be an upcoming raffle
        } else if ( $raffle->raffle_id === $row['raffle_id'] ) { // if it is the current raffle.
            $found_raffle = true; // mark that we have seen it.
        }
    }

    render_json_response([
        "raffle"    => $raffle,
        "next"      => $next_raffle,
        "previous"  => $previous_raffle
    ]);
}