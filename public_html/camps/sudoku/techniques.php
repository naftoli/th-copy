<?php
$rows = array(array(0,1,2,3,4,5,6,7,8), array(9,10,11,12,13,14,15,16,17), array(18,19,20,21,22,23,24,25,26), array(27,28,29,30,31,32,33,34,35), array(36,37,38,39,40,41,42,43,44), array(45,46,47,48,49,50,51,52,53), array(54,55,56,57,58,59,60,61,62), array(63,64,65,66,67,68,69,70,71), array(72,73,74,75,76,77,78,79,80));
$columns = array(array(0,9,18,27,36,45,54,63,72), array(1,10,19,28,37,46,55,64,73), array(2,11,20,29,38,47,56,65,74), array(3,12,21,30,39,48,57,66,75), array(4,13,22,31,40,49,58,67,76), array(5,14,23,32,41,50,59,68,77), array(6,15,24,33,42,51,60,69,78), array(7,16,25,34,43,52,61,70,79), array(8,17,26,35,44,53,62,71,80));
$boxes = array(array(0,1,2,9,10,11,18,19,20), array(3,4,5,12,13,14,21,22,23), array(6,7,8,15,16,17,24,25,26), array(27,28,29,36,37,38,45,46,47), array(30,31,32,39,40,41,48,49,50), array(33,34,35,42,43,44,51,52,53), array(54,55,56,63,64,65,72,73,74), array(57,58,59,66,67,68,75,76,77), array(60,61,62,69,70,71,78,79,80));

$rcbs = array($rows, $columns, $boxes);
$rcb_names = array("row", "column", "box");

function nakeds($length)
{
	global $solution;
	global $rcbs;
	global $cells;
	global $solution;

	// Row, Column, or Box //
	for ($rcbs_no = 2; $rcbs_no < 3; $rcbs_no++)
	{
		$rcb = $rcbs[$rcbs_no];
		
		// Row, Column, and Boxes 1 to 9 //
		for ($rcb_no = 0; $rcb_no < 1; $rcb_no++)
		{
			$valid_cells = array();
			for ($cell_no = 0; $cell_no < 9; $cell_no++)
			{
				if ($cells[$rcb[$rcb_no][$cell_no]]->solved == false && strlen($cells[$rcb[$rcb_no][$cell_no]]->value) == $length)
				{
					array_push($valid_cells, $rcb[$rcb_no][$cell_no]);
					//echo "CELL NUMBER:" . $rcb[$rcb_no][$cell_no] . "<br />";
				}
			}
			
			if (count($valid_cells) >= $length)
			{
				check_for_duplicates($valid_cells, $length);
			}
			
		}
		
	}
	// Row, Column, or Box //
}

function check_for_duplicates($cell_nos, $length)
{
	global $cells;
	
	for ($cno = count($cell_nos) - 1; $cno > -1; $cno--) 
	{
		echo $cells[$cell_nos[$cno]]->value . "<br />";
		
		check_rest_of_array($cell_nos, $cno, $cells[$cell_nos[$cno]]->value, $length);
	}
}

function check_rest_of_array($cell_nos, $index_no, $value, $length)
{
	global $cells;
	
	$found = 0;
	for ($cno = ($index_no - 1); $cno > -1; $cno--) 
	{
		if ($cells[$cell_nos[$cno]]->value == $value)
		{
			$found++;
		}
	}
	
	echo "FOUND:" . $found . " LENGTH:" . $length . "<br />";
	
	if ($found == ($length - 1))
	{
		echo "***FOUND***<br />";
	}
	
}

