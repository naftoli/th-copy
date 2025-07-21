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

$labels = [];
foreach ($info as $cat => $more) {
    foreach ($more as $user_id => $items) {
        foreach ($items as $idx => $item) {
            // find out how many of the same item we have
            if ($idx > 0 && $item['id'] == $items[$idx - 1]['id']) $item_num++;
            else $item_num = 0;
            // get status and whether to show this item
            $show_item = false;
            $status = isset($info['status'][$row['user_id']][$item['id']][$item_num]) ? $info['status'][$row['user_id']][$item['id']][$item_num] : [];
            if (empty($limit_to_status)) $show_item = true;
            else {
                foreach ($limit_to_status as $idx) {
                    if ($idx == 0 && (empty($status) || $status['status'] == 0)) {
                        $show_item = true;
                        break;
                    }
                    else if (!empty($status) && $status['status'] == $idx) {
                        $show_item = true;
                        break;
                    }
                }
            }
            if ($show_item) {
                $labels[$user_id][] = $item['item'];
            }
        }
    }
}

echo "<pre>"; print_r($labels); echo "</pre>";