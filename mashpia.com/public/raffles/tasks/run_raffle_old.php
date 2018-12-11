<?php
// log errors to the page
error_reporting(E_ALL);
ini_set("display_errors", 1);

if( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0){
    ini_set('max_execution_time', 600); // max runtime = 10 min
    
    require(dirname(__FILE__).'/../../db.php'); // just require the database files
    $web = false; // remember that we are on the cli
} else { // from anywhere else...
    $admin_auth = array('school'); // only schools can get to this page 
    require($_SERVER["DOCUMENT_ROOT"].'/header.php'); // load authentication lib
    if ($admin_user['auth'] != 'super') { // only supers can log in to this page now
        echo "Sorry you don't have the privilege(s) necessary to view this page.";
        exit;
    }
    $web = true; // remember that we are on the web
} // end db imports

// other imports
require_once(dirname(__FILE__)."/../shared/classes/Raffle.php");
require_once(dirname(__FILE__)."/../shared/classes/Prize.php");
require_once(dirname(__FILE__)."/../shared/classes/Constants.php");
// namespaces
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
use raffles\weekly\Prize as Prize;
use raffles\shared\Constants as Constants; // was created later and has correct namespace

if($web) echo "<pre>";
// log some data
$script_start_time = time();
echo "Running run_raffle.php on ".date('m/d/Y H:m:s e')." via ".($web ? "Internet" : "CLI/CRON")."\n";
echo "Getting raffles\n";

/***************************** GET THE RAFFLES ********************************/

$raffles = [];

if($web && $_GET['raffle_id']) {
    $raffle = Raffle::load($_GET['raffle_id']);
    if($raffle) $raffles[] = $raffle; // if there is a raffle id in the get request. then add that to the array
} else {
    echo "Getting all raffles where the run date is the SQL servers current date\n";
    $query = mysql_query("SELECT * FROM raffles WHERE DATE(run_date) = CURDATE() AND date_ran IS NULL;");
    while($row = mysql_fetch_assoc($query)){
        $raffles[] = Raffle::loadFromRow($row); // load the raffle and add it to the array
    }
    echo "Found ".count($raffles)." raffles\n";
}
// if there are no raffles to execute, kill the script.
if(count($raffles) == 0){
    echo "Terminating Script: No Raffles to execute.\n";
    die();
}

/***************************** RUN THE RAFFLES ********************************/

