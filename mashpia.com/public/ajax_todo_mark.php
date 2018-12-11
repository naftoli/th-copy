<?
$admin_auth = array('school');
require('header.php');

$action = $_GET['action'];
$school_id = $_GET['school_id'];
$todo_id = $_GET['todo_id'];

//echo "ACTION:" . $action . "<br />";

if ($action == "unmark")
{
	//echo "UN MARKING<br />";
	$sql = "DELETE FROM todo_list_marks WHERE todo_id=" . $todo_id .  " AND auth='school' AND id=" . $school_id; 
	//echo $sql . "<br />";
	$query = mysql_query($sql);
	if ($query)
		echo "1";
	else
		echo "0";
}
else
{
	//echo "MARKING<br />";
	$right_now = date("Y") . "-" . date("m") . "-" . date("d") . " " . date("H") . ":"  . date("i") . ":" . date("s");
	$sql = "INSERT INTO todo_list_marks SET todo_id=" . $todo_id . ", id=" . $school_id . ", auth='school', mark_date='" . $right_now . "'";
	//echo $sql . "<br />";
	$query = mysql_query($sql);
	if ($query)
	{
		echo "1";
	}
	else
	{
		echo "0";
	}
}
?>