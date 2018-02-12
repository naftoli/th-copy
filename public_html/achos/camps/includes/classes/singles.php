<?

function set_to_solved($cell_no, $number) {
	global $row_solved;
	global $column_solved;
	global $box_solved;

	$row_solved[get_row_number($cell_no)][$number - 1] = true;	
	$column_solved[get_column_number($cell_no)][$number - 1] = true;
	$box_solved[get_box_number($cell_no)][$number - 1] = true;
}

function get_row_number($cell_no) {
	return floor($cell_no / 9);
}

function get_column_number($cell_no) {
	return floor($cell_no % 9);
}


function get_box_number($cell_no) {
	global $boxes;
	
	for ($box_no = 0; $box_no < 9; $box_no++) {
		for ($textbox_number = 0; $textbox_number < 9; $textbox_number++) {
			if ($cell_no == $boxes[$box_no][$textbox_number]) {
				return $box_no;
				break;
			}
		}
	}
}

function process_solved_cells() {
	global $textboxes;
	
	$return_value = 0;
	
	for ($cell_no = 0; $cell_no < count($textboxes); $cell_no++) {
		if ($textboxes[$cell_no]->solved == true) {
			set_to_solved($cell_no, $textboxes[$cell_no]->value);
			$return_value = $return_value + eliminate_from_row_column_and_box($cell_no, $textboxes[$cell_no]->value);
		}
	}
	
	return $return_value;
}

function eliminate_from_row_column_and_box($cell_no, $value) {
	global $textboxes;
	global $row_values;
	global $column_values;
	global $box_values;
	
	$return_value = 0;
	
	$row_no1 = $textboxes[$cell_no]->row_number;
	$column_no1 = $textboxes[$cell_no]->column_number;
	$box_no1 = $textboxes[$cell_no]->box_number;
	
	for ($txtbx_no = 0; $txtbx_no < 81; $txtbx_no++) {
	
		if ($txtbx_no != $cell_no) {
			$row_no2 = $textboxes[$txtbx_no]->row_number;
			$column_no2 = $textboxes[$txtbx_no]->column_number;
			$box_no2 = $textboxes[$txtbx_no]->box_number;
			
			if ($row_no1 == $row_no2 || $column_no1 == $column_no2 || $box_no1 == $box_no2) {
				$strpos = strpos($textboxes[$txtbx_no]->value, $value);
				
				if ($strpos > -1) {
					$return_value = $return_value + $textboxes[$txtbx_no]->eliminate_value($value);
					if (strlen($textboxes[$txtbx_no]->value) == 1)
						set_to_solved($txtbx_no, $textboxes[$txtbx_no]->value);
				}
				
			}
			
		}
		
	}
	
	return $return_value;
}

function check_for_singles() {
	global $textboxes;
	$return_value = 0;
	
	for ($no = 1; $no < 10; $no++) {
		for ($rcbno = 0; $rcbno < 9; $rcbno++) {
			$return_value = $return_value + check_rcbs_for_singles($no, $rcbno);
		}
	}
	
	return $return_value;
}

