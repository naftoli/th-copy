<?
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
            $user_id = 0; 
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
                    case 'First':
                        $updatedStudents[$user_id]['first'] = $value;
                        break;
                    case 'Last':
                        $updatedStudents[$user_id]['last'] = $value;
                        break;
                    case 'Hebrew First':
                        if ( empty( $value ) ) { 
                            $msg .= "Error on line $errorLine: $headers[$i] is mandatory.<br />";
                            break;
                        }
                        $updatedStudents[$user_id]['first_he'] = $value;
                        break;
                    case 'Hebrew Last':
                        if ( empty( $value ) ) { 
                            $msg .= "Error on line $errorLine: $headers[$i] is mandatory.<br />";
                            break;
                        }
                        $updatedStudents[$user_id]['last_he'] = $value;
                        break;
                    case 'English DOB': 
                        if ( empty( $value ) ) { 
                            $msg .= "Error on line $errorLine: $headers[$i] is mandatory.<br />";
                            break;
                        }
                        if ( is_numeric( $value ) ) {
                        	//checks if date was added to excel by admin                         
                            //process dob for entry into db
                            $dateError = "";
                            $date = PHPExcel_Shared_Date::ExcelToPHPObject( $value );
                            $timestamp = $date->getTimestamp();
                            $jd = unixtojd( $timestamp );
                            $dob = jdtogregorian( $jd );
                            $arrDate = explode( "/", $dob );                          
                        
                            if ( (int)$arrDate[0] > 12 || (int)$arrDate[0] < 1 ) {
                                $dateError .= "Error on line " . $errorLine . ": Month must be a number between 1 and 12.<br />";
                            }
                            if ( (int)$arrDate[1] > 31 || (int)$arrDate[1] < 1 ) {
                                $dateError .= "Error on line " . $errorLine . ": Day must be a number between 1 and 31.<br />";
                            }
                            
                            //get current year and make sure year is between 4 years ago and 14 years ago
                            $year = date( 'Y' );
                            $start = $year - 14;
                            $end = $year - 4;
                            if ( (int)$arrDate[2] < $start || (int)$arrDate[2] > $end ) {
                                $dateError .= "Error on line " . $errorLine . ": Year cannot be less than $start or more than $end.<br />";
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
                        } else {
                            if ( !strpos( $value, '/' ) ) {
                                $msg .= "Error on line " . $errorLine . ": Date of Birth must follow the format MM/DD/YYYY.<br />";
                            }
                        }
                        break;
					case 'Gender':
						switch ($value) {
							case 'M':
							case 'm':
								$updatedStudents[$user_id]['gender'] = 'M';
								break;
							case 'F':
							case 'f':
								$updatedStudents[$user_id]['gender'] = 'F';
								break;
						}
						break;
					case 'School Type':
						switch ($value) {
							case 'Chabad':
							case 'chabad':
								if ( $updatedStudents[$user_id]['gender'] == 'M' ) {
									$updatedStudents[$user_id]['school_type'] = 2;
								} else if ( $updatedStudents[$user_id]['gender'] == 'M' ) {
									$updatedStudents[$user_id]['school_type'] = 3;
								}
								break;
							case 'Frum':
							case 'frum':
								if ( $updatedStudents[$user_id]['gender'] == 'M' ) {
									$updatedStudents[$user_id]['school_type'] = 12;
								} else if ( $updatedStudents[$user_id]['gender'] == 'M' ) {
									$updatedStudents[$user_id]['school_type'] = 13;
								}
								break;
							default:
								$msg .= "Error on line " . $errorLine . ": School Type must be 'Chabad' or 'Frum'.<br />";
								break;
						}
						break;
					case 'Address':
						$updatedStudents[$user_id]['address'] = $value;
						break;
					case 'Address2':
						$updatedStudents[$user_id]['address2'] = $value;
						break;
					case 'City':
						$updatedStudents[$user_id]['city'] = $value;
						break;
					case 'State':
						$updatedStudents[$user_id]['state'] = $value;
						break;
					case 'Zip':
						$updatedStudents[$user_id]['zip'] = $value;
						break;
					case 'Country':
						$updatedStudents[$user_id]['country'] = $value;
						break;
					case 'Phone':
						$updatedStudents[$user_id]['phone'] = $value;
						break;
					case 'Email':
						if ( !empty( $value ) && !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
							$msg .= "Error on line " . $errorLine . ": Incorrect email format!<br />";
						}
						$updatedStudents[$user_id]['email'] = $value;
						break;
                }
                $i++;
            }
        }
        $errorLine++;
    }
    
	/*
    echo $msg;   
    echo "<pre>";
    print_r( $updatedStudents );
    echo "</pre>";
    exit;
	 * 
	 */
    
    if ( $msg == "" ) {
        //insert all values into db
        foreach ( $updatedStudents as $user_id => $student ) {
        	if ( $user_id == 0 ) {
        		//create student
        		$sql = "";
        	} else {
	            $sql = "update users set 
	                    first = '" . mysql_real_escape_string( $student['first'] ) . "',  
	                    last = '" . mysql_real_escape_string( $student['last'] ) . "', 
	                    first_he = '" . mysql_real_escape_string( $student['first_he'] ) . "', 
	                    last_he = '" . mysql_real_escape_string( $student['last_he'] ) . "', 
	                    gender = '" . mysql_real_escape_string( $student['gender'] ) . "', 
	                    school_type_id = " . mysql_real_escape_string( $student['school_type'] ) . ", 
	                    user_address1 = '" . mysql_real_escape_string( $student['address'] ) . "', 
	                    user_address2 = '" . mysql_real_escape_string( $student['address2'] ) . "', 
	                    user_city = '" . mysql_real_escape_string( $student['city'] ) . "', 
	                    user_state = '" . mysql_real_escape_string( $student['state'] ) . "', 
	                    user_postal = '" . mysql_real_escape_string( $student['zip'] ) . "', 
	                    user_country = '" . mysql_real_escape_string( $student['country'] ) . "', 
	                    user_phone = '" . mysql_real_escape_string( $student['phone'] ) . "', 
	                    email = '" . mysql_real_escape_string( $student['email'] ) . "'";	                    
	            if ( isset( $student['dob'] ) ) {
	                $sql .= ", dob = '" . mysql_real_escape_string( $student['dob'] ) . "', ";
					$sql .= "dob_he = '" . mysql_real_escape_string( $student['dob_he'] ) . "' ";
	            }
	            $sql .= " where user_id = " . $user_id;
			}
			//echo $sql . "<br />";
            if ( !@mysql_query( $sql ) ) {
                $msg = "There was an error uploading your spreadsheet.<br />";
                $msg .= "Please contact Tzivos Hashem.<br />";
                $msg .= "Thank You!<br />";
                break;
            }
            //add birthday mission/tasks
            require_once 'class.birthday.php';
            $b = new Birthday( $user_id );
            $b->setBirthday();
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
    $sql = "select u.user_id, u.first, u.last, u.first_he, u.last_he, u.dob, c.class_grade, c.class_sub, 
    		u.school_type_id, u.user_address1, u.user_address2, u.user_city, u.user_state, 
    		u.user_postal, u.user_country, u.user_phone, u.gender, u.email 
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
    	$students[$row['user_id']]['school_type'] = $row['school_type_id'];
    	$students[$row['user_id']]['address'] = $row['user_address1'];
    	$students[$row['user_id']]['address2'] = $row['user_address2'];
    	$students[$row['user_id']]['city'] = $row['user_city'];
    	$students[$row['user_id']]['state'] = $row['user_state'];
    	$students[$row['user_id']]['zip'] = $row['user_postal'];
		$students[$row['user_id']]['country'] = $row['user_country'];
		$students[$row['user_id']]['phone'] = $row['user_phone'];
		$students[$row['user_id']]['gender'] = $row['gender'];
		$students[$row['user_id']]['email'] = $row['email'];
    }
}

