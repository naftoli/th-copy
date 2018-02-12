<?
//error_reporting(E_ALL);
ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);

$admin_auth = array('school'); 
require('header.php');
require 'PHPExcel/IOFactory.php';
?>
<html>
    <head>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
<?
if ( isset( $_FILES['file'] ) ) {
	require 'class.heDob.php';
	
    //update hebrew names
    $updatedStudents = array();
    
    //load spreadsheet
    $objPHPExcel = PHPExcel_IOFactory::load( $_FILES['file']['tmp_name'] );
    $objWorksheet = $objPHPExcel->getActiveSheet();
    
    //get all info and save to database
    $headers = array();
    $firstRow = true;
    $msg = "";
    $errorLine = 1;
    
    foreach ( $objWorksheet->getRowIterator() as $row ) {
        if ( $firstRow ) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ( $cellIterator as $cell ) {
                $headers[] = trim( $cell->getValue() );
            } 
            $firstRow = false;
        } else {
            $i = 0; 
            $user_id = null; 
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ( $cellIterator as $cell ) {
                $value = trim( $cell->getValue() );
                switch ( $headers[$i] ) { 
                    case 'Student ID':
                        $user_id = $value;
                        break;
                    case 'Grade':
                        break;
                    case 'First Name':
                        $updatedStudents[$user_id]['first'] = $value;
                        break;
                    case 'Last Name':
                        $updatedStudents[$user_id]['last'] = $value;
                        break;
                    case 'Hebrew First Name':
                        if ( empty( $value ) ) { 
                            $msg .= "Error on line $errorLine: $headers[$i] is mandatory.<br />";
                            break;
                        }
                        $updatedStudents[$user_id]['first_he'] = $value;
                        break;
                    case 'Hebrew Last Name':
                        if ( empty( $value ) ) { 
                            $msg .= "Error on line $errorLine: $headers[$i] is mandatory.<br />";
                            break;
                        }
                        $updatedStudents[$user_id]['last_he'] = $value;
                        break;
                    case 'English DOB': 
						//echo $user_id . ": " . $value . "<br />"; 
                        if ( empty( $value ) ) { 
                            $msg .= "Error on line $errorLine: $headers[$i] is mandatory.<br />";
                            break;
                        }
						
						//process dob for entry into db
                        if ( is_numeric( $value ) ) {
                        	//excel dates when added by admin are numeric values usually                                                                           
                            $date = PHPExcel_Shared_Date::ExcelToPHPObject( $value );
                            $timestamp = $date->getTimestamp();
							$jd = unixtojd( $timestamp );
							$value = jdtogregorian( $jd );
						} else if ( !strpos( $value, '/' ) ) {
                            $msg .= "Error on line " . $errorLine . ": Date of Birth must follow the format MM/DD/YYYY.<br />";
                        	break;
						}                         
                        
						$dateError = "";
                        $arrDate = explode( "/", $value );
                        if ( (int)$arrDate[0] > 12 || (int)$arrDate[0] < 1 ) {
                            $dateError .= "Error on line " . $errorLine . ": Month must be a number between 1 and 12.<br />";
                        }
                        if ( (int)$arrDate[1] > 31 || (int)$arrDate[1] < 1 ) {
                            $dateError .= "Error on line " . $errorLine . ": Day must be a number between 1 and 31.<br />";
                        }
                        
                        //get current year and make sure year is not older than 14
                        $year = date( 'Y' );
                        $start = $year - 14;
                        //$end = $year - 4;
                        if ( (int)$arrDate[2] < $start ) {
                            $dateError .= "Error on line " . $errorLine . ": Year cannot be less than $start.<br />";
                        }
                        
                        if ( $dateError != "" ) {
                            $msg .= $dateError; 
                            $msg .= "Error on line " . $errorLine . ": Date of Birth must follow the format MM/DD/YYYY.<br />";
                        } else {
                            $updatedStudents[$user_id]['dob'] = $arrDate[2] . "-" . $arrDate[0] . "-" . $arrDate[1];
                            //determine hebrew date and add to database
                            $jd = gregoriantojd($arrDate[0], $arrDate[1], $arrDate[2]);
                            $jewish = jdtojewish($jd, true, CAL_JEWISH_ADD_GERESHAYIM + CAL_JEWISH_ADD_ALAFIM_GERESH);
                            $j = iconv('WINDOWS-1255', 'UTF-8', $jewish);
                            //echo $j;
                            $updatedStudents[$user_id]['dob_he'] = $j;                            
                        }
                        break;
                }
                $i++;
            }
        }
        $errorLine++;
    }
    
    /*
    echo "<pre>";
    print_r( $updatedStudents );
    echo "</pre>";
    exit;
     * 
     */
    
    if ( $msg == "" ) {
        //insert all values into db
        foreach ( $updatedStudents as $user_id => $student ) {
            $sql = "update users set 
                    first = '" . mysql_real_escape_string( $student['first'] ) . "',  
                    last = '" . mysql_real_escape_string( $student['last'] ) . "', 
                    first_he = '" . mysql_real_escape_string( $student['first_he'] ) . "', 
                    last_he = '" . mysql_real_escape_string( $student['last_he'] ) . "'";
            if ( isset( $student['dob'] ) ) {
                $sql .= ", dob = '" . mysql_real_escape_string( $student['dob'] ) . "',";
                $sql .= " dob_he = '" . mysql_real_escape_string( $student['dob_he'] ) . "'";
            }
            $sql .= " where user_id = " . $user_id;
			//echo $sql . "<br />";
            if ( !@mysql_query( $sql ) ) {
                $msg = "There was an error uploading your spreadsheet.<br />";
                $msg .= "Please contact Tzivos Hashem.<br />";
                $msg .= "Thank You!<br />";
                break;
            }
			
			// delete all existing birthday tasks
			$sql = "delete from birthdays where user_id = " . $user_id;
			mysql_query($sql);
	
            //add birthday mission/tasks
            require_once 'class.birthday.php';
            $b = new Birthday( $user_id );
            $b->setBirthday();
			require_once 'class.birthdayYi.php';
			$by = new BirthdayYi( $user_id );
			$by->setBirthday();
			
			//set dob for syncing with wp
			$hdob = new HeDob( $user_id );
			$hdob->setHeDob();
        }
        if ( $msg == "" ) {
            $msg = "Your information was successfully updated.<br />";
            $msg .= "Thank You!<br />";
        }
    } else {
        $msg .= "Please correct the mistake(s) and then try again.<br />";
    }
}

