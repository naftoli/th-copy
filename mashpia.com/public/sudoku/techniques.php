<?php
$rows = array(array(0,1,2,3,4,5,6,7,8), array(9,10,11,12,13,14,15,16,17), array(18,19,20,21,22,23,24,25,26), array(27,28,29,30,31,32,33,34,35), array(36,37,38,39,40,41,42,43,44), array(45,46,47,48,49,50,51,52,53), array(54,55,56,57,58,59,60,61,62), array(63,64,65,66,67,68,69,70,71), array(72,73,74,75,76,77,78,79,80));
$columns = array(array(0,9,18,27,36,45,54,63,72), array(1,10,19,28,37,46,55,64,73), array(2,11,20,29,38,47,56,65,74), array(3,12,21,30,39,48,57,66,75), array(4,13,22,31,40,49,58,67,76), array(5,14,23,32,41,50,59,68,77), array(6,15,24,33,42,51,60,69,78), array(7,16,25,34,43,52,61,70,79), array(8,17,26,35,44,53,62,71,80));
$boxes = array(array(0,1,2,9,10,11,18,19,20), array(3,4,5,12,13,14,21,22,23), array(6,7,8,15,16,17,24,25,26), array(27,28,29,36,37,38,45,46,47), array(30,31,32,39,40,41,48,49,50), array(33,34,35,42,43,44,51,52,53), array(54,55,56,63,64,65,72,73,74), array(57,58,59,66,67,68,75,76,77), array(60,61,62,69,70,71,78,79,80));
$rcbs = array($rows, $columns, $boxes);
$rcb_names = array("row", "column", "box");
$letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I");

function hiddens($size)
{
	global $cells;
	global $rcbs;
	
	// ***** Loop through the rows, then columns, then boxes ***** //
	for ($rcbs_number = 2; $rcbs_number < 3; $rcbs_number++)
	{
		$rcb = $rcbs[$rcbs_number];
		$rcb_name = $rcb_names[$rcbs_number];
		
		// ***** Loop through all nine rows, columns, and boxes ***** //
		for ($rcb_number = 8; $rcb_number < 9; $rcb_number++)
		{
			$numbers = array();
			
			// ***** Loop through all nine numbers ***** //
			for ($number = 0; $number < 9; $number++)
			{
				$found = 0;
				$cell_numbers = array();
				
				// ***** Loop through all nine cells ***** //
				for ($cell_number = 0; $cell_number < 9; $cell_number++)
				{
					$cell_no = $rcb[$rcb_number][$cell_number];
					
					if ($cells[$cell_no]->values[$number] == true)
					{
						$found++;
						array_push($cell_numbers, $cell_no);
					}
					
				}
				
				if ($found == $size)
				{
					array_push($numbers, array($number, $cell_numbers));					
					//echo "FOUND NUMBER:" . ($number + 1) . " IN " . $rcb_name . ($rcb_number + 1) . "<br />";
				}
				
			}
			
			for ($nno = 0; $nno < count($numbers); $nno++)
			{
				echo "NUMBER:" . ($numbers[$nno][0] + 1) . "<br />";
				for ($cno = 0; $cno < count($numbers[$nno][1]); $cno++)
				{
					echo "CELL:" . ($numbers[$nno][1][$cno] + 1) . "<br />";
				}
			}
			
			//echo "AMOUNT OF NUMBERS:" . count($numbers) . "<br />";
			//var_dump($numbers);
			
		}
	
	}
}

function nakeds($size)
{
	global $cells;
	global $rcbs;
	
	// ***** Loop through the rows, then columns, then boxes ***** //
	for ($rcbs_number = 0; $rcbs_number < 3; $rcbs_number++)
	{
		$rcb = $rcbs[$rcbs_number];
		$rcb_name = $rcb_names[$rcbs_number];
		
		// ***** Loop through all nine rows, columns, and boxes ***** //
		for ($rcb_number = 0; $rcb_number < 9; $rcb_number++)
		{
		
			// ***** Loop through the nine cells ***** //
			for ($cell_number = 0; $cell_number < 8; $cell_number++)
			{
				$cell_no = $rcb[$rcb_number][$cell_number];
				
				if (strlen($cells[$cell_no]->numbers) == 2)
				{
					check_rest_of_rcb_for_naked($rcb, $rcb_number, $cell_number, $cell_no, $cells[$cell_no]->numbers, $size);
				}
				
			}
			
		}
		
	}
}

