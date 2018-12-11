<?
require_once('db.php');
require_once('lang.php');
require_once('file_save.php');
require_once('camp_card_printer.php');

$camp_id = $_GET["camp_id"];
$task_id = $_GET["task_id"];
$miles = $_GET["miles"];
$left_circle = $_GET["left_circle"];
$right_circle = $_GET["right_circle"];
$number_of_cards = $_GET["number_of_cards"];

$sql = "SELECT * FROM campaign_tasks WHERE task_id=" . $task_id;
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
$echo_string = $echo_string . "<p style='text-align:center;' class='noprint'><input type='button' onclick='print();' value='Print'></p>";
$echo_string = $echo_string . "<TABLE class='fronts'>\n";

for ($cntr = 0; $cntr < $number_of_cards; $cntr++) {
	$remainder = $cntr % 2;
	
	if ($remainder == 0)
		$echo_string = $echo_string . "\n\t<TR>";
		
	$echo_string = $echo_string . display_card_front($expires, $camp_name, $camp_city, $camp_state, $camp_logo_id);
	
	if ($remainder == 1 || $cntr == ($number_of_cards - 1))
		$echo_string = $echo_string . "\n\t</TR>\n";
	
}
echo $echo_string . "\n</TABLE><br /><hr><br />";

$echo_string = "<TABLE class='backs'>\n";
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

		$sql = "INSERT INTO camp_card_codes (code_id, camp_id, task_id, points, left_circle, right_circle, expiration_date) VALUES (" . $id . ", " . $camp_id . ", " . $task_id . ", " . $miles . ", '" . $left_circle . "', '" . $right_circle . "', FROM_DAYS(" . $expires . "-1721060)";
		//mq("INSERT INTO camp_card_codes (code_id, camp_id, task_id, points, left_circle, right_circle, expiration_date) VALUES (" . $id . ", $school_id, $subject_id, $values, FROM_DAYS($expires-1721060))");

	$id = $prefix . str_pad($id, 19, '0', STR_PAD_LEFT);
		
	$echo_string = $echo_string . display_card_back($id, $miles, $left_circle, $task_name, $right_circle);
	
	if ($remainder == 1 || $cntr == ($number_of_cards - 1))
		$echo_string = $echo_string . "\n\t</TR>\n";
	
}
echo  $echo_string . "\n</TABLE>";
?>