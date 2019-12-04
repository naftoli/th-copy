<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/PHPExcel/IOFactory.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load("GrandRaffle1Prizes.xlsx");
$objWorksheet = $objPHPExcel->getActiveSheet();

$data = [];
foreach ( $objWorksheet->getRowIterator() as $row ) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $prizes = [];
    foreach ( $cellIterator as $i => $cell ) {
        $value = trim( $cell->getValue() );
        if ( $i == 0 ) {
            $school_id = intval( $value );
        } else {
            $prizes[] = intval( $value );
        }
    }
    echo "School: " . $school_id . "<br />";
    echo "<pre>"; print_r( $prizes ); echo "</pre>";
    echo "<br /><br />";
}