<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
	</head>

	<body>
<?	
require 'db.php';
require 'PHPExcel/IOFactory.php';

//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load( "downloads/allHeCats.xlsx" );
$objWorksheet = $objPHPExcel->getActiveSheet();

mysql_query("set autocommit=0");
mysql_query("begin");
$success = true;
foreach ( $objWorksheet->getRowIterator() as $row ) {
    $i = 0; 
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    foreach ( $cellIterator as $cell ) {
        $value = trim( $cell->getValue() );
        switch ( $i++ ) {
			case 0:
				$cat = $value;
				break;
			case 1:
				$task = $value;
				break;
		}
	}
	$sql = "update date_tasks set cat = \"" . mysql_real_escape_string($cat) . "\" 
			where name = \"" . mysql_real_escape_string($task) . "\"";
	//echo $sql . "<br />";
	if (! mysql_query($sql)) {
		echo mysql_error() . "<br />";
		$success = false;
		break 2;
	}
}

if ($success) {
	mysql_query("commit");
} else {
	mysql_query("rollback");
}
mysql_query("set autocommit=1");
?>
	</body>
</html>