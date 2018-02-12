<?php
require 'db.php';
require 'PHPExcel/IOFactory.php';

//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load("toUpdate.xlsx");
$objWorksheet = $objPHPExcel->getActiveSheet();

$qrys = array();
$firstRow = true;
foreach ($objWorksheet->getRowIterator() as $row) {
    if ($firstRow) {
        $firstRow = false;
        continue;
    }
    $i = 0;
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    foreach ($cellIterator as $cell) {
        $value = trim($cell->getValue());
        switch ($i++) {
            case 0:
                $admin = $value;
                break;
            case 1:
                $last = $value;
                break;
            case 2:
                $phone = $value;
                break;
            case 3:
                $email = $value;
                break;
        }
    }
    $qrys[] = "update admins 
                set last = '" . $last . "',
                admin_phone_mobile = '" . $phone . "',
                admin_email = '" . $email . "'
                where admin_id = " . $admin;
}

$updated = 0;
foreach ($qrys as $qry) {
    echo $qry . "<br />";
    if (mysql_query($qry)) {
        $updated++;
    } else {
        echo mysql_error() . "<br />";
    }
}
echo "Updated: " . $updated;
?>