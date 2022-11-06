<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', 1);

require '../../../db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
$item_id = 4;

$query ="
    SELECT 
        count(*) as total 
    FROM
        mashpia_purchases.purchase_details
            JOIN
        mashpia_purchases.purchases USING (purchase_id)
    WHERE
        year = $year AND item_id = " . $item_id;
$result = mysql_query($query);
$row = mysql_fetch_assoc($result);
echo intval($row['total']);