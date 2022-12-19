<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimPurchases.php';

$type = $_POST['type'];

$m = new MivtzoimPurchases();
$items = $m->getItemsByType($type);
echo json_encode($items);