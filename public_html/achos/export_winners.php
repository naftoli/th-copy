<?
$school_id = $_GET['school_id'];
$auction_id = $_GET['auction_id'];
$write_string = "";

require_once('db.php');

$sql = "SELECT users.first, users.last, user_id, user_serial, school_name, school_number, classes.class_grade, classes.class_sub, prizes_auction.prize_name, prize_number, prize_id, auction_winners.quantity ";
$sql = $sql . "FROM auction_winners ";
$sql = $sql . "JOIN users USING (user_id) ";
$sql = $sql . "JOIN prizes_auction USING (prize_id) ";
$sql = $sql . "JOIN auctions USING (auction_id) ";
$sql = $sql . "LEFT JOIN schools ON (users.school_id= schools.school_id) ";
$sql = $sql . "LEFT JOIN classes USING (class_id) ";
$sql = $sql . "WHERE auction_id=" . $auction_id . " ";
if ($school_id > 0)
	$sql = $sql . "AND users.school_id=" . $school_id . " ";
$sql = $sql ."AND auctions.auction_ran=1 ";
$sql = $sql ."ORDER BY prizes_auction.prize_number, auction_winners.prize_id, school_name, classes.class_grade, classes.class_sub, class_id, users.last, users.first, auction_winners.auction_id";

$result = mqu($sql);
$fields = mysql_num_fields($result);

$write_string = csv_escape(mysql_field_name($result, 0));

for($i = 1; $i < $fields; $i++) 
{
	$write_string = $write_string . ", " . csv_escape(mysql_field_name($result, $i));
}
	
$write_string = $write_string . "\r\n";

while($row = mysql_fetch_row($result)) 
{
	$write_string = $write_string . csv_escape($row[0]);
	
	for($i = 1; $i < $fields; $i++) 
	{
		$write_string = $write_string . "," . csv_escape($row[$i]);
	}
		
	$write_string = $write_string . "\r\n";
}

function csv_escape($str) 
{
	$str = str_replace(array('"', ',', "\n", "\r"), array('""', ',', "\n", "\r"), $str, &$count);
	
	if ($count) 
	{
		return "\"$str\"";
	} 
	else 
	{
		return $str;
	}
}

//$right_now = date('dmYhis');
//$file_name = "exports/auction_winners_" . $right_now . ".csv";
$write_file = "exports/auction_winners.csv";
$file_open = fopen($write_file, 'w') or die("can't open file");
fwrite($file_open, $write_string);
fclose($file_open);

echo $right_now;
?>
