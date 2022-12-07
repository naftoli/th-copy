<?php
require_once(dirname(__FILE__)."/../../shared/classes/Prize.php");
require_once(dirname(__FILE__)."/../../shared/classes/Constants.php");
require_once(dirname(__FILE__)."/../../../class.globalSettings.php");
$year = GlobalSettings::getCurrentYear();

use raffles\weekly\Prize as Prize;
use raffles\shared\Constants as Constants; // was created later and has correct namespace

/************************** ONLY ONE CHILD PER FAMILY **************************/
function check_first_in_family($user, &$winning_families){
    // siblings cannot win (if there are more students in the school then the max amount of winners)
    if(isset($winning_families[$user['admin_id']])){ // if the users "admin_id" is already in the array
        return false;
    } elseif($user['admin_id']) { // do not count for null/blank
        if($user['user_id']) $winning_families[$user['admin_id']] = $user['user_id']; // mark the family as having already won. (avoid nulls);
    }
    return true; // they are the first in the family
}

function alreadyWon($user) {
    global $year;
    $sql = "select * from raffle_winners where user_id = $user and raffle_id in (
        select raffle_id from raffles where type = 'weekly' and year = $year 
    )";
    echo $sql; exit;
    $result = mysql_query($sql);
    return mysql_num_rows($result) > 0;
}

