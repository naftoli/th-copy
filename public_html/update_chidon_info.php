<?php
require 'db.php';
require 'PHPExcel/IOFactory.php';

//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load( "chidon_details.xlsx" );
$objWorksheet = $objPHPExcel->getActiveSheet();

$info = array();
$first = true;
foreach ( $objWorksheet->getRowIterator() as $row ) {
    if ( $first ) {
        $first = false;
        continue;
    }
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $j = 0;    
    foreach ( $cellIterator as $cell ) {
        $value = trim( $cell->getValue() );
        switch ( $j++ ) {
            case 0:
                $id = $value;
                break;
            case 1:
                $plaque = $value;
                break;
        }
    }
    /*
    // get team id
    $sql = "select team_id from th_chidon_teams where team = '" . $team . "'";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $team_id = $row['team_id'];
    
    // get bunk id
    $sql = "select bunk_id from th_chidon_bunks where bunk_name = '" . $bunk . "'";
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $bunk_id = $row['bunk_id'];
    
    $sql = "update th_chidon set
            team_id = " . $team_id . ",
            bunk_id = " . $bunk_id . ", 
            test_table = " . $test_table . ",
            bowling_lane = " . $bowling . ",
            school_bus = " . $school_bus . ",
            double_decker = " . $double_decker . " 
            where th_chidon_id = " . $id;
    */
    $sql = "update th_chidon set cert_number = " . $plaque . " where th_chidon_id = " . $id;
    //echo $sql . "<br />";
    mysql_query($sql) or die( $sql . "<br />" . mysql_error() );
}
echo "Done.";
