<?php
require '../../../db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();

$query ="
    SELECT 
        COUNT(*) as total
    FROM
        purchase_details
    WHERE
        item_id = 1
            AND purchase_id IN (SELECT 
                purchase_id
            FROM
                purchases
            WHERE
                year = $year)";
$result = mysql_query($query);
$row = mysql_fetch_assoc($result);
echo intval($row['total']);