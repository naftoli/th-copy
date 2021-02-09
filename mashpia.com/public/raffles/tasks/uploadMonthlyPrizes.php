<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/PHPExcel/IOFactory.php';

if ( $admin_user['auth'] != 'super' ) {
    echo "No Permission.";
    exit;
}

$schools = [];
$result = $MASHPIA_DB->query("select school_number, school_id from schools");
$rows = $result->fetchAll();
foreach ($rows as $row) {
    $schools[$row['school_number']] = $row['school_id'];
}

$i = '';
$raffle_id = 213;
if (isset($_GET['id'])) {
    $i = '_' . $_GET['id'];
    switch (intval($_GET['id'])) {
        case 2:
            $raffle_id = 213;
            break;
        case 3:
            $raffle_id = 214;
            break;
        case 4:
            $raffle_id = 215;
            break;
    }
}

//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load("GrandRafflePrizes5781{$i}.xlsx");
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
            $school_number = intval( $value );
        } else {
            $prizes[] = intval( $value );
        }
    }
//     echo "School: " . $school_number . "<br />";
//     echo "<pre>"; print_r( $prizes ); echo "</pre>";
//     echo "<br /><br />";
    foreach ($prizes as $prize) {
        if ($prize > 0) {
            $res = $stmt->execute([
                ':raffle' => $raffle_id,
                ':prize'  => $prize,
                ':school' => $schools[$school_number]
            ]);
            if (!$res) {
                $success = false;
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