function check_rest_of_rcb_for_naked($rcb, $rcb_number, $cell_number_1, $cell_number_2, $numbers, $size)
{
	global $cells;
	
	$cell_numbers = array();
	array_push($cell_numbers, $cell_number_2);
	
	$found = 0;
	
	for ($cell_number = ($cell_number_1 + 1); $cell_number < 9; $cell_number++)
	{
		$cell_no = $rcb[$rcb_number][$cell_number];
		if ($cells[$cell_no]->numbers == $numbers)
		{
			$found++;
			array_push($cell_numbers, $cell_no);
		}
	}
	
	if ($found == ($size - 1))
	{
		eliminate_naked_from_other_cells($rcb, $rcb_number, $numbers, $cell_numbers);
	}
	
}

function eliminate_naked_from_other_cells($rcb, $rcb_number, $numbers, $cell_numbers)
{
	global $cells;
	global $solution;
	
	for ($cell_number = 0; $cell_number < 9; $cell_number++)
	{
		$cell_no = $rcb[$rcb_number][$cell_number];
		
		if (in_array($cell_no, $cell_numbers) == false)
		{
			$cell_value = $cells[$cell_no]->numbers;
			
			if ($cell_value != "")
			{
				
				for ($cno = 0; $cno < strlen($cell_value); $cno++)
				{
					$number = substr($cell_value, $cno, 1);
					$strpos = strpos($numbers, $number);
					
					if ($strpos > -1)
					{
						$solution = $solution . "<span style='color:green;'>Found naked " . $numbers . " in ";
						for ($cnos = 0; $cnos < count($cell_numbers); $cnos++)
						{
							$solution = $solution . get_cell_name($cell_numbers[$cnos]) . ",";
						}
						$solution = substr($solution, 0, strlen($solution) - 1);
						$solution = $solution . "</span><br />";
						
						$solution = $solution . "<span style='color:red;'>Rermoving " . $number . " from " . get_cell_name($cell_no) . "<br />";
						$cells[$cell_no]->background_colors[$number - 1] = "red";
					}
					
				}
			
			}
			
		}
	}
}

function singles_left()
{
	global $cells;
	global $solution;
	
	$singles_left = false;
	
	for ($cell_no = 0; $cell_no < 81; $cell_no++) 
	{
		if ($cells[$cell_no]->value == "")
		{
			$found = 0;
			$green_number = 0;
			
			for ($number = 0; $number < 9; $number++)
			{
			
				if ($cells[$cell_no]->values[$number] == true)
				{
				
					if ($cells[$cell_no]->background_colors[$number] == "green")
					{
						$found = 0;
						break;
					}
					elseif ($cells[$cell_no]->background_colors[$number] == "")
					{
						$found++;
						$green_number = $number;
					}
					
				}
				
			}
			
			if ($found == 1)
			{
				$singles_left = true;
				$cells[$cell_no]->background_colors[$green_number] = "green";
				$solution = $solution . "<span style='color:green;'>There is only a " . ($green_number + 1) . " remaing in " . get_cell_name($cell_no) . "</span><br />";
				eliminate_number_from_rcb_two($cell_no, $green_number);
			}
			
		}
	}
	
	return $singles_left;
}

function rcb_singles()
{
	global $cells;
	global $rows;
	global $columns;
	global $boxes;
	global $rcbs;
	global $rcb_names;
	global $letters;
	global $solution;
	
	$rcb_single = false;
	
	// ***** Loop through the rows, then columns, then boxes ***** //
	for ($rcbs_number = 0; $rcbs_number < 3; $rcbs_number++)
	{
		$rcb = $rcbs[$rcbs_number];
		$rcb_name = $rcb_names[$rcbs_number];
		//echo $rcb_name . "<br />";
		
		// ***** Loop through all nine rows, columns, and boxes ***** //
		for ($rcb_number = 0; $rcb_number < 9; $rcb_number++)
		{
		
			// ***** Loop through the nine numbers ***** //
			for ($number = 0; $number < 9; $number++)
			{
				$found = 0;
				$single_cell = 99;
				
				// ***** Loop through the nine cells ***** //
				for ($cell_number = 0; $cell_number < 9; $cell_number++)
				{
					$cell_no = $rcb[$rcb_number][$cell_number];
					
					if ($cells[$cell_no]->value == "" && $cells[$cell_no]->values[$number] == true)
					{
						$found++;
						$single_cell = $cell_no;
					}
				}
				// ***** Loop through the nine cells ***** //
				
				if ($found == 1 && $cells[$single_cell]->rcb_single == false)
				{
					$rcb_single = true;
					
					$cells[$single_cell]->background_colors[$number] = "green";
					$cells[$single_cell]->rcb_single = true;
					
					if ($rcb_names[$rcbs_number] == "row")
						$solution = $solution . "<span style='color:green;'>Found a single " . ($number + 1) . " in " . $rcb_name . " " . $letters[$rcb_number] . "</span><br />";
					else
						$solution = $solution . "<span style='color:green;'>Found a single " . ($number + 1) . " in " . $rcb_name . " " . ($rcb_number + 1) . "</span><br />";
						
					remove_rest_of_numbers_from_cell($single_cell, $number);
					eliminate_number_from_rcb_two($single_cell, $number);
				}
				
			}
			// ***** Loop through the nine numbers ***** //
			
		}
		// ***** Loop through all nine rows, columns, and boxes ***** //
		
	}
	// ***** Loop through the rows, then columns, then boxes ***** //
	
	return $rcb_single;
}

