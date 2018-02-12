<?php

session_start();

$action = "";
$puzzle = "";
$solution = "";

$boxes = array(array(0,1,2,9,10,11,18,19,20), array(3,4,5,12,13,14,21,22,23), array(6,7,8,15,16,17,24,25,26), array(27,28,29,36,37,38,45,46,47), array(30,31,32,39,40,41,48,49,50), array(33,34,35,42,43,44,51,52,53), array(54,55,56,63,64,65,72,73,74), array(57,58,59,66,67,68,75,76,77), array(60,61,62,69,70,71,78,79,80));

if (isset($_POST["action"]))
{
	echo "ACTION:" . $_POST["action"] . "<br />";
	
	include ("cell.php");
	include ("techniques.php");

	$action = $_POST["action"];	
	$solved = 0;
	$cells = array();	
	for ($cell_no = 0; $cell_no < 81; $cell_no++)
	{
		$value = $_POST["cell_" . $cell_no];		
		$cell = new cell($cell_no, $value);
		array_push($cells, $cell);
				
		if ($action == "step")
		{
			if (strlen($value) == 1) 
			{
				$puzzle = $puzzle . $value;
				$solved++;
			}
			else
			{
				$puzzle = $puzzle . ";";
			}
		}
		
	}
		
	if ($action == "step") 
	{
		$_SESSION["puzzle"] = $puzzle;		
	}
	
	
	$continue = true;
	$counter = 0;
	do {
		$continue = eliminate_from_rcbs();
		
		if ($continue == true)
			$continue = singles();
		
		if ($continue == true)
			$continue = nakeds(2);
		
		$counter++;
	} while ($solved < 81 && $continue == true && $counter < 10);
	
	for ($cell_no = 0; $cell_no < 81; $cell_no++) 
	{
		$cells[$cell_no]->set_inner_html($cell_no);
	}	
	
}
else
{
	$board = draw_board();
}

