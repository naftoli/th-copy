<?php
require_once("db.php");
$intSerial = $_GET["s"];
if (!preg_match("/^[0-9]+$/", $intSerial))
{
	print "Sorry, there was an error: mashpia.com-GP2101-D8F76G";
	exit;
}
// Get the user id from the code
$intSerial = preg_replace("/^3/", "", $intSerial);
$strSql = "SELECT user_id FROM users WHERE user_code = " . $intSerial;
//print $strSql;exit;
$objResult = mysql_query($strSql) or die("Sorry, there was an error: mashpia.com-GP2102-FSD7GS");
if ($objResult && mysql_num_rows($objResult))
{
	$intUserId = mysql_result ($objResult, 0);
} else {
	print "Sorry, there was an error: mashpia.com-GP2103-DF87DF";
	exit;
}

$intPointsSum = 0;
if (isset($_GET["other"]))
{
	$strSql = "SELECT SUM(award_points) FROM points WHERE user_id =$intUserId GROUP BY user_id";
	$objResult = mysql_query($strSql) or die("Sorry, there was an error: mashpia.com-GP2104-78DF7S");
	if ($objResult && mysql_num_rows($objResult))
	{
		$intPointsSum += mysql_result ($objResult, 0);
	}
}
if (isset($_GET["marking"]))
{
	$strSql = "SELECT SUM(mark_points) FROM date_tasks_marks WHERE user_id =$intUserId GROUP BY user_id";
	$objResult = mysql_query($strSql) or die("Sorry, there was an error: mashpia.com-GP2105-SD787S");
	if ($objResult && mysql_num_rows($objResult))
	{
		$intPointsSum += mysql_result ($objResult, 0);
	}
}	

print $intPointsSum;
?>