function remove_rest_of_numbers_from_cell($single_cell, $single_number)
{
	global $cells;
	global $solution;
	
	for ($no = 0; $no < 9; $no++)		
	{
	
		if ($no != $single_number)				
		{
		
			if ($cells[$single_cell]->values[$no] == true)
			{
				$cells[$single_cell]->background_colors[$no] = "red";
				$solution = $solution . "<span style='color:red;'>Removing " . ($no + 1) . " from " . get_cell_name($single_cell) . "</span><br />";
			}
			
		}
		
	}
	
}

function singles($puzzle)
{
	global $cells;
	global $solution;
	
	$singles_found = false;
	
	// ***** Loop through all the cells ***** //
	for ($cell_no = 0; $cell_no < 81; $cell_no++)
	{
		
		// ***** If the cell was not one of the original numbers enetered ***** //
		if ($cells[$cell_no]->entered == false && $cells[$cell_no]->single == false)
		{
		
			$found = 0;
			$number = 0;
			for ($no = 0; $no < 9; $no++)
			{
				if ($cells[$cell_no]->values[$no] == true && $cells[$cell_no]->background_colors[$no] != "red")
				{
					$found++;
					$number = $no;
				}
			}
			
			if ($found == 1)
			{
				$solution = $solution . "<span style='color:green;'>Found a single " . ($number + 1) . " in cell " . $cell_no . "</span><br />";
				$cells[$cell_no]->background_colors[$number] = "green";
				$cells[$cell_no]->single = true;
				$singles_found = true;
				
				eliminate_number_from_rcb_two($cell_no, $number);
			}
		}
		// ***** If the cell was not one of the original numbers enetered ***** //
		
	}
	// ***** Loop through all the cells ***** //
	
	return $singles_found;
}

function eliminate_number_from_rcb_two($cell_no_1, $number)
{
	global $cells;
	global $rows;
	global $columns;
	global $boxes;
	global $rcbs;
	global $solution;
	
	$row_number = $cells[$cell_no_1]->row_no;
	$column_number = $cells[$cell_no_1]->column_no;
	$box_number = $cells[$cell_no_1]->box_no;
	
	for ($rcbs_number = 0; $rcbs_number < 3; $rcbs_number++)
	{
		if ($rcbs_number == 0)
			$rcb = $rcbs[$rcbs_number][$row_number];
		elseif ($rcbs_number == 1)
			$rcb = $rcbs[$rcbs_number][$column_number];
		elseif ($rcbs_number == 2)
			$rcb = $rcbs[$rcbs_number][$box_number];
		
		for ($cell_number = 0; $cell_number < 9; $cell_number++)
		{
			$cell_no_2 = $rcb[$cell_number];
			
			if ($cell_no_1 != $cell_no_2 && $cells[$cell_no_2]->values[$number] == true && $cells[$cell_no_2]->background_colors[$number] != "red")
			{
				$solution = $solution . "<span style='color:red;'>Removing " . ($number + 1) . " from cell " . get_cell_name($cell_no_2) . "</span><br />";
				$cells[$cell_no_2]->background_colors[$number] = "red";		
			}
			
		}
	}
		
}

function get_cell_name($cell_no)
{
	global $cells;
	global $letters;
	
	$row_no = $cells[$cell_no]->row_no;
	$column_no = $cells[$cell_no]->column_no;
	
	return $letters[$row_no] . ($column_no + 1) . "";
	
}

function eliminate_entered_values_from_rcbs()
{
	global $cells;
	
	for ($cell_no = 0; $cell_no < 81; $cell_no++)
	{
		if ($cells[$cell_no]->entered == true) 
			eliminate_number_from_rcb($cell_no, $cells[$cell_no]->value);
	}
}

