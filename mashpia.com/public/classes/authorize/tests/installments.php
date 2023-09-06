<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../Installments.php';
use \classes\authorize\Installments as Installments;

$installments = new Installments();
$response = $installments->createSubscription(3, 3, 1601946728, 0);
echo "<pre>"; print_r($response); echo "</pre>";