<?php
//error_reporting(E_ALL);
ini_set("display_errors", 1);

// import authentication
$admin_auth = array('school'); 
require($_SERVER["DOCUMENT_ROOT"].'/header.php');
// import the required files
require_once(dirname(__FILE__).'/../classes/Raffle.php');
require_once(dirname(__FILE__).'/../classes/Prize.php');
require_once(dirname(__FILE__).'/../../yearly/classes/YearlyRaffle.php');

use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
use raffles\weekly\Prize as Prize; // use the raffle from its namespace
use raffles\yearly\YearlyRaffle as YearlyRaffle;

require_once(dirname(__FILE__).'/../../shared/functions.php');
// get the values from the post request
$raffle_id = $_POST['raffle_id'];
$user_id =$_POST['user_id'];
$eligible = $_POST['eligible'] === 'true'? true: false;;

if($eligible){
    $sql = "INSERT INTO raffle_eligibility VALUES ($raffle_id, $user_id, 1)";
} else {
    $sql = "DELETE FROM raffle_eligibility WHERE raffle_id=$raffle_id AND user_id=$user_id;";
}
$query = mysql_query($sql); // run the query

if($query){ // operation was a success
    echo json_encode(["success" => true, "error" => "No Error"]);
} else { // it failed for unknown reason
    echo json_encode(["success" => false, "error" => "Server Error, Please contact support", "data" => [$_POST, $sql, $eligible]]);
}

// if yearly raffle add / remove from yearly cache
$r = Raffle::load( $raffle_id );
if ( $r->type == 'yearly' ) {
    $yr = new YearlyRaffle;
    $days = $yr->getDayCount();
    $year = $yr->getYear();
    if ( $eligible ) {
        mysql_query("INSERT IGNORE INTO user_yearly_raffle (user_id, days, year) VALUES($user_id, $days, $year)");
    } else {
        mysql_query("DELETE FROM user_yearly_raffle WHERE user_id = " . $user_id . " AND year = " . $year);
    }
}

die(); // end the script here