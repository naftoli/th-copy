<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<?php
		if (isset($_POST['submit'])) {
			if (file_exists($_FILES['parshos']['tmp_name'])) {
				require_once 'db.php';
				require_once 'PHPExcel/IOFactory.php';
				
				//load spreadsheet
				$file = $_FILES['parshos']['tmp_name'];
	            $objPHPExcel = PHPExcel_IOFactory::load( $file );
				$objWorksheet = $objPHPExcel->getActiveSheet();
				
				$columns = array('start', 'end', 'name', 'year');
				$qry = array();
				//$first = true;
				foreach ($objWorksheet->getRowIterator() as $row) {
					//if ($first) {
					//	$first = false;
					//	continue;
					//}
                	$cellIterator = $row->getCellIterator();
					$i = 0;
					$sql = "insert into parshos set ";
					foreach($cellIterator as $cell) { 
                    	$val = mysql_real_escape_string(trim($cell->getValue()));
						if ($i == 2) {
							$sql .= $columns[$i++] . " = \"" . $val . "\", ";
						} else if ($i == 3) {
							$sql .= $columns[$i++] . " = '" . $val . "'";
						} else {
							$sql .= $columns[$i++] . " = " . $val . ", ";
						}
					}
					$qry[] = $sql;
				}
				
				//echo "<pre>"; print_r($qry); echo "</pre>";
				foreach ($qry as $sql) {
					mysql_query($sql) or die( mysql_error() );
				}
				echo "done.";
			}
		} else {
		?>
		<form action="createParshos.php" method="post" enctype="multipart/form-data">
			<input type="file" name="parshos" /><br />
			<input type="submit" name="submit" value="submit" />
		</form>
		<? } ?>
	</body>
</html>