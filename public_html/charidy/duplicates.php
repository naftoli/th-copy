<?php
ini_set('display_errors', 1);
require '../db.php';
require '../PHPExcel/IOFactory.php';

function getDuplicateInfo($arr1, $arr2) {
    $duplicates = array();
    foreach ($arr1 as $id => $item) {
        if (!empty($item)) {
            foreach ($arr2 as $id2 => $item2) {
                if ($item == $item2) {
                    if ($id != $id2) {
                        $duplicates[$id][] = $id2;
                    }
                }
            }
        }
    }
    displayDuplicates($duplicates);
}

function displayDuplicates($duplicates) {
    foreach ($duplicates as $line => $other) {
        echo "Donor ID's:" . $line . ", ";
        foreach ($other as $line2) {
            echo $line2 . ", ";
        }
        echo "<br />";
    }
    echo "<br />";
}

$file = "master.xlsx";
//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load( $file );
$objWorksheet = $objPHPExcel->getActiveSheet();

$first = true;
$emails1 = array();
$emails2 = array();
$names1 = array();
$names2 = array();
$phones1 = array();
$phones2 = array();
$phones3 = array();
$patterns = array(
    '/\s+/',
    '/^[:digit:]/'
);

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
                $id = $value;
                break;
            case 1:
                $email1 = $value;
                break;
            case 2:
                $email2 = $value;
                break;
            case 3:
                $fname1 = $value;
                break;
            case 4:
                $lname1 = $value;
                break;
            case 5:
                $lname2 = $value;
                break;
            case 6:
                $fname2 = $value;
                break;
            case 7:
                $phone1 = preg_replace($patterns, '', $value);
                break;
            case 8:
                $phone2 = preg_replace($patterns, '', $value);
                break;
            case 9:
                $phone3 = preg_replace($patterns, '', $value);
                break;
        }
        $emails1[$id] = $email1;
        $emails2[$id] = $email2;
        $names1[$id] = $lname1;
        $names2[$id] = $lname2;
        $phones1[$id] = $phone1;
        $phones2[$id] = $phone2;
        $phones3[$id] = $phone3;
    }
}
$emailsD = array_intersect($emails1, $emails2);
$namesD = array_intersect($names1, $names2);
$phonesD = array_intersect($phones1, $phones2);
$phonesD2 = array_intersect($phones1, $phones3);

echo "Email Duplicates:<br />";
getDuplicateInfo($emailsD, $emails2);

echo "Last Names Duplicates:<br />";
echo "(It searches by last names ONLY, it can't check also first names as that would take much longer to program...)<br />";
getDuplicateInfo($namesD, $names2);


