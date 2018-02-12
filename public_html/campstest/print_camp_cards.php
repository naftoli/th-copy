<?php

if (isset($_POST['amount'])) {

	$admin_auth = array('camp');
	require('../lang.php');
	require_once('../db.php');
	require_once('../file_save.php');
	require_once('camp_card_printer_two.php');
	
	$miles = "";
	$left_circle = "";
	$task_name = "";
	$right_circle = "";
	
	$amount = $_POST['amount'];
	$number_of_pages = $_POST['number_of_pages'];
	$expires = unixtojd() + 30;
		
	$echo_string = "<hr>";
	$echo_string = $echo_string . "<p style='text-align:center;'><input type='button' class='printbutton' onclick='print();' value='Print'></p>";
	
	for ($cntr = 0; $cntr < $number_of_pages; $cntr++) {
	
		$echo_string = $echo_string . "<TABLE id='backs' class='backs'>\n";
		
		for ($cntr2 = 0; $cntr2 < 10; $cntr2++) {
				
			$remainder = $cntr2 % 2;
			
			if ($remainder == 0)
				$echo_string = $echo_string . "\n\t<TR>";
				
			$count = 0;
			do {
				if ($count++ > 100000) 
					trigger_error('could not get ID', E_USER_ERROR);
						
				$id = mysql_result(mysql_query('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
			} while (mysql_result(mysql_query("SELECT COUNT(*) FROM camp_card_codes WHERE code_id=" . $id),0) != 0);

			$sql = "INSERT INTO camp_card_codes (code_id, camp_id, task_id, points, left_circle, right_circle, expiration_date) VALUES (" . $id . ", 0, 0, " . $amount . ", '0', '0', FROM_DAYS(" . $expires . "-1721060))";			
			mysql_query($sql);
				
			$id = $prefix . str_pad($id, 19, '0', STR_PAD_LEFT);
			
			$echo_string = $echo_string . display_card_back($id, $amount, $number_of_pages);
			
			if ($remainder == 1)
				$echo_string = $echo_string . "\n\t</TR>\n";
									
		}
		$echo_string = $echo_string . "\n</TABLE>\n<hr>";
	}
	
	

}
?>
<HTML DIR="LTR">

	<HEAD>
		<TITLE>Print Achievement Cards - Tzivos Hashem Management System</TITLE>
		<LINK href="card_printer.css" rel="stylesheet" type="text/css">
		<STYLE type="text/css">
			.fronts, .backs {
			  margin: auto;
			}

			.fronts, .backs {
			  page-break-after: always;
			}

			.fronts td, .backs td {
			  border: 1px dashed black;
			  vertical-align: middle;
			  height: 2.125in;
			  width: 3.65in;
			}

			.fronts td td, .backs td td {
			  width: auto;
			  height: auto;
			  border: none;
			}

			@media print {
				#nav {
					display:none;
				}
				
				#wrapper > div a.slider_back {
					display:none;
				}
				
				input.printbutton {
					display:none;
				}	

				.noPrint { 
					display: none; 
				} 	
				
				hr {
					display: none;
				}
				
				
				.card_front .border {
					border-style:none;
				}
				
				.fronts td, .backs td {
				  /*border: 0px dashed black;*/
				  border-style:none;
				  vertical-align: middle;
				  height: 2.125in;
				  width: 3.65in;
				}
				
			}			
		</STYLE>

	</HEAD>

	<BODY>
	
		<div class="noPrint">
			<form method="post" action="print_camp_cards.php">
				<label>
					Choose a Value:
					<select name="amount" id="amount">
						<option value="1">1 Cent</option>
						<option value="5">5 Cents</option>
						<option value="10">10 Cents</option>
						<option value="25">25 Cents</option>
					</select>				
				</label>
				<br />
				<label>
					Number of Pages (10 cards per page):
					<select name="number_of_pages" id="number_of_pages">
					<? for ($cntr = 1; $cntr < 201; $cntr++) : ?>
						<option value="<?=$cntr;?>"><?=$cntr;?></option>
					<? endfor; ?>
					</select>				
				</label>				
				<br />
				<input type="submit" value="GO">
			</form>
		</div>
		
		<? echo $echo_string; ?>
	</BODY>

<HTML>	
	