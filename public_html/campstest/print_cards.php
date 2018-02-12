<?php
$admin_auth = array('camp');
require('../lang.php');
require_once('../db.php');
require_once('../file_save.php');
require_once('camp_card_printer.php');

$camp_id = $_GET['camp_id'];

if (isset($_GET['camp_campaign_id']))
	$camp_campaign_id = $_GET['camp_campaign_id'];
else
	$camp_campaign_id = -1;

if (isset($_GET['camp_mission_id']))
	$camp_mission_id = $_GET['camp_mission_id'];
else
	$camp_mission_id = -1;

if (isset($_GET['camp_task_id']))
	$camp_task_id =$_GET['camp_task_id'];
else
	$camp_task_id = -1;

$sql = "";
$echo_string = "";

if ($camp_task_id > -1) {
	$task_id = camp_task_id;
	$miles = 50;
	$left_circle = 50;
	$right_circle = 50;
	$number_of_cards = 5;

	$sql = "SELECT * FROM camp_tasks WHERE camp_task_id=" . $camp_task_id;
	$query = mysql_query($sql);
	$task = mysql_fetch_assoc($query);
	$task_name = $task['task_name'];

	$sql = "SELECT * FROM camps WHERE camp_id=" . $camp_id;
	$query = mysql_query($sql);
	$camp = mysql_fetch_assoc($query);
	$camp_name = $camp['camp_name'];
	$camp_city = $camp['camp_city'];
	$camp_state = $camp['camp_state'];
	$camp_logo_id = $camp['camp_logo_id'];

	$expires = unixtojd() + 30;
	$prefix = "1";

	$echo_string = "<hr>";
	$echo_string = $echo_string . "<p style='text-align:center;'><input type='button' class='printbutton' onclick='print();' value='Print'></p>";
	$echo_string = $echo_string . "<TABLE id='fronts' class='fronts'>\n";

	for ($cntr = 0; $cntr < $number_of_cards; $cntr++) {
		$remainder = $cntr % 2;
		
		if ($remainder == 0)
			$echo_string = $echo_string . "\n\t<TR>";
			
		$echo_string = $echo_string . display_card_front($expires, $camp_name, $camp_city, $camp_state, $camp_logo_id);
		
		if ($remainder == 1 || $cntr == ($number_of_cards - 1))
			$echo_string = $echo_string . "\n\t</TR>\n";
		
	}
	$echo_string = $echo_string . "\n</TABLE><br /><hr><br />";

	$echo_string = $echo_string . "<TABLE id='backs' class='backs'>\n";
	for ($cntr = 0; $cntr < $number_of_cards; $cntr++) {
		$remainder = $cntr % 2;
		
		if ($remainder == 0)
			$echo_string = $echo_string . "\n\t<TR>";
			
		$count = 0;
		do {
			if ($count++ > 100000) 
				trigger_error('could not get ID', E_USER_ERROR);
					
			$id = mysql_result(mq('SELECT FLOOR(RAND() * 9223372036854775807)'),0);
		} while (mysql_result(mq("SELECT COUNT(*) FROM camp_card_codes WHERE code_id=" . $id),0) != 0);

			//$sql = "INSERT INTO camp_card_codes (code_id, camp_id, task_id, points, left_circle, right_circle, expiration_date) VALUES (" . $id . ", " . $camp_id . ", " . $task_id . ", " . $miles . ", '" . $left_circle . "', '" . $right_circle . "', FROM_DAYS(" . $expires . "-1721060)";
			$sql = "INSERT INTO camp_card_codes (code_id, camp_id, task_id, points, left_circle, right_circle, expiration_date) VALUES (" . $id . ", 0, " . $task_id . ", " . $miles . ", '" . $left_circle . "', '" . $right_circle . "', FROM_DAYS(" . $expires . "-1721060)";			
			//mq("INSERT INTO camp_card_codes (code_id, camp_id, task_id, points, left_circle, right_circle, expiration_date) VALUES (" . $id . ", $school_id, $subject_id, $values, FROM_DAYS($expires-1721060))");

		$id = $prefix . str_pad($id, 19, '0', STR_PAD_LEFT);
		
		$echo_string = $echo_string . display_card_back($id, $miles, $left_circle, $task_name, $right_circle);
		
		if ($remainder == 1 || $cntr == ($number_of_cards - 1))
			$echo_string = $echo_string . "\n\t</TR>\n";
		
	}
	$echo_string = $echo_string . "\n</TABLE>";

	}
elseif ($camp_mission_id > -1) {
	$current_page = T_('Tasks');
	$id_name = "camp_task_id";
	$sql = "SELECT camp_task_id AS id, task_name AS name FROM camp_tasks WHERE camp_mission_id=" . $camp_mission_id;
	$query = mq($sql);
}
elseif ($camp_campaign_id > -1) {
	$current_page = T_('Missions');
	$id_name = "camp_mission_id";
	$sql = "SELECT camp_mission_id AS id, mission_name AS name FROM camp_missions WHERE camp_campaign_id=" . $camp_campaign_id;
	$query = mq($sql);
}
else {
	$current_page = T_('Campaigns');
	$id_name = "camp_campaign_id";
	$sql = "SELECT camp_campaign_id AS id, campaign_name AS name FROM camp_campaigns WHERE camp_id=" . $camp_id;
	$query = mq($sql);
}
?>

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
			  width: 3.125in;
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
				
			}			
		</STYLE>

<div class="slider">

	<div class="col_title">
		<span>
			<?=$current_page;?>
		</span>
		<a class="slider_back">
			Dashboard
		</a>
	</div>
	
	<br />
	
	<div class="col_content">
					
		<div id="lists-bunks" class="lists">
						
			<div class="content">
				
				<? if ($echo_string != "") : ?>
					<? echo $echo_string; ?>
				<? else : ?>
				<ul>
					
					<? while ($row = mysql_fetch_assoc($query)) :?>
					<li>
						<a href="print_cards.php?camp_id=<?=$camp_id;?>&<?=$id_name;?>=<?=$row['id'];?>">
							<div class="name">&nbsp;&nbsp;&nbsp;&nbsp;<?=$row['name'];?></div>
						</a>
					</li>
					<? endwhile; ?>
				</ul>
				<? endif; ?>
			</div>
		</div>
	</div>
</div>