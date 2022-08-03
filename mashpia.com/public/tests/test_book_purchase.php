<?php
ini_set('display_errors', 1);
ini_set('error_reporting', 1);

require $_SERVER['DOCUMENT_ROOT'] . "/api/header/header.php";

$s =  Soldier::find(8273);
$s->addBookPurchase(5783, 8273, "parent_account", '', '', '', 2007);