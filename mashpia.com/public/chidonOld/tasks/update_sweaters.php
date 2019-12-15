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
    $i = 0;
    foreach ( $cellIterator as $cell ) {
        $value = trim( $cell->getValue() );
        if ( $i++ > 0 ) $size = $value;
        else $id = intval( $value );
    }
    $info[$id] = $size;
}

foreach ( $info as $user => $size ) {
    $sql = "update th_chidon set sweater_size = '" . strtolower( $size ) . "' where th_chidon_id = " . $user;
    echo $sql . "<br />";
}