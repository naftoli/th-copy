<?php

function get_row_number($cell_no) 
{
	return floor($cell_no / 9);
}

function get_column_number($cell_no) 
{
	return floor($cell_no % 9);
}

function get_box_number($cell_no) 
{
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

function set_to_solved($cell_no, $number) 
{
	global $row_solved;
	global $column_solved;
	global $box_solved;

	$row_solved[get_row_number($cell_no)][$number - 1] = true;	
	$column_solved[get_column_number($cell_no)][$number - 1] = true;
	$box_solved[get_box_number($cell_no)][$number - 1] = true;
}

function process_solved_cells() 
{
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

function eliminate_from_row_column_and_box($cell_no, $value) 
{
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
?>