function weekly_raffle($raffle){
    /************************** GET USER INFO **************************/
    echo "\n\nRunning raffle_id ".$raffle->raffle_id."\n"; // log that we are running a raffle
    $start_time = time(); // log how long it takes to get all the eligible users
    $schools = $raffle->get_eligable_user_ids(false, true, true); // no specific user but do show the log
    echo "Found eligible users in ". (time() - $start_time) ." seconds\n";
    
    ksort($schools); // show the scools
    
    // log out all the schools
    echo "School ID\tEligible Students\n";
    foreach($schools as $school_id => $eligible_students){
        echo $school_id."\t\t".count($eligible_students)."\n";
    }
    
    /************************** GET PRIZE INFO **************************/
    echo "Getting total amount of prizes in raffle...\n";
    $prizes = $raffle->get_prizes(); // load the prizes on the instance
    $total_prizes = $raffle->prize_count;
    echo "Found $total_prizes Prizes. (".count($raffle->prizes)." unique prizes)\n";
    
    /************************** LOG SCHOOL COUNT **************************/
    echo "found ".count($schools)." unique schools\n";
    
    /************************** COMPUTE THE LIMITS PER SCHOOL **************************/
    echo "Computing max winnings for each school...\n";
    $max_school_winnings = Constants::get_raffle_school_max_winners(); // the max a single school can win (taken from constants.php)
    // turn school into nested array formatted like school_id => [current_winnings, max winnings]
    $school_limits = [];
    foreach($schools as $school_id => $eligible_students){
        $school_max = $max_school_winnings[$school_id];
        $school_count = count($eligible_students);
        //if($raffle->type == "monthly"){
        //    $school_max *= 5; // multiply by 5 for the first monthly raffle
        //}
        $school_limits[$school_id] = ($school_count > $school_max ? $school_max : $school_count); // set the max winnings to either everyone or the max. whichever is lower
    }
    //ksort($schools); // sort the schools. (why not, did this for logging inititally, do not think it is needed anymore)
    $draw_num = 0; // to display the number of the drawing
    
    //echo "<pre>";
    //print_r($school_limits);
    //echo "</pre>";
    
    /************************** RUN RAFFLE **************************/
    echo "Beginning Raffle...\n";
    echo "Drawing the quota from each school...\n";
    echo "School\t Draw #\t Result\t\t\tusers left\t\tprizes left\t\tunique prizes left\n";
    
    // set up some variables for the raffle
    $winners = []; // an array of all the users so they can be added to the database in the end
//    $winning_families = []; // array to keep track of what families have won in this raffle
    $remaining_users = []; // the users that are left after the first two rounds of picking
    
    /************************** ROUND ONE: GIVE ALL THE SCHOOLS THEIR QUOTA **************************/
    foreach($schools as $school_id => $user_ids) { // for each school
        for($i = 0; $i < $school_limits[$school_id]; $i++){
            if(count($user_ids) == 0) continue; // skip the loop if the school is out of eligible winners... (e.g. if one is a sibling...)
            $user = $user_ids[array_rand($user_ids)];   // get a random id from the array of user arrays (keys)
            $draw_num++; // count up the drawing
            
            /************************** ONLY ONE CHILD PER FAMILY **************************/
            // if the user is not the first in the family and the school has more then it's quota to give out...
//            if(!check_first_in_family($user, $winning_families) && count($user_ids) <= $school_limits[$school_id]) {
//            if (alreadyWon($user, $raffle) && count($user_ids) <= $school_limits[$school_id]) {
            if (alreadyWon($user)) {
                echo $user['school_id']."\t#".$draw_num."\t".$user['user_id'] . " has already won last raffle. Removing from raffle\n";
                unset($user_ids[$user['user_id']]); // remove the user from the array
                $i--; continue; // redo the draw
            }
            // now that we have an eligible winner
            if (count($prizes) == 0) return $winners; // if there are no more prizes then return the winners
            unset($user_ids[$user['user_id']]); // remove the user from the array
            // select a prize
            $prize = $prizes[array_rand($prizes)]; // get a prize;
            $prize->qty -= 1;   $total_prizes--; // remove the prize from the counters
            // remove the prize if it was already picked...
            if($prize->qty == 0) unset($prizes[$prize->prize_id]);
            
            $winners[] = ['user_id' => $user['user_id'], 'school_id' => $user['school_id'], 'prize_id' => $prize->prize_id];
            echo $user['school_id']."\t#".$draw_num."\t".$user['user_id']." won prize_id ".$prize->prize_id."\t"
                .count($user_ids)." users left\t\t"."$total_prizes prizes left\t\t".count($prizes)." unique prizes left\n";
        } // end foreach school user within the limits
        if($school_id != 61 && count($user_ids) > 0){ // if the school is not myshliach
            $remaining_users = array_merge($remaining_users, $user_ids); // add the users to the remaining users array
        }
    } // end round one
    
    /************************** ROUND TWO: GIVE ALL THE EXTRA PRIZES TO MYSHLIACH CHILDEREN **************************/
    // echo "\nDrawing the extra prizes from MyShliach...\n";
    // echo "School\t Draw #\t Result\t\t\tusers left\t\tprizes left\t\tunique prizes left\n";
    
    // $myshliach = $schools[61]; // get the myshlicah school
    // $myshliach_limit = 4;
    // while (count($myshliach) > 0 && count($prizes) > 0 && $myshliach_limit-- > 0) {
    //     $user = $myshliach[array_rand($myshliach)];   // get a random id from the array of user arrays (keys)
    //     $draw_num++; // count up the drawing
    //     // make sure that a simbling did not win...
    //     if(!check_first_in_family($user, $winning_families) && count($user_ids) <= $school_limits[$school_id]){
    //         echo $user['school_id']."\t#".$draw_num."\t".$user['user_id'] . " is currently ineligible. Sibling (user_id: ".$winning_families[$user['admin_id']].") has already won. Removing from raffle\n";
    //         continue; // go to the next draw
    //     }
    //     unset($myshliach[$user['user_id']]); // remove the user from the array
    //     // select a prize
    //     $prize = $prizes[array_rand($prizes)]; // get a prize;
    //     $prize->qty -= 1;   $total_prizes--; // remove the prize from the counters
    //     // remove the prize if it was already picked...
    //     if($prize->qty == 0) unset($prizes[$prize->prize_id]);
        
    //     $winners[] = ['user_id' => $user['user_id'], 'school_id' => $user['school_id'], 'prize_id' => $prize->prize_id];
    //     echo $user['school_id']."\t#".$draw_num."\t".$user['user_id']." won prize_id ".$prize->prize_id."\t"
    //         .count($user_ids)." users left\t\t"."$total_prizes prizes left\t\t".count($prizes)." unique prizes left\n";
    // } // end round two...
    
    /************************** ROUND THREE: GIVE ALL THE EXTRA PRIZES TO NON-SIBLINGS **************************/
    //echo "\nDrawing the extra prizes from remaining users (non-siblings)...\n";
    //echo "School\t Draw #\t Result\t\t\tusers left\t\tprizes left\t\tunique prizes left\n";
    //
    //while (count($remaining_users) > 0 && count($prizes) > 0){
    //    $user = $remaining_users[array_rand($remaining_users)];   // get a random id from the array of user arrays (keys)
    //    $draw_num++; // count up the drawing
    //    if(!check_first_in_family($user, $winning_families) && count($user_ids) <= $school_limits[$school_id]){
    //        echo $user['school_id']."\t#".$draw_num."\t".$user['user_id'] . " is currently ineligible. Sibling (user_id: ".$winning_families[$user['admin_id']].") has already won. Removing from raffle\n";
    //        continue; // go to the next draw
    //    }
    //    unset($myshliach[$user['user_id']]); // remove the user from the array
    //    // select a prize
    //    $prize = $prizes[array_rand($prizes)]; // get a prize;
    //    $prize->qty -= 1;   $total_prizes--; // remove the prize from the counters
    //    // remove the prize if it was already picked...
    //    if($prize->qty == 0) unset($prizes[$prize->prize_id]);
    //    
    //    $winners[] = ['user_id' => $user['user_id'], 'school_id' => $user['school_id'], 'prize_id' => $prize->prize_id];
    //    echo $user['school_id']."\t#".$draw_num."\t".$user['user_id']." won prize_id ".$prize->prize_id."\t"
    //        .count($user_ids)." users left\t\t"."$total_prizes prizes left\t\t".count($prizes)." unique prizes left\n";
    //}
    
    return $winners; // return the winners array
}