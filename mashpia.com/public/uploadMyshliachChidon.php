<?php
ini_set('display_errors',1);
require 'db.php';
require 'PHPExcel/IOFactory.php';

$year = 5777;

$file = "MyshliachChidon.xlsx";
$objPHPExcel = PHPExcel_IOFactory::load($file);
$objWorksheet = $objPHPExcel->getActiveSheet();

$firstRow = true;
$msg = "";
$errorLine = 1;
$contestants = array();
foreach ( $objWorksheet->getRowIterator() as $row ) {
    if ( $firstRow ) {
        $firstRow = false;
    } else {
        $i = 0;
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ( $cellIterator as $cell ) {
            $value = trim( $cell->getValue() );
            switch ($i++) {
                case 0:
                    $serial_num = $value;
                    break;
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                case 6:
                    $tests[$i-2] = $value * 100;
                    break;
                case 7:
                    $previous = $value;
                    break;
            }
        }
        $contestants[] = array(
            'serial'    => $serial_num,
            'previous'  => explode('/', $previous),
            'tests'     => $tests
        );
    }
}

//echo "<pre>"; print_r($contestants); echo "</pre>";
$updated = 0;
foreach ($contestants as $child) {
    $sql = "select school_id, user_id from users 
            where user_serial = " . $child['serial'];
    $result = mysql_query($sql) or die(mysql_error());
    if (mysql_num_rows($result) > 0) {
        $row = mysql_fetch_assoc($result);
        $school_id = $row['school_id'];
        $user_id = $row['user_id'];
        $sql = "insert into th_chidon
                set year = " . $year . ",
                school_id = " . $school_id . ",
                user_id = " . $user_id . ",
                history = '" . implode(',', $child['previous']) . "',
                size = 'children m',
                reg_date = now(),
                test1a = " . $child['tests'][0] . ",
                test1b = " . $child['tests'][1] . ",
                test2a = " . $child['tests'][2] . ",
                test2b = " . $child['tests'][3] . ",
                test3a = " . $child['tests'][4] . ",
                test3b = " . $child['tests'][5];
        if (mysql_query($sql)) $updated++;
    }
}
echo "Updated: " . $updated;