<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

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
    $sweaters[$type][$row['size']] = [
        'qty'   => $row['quantity'],
        'img'   => $row['sweater_picture']
    ];
}

$purchases = [];
$sql = "select * from extra_purchases where item = 'sweater' and year = " . $year;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    if (isset($purchases[$row['type_of_sweater']][$row['size']])) $purchases[$row['type_of_sweater']][$row['size']] += intval($row['amount']);
    else $purchases[$row['type_of_sweater']][$row['size']] = intval($row['amount']);
}

$info = [];
foreach ($sweaters as $type => $more) {
    foreach ($more as $size => $details) {
        $sizeInfo = explode(' ', $size);
        $sweater_size = strtolower($sizeInfo[1]);
        // check how many of this type and size were purchased
        $purchased = $purchases[$type][$sweater_size];
        $available = intval($details['qty']) - $purchased;
        $info[$type][$sweater_size]['qty'] = $available;
        $info[$type][$sweater_size]['img'] = $details['img'];
    }
}
echo json_encode( $info );