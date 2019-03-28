<?php
/***************** DEBUGGING SETTINGS **********************/
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true;
    //echo "<h2>Debug log:</h2>";
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// import getWinners
require_once(dirname(__FILE__).'/../../shipping/functions/getWinners.php');
// return if there are any errors
echo json_encode(["success" => getWinners::mark_winner_shipped($_POST['marked'], $_POST['user_id'], $_POST['raffle_id'], $_POST['prize_id'])]);