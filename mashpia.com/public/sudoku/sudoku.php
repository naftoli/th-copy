<?php
session_start();

include ("techniques.php");

$action = 0;
$puzzle = "";
$solution = "";

if (isset($_POST["action"]))
{
	$action = $_POST["action"];
	
	include ("cell.php");
	
	$cells = array();	
	for ($cell_no = 0; $cell_no < 81; $cell_no++)
	{		
		if ($action == "1")
		{
			$cell = new cell($cell_no, $_POST["cell_" . $cell_no], true, $puzzle);
			array_push($cells, $cell);		
			$puzzle = $puzzle . $_POST["cell_" . $cell_no];
		}
		else
		{
			$cell = new cell($cell_no, $_POST["cell_" . $cell_no], false, $_SESSION["puzzle"]);
			array_push($cells, $cell);		
		}
	}
	
	if ($action == "1") 
	{
		$_SESSION["puzzle"] = $puzzle;
		eliminate_entered_values_from_rcbs();
		$solution = "<span style='color:red;'>Eliminated all entered values from their associated rows, columns, and boxes</span><br /><br />";
		
		$singles_found = false;
		do {
			$singles_found = singles($_SESSION["puzzle"]);	
		} while ($singles_found == true);
	}
	else
	{
			$continue = true;
			
			$singles_found = false;
			do {
				$singles_found = singles($_SESSION["puzzle"]);	
				if ($singles_found == true)
					$continue = false;
			} while ($singles_found == true);
		
			if ($continue == true)
			{
				$rcb_singles_found = false;
				do {
					$rcb_singles_found = rcb_singles();	
					if ($rcb_singles_found == true)
						$continue = false;				
				} while ($rcb_singles_found == true);
			}
			
		if ($continue == true)
		{
			nakeds(2);
		}
	}
	
	$singles_left = false;
	do {
		$singles_left = singles_left();
	} while ($singles_left == true);
	
	
	if ($action > 6)
	{
		hiddens(2);
	}
	
	draw_board_two($_SESSION["puzzle"]);
	
}
else
{
	draw_board_one();
}
?>

<html>
	<head>
		<title>Sudoku Solver</title>
		
		<script src="http://mashpia.com/kiosk/scripts/jquery.core.js" type="text/javascript">
		</script>

		<style type="text/css">
		</style>
				
		<script type="text/javascript">
			$(document).ready(function() {
			
				var puzzle = "4   1       3 9 4  7   5  9    6  21  4 7 6  19  5    9  4   7  3 6 8       3   6";
				//var puzzle = "5    61   83      6 1  43    2 4     4 8       629  1    6       851  24 3     5 ";
				//var puzzle = "8 4  1      8  7 9      5      723   71 3    9 2                 3 6  97  9 5  4 ";
				var action = <?=$action;?>;
				
				$.each(puzzle, function(index, value) { 
					$("#cell_" + index).val(value);
				});

				$("#submit_button").click(function() {
				
					if ($("#action").val() == "")
					{
						$("#action").val("1");
						$("#sudoku_form").submit();
					}
					else
					{
						
						$("#action").val(action + 1);
						var values = $("#sudoku_form").find("div[name=values]");
						
						// ***** Loop through the 81 cells ***** //
						$.each(values, function(cell_no, values_value) 
						{ 
						
							if ($(this).attr("class") == "solved")
							{
								var value = $(values_value).find("div").html();
								value = strip_characters(value);
								$("#cell_" + cell_no).val(value);								
							}
							else
							{
								var spans = $(values_value).find("span");
								var value = "";
								
								// ***** Loop through all the numbers in the unsolved cell ***** //
								$.each(spans, function(number, span_value) 
								{ 
									if ($(this).css("background-color") != "rgb(255, 0, 0)")
										value = value + $(this).html() + "";
								});
								// ***** Loop through all the numbers in the unsolved cell ***** //
							
								$("#cell_" + cell_no).val(value);
							}
							
						});
						// ***** Loop through the 81 cells ***** //
						
						$("#sudoku_form").submit();
					}
					
					
				});
				
				function strip_characters(value)
				{
					var new_value = "";
					
					for (cno = 0; cno < value.length; cno++)
					{
						var character = value.substr(cno, 1);
						if (character == parseInt(character))
							new_value = new_value + character;
					}
					
					return new_value;
				}
				
			});
		</script>
	</head>
	
	<body onload="load_puzzle();">
				
		<form action="sudoku.php" method="post" name="sudoku_form" id="sudoku_form">	
			<input type="hidden" name="action" id="action" value="<?=$action;?>">
			
			<?=$board;?>
			
			<input type="button" value="STEP" name="submit_button" id="submit_button" style="position:absolute; left:500px; top:225px;">
		</form>
		
		<div style="position:absolute; left:600px;">
			<?=$solution;?>
		</div>

		<? $left = 23; ?>
		<? for ($column = 0; $column < 9; $column++) : ?>
		<div style="position:absolute; top:20px; left:<?=(($column + 1) * 44) + $left;?>px;">
			<?//=($column + 1);?>
		</div>
		<? endfor; ?>
		
		<? $top = 8; ?>
		<? for ($row = 0; $row < 9; $row++) : ?>
		<div style="position:absolute; top:<?=(($row + 1) * 44) + $top;?>px; left:20px;">
			<?//=$letters[$row];?>
		</div>		
		<? endfor; ?>
	</body>
	
</html>