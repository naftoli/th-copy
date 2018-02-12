<?php
session_start();
$amount = $_POST['amount'];
$_SESSION['sponsor_amount'] += $amount;
?>