function eliminate_number_from_rcb($cell_no_1, $number)
{
	global $cells;
	global $rows;
	global $columns;
	global $boxes;
	
	$row_number = $cells[$cell_no_1]->row_no;
	$column_number = $cells[$cell_no_1]->column_no;
	$box_number = $cells[$cell_no_1]->box_no;
	
	for ($cell_no_2 = 0; $cell_no_2 < 9; $cell_no_2++)
	{	
		$row_cell_no = $rows[$row_number][$cell_no_2];
		$column_cell_no = $columns[$column_number][$cell_no_2];
		$box_cell_no = $boxes[$box_number][$cell_no_2];
		
		if ($cells[$row_cell_no]->value == "" && $cells[$row_cell_no]->values[$number - 1] == true)	
			$cells[$row_cell_no]->background_colors[$number - 1] = "red";
		
		if ($cells[$column_cell_no]->value == "" && $cells[$column_cell_no]->values[$number - 1] == true)	
			$cells[$column_cell_no]->background_colors[$number - 1] = "red";

		if ($cells[$box_cell_no]->value == "" && $cells[$box_cell_no]->values[$number - 1] == true)	
			$cells[$box_cell_no]->background_colors[$number - 1] = "red";
	}
}

function draw_board_two($puzzle)
{
	global $board;
	global $cells;

	$cell_no = 0;
	$top = 40;
	for ($row_no = 0; $row_no < 9; $row_no++)
	{
		$board = $board . "\n\t\t\t\t<div name='row_" . $row_no ."' style='position:absolute; top:" . $top . ";'>";
		
		$left = 240;
		for ($td_no = 0; $td_no < 9; $td_no++)
		{
			if (get_box_number(($row_no * 9) + $td_no) % 2 == 0 )
				$background_color = "#CCCCFF";
			else
				$background_color = "#CCFFCC";	
		
			$board = $board . "\n\t\t\t\t\t<div style='background-color:" . $background_color . "; position:absolute; border:1px solid; height:40px; width:40px; left:" . $left . "px;'>";						
			
			$board = $board . $cells[$cell_no]->draw_cell();
						
			$board = $board . "\n\t\t\t\t\t</div>";
			
			$board = $board . "\n\t\t\t\t\t<input type='hidden' name='cell_" . $cell_no . "' id='cell_" . $cell_no . "' value=''>";
			
			$left = $left + 44;
			$cell_no++;
		}
		
		$board = $board . "\n\t\t\t\t</div>";	
		
		$top = $top + 44;
	}
	
}

function get_box_number($cell_number)
{
	$boxes = array(array(0,1,2,9,10,11,18,19,20), array(3,4,5,12,13,14,21,22,23), array(6,7,8,15,16,17,24,25,26), array(27,28,29,36,37,38,45,46,47), array(30,31,32,39,40,41,48,49,50), array(33,34,35,42,43,44,51,52,53), array(54,55,56,63,64,65,72,73,74), array(57,58,59,66,67,68,75,76,77), array(60,61,62,69,70,71,78,79,80));
	
	for ($box_no = 0; $box_no < 9; $box_no++) 
	{
		for ($cell_no = 0; $cell_no < 9; $cell_no++) 
		{
			if ($cell_number == $boxes[$box_no][$cell_no])
			{
				return $box_no;
				break 2;
			}
		}
	}
}

function draw_board_one()
{
	global $board;
	
	$top = 40;
	$cell_no = 0;
	for ($row_no = 0; $row_no < 9; $row_no++)
	{
		$board = $board . "\n\t\t\t\t<div name='row_" . $row_no ."' style='position:absolute; top:" . $top . ";'>";
		
		$left = 40;
		for ($td_no = 0; $td_no < 9; $td_no++)
		{
			if (get_box_number(($row_no * 9) + $td_no) % 2 == 0 )
				$background_color = "#CCCCFF";
			else
				$background_color = "#CCFFCC";	
		
			$board = $board . "\n\t\t\t\t\t<div style='background-color:" . $background_color . "; position:absolute; border:1px solid; height:40px; width:40px; left:" . $left . "px;'>";
			
			$board = $board . "<input type='text' id='cell_" . $cell_no . "' name='cell_" . $cell_no . "' maxlength='1' style='font-size:14pt; color:red; text-align:center; border:0px; height:40px; width:40px; background-color:" . $background_color . ";'>";
			
			$board = $board . "\n\t\t\t\t\t</div>";
			
			$left = $left + 44;
			$cell_no++;
		}
		
		$board = $board . "\n\t\t\t\t</div>";	
		
		$top = $top + 44;
	}
		
}
?>