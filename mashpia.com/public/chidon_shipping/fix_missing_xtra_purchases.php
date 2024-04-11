<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

if ($admin_user['auth'] != 'super') {
    die('Access denied');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require_once 'class.chidonShipping.php';
$cs = new ChidonShipping($year);

$stmt = $MASHPIA_DB->prepare("
            INSERT IGNORE INTO th_chidon_shipping
            SET 
                year = :year, 
                user_id = :user, 
                item_id = :item, 
                shipped = :shipped, 
                missing = :missing, 
                damaged = :damaged, 
                received = :received, 
                description = :desc, 
                item_num = :num
            ON DUPLICATE KEY UPDATE 
                shipped = :shipped, 
                missing = :missing, 
                damaged = :damaged, 
                received = :received,
                description = :desc, 
                item_num = :num"
);

$purchases = $cs->getExtraPurchases('', 0, [], true);
echo "<pre>"; print_r($purchases); echo "</pre>";
foreach ($purchases as $user_id => $more) {
    foreach ($more as $purchase) {
        $stmt->execute([
            'year' => $year,
            'user' => $user_id,
            'item' => $purchase['id'],
            'shipped' => 1,
            'missing' => 1,
            'damaged' => 0,
            'received' => 0,
            'desc' => '',
            'num' => 0
        ]);
    }
}
echo "done.";