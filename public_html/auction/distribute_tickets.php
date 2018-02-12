<?
$admin_auth = array('school'); 
require('../header.php');  
require('auction.php');
require('ticket_distribution.php');
$ticket_distribution = new ticket_distribution($_GET['auction_id']);
?>