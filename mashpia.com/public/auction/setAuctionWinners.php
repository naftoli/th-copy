<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$auction = 82;
require '../db.php';
require '../PHPExcel/IOFactory.php';

//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load( "auction_winners.xlsx" );
$objWorksheet = $objPHPExcel->getActiveSheet();

$j = 1;
foreach ( $objWorksheet->getRowIterator() as $row ) {
    $i = 0;
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $prize = 0;
    $user = 0;
    foreach ( $cellIterator as $cell ) {
        $value = trim( $cell->getValue() );
        //echo $value;
        /*
        switch ($i++) {
            case 0:
                $order = $value;
                break;
            case 1:
                $prize = $value;
                break;
            case 2:
                $user = $value;
                break;
        }
         */
        if ($i == 0) {
            $prize = $value;
        } else if ($i == 1) {
            $user = $value;
        }
        $i++;
    }
    //echo "<br />";
    // get user id
    $sql = "select user_id from users where user_serial = " . $user;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $user_id = $row['user_id'];
        $sql = "insert into auction_winners 
                set auction_id = $auction, 
                user_id = $user_id, 
                prize_id = $prize, 
                display_order = " . $j++ . ", 
                quantity = 1";
        @mysql_query($sql);
    }
    echo "done.";
}