function check_rcbs_for_singles($no, $rcbno) {
	global $textboxes;
	global $rows;
	global $columns;
	global $boxes;
	
	////echo "0.3) TEXTBOX 55 VALUE:" . $textboxes[55]->value . "<br />";
	$return_value = 0;
	
	$search_string = $no . "";	
	$row_found = 0;
	$rw_txtbx = 0;
	$column_found = 0;
	$clmn_txtbx = 0;
	$box_found = 0;
	$bx_txtbx = 0;
	
	for ($tbno = 0; $tbno < 9; $tbno++) {
		$row_textbox = $rows[$rcbno][$tbno];
		$column_textbox = $columns[$rcbno][$tbno];
		$box_textbox = $boxes[$rcbno][$tbno];
		
		$strpos = strpos($textboxes[$row_textbox]->value, $search_string);		
		if ($strpos > -1) {
			$row_found++;
			$rw_txtbx = $row_textbox;
		}
		
		$strpos = strpos($textboxes[$column_textbox]->value, $search_string);		
		if ($strpos > -1) {
			$column_found++;
			$clmn_txtbx = $column_textbox;
		}
		
		$strpos = strpos($textboxes[$box_textbox]->value, $search_string);		
		if ($strpos > -1) {
			$box_found++;
			$bx_txtbx = $box_textbox;
		}		
	}
	
	if ($row_found == 1) {
		if ($textboxes[$rw_txtbx]->solved == false) {
			$return_value++;
			$textboxes[$rw_txtbx]->set_value_two($search_string);
			set_to_solved($rw_txtbx, $search_string);
			eliminate_from_row_column_and_box($rw_txtbx, $search_string);
		}
	}
	
	if ($column_found == 1) {		
		if ($textboxes[$clmn_txtbx]->solved == false) {
			$return_value++;
			$textboxes[$clmn_txtbx]->set_value_two($search_string);
			set_to_solved($clmn_txtbx, $search_string);
			eliminate_from_row_column_and_box($clmn_txtbx, $search_string);
		}
	}
	
	if ($box_found == 1) {
		if ($textboxes[$bx_txtbx]->solved == false) {
			$return_value++;
			$textboxes[$bx_txtbx]->set_value_two($search_string);
			set_to_solved($bx_txtbx, $search_string);
			eliminate_from_row_column_and_box($bx_txtbx, $search_string);			
		}
	}
	
	return $return_value;
}

function check_for_combos($size) {
	global $rcbs;
	global $rows;
	global $columns;
	global $boxes;
	global $textboxes;
	
	$return_value = 0;
	
	for ($rcbs_no = 0; $rcbs_no < 3; $rcbs_no++) {
		$rcb = $rcbs[$rcbs_no];
		
		for ($rcb_no = 0; $rcb_no < 9; $rcb_no++) {
			
			for ($cell_no = 0; $cell_no < 9; $cell_no++) {
				$textbox_number = $rcb[$rcb_no][$cell_no];
		
				if (strlen($textboxes[$textbox_number]->value) == $size) {
					$cell_1 = $textbox_number;
					
					if ($cell_no < 8) {
						$cell_2 = check_rest_of_rcb_for_naked($rcb, $rcb_no, $cell_no, $textboxes[$textbox_number]->value);
						if ($cell_2 > 0) {
							$return_value = $return_value + eliminate_value_from_other_cells($rcb, $rcb_no, $cell_1, $cell_2, $textboxes[$textbox_number]->value);
						}
					}
				}
				
			}
			
		}
	}
	
	return $return_value;
}

function eliminate_value_from_other_cells($rcb, $rcb_no, $cell_1, $cell_2, $value) {
	global $textboxes;
	global $technique_message;
	global $eliminate_message;
	
	$return_value = 0;
	
	for ($cll_no = 0; $cll_no < 9; $cll_no++) {
		$textbox_number = $rcb[$rcb_no][$cll_no];		
		
		if ($textbox_number != $cell_1 && $textbox_number != $cell_2) {			
		
			if ($textboxes[$textbox_number]->solved == false) {
			
				for ($cno = 0; $cno < strlen($textboxes[$textbox_number]->value); $cno++) {
					$number = substr($textboxes[$textbox_number]->value, $cno, 1);
					
					$strpos = strpos($value, $number);
					
					if ($strpos > -1) {
						$return_value++;
						$textboxes[$textbox_number]->insert_span($number);
						$eliminate_message = $eliminate_message . "Eliminating " . $number . " from textbox " . $textbox_number . "<br />";
						$technique_message = $technique_message . "Found naked " . $value . " in textboxes " . $cell_1 . " and " . $cell_2 . "<br />";
					}					
					
				}
				
			}
			
		}
		
	}
	
	return $return_value;
}

function check_rest_of_rcb_for_naked($rcb, $rcb_no, $cell_no, $value) {
	global $textboxes;

	for ($cll_no = ($cell_no + 1); $cll_no < 9; $cll_no++) {
		$textbox_number = $rcb[$rcb_no][$cll_no];
		if ($textboxes[$textbox_number]->value == $value) {
			return $textbox_number;
			break;
		}
	}
	
}
?>
