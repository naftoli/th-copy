<?php
ini_set('diplay_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim_purchases/classes/MivtzoimPurchases.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$year = GlobalSettings::getCurrentYear();
$type = $_POST['type'];

$m = new MivtzoimPurchases();
$items = $m->getItemsByType($type);
// get purchases per item
$purchases = $m->getPurchasesPerItem($year, $type);
// update in stock / available
foreach ($items as &$item) {
    $stock = intval($item['stock']);
    if (isset($purchases[$item['item_id']])) $stock -= intval($purchases[$item['item_id']]);
    if ($stock < 0) $stock = 0;
    $item['stock'] = $stock;
}
echo json_encode($items);