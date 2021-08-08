<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$sweaters = [];
$sql = "select * from chidon_sweaters where year = " . $year;
$result = mysql_query($sql);
while ( $row = mysql_fetch_assoc($result) ) {
    switch ($row['sweater_name']) {
        case 'Proud Chidon Mother':
            $type = 'mother';
            break;
        case 'Proud Chidon Father':
            $type = 'father';
            break;
        case 'Proud Chidon Bubby':
            $type = 'bubby';
            break;
        case 'Proud Chidon Zaidy':
            $type = 'zaidy';
            break;
    }
    // find out real quantity/stock
    $arrSize = explode(' ', $row['size']);
    $size = strtolower($arrSize[1]);
    $sqlQty = "select count(*) as total from th_chidon_parent_purchases 
                where sweater_{$type} = '$size'";
//    echo $sqlQty;
    $resQty = mysql_query($sqlQty);
    $rowQty = mysql_fetch_assoc($resQty);
    $sweaters[$type][$row['size']] = [
        // 'qty'   =>  intval($row['quantity']) - intval($row['purchased']),
        'qty'   =>  intval($row['quantity']) - intval($rowQty['total']),
        'img'   =>  $row['sweater_picture']
    ];
}
echo json_encode( $sweaters );