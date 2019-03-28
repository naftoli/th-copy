<?
require 'db.php';
require 'PHPExcel/IOFactory.php';

$sql = array();
if (isset($_POST['submit'])) {
	$file = "SystemTasks/missions.xlsx";
	if (file_exists($_FILES['missions']['tmp_name'])) {
	    if (move_uploaded_file($_FILES['missions']['tmp_name'], $file)) {
	    	
	    	//load spreadsheet
	        $objPHPExcel = PHPExcel_IOFactory::load( $file );
	        $objWorksheet = $objPHPExcel->getActiveSheet();
			
			foreach ( $objWorksheet->getRowIterator() as $row ) {
	            $cellIterator = $row->getCellIterator();
	            $cellIterator->setIterateOnlyExistingCells(false);
				
				foreach( $cellIterator as $k => $cell ) { 
	                if ($k == 0) {
	                	$name = trim($cell);
	                } else if ($k == 1) {
	                	$year = 5775; 
						$arrStart = explode(',', trim($cell));
	                    $start = jewishtojd($arrStart[0], $arrStart[1], $year);
	                } else if ($k == 2) {
	                	$year = 5775; 
						$arrEnd = explode(',', trim($cell));
	                    $end = jewishtojd($arrEnd[0], $arrEnd[1], $year);
	                }
				}
				$sql[] = "update date_tasks_missions set mission_name = \"" . mysql_real_escape_string($name) . "\" 
						where start_date = " . $start . " and end_date = " . $end . " 
						and subject_id = 40";
			}
		}
	}
	echo "<pre>"; print_r($sql); echo "</pre>"; 
	$updated = 0;
	foreach ($sql as $qry) {
		if (mysql_query($qry)) {
			$updated++;
		} else {
			echo mysql_error();
		}
	}
	echo "Updated: " . $updated;
} else {
?>
<html>
	<head>
		
	</head>
	
	<body>
		<form enctype="multipart/form-data" action="fixMissionNames.php" method="post">
            Choose file to upload:<br />
            <input name="missions" type="file" /><br /><br />
            <input type="submit" name="submit" value="Upload" />
        </form>
	</body>
</html>  
<? } ?>       	