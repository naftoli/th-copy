<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

$data = json_decode($_COOKIE['for_labels'], true);
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
$medals_dates = $data['medals_dates'];
$ranks_dates = $data['ranks_dates'];
$gender = $data['gender'];
$schools = $data['school'];

$info = [];
foreach ($schools as $schoolID) {
    foreach ($items_chosen as $cat => $itemsPerCat) {
        if ($cat == 'medals') {
            $info[$cat] = $cs->getMedals($gender, $schoolID, $itemsPerCat, $medals_dates);
        } else if ($cat == 'ranks') {
            $info[$cat] = [];
            foreach ($itemsPerCat as $item) {
                if ($item == 'rank medals') {
                    $info[$cat] += $cs->getRankMedals($gender, $schoolID, $ranks_dates);
                } else if ($item == 'rank books') {
                    $info[$cat] += $cs->getRankBooks($gender, $schoolID, $ranks_dates);
                }
            }
        } 
    }
}
$info['status'] = $cs->getStatus();

echo "<pre>"; print_r($info); echo "</pre>";