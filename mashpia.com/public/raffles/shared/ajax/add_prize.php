<?php
// debugging, show errors. Note, this breaks the JS as it expects valid JSON to be returned.
error_reporting(E_ALL);
ini_set("display_errors", 1);

// import authentication
$admin_auth = array('school'); 
require($_SERVER["DOCUMENT_ROOT"].'/header.php');
// import the required files
require_once(dirname(__FILE__).'/../classes/Raffle.php');
require_once(dirname(__FILE__).'/../classes/Prize.php');

use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace
use raffles\weekly\Prize as Prize; // use the raffle from its namespace

require_once(dirname(__FILE__).'/../../shared/functions.php');

$raffle_id = mysql_real_escape_string($_POST['raffle_id']);

$raffle = Raffle::load($raffle_id);
$prize = Prize::load($_POST['prize_id']);
$qty = $_POST['qty'];

// first check that we do not have more than 100 prizes
$sql = "SELECT sum(qty) as `total` FROM raffle_prizes where raffle_id=$raffle_id AND prize_id != ".$prize->prize_id;
$row = mysql_fetch_assoc(mysql_query($sql)); // run the query
$total = $row['total'] + intval($qty);


// make sure that the total number of prizes is below 100
if($total > 100 && $raffle->type == "weekly"){ // if it is more
    // get the current amount that that prize has to send back
    $total_sql = "SELECT sum(qty) as `total` FROM raffle_prizes where raffle_id=$raffle_id AND prize_id = ".$prize->prize_id;
    $current_row = mysql_fetch_assoc(mysql_query($total_sql)); // run the query
    // return the json
    echo json_encode([ // throw an error
        "success" => false,
        "error" =>    "You cannot add more than 100 prizes to a raffle. This raffle currently has ". ($row['total'] ? $row['total'] : 0). " other prizes\n".
                      "Please note that the number of prizes has NOT updated",
        "total" => $current_row['total']
    ]);
    die(); // sent response. kill the script now
}

// set the qty to 0 if it is blank
if($qty == ""){
    $qty = 0;
}

// attempt to add the prize
if($raffle->add_prize($prize, $qty)){
    echo json_encode(["success" => true, "error" => "No Error", "total" => $qty, "running_total" => $total]);
} else {
    echo json_encode(["success" => false, "error" => "Server Error, Please contact support"]);
}
