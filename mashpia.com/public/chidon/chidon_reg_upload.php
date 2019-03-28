<?
ini_set('display_errors', TRUE);
require '../PHPExcel/IOFactory.php';
require '../db.php';

if ( isset( $_FILES['file'] ) ) {
	//load spreadsheet
    $objPHPExcel = PHPExcel_IOFactory::load( $_FILES['file']['tmp_name'] );
    $objWorksheet = $objPHPExcel->getActiveSheet();
	
	//get all info and save to database
    $headers = array();
    $firstRow = true;
    $rowNum = 0;
	$reg = array();
	$error = '';
	
	$headers = array('grade', 'size', 'first', 'last', 'hfirst', 'hlast', 'book', 'test1', 
					'bonus', 'test2', 'test3', 'pname', 'pemail', 'pcell', 'notes');
	$columns = count($headers);
    
    foreach ( $objWorksheet->getRowIterator() as $row ) {
        if ( $firstRow ) {
            $firstRow = false;
			continue;
        } else {
            $i = 0; 
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ( $cellIterator as $cell ) {
            	$value = trim( $cell->getValue() );
				
            	if ($i == $columns) break;
				if ($i == 0 && empty($value)) break;
				
				//test3 and notes are optional
				if ($value == '' && !in_array($i, array(10,14))) {
					$error .= "You have some missing information on line " . ($rowNum + 2) . " column '" . $headers[$i] . "'<br />";
					break;
				}				
				
                $reg[$rowNum][$headers[$i++]] = mysql_real_escape_string( $value );
			}
		}
		$rowNum++;
	}
	//echo "<pre>"; 
	//print_r( $reg );
	
	if (empty($error)) {
		$qrys = array();
		$id = $_POST['school'];
		foreach ($reg as $row) {
			$sql = "insert into chidon_reg 
				set chidon_schools_id = $id, 
				grade = '" . $row['grade'] . "', 
				type = 'contestant',  
				name = '" . $row['first'] . "', 
				last_name = '" . $row['last'] . "', 
				hfname = '" . $row['hfirst'] . "', 
				hlname = '" . $row['hlast'] . "', 
				book = '" . $row['book'] . "', 
				fee = 115, 
				mark = 0, 
				mark1 = " . $row['test1'] . ", 
				mark2 = " . $row['test2'] . ", 
				mark3 = " . ($row['test3'] ? $row['test3'] : 0) . ", 
				bonus = " . $row['bonus'] . ", 
				notes = '" . $row['notes'] . "', 
				parent_name = '" . $row['pname'] . "', 
				parent_email = '" . $row['pemail'] . "', 
				parent_cell = '" . $row['pcell'] . "', 
				size = '" . $row['size'] . "'";
			$qrys[] = $sql;
		}
		//print_r($qrys);
		//echo "</pre>";
		
		$total = count($qrys);
		mysql_query("set autocommit=0");
		mysql_query("begin");
		$num = 0;
		foreach ($qrys as $qry) {
			if (@mysql_query($qry)) {
				$num++;
			} else {
				//echo mysql_error();
				break;
			}
		}
		
		if ($num == $total) {
			mysql_query("commit");
		} else {
			mysql_query("rollback");
			if (empty($error)) $error = "There was an error trying to save your information.";
		}
		mysql_query("set autocommit=1");
	}
	
	header("Location: register_" . $_POST['gender'] . ".php?id=" . $_POST['school'] . "&uerror=" . urlencode($error));
	exit;
}
?>