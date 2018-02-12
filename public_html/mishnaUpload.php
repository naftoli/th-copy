<?
$admin_auth = array('school');
require_once 'header.php';

if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}

if (isset($_POST['submit'])) {
	if (file_exists($_FILES['tasks']['tmp_name'])) {
		
		require_once 'PHPExcel/IOFactory.php';
		
		//load spreadsheet
	    $objPHPExcel = PHPExcel_IOFactory::load( $_FILES['tasks']['tmp_name'] );
	    $objWorksheet = $objPHPExcel->getActiveSheet();
		
		$sql = array();
		foreach ( $objWorksheet->getRowIterator() as $row ) {
			
	        $cellIterator = $row->getCellIterator();
	        $cellIterator->setIterateOnlyExistingCells(false);
			
			$i = 0;
			foreach ( $cellIterator as $cell ) { 
	            $val = trim($cell->getValue());
				switch ($i++) {
					case 0:
						$seder = $val;
						break;
					case 1:
						$mesechtaNumber = $val;
						break;
					case 2:
						$mesechta = $val;
						break;
					case 3:
						$perek = $val;
						break;
					case 4:
						$mishna = $val;
						break;
					case 5:
						$numLines = $val;
						break;
					default:
						break;
				}
			}
			if ($perek == 999 || $perek == 0 || $mishna == 999 || $mishna == 0) continue;
			$info[$seder][$mesechtaNumber][$mesechta][$perek][$mishna] = $numLines;
		}
	}

	//echo "<pre>"; print_r($info); echo "</pre>";
	$sedorim = array();
	$mesechtos = array();
	$mishnos = array();
	
	mysql_query("set autocommit = 0");
	mysql_query("begin");
	$success = true;
	foreach ($info as $seder => $arr) {
		$sedorim = "insert into sedorim set seder = '" . $seder . "'";
		if (mysql_query($sedorim)) {
			$sederID = mysql_insert_id();
			foreach ($arr as $other) {
				foreach ($other as $mesechta => $arr) {
					$mesechtos = "insert into mesechtos set 
									seder_id = $sederID, 
									mesechto = '" . $mesechta . "'";
					if (mysql_query($mesechtos)) {
						$mesechtoID = mysql_insert_id();
						foreach ($arr as $perek => $other) {
							foreach ($other as $mishna => $lines) {
								$mishnos = "insert into mishnos 
											set seder_id = $sederID, 
											mesechto_id = $mesechtoID, 
											perek = $perek, 
											mishna = $mishna,  
											num_lines = $lines";
								if (!mysql_query($mishnos)) {
									$success = false;
									$error = $mishnos . "<br />" . mysql_error();
									break 5;
								}
							} 
						}
					} else {
						$success = false;
						$error = mysql_error();
						break 3;
					}
				}
			}
		} else {
			$success = false;
			$error = mysql_error();
			break;
		}
	}
	
	if ($success) {
		mysql_query("commit");
	} else {
		mysql_query("rollback");
		echo $error;
	}
	mysql_query("set autocommit = 1");
	exit;
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
        <h1>Upload Mishna Info</h1>
        
		<div id="task_form">
            <h2>Upload File</h2>
            <form enctype="multipart/form-data" action="mishnaUpload.php" method="post">
                Choose file to upload:<br />
                <input name="tasks" type="file" id='file' /><br /><br />
                <input type="submit" name="submit" value="Upload" id='submit' />
            </form>
        </div>
	</body>
</html>