function singles()
{
	global $solution;
	global $rcbs;
	global $cells;
	global $solution;
	global $rcb_names;
	
	$return = true;
	
	for ($rcb_no = 0; $rcb_no < 9; $rcb_no++)
	{
	
		for ($rcbs_no = 0; $rcbs_no < 3; $rcbs_no++)
		{
			$rcb = $rcbs[$rcbs_no];
						
			for ($no = 1; $no < 10; $no++)
			{
				$number = $no . "";
				
				$found = 0;
				$cell_number = 0;
				
				for ($cell_no = 0; $cell_no < 9; $cell_no++)
				{
				
					if ($cells[$rcb[$rcb_no][$cell_no]]->solved == false)
					{
					
						if (strpos($cells[$rcb[$rcb_no][$cell_no]]->value, $number) > -1)
						{
							$found++;
							$cell_number = $rcb[$rcb_no][$cell_no];
						}
						
					}
					
				}
				
				if ($found == 1 && $cells[$cell_number]->single == false)
				{
					$solution = $solution . get_row_and_column($cell_number) . " Number " . $number . " is unique " . $rcb_names[$rcbs_no] . "<br />";
					$return = false;
					$cells[$cell_number]->single = true;
					$cells[$cell_number]->spans[$no - 1] = "green";
				}				
				
			}
			
		}
		
	}
	
	return $return;
	
}

function eliminate_from_rcbs()
{
	global $cells;
	global $rows;
	global $columns;
	global $boxes;
	
	$continue = true;
	
	for ($cell_no_1 = 0; $cell_no_1 < 81; $cell_no_1++)
	{
		
		if (strlen($cells[$cell_no_1]->value) == 1 && $cells[$cell_no_1]->solved == false)
		{
			//if (substr($_SESSION["puzzle"], $cell_no_1, 1) == ";")
			//{
			//	$continue = false;
			//	echo "CELL NO:" . $cell_no_1 . "<br />";
			//}
			$cells[$cell_no_1]->solved = true;
			
			$row_no = $cells[$cell_no_1]->row_no;
			$column_no = $cells[$cell_no_1]->column_no;
			$box_no = $cells[$cell_no_1]->box_no;
			
			//echo "BOX NO:" . $box_no . "<br />";
			
			for ($cell_no_2 = 0; $cell_no_2 < 9; $cell_no_2++)			
			{
				// ***** ROW ***** //
				$cell_no_3 = $rows[$row_no][$cell_no_2];				
				if ($cell_no_3 != $cell_no_1 && strpos($cells[$cell_no_3]->value, $cells[$cell_no_1]->value) > -1)
				{
					if ($continue == true)
						$continue = eliminate_value_from_cell($cell_no_3, $cells[$cell_no_1]->value);
					else
						eliminate_value_from_cell($cell_no_3, $cells[$cell_no_1]->value);
				}
				
				// ***** COLUMN ***** //
				$cell_no_3 = $columns[$column_no][$cell_no_2];		
				if ($cell_no_3 != $cell_no_1 && strpos($cells[$cell_no_3]->value, $cells[$cell_no_1]->value) > -1)
				{
					if ($continue == true)
						$continue = eliminate_value_from_cell($cell_no_3, $cells[$cell_no_1]->value);
					else
						eliminate_value_from_cell($cell_no_3, $cells[$cell_no_1]->value);
				}
				
				// ***** BOX ***** //
				$cell_no_3 = $boxes[$box_no][$cell_no_2];				
				if ($cell_no_3 != $cell_no_1 && strpos($cells[$cell_no_3]->value, $cells[$cell_no_1]->value) > -1)
				{
					if ($continue == true)
						$continue = eliminate_value_from_cell($cell_no_3, $cells[$cell_no_1]->value);
					else
						eliminate_value_from_cell($cell_no_3, $cells[$cell_no_1]->value);
				}
			}
			
		}
		
	}
	
	//echo "1) CONTINUE:" . $continue . "<br />";
	return $continue;
}

function eliminate_value_from_cell($cell_no, $value)
{
	global $cells;
	global $solved;
	
	$continue = true;
	$new_value  = "";
	
	for ($cno = 0; $cno < strlen($cells[$cell_no]->value); $cno++)
	{
		$character = substr($cells[$cell_no]->value, $cno, 1);
		
		if ($character != $value)
			$new_value = $new_value . $character;
		else
			$continue = false;
			
	}
	
	if (strlen($new_value) == 1)
	{
		$solved++;
	}
	$cells[$cell_no]->value = $new_value;
	
	return $continue;
}

function get_row_and_column($cell_no)
{
	global $cells;
	
	$rows = array("A", "B", "C", "D", "E", "F", "G", "H", "I");
	
	return $rows[$cells[$cell_no]->row_no] . ($cells[$cell_no]->column_no + 1);
	
}
?>