//add all children from school to spreadsheet
$inputFileName = 'user_info.xls';

$school_id = null;
foreach ( $schools as $id => $school ) {
    $school_id = $id;
    break;
}
$file = "user_info_{$school_id}.xls";

//delete hebrew_names_school_id.xls if exists
if ( file_exists( $file ) ) {
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
	$objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['gender'] );
    $column++;
    if ( is_array( $dob ) ) {
        $newDob = $dob[1] . '/' . $dob[2] . '/' . $dob[0];
        $objPHPExcel->getActiveSheet()->setCellValue( "$column$row", "$newDob" );
    }	
    $column++;
	$objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['address'] );
    $column++;
	$objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['address2'] );
    $column++;
	$objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['city'] );
    $column++;
	$objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['state'] );
    $column++;
	$objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['zip'] );
    $column++;
	$objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['country'] );
    $column++;
	$objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['phone'] );
    $column++;
	$objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $student['email'] );
	$column++;
	//find out if school type is chabad or frum
	switch( $student['school_type'] ) {
		case '2':
		case '3':
			$school_type = 'Chabad';
			break;
		case '12':
		case '13':
			$school_type = 'Frum';
			break;
		default:
			$school_type = '';
			break;
	}
	$objPHPExcel->getActiveSheet()->setCellValue( "$column$row", $school_type );
    $column = 'A';
    $row++;
}

//update protection
//$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);

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
            <p>2. Add/Edit editable fields and save file.</p>
            <p>3. Upload the file.</p>
        </div>
        <div class="box_content">
            <form action="user_info.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
            <label><?=T_('Upload your saved spreadsheet')?>
            <br /><input type="file" name="file" class="file"></label>
            <br /><input type="submit" name="submit" value="upload" />
            </form>
        </div>
    </body>
</html>