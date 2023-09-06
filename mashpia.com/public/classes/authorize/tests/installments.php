<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../Installments.php';
use \classes\authorize\Installments as Installments;

$installments = new Installments(1601946728);
$response = $installments->createSubscription(3, 3, 0);
