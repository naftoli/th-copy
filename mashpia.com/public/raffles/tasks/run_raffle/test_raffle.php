<?php
ini_set('display_errors',1);
require $_SERVER['DOCUMENT_ROOT'] . '/db.php'; // just require the database files
require $_SERVER['DOCUMENT_ROOT'] . '/raffles/shared/classes/Raffle.php';
// namespaces
use raffles\weekly\Raffle as Raffle; // use the raffle from its namespace

$raffle = Raffle::load($_GET['raffle_id']);
$schools = $raffle->get_eligable_user_ids(false, true, true, false, 0, true);
echo "<pre>";
print_r($schools); 
echo "</pre>";