$admin_id = $admin_user['admin_id'];
$auth = $admin_auth[0];

require 'class.adminSchools.php';
$a = new AdminSchools( $admin_id, $auth );
$schools = $a->getSchools();

$students = array();
foreach ( $schools as $id => $school ) {
    //get names of all children 
    $sql = "select u.user_id, u.first, u.last, u.first_he, u.last_he, u.dob, c.class_grade, c.class_sub 
            from users u 
            join classes c using (class_id) 
            where u.school_id = $id 
            and u.user_registered > 0 
            order by c.class_grade, c.class_sub, u.last, u.first";
    $result = mysql_query( $sql );
    while ( $row = mysql_fetch_assoc( $result ) ) { 
        $students[$row['user_id']]['first'] = $row['first'];
        $students[$row['user_id']]['last'] = $row['last'];
        $students[$row['user_id']]['first_he'] = $row['first_he'];
        $students[$row['user_id']]['last_he'] = $row['last_he'];
        $students[$row['user_id']]['dob'] = $row['dob']; 
        $students[$row['user_id']]['grade'] = empty( $row['class_sub'] ) ? $row['class_grade'] : $row['class_grade'] . '-' . $row['class_sub'];
    }
}

//add all children from school to spreadsheet
$inputFileName = 'hebrew_names.xls';

$school_id = null;
foreach ( $schools as $id => $school ) {
    $school_id = $id;
    break;
}
$file = "hebrew_names_{$school_id}.xls";

//delete hebrew_names_school_id.xls if exists
if ( file_exists( $file ) ) {
	//chmod( $file, 0777 );
    unlink( $file );
}

// Read the file
$objPHPExcel = PHPExcel_IOFactory::load( $inputFileName );
$row = 2;
$column = 'A';

// Change the file
foreach( $students as $id => $student ) {
    $dob = $student['dob'];
    if ( trim( $dob ) != "" ) {
        $dob = explode( '-', $student['dob'] );
    }
    $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", "$id" );
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
    if ( is_array( $dob ) ) {
        $newDob = $dob[1] . '/' . $dob[2] . '/' . $dob[0];
        $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", "$newDob" );
    }
    $column = 'A';
    $row++;
}

//update protection
$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);

// Write the file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
$objWriter->save( $file );

include('admin_header.php');
?>    
        <h1>Update Student Info</h1>
        <? if ( isset( $msg ) ) {
            echo "<div style='color:red'>" . $msg . "</div><br />";
        } ?>
        <div class="infobox">
            <p>Directions:</p>
            <p>1. Please download <a href="<?=$file?>">spreadsheet</a>.</p>
            <p>2. Add/Edit Hebrew names and English DOB to the file and save file.</p>
            <p>3. Upload the file.</p>
        </div>
        <div class="box_content">
            <form action="hebrew_names.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
            <label><?=T_('Upload your saved spreadsheet')?>
            <br /><input type="file" name="file" class="file"></label>
            <br /><input type="submit" name="submit" value="upload" />
            </form>
        </div>
    </body>
</html>