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

function display_card_front($expires, $camp_name, $camp_city, $camp_state, $camp_logo_id) {
	global $align_start, $align_end;

	$return_string = "";
	
	$return_string = "\n\t\t<TD>\n";
	
	$return_string = $return_string . "\t\t\t<DIV class='card_front card_front_" . $align_start . "'>\n";	
	
	$return_string = $return_string . "\t\t\t\t<P>\n";
	$return_string = $return_string . "\t\t\t\t\t<IMG src='../images/Chayolei_Tzivos_Hashem.png' alt='" . T_('Chayolei Tzivos Hashem') . "'><BR>\n";
	$return_string = $return_string . "\t\t\t\t\t<B><IMG src='../images/Achievement_Card.png' alt='" . T_('Achievement Card') . "'></B>\n";
	$return_string = $return_string . "\t\t\t\t</P>\n";
	
	$return_string = $return_string . "\t\t\t\t<P>" . T_('This card is only valid in') . "</P>\n";
	
	// ***** TABLE ***** //
	$return_string = $return_string . "\t\t\t\t<TABLE>\n\t\t\t\t\t<TR>\n\t\t\t\t\t\t\t<TD>\n\t\t\t";
	$return_string = $return_string . "\t\t\t\t\t<TR>\n";
	
	// ***** TD ***** //
	if ($camp_logo_id > 0) {
		$return_string = $return_string . "\t\t\t\t\t\t<TD>\n";
		$return_string = $return_string . linkImgFile($camp_logo_id) . "\n";
		$return_string = $return_string . "\t\t\t\t\t\t</TD>\n";
	}
	// ***** TD ***** //
	
	// ***** TD ***** //
	$return_string = $return_string . "\t\t\t\t\t\t<TD>\n";
	$return_string = $return_string . "\t\t\t\t\t\t\t<B>" . es($camp_name) . "</B><BR>\t\t\t\t\t\t\t";
	$return_string = $return_string . es($camp_city) . ", " . es($camp_state) . "\n";
	$return_string = $return_string . "\t\t\t\t\t\t</TD>\n";
	// ***** TD ***** //
	
	$return_string = $return_string . "\t\t\t\t\t</TR>\n";
	$return_string = $return_string . "\t\t\t\t</TABLE>\n";
	// ***** TABLE ***** //
	
	$return_string = $return_string . "\t\t<P>" . T_('This card expires') . ": <B>" . dateToHebrewCommaYear($expires) . "</B>\n\t\t</P>\n";
	
	$return_string = $return_string . "\t\t\t</DIV>\n";
	
	$return_string = $return_string . "\t\t</TD>";
	
	return $return_string;
}

function display_card_back($id, $points, $left_circle, $task_name, $right_circle) {
	global $align_start, $align_end;

	$return_string = "";
	
	$return_string = "\n\t\t<TD>\n";
	
	$return_string = $return_string . "\n\t<DIV class='card_back'>\n";
	$return_string = $return_string . "\t\t<DIV class='border'>\n";
	
	// ***** TABLE ***** //
	$return_string = $return_string . "<TABLE style='width:100%;'>";
	$return_string = $return_string . "<TR>";
	
	// ***** TD ***** //
	$return_string = $return_string . "<TD style='width: 33%;'>";
	$return_string = $return_string . "<DIV class='circle'>";
	$return_string = $return_string . $left_circle;
	$return_string = $return_string . "</DIV>";
	$return_string = $return_string .  es($description);
	$return_string = $return_string .  "</TD>";
	// ***** TD ***** //
	
	// ***** TH ***** //
	$return_string = $return_string .  "<TH>";
	$return_string = $return_string . $task_name;
	//$return_string = $return_string .  (!is_null($subject_image_id) ? linkImgFile($subject_image_id) : '');
	$return_string = $return_string .  "</TH>";
	// ***** TH ***** //
	
	// ***** TD ***** //
	$return_string = $return_string . "<TD style='width: 33%;'>";
	$return_string = $return_string . "<DIV class='circle'>";
	$return_string = $return_string . $right_circle;
	$return_string = $return_string . "</DIV>";
	$return_string = $return_string . "</TD>";
	// ***** TD ***** //
	
	$return_string = $return_string . "</TR>";
	$return_string = $return_string . "</TABLE>";
	// ***** TABLE ***** //
	
	// ***** BAR CODE ***** //
	$return_string = $return_string . "<DIV class='barcode'>";
	$return_string = $return_string . "<IMG SRC='barcode.php/" . $id . "' alt=''><BR>" . $id;
	$return_string = $return_string . "</DIV>";
	// ***** BAR CODE ***** //
	
	// ***** MILES ***** //
	$return_string = $return_string . "<DIV class='points'>";
	$return_string = $return_string . "<TABLE>";
	$return_string = $return_string . "<TR>";
	$return_string = $return_string . "<TD>";
	$return_string = $return_string . "<DIV class='border'>";
	$return_string = $return_string . floatval($points) . " " . T_('Miles');
	$return_string = $return_string . "</DIV>";
	$return_string = $return_string . "</TD>";
	$return_string = $return_string . "</TR>";
	$return_string = $return_string . "</TABLE>";
	$return_string = $return_string . "</DIV>";
	// ***** MILES ***** //
	
	$return_string = $return_string . "\n\t\t</DIV>\n";
	$return_string = $return_string . "\n\t</DIV>\n";
	
	$return_string = $return_string . "\t\t</TD>";
	
	return $return_string;
}
?>
