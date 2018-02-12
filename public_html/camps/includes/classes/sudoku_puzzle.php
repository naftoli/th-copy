<?php
include ("textbox.php");

$debug_msg = "";

$textboxes = array();
$techniques = array();
$technique_no = 0;
$removals = array();
$letters = array("A", "B", "C", "D", "E", "F", "G", "H", "I");

$action = "";

if (isset($_POST["action"])) 
{

	$action = $_POST["action"];
	
	include ("sudoku_techniques.php");
	
	$rows = array(array(0,1,2,3,4,5,6,7,8),array(9,10,11,12,13,14,15,16,17),array(18,19,20,21,22,23,24,25,26),array(27,28,29,30,31,32,33,34,35),array(36,37,38,39,40,41,42,43,44),array(45,46,47,48,49,50,51,52,53),array(54,55,56,57,58,59,60,61,62),array(63,64,65,66,67,68,69,70,71),array(72,73,74,75,76,77,78,79,80));
	$columns = array(array(0,9,18,27,36,45,54,63,72),array(1,10,19,28,37,46,55,64,73),array(2,11,20,29,38,47,56,65,74),array(3,12,21,30,39,48,57,66,75),array(4,13,22,31,40,49,58,67,76),array(5,14,23,32,41,50,59,68,77),array(6,15,24,33,42,51,60,69,78),array(7,16,25,34,43,52,61,70,79),array(8,17,26,35,44,53,62,71,80));
	$boxes = array(array(0,1,2,9,10,11,18,19,20),array(3,4,5,12,13,14,21,22,23),array(6,7,8,15,16,17,24,25,26),array(27,28,29,36,37,38,45,46,47),array(30,31,32,39,40,41,48,49,50),array(33,34,35,42,43,44,51,52,53),array(54,55,56,63,64,65,72,73,74),array(57,58,59,66,67,68,75,76,77),array(60,61,62,69,70,71,78,79,80));
	$rcbs = array($rows, $columns, $boxes);

	for ($cell_no = 0; $cell_no < 81; $cell_no++) {
		$post_item = "cell_" . $cell_no;
		$value = $_POST[$post_item];
		$textbox = new textbox($cell_no);		
		$textbox->set_value_one($value);
		
		if (strlen($value) == 1)
		{
			set_to_solved($cell_no, $value);
		}
		
		array_push($textboxes,$textbox);				
	}
		
	$continue = true;
	do
	{
		$continue = false;
	//	$continue = process_solved_cells();

	//	if ($continue == 0) {
	//		for ($ptq = 2; $ptq < 5; $ptq++) {
	//			for ($rcbsno = 0; $rcbsno < 3; $rcbsno++) {
	//				$continue = $continue + hidden_pts($ptq, $rcbsno);
	//			}
	//		}
	///	}
	} while ($continue == true);		
	
	//line_box_reduction($rows, "rows");
	//line_box_reduction($columns, "columns");
	
	for ($cell_no = 0; $cell_no < 81; $cell_no++) {
		//if ($textboxes[$cell_no]->span == false) 
		$textboxes[$cell_no]->set_inner_html();
	}	
	
}
else {
	for ($textbox_number = 0; $textbox_number < 81; $textbox_number++) {
		$textbox = new textbox($textbox_number);
		array_push($textboxes, $textbox);			
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

	<HEAD>
		<link href="sudoku.css" rel="stylesheet" type="text/css">
		
		<STYLE type="text/css">
			.entered {
				font-family:verdana, arial, sans-serif;
				font-size: 12pt;
				border-bottom: 1px solid #888888;
				border-top: 1px solid #888888;
				border-left: 1px solid #888888;
				border-right: 1px solid #888888;
				width:44px;
				height:44px;
				text-align:center;
				color:red;
			}
			
			.solved {
				font-family:verdana, arial, sans-serif;
				font-size: 12pt;
				border-bottom: 1px solid #888888;
				border-top: 1px solid #888888;
				border-left: 1px solid #888888;
				border-right: 1px solid #888888;
				width:44px;
				height:44px;
				text-align:center;
			}		
		</style>			
		<script>
			function load_puzzle() {
				//var puzzle = ";;;1;;5;;;;1;4;;;;;6;7;;;8;;;;2;4;;;;6;3;;7;;;1;;9;;;;;;;;3;;1;;;9;;5;2;;;;7;2;;;;8;;;2;6;;;;;3;5;;;;4;;9;;;";
				//var puzzle = ";;;;;4;;2;8;4;;6;;;;;;5;1;;;;3;;6;;;;;;3;;1;;;;;8;7;;;;1;4;;;;;7;;9;;;;;;2;;1;;;;3;9;;;;;;5;;7;6;7;;4;;;;;";
				//var puzzle = "4;;;;1;;;;;;;;3;;9;;4;;;7;;;;5;;;9;;;;;6;;;2;1;;;4;;7;;6;;;1;9;;;5;;;;;9;;;4;;;;7;;;3;;6;;8;;;;;;;;3;;;;6";
				//var puzzle = "4;;9;7;1;6;;;;6;1;;3;8;9;;4;;;7;;2;4;5;1;6;9;;;;9;6;4;;2;1;;;4;1;7;3;6;9;;1;9;6;8;5;2;;3;;9;6;;4;2;1;;7;;;3;;6;9;8;;;;;4;;5;3;7;9;;6";
				//var puzzle = ";8;;1;;;;;5;4;;;;6;;;;;7;6;5;2;4;;;9;;;9;;;7;;4;;;;;;;;5;1;;;8;1;;9;;;;;3;;;8;7;;6;;;;;;6;;;;;5;2;;;;;1;;7;3;";
				var puzzle = ";;;1;;5;;;;1;4;;;;;6;7;;;8;;;;2;4;;;;6;3;;7;;;1;;9;;;;;;;;3;;1;;;9;;5;2;;;;7;2;;;;8;;;2;6;;;;;3;5;;;;4;;9;;;;";
				
				var values = puzzle.split(";");
				for (tno = 0; tno < values.length; tno++) {
					var value = values[tno];
					var td = document.getElementById("textbox_" + tno);
					var cell = document.getElementById("cell_" + tno);
					
					if (value.length == 1) {
						td.innerHTML = value;
						cell.value = value;
						td.setAttribute("class", "entered");
					}
					else {
						cell.value = "123456789";
						td.innerHTML = "<table cellspacing='0' align='center' class='candtb' id='cl00'><tbody><tr><td id='w000'>1</td><td id='w001'>2</td><td id='w002'>3</td></tr><tr><td id='w003'>4</td><td id='w004'>5</td><td id='w005'>6</td></tr><tr><td id='w006'>7</td><td id='w007'>8</td><td id='w008'>9</td></tr></tbody></table>";
					}
					
				}
			}
		</script>


	</HEAD>

	<? if ($action == "") : ?>
	<BODY onload="load_puzzle();">
	<? else : ?>
	<BODY>
	<? endif; ?>
		
		<!-- ********** ROW letters and COLUMN numbers ********** -->
		<!--
		<div style="position:absolute; top:10px; left:60px;">1</div>
		<div style="position:absolute; top:10px; left:105px;">2</div>
		<div style="position:absolute; top:10px; left:150px;">3</div>
		<div style="position:absolute; top:10px; left:195px;">4</div>
		<div style="position:absolute; top:10px; left:240px;">5</div>
		<div style="position:absolute; top:10px; left:285px;">6</div>
		<div style="position:absolute; top:10px; left:330px;">7</div>
		<div style="position:absolute; top:10px; left:375px;">8</div>
		<div style="position:absolute; top:10px; left:420px;">9</div>
		
		<div style="position:absolute; top:50px; left:10px;">A</div>
		<div style="position:absolute; top:102px; left:10px;">B</div>
		<div style="position:absolute; top:154px; left:10px;">C</div>
		<div style="position:absolute; top:206px; left:10px;">D</div>
		<div style="position:absolute; top:258px; left:10px;">E</div>
		<div style="position:absolute; top:310px; left:10px;">F</div>
		<div style="position:absolute; top:362px; left:10px;">G</div>
		<div style="position:absolute; top:414px; left:10px;">H</div>
		<div style="position:absolute; top:466px; left:10px;">I</div>
		-->
		<!-- ********** ROW letters and COLUMN numbers ********** -->
		
		<FORM method="post" name="sudoku_form" id="sudoku_form" action="sudoku_puzzle.php" onsubmit="remove_spans(this);">
			<input type="hidden" name="action" value="solve">
			
			
			<TABLE name="sudoku_table" id="sudoku_table" style="position:absolute; top:40px; left:40px;">
				<? $prev_row = -1; ?>
				
				<? for ($textbox_number = 0; $textbox_number < count($textboxes); $textbox_number++) : ?>
									
					<? if ($prev_row != $textboxes[$textbox_number]->row_number) : ?>
						<? $prev_row = $textboxes[$textbox_number]->row_number; ?>
				<TR>			
						<? endif; ?>
						
							<input type="hidden" id="cell_<?=$textbox_number;?>" name="cell_<?=$textbox_number;?>" value="<?=$textboxes[$textbox_number]->value;?>">
							
					<TD id="textbox_<?=$textbox_number;?>" class="<?=$textboxes[$textbox_number]->class_name;?>" style="height:50px; width:40px; background-color:<?=$textboxes[$textbox_number]->background_color;?>; color:<?=$textboxes[$textbox_number]->color;?>;border:1px solid black;">
						<? if ($action != "") : ?>
						<?=$textboxes[$textbox_number]->inner_html;?>
						<? endif; ?>
					</TD>
							
						<? if ($textboxes[$textbox_number]->row_number > $prev_row && $prev_row != -1) : ?>
				</TR>
						<? endif; ?>
						
						<? $prev_row = $textboxes[$textbox_number]->row_number; ?>
					<? endfor; ?>
			</TABLE>			
			
			<input type="submit" value="STEP" style="position:absolute; top:550px; left:215px;">
		<FORM>
		

		<?
			echo "TEXTBOX 1:" . $textboxes[0]->value . "<brr />";
		?>
	</BODY>
	
</HTML>