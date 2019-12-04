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

$raffle_id = 189;

//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load("GrandRaffle2Prizes.xlsx");
$objWorksheet = $objPHPExcel->getActiveSheet();

$stmt = $MASHPIA_DB->prepare("
    INSERT INTO raffles_monthly SET 
    raffle_id = :raffle, 
    prize_id = :prize, 
    school_id = :school
");

$success = true;
$MASHPIA_DB->beginTransaction();
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
    // echo "School: " . $school_id . "<br />";
    // echo "<pre>"; print_r( $prizes ); echo "</pre>";
    // echo "<br /><br />";
    foreach ( $prizes as $prize ) {
        if ( $prize > 0 ) {
            $res = $stmt->execute([
                ':raffle'   =>  $raffle_id, 
                ':prize'    =>  $prize, 
                ':school'   =>  $school_id
            ]);
            if ( !$res ) {
                $succes = false;
                break 2;
            }
        }
    }
}
if ( $success ) {
    $MASHPIA_DB->commit();
    echo "done.";
} else {
    $MASHPIA_DB->rollBack();
    echo "errors.";
}