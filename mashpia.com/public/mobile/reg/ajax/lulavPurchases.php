<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', 1);

require '../../../db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$item_id = 1;
if (isset($_POST['item_id'])) $item_id = $_POST['item_id'];

$query ="
    SELECT 
        COUNT(*) as total
    FROM
        mashpia_purchases.purchase_details
    WHERE
        item_id = $item_id 
            AND purchase_id IN (SELECT 
                purchase_id
            FROM
                mashpia_purchases.purchases
            WHERE
                year = $year)";
$result = mysql_query($query);
$row = mysql_fetch_assoc($result);
echo intval($row['total']);