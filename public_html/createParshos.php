<?
$admin_auth = array('school');
require_once 'header.php';

if ($admin_user['auth'] != 'super') {
    echo "Sorry you don't have the privilege(s) necessary to view this page.";
    exit;
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
    </head>
    
    <body> 
        <? require 'admin_header.php'; ?>
        <h1>Create Parshos</h1>
		<? 
		if (isset($_POST['submit'])) {
			require_once 'PHPExcel/IOFactory.php';
			$file = $_FILES['parshos']['tmp_name'];
			
			//load spreadsheet
			$objPHPExcel = PHPExcel_IOFactory::load($file);
			$objWorksheet = $objPHPExcel->getActiveSheet();
			
			$sql = array();
			foreach ($objWorksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
				$column = 0;
				foreach ($cellIterator as $cell) { 
                    $val = mysql_real_escape_string(trim($cell->getValue()));
					//echo $val . "<br />";
					switch ($column) {
						case 0:
							$name = $val;
							break;
						case 1:
							$start = $val;
							break;
						case 2:
							$end = $val;
							break;
						case 3:
							$year = $val;
							break;
					}
					$column++;
				}
				$sql[] = "insert into parshos values('', $start, $end, '$name', $year)";
			}
			
			$success = 0;
			$errors = array();
			foreach ($sql as $qry) {
				if (@mysql_query($qry)) {
					$success++;
				} else {
					$errors[] = $qry . ' ' . mysql_error();
				}
			}
			
			echo "Uploaded " . $success . " parshos";
			if (!empty($errors)) {
				foreach ($errors as $error) {
					echo $error . "<br />";
				}
			}
			exit;
		}
		?>
		<h2>Upload File</h2>
        <form enctype="multipart/form-data" action="createParshos.php" method="post">
            Choose file to upload:<br />
            <input name="parshos" type="file" id='file' /><br /><br />
            <input type="submit" name="submit" value="Upload" id='submit' />
        </form>
	</body>
</html>