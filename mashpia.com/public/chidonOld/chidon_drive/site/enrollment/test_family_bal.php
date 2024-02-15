<?php
require_once 'class.tripRegistration.php';

$t = new TripRegistration(196634, 5784);
$balance = $t->getFamilyBalance();
echo $balance;