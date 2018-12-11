<?
$auction = 63;
require '../db.php';
require '../PHPExcel/IOFactory.php';

//load spreadsheet
$objPHPExcel = PHPExcel_IOFactory::load( "AllSmallWinners.xlsx" );
$objWorksheet = $objPHPExcel->getActiveSheet();

$j = 1;
$firstRow = true;
foreach ( $objWorksheet->getRowIterator() as $row ) {
    if ( $firstRow ) {
        $firstRow = false;
    } else {
    	$i = 0;
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
		$order = 0;
		$prize = 0;
		$user = 0;
        foreach ( $cellIterator as $cell ) {
        	$value = trim( $cell->getValue() );
			//echo $value;
			/*
			switch ($i++) {
				case 0:
					$order = $value;
					break;
				case 1:
					$prize = $value;
					break;
				case 2:
					$user = $value;
					break;
			}
			 */
			if ($i == 0) {
				$prize = $value;
			} else if ($i == 1) {
				$user = $value;
			}
			$i++;
		}
		//echo "<br />";
		$sql = "insert into auction_winners 
				set auction_id = $auction, 
				user_id = $user, 
				prize_id = $prize, 
				display_order = " . $j++ . ", 
				quantity = 1";
		@mysql_query($sql);
	}
}
?>