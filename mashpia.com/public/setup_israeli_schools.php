<?
require 'db.php';

$sql = "select max(school_number) as num from schools";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$number = $row['num'];
$number++;

$schools = array(
	
);
