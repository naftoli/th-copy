<?php
require '../db.php';
require '../PHPExcel/IOFactory.php';

$year = 5776;
$file = "charidy_staff.xlsx";
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
                $school_id = empty($value) ? 0 : $value;
                break;
            case 1:
                $school_name = $value;
                break;
            case 2:
                $principal = $value;
                break;
            case 3:
                $email = $value;
                break;
            case 4:
                $office = $value;
                break;
            case 5:
                $cell_num = $value;
                break;
            case 6:
                break;
            case 7:
                $bc = $value;
                break;
            case 8:
                $email2 = $value;
                break;
            case 9:
                $cell2 = $value;
                break;
        }
    }
    $sql = "insert into charidy_school_staff set
            school_id = " . $school_id . ",
            school_name = '" . $school_name . "',
            staff_name = '" . $principal . "',
            staff_type = 'principal',
            email = '" . $email . "',
            office_number = '" . $office . "',
            cell_number = '" . $cell_num . "',
            year = " . $year;
    //echo $sql . "<br />";
    $inserts[] = $sql;
    $sql = "insert into charidy_school_staff set
            school_id = " . $school_id . ",
            school_name = '" . $school_name . "',
            staff_name = '" . $bc . "',
            staff_type = 'bc',
            cell_number = '" . $cell2 . "',
            email = '" . $email2 . "',
            year = " . $year;
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
