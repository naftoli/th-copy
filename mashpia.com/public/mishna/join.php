<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPExcel/IOFactory.php';

$admin_id = $admin_user['admin_id'];
$auth = $admin_auth[0];

require 'class.adminSchools.php';
$a = new AdminSchools( $admin_id, $auth );
$schools = $a->getSchools();

$students = [];
foreach ( $schools as $id => $school ) {
    //get names of all children
    $sql = "select u.user_id, u.user_serial, u.first, u.last, u.first_he, u.last_he, u.dob, u.gender, c.class_grade, c.class_sub 
            from users u 
            join classes c using (class_id) 
            where u.school_id = $id 
            and u.user_registered > 0 
            order by c.class_grade, c.class_sub, u.last, u.first";
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) {
        $students[$row['user_id']]['user_serial'] = $row['user_serial'];
        $students[$row['user_id']]['first'] = $row['first'];
        $students[$row['user_id']]['last'] = $row['last'];
        $students[$row['user_id']]['first_he'] = $row['first_he'];
        $students[$row['user_id']]['last_he'] = $row['last_he'];
        $students[$row['user_id']]['dob'] = $row['dob'];
        $students[$row['user_id']]['gender'] = $row['gender'];
        $students[$row['user_id']]['grade'] = empty( $row['class_sub'] ) ? $row['class_grade'] : $row['class_grade'] . '-' . $row['class_sub'];
    }
}

$school_id = null;
foreach ( $schools as $id => $school ) {
    $school_id = $id;
    break;
}
$file = "chevras_mishnayos_{$school_id}.xls";

//delete hebrew_names_school_id.xls if exists
if ( file_exists( $file ) ) {
    //chmod( $file, 0777 );
    unlink( $file );
}

// Read the file
$objPHPExcel = PHPExcel_IOFactory::load( $file );
$fields = ['User ID', 'Serial Number', 'Mishna Size', 'First Name', 'Last Name', 'First Name Hebrew', 'Last Name Hebrew',
            'DOB', 'Gender', 'Platoon', 'Base'];
$row = 1;
$column = 'A';
foreach( $fields as $field ) {
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $field );
    $column++;
}
$row++;
$column = 'A';

/ Change the file
foreach( $students as $id => $student ) {
    $dob = $student['dob'];
    if ( trim( $dob ) != "" ) {
        $dob = explode( '-', $student['dob'] );
    }
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", "$id" );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['user_serial'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", '' );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['first'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['last'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['first_he'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['last_he'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $dob );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['gender'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['grade'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $schools[$school_id] );
    $column = 'A';
    $row++;
}

//update protection
$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);

// Write the file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
$objWriter->save( $file );

echo "<a href='<?=$file?>'download file</a>";