<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
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
    $sweaters[$type][$row['size']] = intval($row['quantity']) - intval($row['purchased']);
}
echo json_encode( $sweaters );