<?php $debug = false;
error_reporting(E_ALL);
ini_set('display_errors', 1);
/***************** DEBUGGING **********************/
if ( isset( $_GET['debug'] ) ) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $debug = true; // set debug to true
}
/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER['DOCUMENT_ROOT'].'/header.php');

/***************** POST DATA **********************/
$status = ['success' => false, 'error' => 'Under Development'];
$checked = $_POST['checked'] == 'true' ? true : false;
$qty = isset($_POST['qty']) ? $_POST['qty'] : 0;

if($debug) $status['post'] = ['checked' => $checked, 'params' => $_POST['params']];

foreach( $_POST['params'] as $params ) {
    $params = explode(':', $params);
    
    // use a swich statement incase type is pulled from another source later
    switch($params[0]){
        case 'rank':
            include_once(dirname(__FILE__)."/../functions/get_ranks.php");
            $status['success'] = mark_rank($checked, $params[1], $params[2], $params[3], $params[4]);
            $status['error'] = "Sorry, it seems that we could not update your rank cards/books shipping status at this time. Please try again later.";
            break;
        case 'medal':
            include_once(dirname(__FILE__)."/../functions/get_medals.php");
            $status['success'] = mark_medal($checked, $params[1], $params[2], $params[3], $params[4]);
            $status['error'] = "Sorry, it seems that we could not update your medals shipping status at this time. Please try again later.";
            break;
        case 'hachayol':
            include_once(dirname(__FILE__)."/../functions/get_hachayols.php");
            $status['success'] = mark_hachayol($params[3], $params[1], $params[2]);
            $status['error'] = "Sorry, it seems that we could not update the hachayol shipping status at this time. Please try again later.";
            break;
        case 'gift':
            require_once($_SERVER["DOCUMENT_ROOT"]."/yearly_prize/functions/get_shipped_marks.php");
            $status['success'] = mark_shipped($checked, $params[1], $params[2]);
            $status['error'] = "Sorry, it seems that we could not update your gift items shipping status at this time. Please try again later.";
            break;
        case 'prize':
            require_once($_SERVER["DOCUMENT_ROOT"].'/raffles/shared/shipping/functions/getWinners.php');
            $status['success'] = getWinners::mark_winner_shipped($_POST['checked'], $params[1], $params[2], $params[3]);
            $status['error'] = "Sorry, it seems that we could not update your raffle winnings shipping status at this time. Please try again later.";
            break;
        case 'auction':
            include_once(dirname(__FILE__)."/../functions/get_auctions.php");
            $status['success'] = mark_auction($checked, $params[1], $params[2], $params[3]);
            $status['error'] = "Sorry, it seems that we could not update your auction winnings shipping status at this time. Please try again later.";
            break;
        default:
            $status['error'] = "Unknown mark type";
    }
}

echo json_encode($status);
