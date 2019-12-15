<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ( $admin_user['auth'] != 'super' ) {
    echo "Invalid Permissions.";
    exit;
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/PHPExcel/IOFactory.php';

//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load( "sweater_sizes.xlsx" );
$objWorksheet = $objPHPExcel->getActiveSheet();

$info = [];
foreach ( $objWorksheet->getRowIterator() as $row ) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    foreach ( $cellIterator as $idx => $cell ) {
        $value = trim( $cell->getValue() );
        if ( $idx > 0 ) $size = $value;
        else $id = intval( $value );
    }
    $info[$id] = $size;
    echo "ID: " . $id . " Size: " . $size . "<br />";
}

foreach ( $info as $id => $size ) {
    $sql = "update th_chidon set sweater_size = '" . strtolower( $size ) . "' where th_chidon_id = " . $id;
    echo $sql . "<br />";
}