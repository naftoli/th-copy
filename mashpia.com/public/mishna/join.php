<?php
ini_set('display_errors', 1);
$admin_auth = ['school'];

require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/PHPExcel/IOFactory.php';

$admin_id = $admin_user['admin_id'];
$auth = $admin_auth[0];

require  $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
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

$inputFile = 'mishna.xlsx';

$school_id = null;
foreach ( $schools as $id => $school ) {
    $school_id = $id;
    break;
}
$file = "chevras_mishnayos_{$school_id}.xls";

//delete file if exists
if ( file_exists( $file ) ) {
    //chmod( $file, 0777 );
    unlink( $file );
}

// Read the file
$objPHPExcel = PHPExcel_IOFactory::load( $inputFile );

// Change the file
$row = 2;
$column = 'A';
foreach( $students as $id => $student ) {
    $dob = $student['dob'];
    $newDob = '';
    if ( trim( $dob ) != "" ) {
        $dob = explode( '-', $student['dob'] );
        $newDob = $dob[1] . '/' . $dob[2] . '/' . $dob[0];
    }
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $id );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['user_serial'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $schools[$school_id] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['grade'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['first'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['last'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['first_he'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['last_he'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $newDob );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['gender'] );
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", '' );
    $column = 'A';
    $row++;
}

//update protection
//$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);

// Write the file
try {
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
    $objWriter->save($file);
} catch (Exception $e) {
    echo $e->getMessage();
}

include $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php';
?>
<html>
    <head>
        <link href="../admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            button {
                padding: 6px;
            }
        </style>
    </head>
    <body>
        <h1>Chevras Mishnayos</h1>
        <div>
            <a href="<?=$file?>">download file</a>
        </div>
        <br />
        <div>
            Use the following button to import all of your children's lines learned for both mishna and tanya.<br />
            <button id="import">Import Lines From chabadkid.com</button>
        </div>
    </body>
    <script>
        $("#import").click( function(e) {
            e.preventDefault()
            $(this).attr('disabled', true)
            let school_id = <?= $school_id ?>;
            $.get('../world/imports/import.php?school=' + school_id, function(success) {
                if (parseInt(success)) alert('Successfully imported.')
                else alert('Error importing.')
            })
        })
    </script>
</html>