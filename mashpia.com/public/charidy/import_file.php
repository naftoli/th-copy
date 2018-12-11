<?php
require '../db.php';
require '../PHPExcel/IOFactory.php';

$file = "charidy_list.xlsx";
//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load( $file );
$objWorksheet = $objPHPExcel->getActiveSheet();

$first = true;
$inserts = array();
foreach ( $objWorksheet->getRowIterator() as $row ) {
    if ($first) {
        $first = false;
        continue;
    }
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $i = 0;
    foreach ( $cellIterator as $cell ) {
        $value = trim( $cell->getValue() );
        switch ($i++) {
            case 0:
                $name = $value;
                break;
            case 1:
                $donation_5776 = $value;
                break;
            case 2:
                $rank_5776 = $value;
                break;
            case 3:
                $id = $value;
                break;
            case 4:
                if (empty($value)) $value = 0;
                $donation_5777 = $value;
                break;
            case 5:
                if (empty($value)) $value = 0;
                $rank_5777 = $value;
                break;
            case 6:
                if (empty($value)) $value = 0;
                $charidy_id = $value;
                break;
            case 7:
                $email = $value;
                break;
        }
    }
    $sql = "insert into charidy_final_list
            set mailing_id = " . $id . ",
            donation_5776 = " . $donation_5776 . ",
            rank_5776 = " . $rank_5776 . ",
            name = '" . $name . "',
            projected_5777 = " . $donation_5777 . ",
            rank_5777 = " . $rank_5777 . ",
            email = '" . $email . "',
            charidy_id = " . $charidy_id;
    //echo $sql . "<br />";
    $inserts[] = $sql;
}
//exit;
$success = true;
mysql_query("set_autocommit=0");
mysql_query("begin");
foreach ($inserts as $sql) {
    if (!mysql_query($sql)) {
        echo $sql . "<br />" . mysql_error();
        $success = false;
        break;
    }
}
if ($success) {
    mysql_query("commit");
    mysql_query("set autocommit=1");
    echo "success";
} else {
    mysql_query("rollback");
    mysql_query("set autocommit=1");
    echo "there were errors";
}