function get_box_number($cell_number)
{
	global $boxes;
	
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

function draw_board()
{
	$board = "\n\t\t<table style='position:absolute; top:20px; left:20px; border-spacing:1px;'>";
	$board = $board . "\n\t\t\t<tbody>";
	
	for ($row_no = 0; $row_no < 9; $row_no++) 
	{
		$board = $board . "\n\t\t\t\t<tr>";
		$board = $board . "\n\t\t\t\t\t<td>";
		
		$board = $board . "\n\t\t\t\t\t\t<table style='border-spacing:1px;'>";
		$board = $board . "\n\t\t\t\t\t\t\t<tbody>";
		$board = $board . "\n\t\t\t\t\t\t\t\t<tr>";
		for ($cell_no = 0; $cell_no < 9; $cell_no++) 
		{
			$cell_number = ($row_no * 9) + $cell_no;
			
			$box_no = get_box_number($cell_number);			
			
			if ($box_no % 2 == 0 )
				$background_color = "#CCCCFF";
			else
				$background_color = "#CCFFCC";	
			
			$board = $board . "\n\t\t\t\t\t\t\t\t\t<td>";
			$board = $board . "<input id='cell_" . $cell_number . "' name='cell_" . $cell_number . "' type='text' class='sudokuinput' align='center' style='background-color:" . $background_color . "; height:40px; width:40px; border:1px solid; maxlength='1'>";
			$board = $board . "\n\t\t\t\t\t\t\t\t\t</td>";
		}
		$board = $board . "\n\t\t\t\t\t\t\t\t</tr>";
		$board = $board . "\n\t\t\t\t\t\t\t</tbody>";
		$board = $board . "\n\t\t\t\t\t\t</table>";
		
		$board = $board . "\n\t\t\t\t</tr>";
		$board = $board . "\n\t\t\t\t\t</td>";
	}
	
	$board = $board . "\n\t\t\t</tbody>";
	$board = $board . "\n\t\t</table>";

	return $board;
}
?>

<html>
	<head>
		<script src="http://mashpia.com/kiosk/scripts/jquery.core.js" type="text/javascript"></script>

		<style type="text/css">
			.sudokuinput
			{
				padding:0px;
				margin:0px;
				border:0px;
				width: 34px; 
				height:34px; 
				text-align:center;
				font-weight:900;
				font-size:24px;
			}		
		</style>
		

		
		<script>
			//var puzzle = ";;;1;5;;;14;;;;67;;8;;;24;;;63;7;;1;9;;;;;;;3;1;;9;52;;;72;;;8;;26;;;;35;;;4;9;;;";
			//var puzzle = ";;;;;4;284;6;;;;;51;;;3;6;;;;;3;1;;;;87;;;14;;;;7;9;;;;;2;1;;;39;;;;;5;767;4;;;;;";
			//var puzzle = "4;;;1;;;;;;;3;9;4;;7;;;5;;9;;;;6;;21;;4;7;6;;19;;5;;;;9;;4;;;7;;3;6;8;;;;;;;3;;;6";
			//var puzzle = "6;9;;;;5;75;2;;14;;;;;6;9;;;;7;;;3;;;;;;;1;;;5;2;39;;8924;;;83;;1;;;;6;;8;;;;4;;5";
			//var puzzle = ";;;4;;;;853;;;7;;;;;;;;;9;687;;3;6;;;;;74;;;;;;68;;;;5;61;;;;;;34;9;5;;;9;;;;;247";
			//var puzzle = ";;;2;;3;4;9;;;8;7;;;17;;;;99;;14;5;3;5;9;;76;;;7;3;;4;;;6;;;;;75;;;;;;;6;;;;91;;2";
			//var puzzle = "4;;8;25;;;;2;;3;;;;;;;;;8;;8;;;;;7;;1;;2;6;;4;76;;;;9;;2819;;3;;;1;;;;4;3;4;;;;;;";
			//var puzzle = ";4;2;;3;9;;;;;9;5;;;2;;6;;;4;;8;;;;;;;3;;;5;;5;;;3;;1;;;4;1;73;;3;78;;;;;;;;;;;6;";
			//var puzzle = ";;;9;;84;8;;6;;;;;;7;3;;;;67;913;;;;;1;;;59;;4;6;;7;5;;;;;;61;;;;4;;;;67;;;;2;;3;";
			var puzzle = "2;;;;;;;;;5;;;;;46;;326;5;;1;;;;3;;;;;;;8;49;745;;;;;;;;;5;;;;;;19;;;;8;;;;43;;2;";
			var action = "<?=$action;?>";
			
			function load_puzzle()
			{				
				if (action == "")
				{
					
					for (cell_no = 0; cell_no < 81; cell_no++)
					{
						var character = puzzle.substr(cell_no, 1);
						 
						if (character != ";") 
							document.getElementById("cell_" + cell_no).value = character;
					}
				}
			}
			
			function submit_form()
			{
				if (action == "") 
				{
					document.sudoku_form.submit();
				}
				else
				{
					var solved = 0;
					
					for (cell_no = 0; cell_no < 81; cell_no++) 
					{
						var table = $("#" + "textbox_" + cell_no).find("table");
						var rows = $(table).find('tbody > tr').get();
						var break_row = false;
						
						$.each(rows, function(index1, row) {
								
							if ($(row).html() != "")
							{
								var datas = $(row).find('td').get();
								
								$.each(datas, function(index2, data) {
									
									var span = $(data).find("span");
									
									if ($(span).size() > 0)
									{
										solved++;
										document.sudoku_form.elements["cell_" + cell_no].value = $(span).html();
										break_row = true;
										return false;
									}
									else
									{
										if ($(data).css("background-color") == "rgb(0, 128, 0)") 
										{
											solved++;
											document.sudoku_form.elements["cell_" + cell_no].value = $(data).html();
											break_row = true;
											return false;
										}
										else
										{										
											if ($(data).html() == parseInt($(data).html()))
											{
												document.sudoku_form.elements["cell_" + cell_no].value = document.sudoku_form.elements["cell_" + cell_no].value + $(data).html() + "";
											}
										}
									}
									
								});
								
								if (break_row == true)
									return false;
							}
								
						});

					}					
					
					if (solved < 81)
					{
						document.sudoku_form.submit();
					}
				}
			}
		</script>
		
	</head>
	
	<body onload="load_puzzle();">
			
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
		
		<div style="position:absolute; top:45px; left:10px;">A</div>
		<div style="position:absolute; top:95px; left:10px;">B</div>
		<div style="position:absolute; top:145px; left:10px;">C</div>
		<div style="position:absolute; top:195px; left:10px;">D</div>
		<div style="position:absolute; top:240px; left:10px;">E</div>
		<div style="position:absolute; top:290px; left:10px;">F</div>
		<div style="position:absolute; top:340px; left:10px;">G</div>
		<div style="position:absolute; top:390px; left:10px;">H</div>
		<div style="position:absolute; top:440px; left:10px;">I</div>
		-->
		<!-- ********** ROW letters and COLUMN numbers ********** -->
		
		
		<form action="sudoku.php" method="post" name="sudoku_form" id="sudoku_form">	
<? 
	if ($action == "") 
	{
?>
			<input type="hidden" name="action" value="step">
<?
		echo $board; 
	}
	else
	{
?>
			<input type="hidden" name="action" value="solve">
			
			<table name="sudoku_table" id="sudoku_table" style="position:absolute; top:130px; left:30px;">
				<? $prev_row = -1; ?>
				
				<? for ($cell_no = 0; $cell_no < 81; $cell_no++) : ?>
					<? if ($prev_row != $cells[$cell_no]->row_no) : ?>
						<? $prev_row = $cells[$cell_no]->row_no; ?>
				<TR>			
						<? endif; ?>
						
							<input type="hidden" id="cell_<?=$cell_no;?>" name="cell_<?=$cell_no;?>" value="">
							
					<TD id="textbox_<?=$cell_no;?>" class="<?=$cells[$cell_no]->class_name;?>" style="height:40px; width:40px; background-color:<?=$cells[$cell_no]->background_color;?>; color:<?=$cells[$cell_no]->color;?>;border:1px solid black;">
						<? if ($action != "") : ?>
						<? echo $cells[$cell_no]->inner_html; ?>
						<? endif; ?>
					</TD>
							
						<? if ($cells[$cell_no]->row_no > $prev_row && $prev_row != -1) : ?>
				</TR>
						<? endif; ?>
						
						<? $prev_row = $cells[$cell_no]->row_no; ?>
					<? endfor; ?>			
			</table>
			
<?
	}
?>

			<br />
			
			<input onclick="submit_form();" name="submit_button" id="submit_button" type="button" value="STEP" style="position:absolute; top:200px; right:100px;">	
		</form>
		
		<div style="position:absolute; right:200px;">
			<?=$solution;?>
		</div>
		
	</body>
	
</html>