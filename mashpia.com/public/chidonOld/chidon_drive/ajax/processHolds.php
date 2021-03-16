<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';

//***************** LOAD CURRENT YEAR **********************/
require_once __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require 'authorize.php';

// find parents with duplicates so that we don't process holds as of now
$duplicates = [];
$sql = "select admin_id, count(*) as total 
        from th_chidon_parent_purchases 
        where authorize_trans_type = 'hold' 
        and authorize_id > 1 
        group by admin_id 
        having total > 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $duplicates[] = $row['admin_id'];
}

// find parents with holds and their trans id numbers
$parents = [];
$sql = "SELECT 
            admin_id, authorize_id, amount
        FROM
            th_chidon_parent_purchases
        WHERE
            authorize_trans_type = 'hold'
                AND authorize_id > 1";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    // skip parents with duplicated
    if (in_array($row['admin_id'], $duplicates)) continue;
    $parents[] = $row;
}

foreach ($parents as $idx => $parent) {
    $sql = "SELECT 
                SUM(donation_amount) as total 
            FROM
                mashpiadb.chidon_donations
            WHERE
                for_family_id = " . $parent['admin_id'] . " AND chidon_year = " . $year;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $total = $row['total'];
        $parents[$idx]['raised'] = $total;
    }
}

foreach ($parents as $idx => $parent) {
    if ($idx == 5) break;
    echo $idx + 1 . ") ";
    // figure out how much to charge
    $charge = floatval($parent['amount']) - floatval($parent['raised']);
    if ($charge <= 0) {
        echo "Parent ID: " . $parent['admin_id'] . " raised more than they were charged. 
            Raised: " . $parent['raised'] . " On Hold: " . $parent['amount'] . "<br />";
        $response = releaseHold($parent['authorize_id'], true);
    } else {
        echo "Parent ID: " . $parent['admin_id'] . " will be charged: " . $charge . "<br />";
        $response = chargeHold($charge, $parent['authorize_id'], true);
    }
    echo "<pre>";
    print_r($response);
    print_r(parseResponse($response));
    echo "</pre>";
}