foreach($raffles as $raffle){
    /************************** GET USER INFO **************************/
    echo "\n\nRunning raffle_id ".$raffle->raffle_id."\n"; // log that we are running a raffle
    $start_time = time(); // log how long it takes to get all the eligible users
    $user_ids = $raffle->get_eligable_user_ids(false, true); // no specific user but do show the log
    echo "Found ".count($user_ids)." eligible users in ". (time() - $start_time) ." seconds\n";
    
    // set up some variables for the raffle
    $winning_families = []; // array to keep track of what families have won in this raffle
    $school_count = []; // array to mark how many students have won from a specific school
    
    /************************** GET PRIZE INFO **************************/
    echo "Getting total amount of prizes in raffle...\n";
    $prizes = $raffle->get_prizes(); // load the prizes on the instance
    $total_prizes = $raffle->prize_count;
    echo "Found $total_prizes Prizes. (".count($raffle->prizes)." unique prizes)\n";
    
    /************************** GET SCHOOL INFO **************************/
    $schools = [];
    $school_users = [];
    echo "Computing number of schools... ";
    foreach($user_ids as $user){
        if(isset($schools[$user['school_id']])){ // if we found the school
            $schools[$user['school_id']] += 1; // add one more find
        } else { // otherwise add the school_id to the array
            $schools[$user['school_id']] = 1;
            $school_users[$user['school_id']] = []; // create an empty array to be filled
        }
        $school_users[$user['school_id']][$user['user_id']] = $user; // add the user to this array as well
    }
    echo "found ".count($schools)." unique schools\n";
    
    // compute each schools max winnings - Weekly only
    echo "Computing max winnings for each school...\n";
    $max_school_winnings = Constants::get_raffle_school_max_winners(); // the max a single school can win (taken from constants.php)
    $maxed_out_school_count = 1; // the number of schools that have been maxed out (start at 1 so that the count() which starts from 1 will be less then that)
    // turn school into nested array formatted like school_id => [current_winnings, max winnings]
    foreach($schools as $school_id => $school){
        $school_max = $max_school_winnings[$school_id];
        //if($raffle->type == "monthly"){
        //    $school_max *= 5; // multiply by 5 for the first monthly raffle
        //}
        $schools[$school_id] = [0, ($school > $school_max ? $school_max : $school)]; // set the max winnings to either everyone or the max. whichever is lower
        if($schools[$school_id][1] == 0) $maxed_out_school_count += 1; // a school is maxed out if there are no students
    }
    //ksort($schools); // sort the schools. (why not, did this for logging inititally, do not think it is needed anymore)
    $draw_num = 0; // to display the number of the drawing
    
    /************************** GET ELIGIBLE MYSHLIACH KIDS **************************/
    echo "Creating array of eligible MyShliach students...\n";
    $my_shliach = $school_users['61']; // the array to store myshliach users
    /************************** RUN RAFFLE **************************/
    echo "Beginning Raffle...\n";
    echo "Draw #\t Result\t\t\tusers left\t\tprizes left\t\tunique prizes left\n";
    
    $winners = []; // an array of all the users so they can be added to the database in the end
    
    while(count($user_ids) > 0 && count($prizes) > 0){ // while there are still users and prizes
        $user = $user_ids[array_rand($user_ids)];     // get a random id from the array of user arrays (keys)
        $draw_num++; // count up the drawing
        
        /************************** EXTRA DRAWS GO TO MYSHLIACH **************************/
        if($maxed_out_school_count >= count($schools) && count($my_shliach) > 0){ // if the schools have been maxed out and there are students available in myshliach
            echo "Schools maxed out, Selecting student from MyShliach...\n";
            $user = $my_shliach[array_rand($my_shliach)];   // set the selected user to a random myshliach kid
        }
        /************************** LIMIT WINNERS PER SCHOOL **************************/
        // make sure that the user is not in a school that has reached it's limit yet - for weekly raffles
        $school_won_amounts = &$schools[$user['school_id']]; // pass by value so that the main array updates (a.k.a do not remove the &)
        if($maxed_out_school_count <= count($schools) && $school_won_amounts[0] < $school_won_amounts[1]){ // not <= as that will allow one more studnet to win then should be allowed
            $school_won_amounts[0] += 1; // add one
            if($school_won_amounts[0] == $school_won_amounts[1]) $maxed_out_school_count += 1; // if the school hits it's max, run it
        } elseif($maxed_out_school_count >= count($schools)) { // was just >, so it was always ineligible
            $school_won_amounts[0] += 1; // add one for the record
        } else {
            // log that the user did not win this drawing
            echo "#".$draw_num."\t".$user['user_id'] . " is currently ineligible. School (".$user['school_id'].") has reached its max winner count. Not removing from raffle\n";
            continue; // go to the next drawing
        }
        /************************** ONLY ONE CHILD PER FAMILY **************************/
        // siblings cannot win (if there are more students in the school then the max amount of winners)
        if(isset($winning_families[$user['admin_id']]) && count($school_users[$user['school_id']]) > $schools[$user['school_id']][1]){ // if the users "admin_id" is already in the array
            // log that the user is ineligible due to bad luck
            echo "#".$draw_num."\t".$user['user_id'] . " is currently ineligible. Sibling (user_id: ".$winning_families[$user['admin_id']].") has already won. Removing from raffle\n";
            unset($user_ids[$user['user_id']]); // remove the user from the array
            unset($school_users[$user['school_id']][$user['user_id']]); // remove the student from second array
            if($user['school_id'] == 61) unset($my_shliach[$user['user_id']]); // 61 is the school id for myschliach. Remove the student if he won
            continue; // go to the next drawing
        } elseif($user['admin_id']) { // do not count for null
            if($user['user_id']) $winning_families[$user['admin_id']] = $user['user_id']; // mark the family as having already won. (avoid nulls);
        }
        
        $prize = $prizes[array_rand($prizes)]; // get a prize;
        $prize->qty -= 1;
        $total_prizes--;
        
        if($prize->qty == 0){
            unset($prizes[$prize->prize_id]);
        }
        
        //print_r($user);
        
        $winners[] = ['user_id' => $user['user_id'], 'prize_id' => $prize->prize_id];
        
        unset($user_ids[$user['user_id']]);
        echo "#".$draw_num."\t".$user['user_id']." won prize_id ".$prize->prize_id."\t"
            .count($user_ids)." users left\t\t"
            ."$total_prizes prizes left\t\t"
            .count($prizes)." unique prizes left\n";
    } // end running the raffle
    
    /***************************** CLEAN UP AFTER RAFFLE ********************************/
    
    //if($web) print_r($schools);
    echo "\nMaxed out schools: $maxed_out_school_count/".count($schools);
    //if($web) print_r($winning_families);
    if($web && !$_GET['save']) continue; // if it is on the website and save is not set to on then do not save the results
    
    /***************************** SAVE THE WINNERS INTO THE DATABASE ********************************/
    // save the winners to the database    
    echo "\nDrawing compleated at ".date('m/d/Y H:m:s e').". Saving winners to database ";
    foreach($winners as $winner){
        $raffle_id = $raffle->raffle_id;
        $prize_id = $winner['prize_id'];
        $user_id = $winner['user_id'];
        
        $persist_sql = "INSERT IGNORE INTO raffle_winners VALUES($raffle_id, $prize_id, $user_id, 0)"; // insert the win into the raffle_winners table
        echo mysql_query($persist_sql) ? "✓" : "x";
    }
    
    /***************************** UPDATE THE RAFFLE OBJECT ********************************/
    
    echo "\nUpdating Raffle in Database...";
    
    $raffle->date_ran = new DateTime(); // log the time it ran into the database
    $raffle->show_on_mobile = 1; // show it on the mobile site
    echo $raffle->update() ? "✓" : "x";
} // end foreach raffle loop

echo "\n\nRaffle Script finished in ". (time() - $script_start_time) ." seconds at ".date('m/d/Y H:m:s e')."\n";
