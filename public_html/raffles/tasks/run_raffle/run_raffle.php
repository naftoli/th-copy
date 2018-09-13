<?php
// log errors to the page
error_reporting(E_ALL);
ini_set("display_errors", 1);

if( empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0){
    ini_set('max_execution_time', 600); // max runtime = 10 min
    
    require(dirname(__FILE__).'/../../../db.php'); // just require the database files
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
require_once(dirname(__FILE__)."/../../shared/classes/Raffle.php");
require_once(dirname(__FILE__)."/monthly_raffle.php");
require_once(dirname(__FILE__)."/weekly_raffle.php");
// namespaces
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace

if($web) echo "<pre>";
// log some data
$script_start_time = time();
echo "Running run_raffle.php on ".date('m/d/Y H:m:s e')." via ".($web ? "Internet" : "CLI/CRON")."\n";
echo "Getting raffles...\n";

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
    // run weekly raffles
    if($raffle->type == "weekly"){
        $winners = weekly_raffle($raffle);
    }
    // run monthly raffles
    if($raffle->type == "monthly"){
        $winners = monthly_raffle($raffle);
    }
    
    $test_schools_array = [];
    foreach($winners as $winner){
        if(isset($test_schools_array[$winner['school_id']])){
            $test_schools_array[$winner['school_id']] += 1;
        } else {
            $test_schools_array[$winner['school_id']] = 1;
        }
    }
    
    /***************************** SKIP THE SAVING IF THE WEB DID NOT SEND THE "SAVE" COMMAND ********************************/
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
    
    /***************************** UPDATE THE RAFFLE ROW ITSELF ********************************/
    echo "\nUpdating Raffle in Database...";
    
    $raffle->date_ran = new DateTime(); // log the time it ran into the database
    $raffle->show_on_mobile = count( $winners ) > 0 ? 1 : 0 ; // show it on the mobile site
    echo $raffle->update() ? "✓" : "x";
} // end foreach raffle loop

echo "\n\nRaffle Script finished in ". (time() - $script_start_time) ." seconds at ".date('m/d/Y H:m:s e')."\n";
    
