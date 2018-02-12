<?
function dateToHebrew($date) {
	if(is_null($date))
		return '';
	else
		return mb_convert_encoding(jdtojewish($date, true, CAL_JEWISH_ADD_GERESHAYIM), 'UTF-8', 'ISO-8859-8');
}

function dateToHebrewCommaYear($date) {
	if(is_null($date)) 
		return '';
		
	@list($day, $month, $year, $year2) = mb_split(' ', dateToHebrew($date));
	
	if(!empty($year2)) {
		$month = "$month $year";
	}
	
	return "$day $month, $year";
}

function es($string) {
	return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}


function display_card_back($id, $amount, $number_of_pages) {

// scan this barcode to deposit 1 mile into your account
// 8 points tahoma

	global $align_start, $align_end;

	if ($amount == 1)
		$cents = " mile ";
	else
		$cents = " miles ";
		
	$return_string = "";
	
	$return_string = "\n\t\t<TD>\n";
	
	$return_string = $return_string . "\n\t<DIV class='card_back'>\n";
	$return_string = $return_string . "\t\t<DIV class='border'>\n";
	
	// ***** TABLE ***** //
	$return_string = $return_string . "<TABLE style='width:100%;'>";
	$return_string = $return_string . "<TR>";
	
	
	// ***** TD ***** //
	$return_string = $return_string . "<TD style='width: 33%; font:8px Tahoma, Verdana, Arial, Helvetica, sans-serif;'>";
	//$return_string = $return_string . "<DIV class='circle'>";
	//$return_string = $return_string . "<CENTER>" . $amount . $cents . "</CENTER>";
	
	$return_string = $return_string . "<CENTER>Scan this barcode to deposit " . $amount . $cents . " into your account</CENTER>";
	
	//$return_string = $return_string . "</DIV>";
	$return_string = $return_string . "</TD>";
	// ***** TD ***** //	
	
	$return_string = $return_string . "</TR>";
	$return_string = $return_string . "</TABLE>";
	// ***** TABLE ***** //
	
	// ***** BAR CODE ***** //
	$return_string = $return_string . "<DIV class='barcode'>";
	$return_string = $return_string . "<IMG SRC='barcode.php/" . $id . "' alt=''><BR><CENTER>" . $id . "</CENTER>";
	$return_string = $return_string . "</DIV>";
	// ***** BAR CODE ***** //
		
	$return_string = $return_string . "\n\t\t</DIV>\n";
	$return_string = $return_string . "\n\t</DIV>\n";
	
	$return_string = $return_string . "\t\t</TD>";
	
	return $return_string;
}
?>
