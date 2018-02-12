<?php
require '../db.php';
require '../PHPExcel/IOFactory.php';

$year = 5776;
$file = "charidy_staff2.xlsx";
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
                $email = $value;
                break;
            case 2:
                $cell_number = $value;
                break;
            case 3:
                $grade = $value;
                break;
        }
    }
    $sql = "insert into charidy_school_staff set
            school_id = 0,
            school_name = '',
            staff_name = '" . $name . "',
            staff_type = 'teacher',
            email = '" . $email . "',
            cell_number = '" . $cell_number . "',
            grade = '" . $grade . "', 
            year = " . $year;
    //echo $sql . "<br />";
    $inserts[] = $sql;
}

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
