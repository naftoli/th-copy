<?
$admin_auth = array('school');
require_once 'header.php';

if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}

$sql1 = "select subject_id, subject_name from subjects s 
        join school_type_subjects sts using (subject_id) 
        where s.subject_type in ('', 'WWTC', 'Tanya') 
        and sts.school_type_id in (2,3,12,13) 
        and s.subject_id != 91  
        order by s.subject_id";
$result1 = mysql_query($sql1);
$campaigns = array();
while ($row1 = mysql_fetch_assoc($result1)) {
    $campaigns[$row1['subject_id']] = $row1['subject_name'];
}

if (isset($_POST['submit'])) {
	if (file_exists($_FILES['tasks']['tmp_name'])) {
	    	
	    require_once 'PHPExcel/IOFactory.php';
	    //$subject_id = $_POST['subject'];
    
	    //load spreadsheet
	    $objPHPExcel = PHPExcel_IOFactory::load( $_FILES['tasks']['tmp_name'] );
	    $objWorksheet = $objPHPExcel->getActiveSheet();
		
		$sql = array();
		//$firstRow = true;
		foreach ( $objWorksheet->getRowIterator() as $row ) {
			
			//if ($firstRow) {
			//	$firstRow = false;
			//	continue;
			//}
			
	        $cellIterator = $row->getCellIterator();
	        $cellIterator->setIterateOnlyExistingCells(false);
			
			$i = 0;
			foreach ( $cellIterator as $cell ) { 
	            $val = trim($cell->getValue());
	            switch ($i++) {
					//case 0:
					//	$taskID = $val;
					//	break;
					case 0:
						$subject_id = $val;
						break;
					case 1:
						$short = $val;
						break;
					case 2:
						$task = $val;
					case 3:
						$picNameBoy = $val;
						break;
					case 4:
						$picNameGirl = $val;
						break;
	            }
			}
			//echo $short . '-' . $picNameBoy . "<br />";
			//if ($picNameBoy == '') continue;
			
			$types = array(2,3);
			foreach ($types as $type) {
				$mediumPic = $picNameBoy;
				if ($type == 3 || $type == 13) {
					if ($picNameGirl != '') {
						$mediumPic = $picNameGirl;
					}
				} 
				
				if ($mediumPic == '') continue;	
				$s = "update date_tasks dt 
						join date_tasks_missions dtm using (date_tasks_mission_id) 
						set dt.medium_pic = \"" . mysql_real_escape_string($mediumPic) . "\"  
						where dtm.subject_id = " . $subject_id . " 
						and dtm.school_type_id = " . $type . " 
						and dt.short_name = \"" . mysql_real_escape_string($short) . "\" 
						and dt.name like \"%" . mysql_real_escape_string($task) . "%\"";
				//echo $s . "<br />";
				$sql[] = $s;
			}
		}

		//echo "<pre>"; print_r($sql); echo "</pre>"; exit;
		$updated = 0;
		$errors = array();
		foreach ($sql as $qry) {
			if (mysql_query($qry)) {
				$updated++;
			} else {
				$errors[] = $qry . "<br />" . mysql_error();
			}
		}
		
		echo "Queries ran: " . count($sql) . "<br />";
		echo "Updated: " . $updated . "<br />";
		if ( !empty( $errors ) ) {
			echo "<pre>"; print_r($errors); echo "</pre>";
		}
	}
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type='text/javascript'>
            $(function() {
                $("#submit").click(function() {
                    if ($("#subject").val() == 0) {
                        alert("You have not chosen a campaign!");
                        return false;
                    }
                    if ($("#file").val() == '') {
                        alert("You have not uploaded a file!");
                        return false;
                    }
                });
            });
        </script>
        <style>
            td {
                font-size: 12px;
                font-family: Arial, Helvetica, sans-serif;
                border: 1px solid black;
            }
            #task_form, #files {
                font-size: 14px;
            }
            #files {
                line-height: 1.6;
            }
        </style>
    </head>
    
    <body> 
        <? require 'admin_header.php'; ?>
        <h1>Upload Mission Pictures</h1>
        
		<div id="task_form">
            <h2>Upload File</h2>
            <form enctype="multipart/form-data" action="uploadMissionPics.php" method="post">
            	<!--
                Choose Campaign: <br />
                <select name="subject" id='subject'>
                    <option value='0'>Choose One</option>
                    <?
                    foreach ($campaigns as $id => $campaign) {
                        echo "<option value='$id'>" . $campaign . "</option>";
                    }
                    ?>
                </select><br /><br />
               -->
                Choose file to upload:<br />
                <input name="tasks" type="file" id='file' /><br /><br />
                <input type="submit" name="submit" value="Upload" id='submit' />
            </form>
        </div>