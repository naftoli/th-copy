<?
$admin_auth = array('school'); 
require('header.php');
require 'PHPExcel/IOFactory.php';

if (isset($_FILES['file'])) {
    //update student emails
    $updatedStudents = array();
    
    //load spreadsheet
    $objPHPExcel = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
    $objWorksheet = $objPHPExcel->getActiveSheet();
    
    //get all info and save to database
    $headers = array();
    $firstRow = true;
    $msg = "";
    $errorLine = 1;
    
    foreach ($objWorksheet->getRowIterator() as $row) {
        if ($firstRow) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                $headers[] = trim($cell->getValue());
            } 
            $firstRow = false;
        } else {
            $i = 0; 
            $user_id = null; 
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell) {
                $value = trim($cell->getValue());
                switch ($headers[$i]) { 
                    case 'Student ID':
                        $user_id = $value;
                        break;
                    case 'First':
                        $updatedStudents[$user_id]['first'] = $value;
                        break;
                    case 'Last':
                        $updatedStudents[$user_id]['last'] = $value;
                        break;
					case 'Email':
						if (!empty($value) && !strpos($value, '@')) {
							$msg .= "Error on line $errorLine: email is invalid.<br />";
                            break;
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
    echo "<pre>";
    print_r( $updatedStudents );
    echo "</pre>";
    exit;
     * 
     */
    
    if ($msg == "") {
        //insert all values into db
        foreach ($updatedStudents as $user_id => $student) {
            $sql = "update users set 
                    first = '" . mysql_real_escape_string($student['first']) . "',  
                    last = '" . mysql_real_escape_string($student['last']) . "', 
                    email = '" . mysql_real_escape_string($student['email']) . "'";
            $sql .= " where user_id = " . $user_id;
			//echo $sql . "<br />";
            if (!@mysql_query($sql)) {
                $msg = "There was an error uploading your spreadsheet.<br />";
                $msg .= "Please contact Tzivos Hashem.<br />";
                $msg .= "Thank You!<br />";
                break;
            }
        }
        if ($msg == "") {
            $msg = "Your information was successfully updated.<br />";
			$msg .= "If you would like to send out an email to the parent(s) of the chayolim click <a href='student_emails.php'>here</a><br />";
            $msg .= "Thank You!<br />";
        }
    } else {
        $msg .= "Please correct the mistake(s) and then try again.<br />";
    }
}

require 'class.adminSchools.php';
$a = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $a->getSchools();

$students = array();
foreach ($schools as $id => $school) {
    //get names of all children 
    $sql = "select user_id, first, last, email 
            from users 
            where school_id = $id 
            and user_registered > 0 
            order by last, first";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) { 
        $students[$row['user_id']]['first'] = $row['first'];
        $students[$row['user_id']]['last'] = $row['last'];
        $students[$row['user_id']]['email'] = $row['email'];
    }
}

//add all children from school to spreadsheet
$inputFileName = 'student_emails.xls';

$school_id = null;
foreach ($schools as $id => $school) {
    $school_id = $id;
    break;
}
$file = "student_emails_{$school_id}.xls";

//delete hebrew_names_school_id.xls if exists
if (file_exists($file)) {
    unlink($file);
}

// Read the file
$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
$row = 2;
$column = 'A';

// Change the file
foreach($students as $id => $student) {
    $objPHPExcel->getActiveSheet()->setCellValue("$column$row", "$id");
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue("$column$row", $student['first']);
    $column++;
    $objPHPExcel->getActiveSheet()->setCellValue("$column$row", $student['last']);
	$column++;
    $objPHPExcel->getActiveSheet()->setCellValue("$column$row", $student['email']);
    $column = 'A';
    $row++;
}

//update protection
//$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);

// Write the file
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
$objWriter->save($file);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<title>Student Emails</title>
		<script src="jquery-1.8.1.min.js"></script>
        <script src="scripts/jquery.styleselect.js"></script>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Student Emails</h1>
		
		<? 
		if (isset($msg)) {
            echo "<div style='color:red'>" . $msg . "</div><br />";
        } 
        ?>
		
		<div class="infobox">
            <p>Directions:</p>
            <p>1. Please download <a href="<?=$file?>">spreadsheet</a>.</p>
            <p>2. Add/Edit emails to the file and save.</p>
            <p>3. Upload edited file.</p>
        </div>
        
        <div class="box_content">
            <form action="student_emails.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
	            <label><?=T_('Upload your saved spreadsheet')?>
	            <br /><input type="file" name="file" class="file"></label>
	            <br /><input type="submit" name="submit" value="upload" />
            </form>
        </div>
        
	</body>
</html>