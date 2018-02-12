<?php
session_start();
$amount = $_POST['amount'];
$_SESSION['sponsor'] = $amount;
echo $amount;
/*
include ("db.php");
$year = date('Y');

$sql = "SELECT * FROM admin_sponsors WHERE admin_id=" . $_GET['admin_id'] . " AND year=" . $year;
$query = mysql_query($sql);	
$row = mysql_fetch_assoc($query);

if ($row['admin_sponsor_id'] > 0) 
	$sql = "UPDATE admin_sponsors SET amount=" . $_GET['amount'] . " WHERE admin_sponsor_id=" . $row['admin_sponsor_id'];
else 
	$sql = "INSERT INTO admin_sponsors (admin_id, amount, date, year) VALUES(" . $_GET['admin_id'] . ", " . $_GET['amount'] . ", CURDATE(), " . $year . ")";
	
$query = mysql_query($sql);	

if ($query) 
	echo json_encode('1');
else 
	echo json_encode('0');
 * 
 */
?>