<?php
require_once(dirname(__FILE__)."/../../shared/classes/Prize.php");
require_once(dirname(__FILE__)."/../../shared/classes/Constants.php");
require_once(dirname(__FILE__)."/../../../class.globalSettings.php");

use raffles\weekly\Prize as Prize;
use raffles\shared\Constants as Constants; // was created later and has correct namespace

function monthly_raffle($raffle){
    /************************** GET USER INFO **************************/
    echo "\n\nRunning raffle_id ".$raffle->raffle_id."\n"; // log that we are running a raffle
    $start_time = time(); // log how long it takes to get all the eligible users
    $schools = $raffle->get_eligable_user_ids(false, true, true); // no specific user but do show the log
    echo "Found eligible users in ". (time() - $start_time) ." seconds\n";
    
    /************************** GET PRIZE INFO **************************/
    echo "Getting prizes for monthly raffle...\n";
    $prize_sql = "SELECT * FROM raffles_monthly WHERE raffle_id=".$raffle->raffle_id.";";
    $prize_query = mysql_query($prize_sql); $prizes = [];
    while($prize = mysql_fetch_assoc($prize_query)) $prizes[] = $prize;

    echo "<pre>";
//    print_r($schools);
    print_r($prizes);
    echo "</pre>";
    
    /************************** RUN RAFFLE **************************/
    echo "Beginning Raffle...\n";
    echo "School\tUser ID\tResult\n";
    
    // set up some variables for the raffle
    $winners = []; // an array of all the users so they can be added to the database in the end

    /************************** ROUND ONE: GIVE ALL THE SCHOOLS THEIR QUOTA **************************/
    foreach($prizes as $prize) { // for each school
        $user_ids = &$schools[$prize['school_id']];
        $winner_found = false;
        while (!$winner_found && $user_ids) {
            $user = $user_ids[array_rand($user_ids)];   // get a random id from the array of user arrays (keys)
            echo "User: " . $user;
            print_r($user); exit;
            /************************** ONLY ONE CHILD PER FAMILY **************************/
            // if the user is not the first in the family and the school has more children
            if (alreadyWon($user['user_id'], 'monthly') && count($user_ids) > 1) {
                 echo $user['school_id']."\t".$user['user_id'] . " has already won this yr. Removing from raffle\n";
                 unset($user_ids[$user['user_id']]); // remove the user from the array
                 continue; // try again
            }

            // now that we have an eligible winner
            unset($user_ids[$user['user_id']]); // remove the user from the array
            $winner_found = true;
            
            $winners[] = ['user_id' => $user['user_id'], 'school_id' => $prize['school_id'], 'prize_id' => $prize['prize_id']];
            echo $prize['school_id']."\t".$user['user_id']."\twon prize_id ".$prize['prize_id']."\n";
        }
    } // end round one
    
    return $winners; // return the winners array
}