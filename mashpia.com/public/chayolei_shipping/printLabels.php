<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$data = json_decode($_COOKIE['for_labels'], true);
$_POST = $data;
//echo "<pre>"; print_r($data); echo "</pre>";

if (empty($data)) {
  echo "No data provided";
  exit;
}

require 'class.chayoleiShipping.php';
require 'data.php';

$cs = new ChayoleiShipping();
$cs->setYear($data['year']);

$items_chosen = $data['items'];
$gender = $data['gender'];
$schools = $data['schools'];

$info = [];
foreach ($schools as $schoolID) {
    foreach ($items_chosen as $cat => $itemsPerCat) {
        $listOfItems = array_keys($itemsPerCat);
        $nameOfFunc = 'get' . str_replace(' ', '', ucwords($cat));
        $info[$cat] = $cs->$nameOfFunc($gender, $schoolID, $listOfItems);
    }
}

$getStatus = false;
foreach ($info as $cat => $items) {
    if (!empty($items)) {
        $getStatus = true;
        break;
    }
}
if ($getStatus) {
    $info['status'] = $cs->getStatus();
}
echo "<pre>"; print_r($info); echo "</pre>";