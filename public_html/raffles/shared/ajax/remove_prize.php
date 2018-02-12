<?php

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

$raffle = Raffle::load($_POST['raffle_id']);
$prize = Prize::load($_POST['prize_id']);
$qty = $_POST['qty'];

if($raffle->remove_prize($prize, $qty)){
    echo json_encode(["success" => true, "error" => "No Error", "total" => 0]);
} else {
    echo json_encode(["success" => false, "error" => "Server Error, Please contact support"]);
}