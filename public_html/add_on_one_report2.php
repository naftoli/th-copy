<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>School List</title>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin->auth == 'super') : ?>
<h1>Add-ons Report</h1>
<?
include_once('db.php');

$sql = "SELECT inv.*, s.school_name FROM `invoice_items` as inv, schools as s 
	where s.school_id = inv.school_id 
	and inv.item_price > 36 
	and inv.item_price <> 72 
	and inv.item_price <> 60 
	and inv.item_price <> 96 
	and inv.item_price <> 108 
	and inv.item_price <> 120 
	and inv.item_price <> 132 
	and inv.item_price <> 180 
	and inv.item_price <> 144 
	and inv.item_date > '2010-09-02'
	order by s.school_name, inv.item_price";
$result = mysql_query($sql);
$ids = array();
$errors = array();
$total = 0;
while ($row = mysql_fetch_assoc($result)) {
	$names = explode('-', $row['item_description']);
	for ($i = 0; $i < count($names); $i++) $names[$i] = trim($names[$i]);
	echo $row['school_name'] . ", " . $row['item_price'] . ", " . $row['item_description'] . "<br />";
	$total++;
	$sql2 = "select user_id from users where first = '$names[1]' and last = '$names[2]'";
	//echo $sql;
	$result2 = mysql_query($sql2) or die(mysql_error());
	if (mysql_num_rows($result2) > 0) {
		$row2 = mysql_fetch_assoc($result2);
		$ids[] = $row2['user_id'];
	} else {
		$errors[] = $row['school_name'] . ", " . $row['item_price'] . ", " . $row['item_description'] . "<br />";
	}
}
echo "<br />total: " . $total . "<br /><br />";

$sql = "SELECT amount, description
	FROM transactions
	WHERE description LIKE  '%child registration%' 
	and amount > 36 
	and amount <> 72 
	and amount <> 60 
	and amount <> 96 
	and amount <> 108 
	and amount <> 120 
	and amount <> 132 
	and amount <> 180 
	and amount <> 144 
	AND response LIKE  '1%' 
	order by amount";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	echo $row['amount'] . " - " . $row['description'] . "<br />";
}

$descriptions = array();
//$sql = "select description from transactions where description like '%child registration%' and response like '1%'";
$sql = "SELECT amount, description
	FROM transactions
	WHERE description LIKE  '%child registration%' 
	and amount > 36 
	and amount <> 72 
	and amount <> 60 
	and amount <> 96 
	and amount <> 108 
	and amount <> 120 
	and amount <> 132 
	and amount <> 180 
	and amount <> 144 
	AND response LIKE  '1%' 
	order by amount";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$str = substr($row['description'], strpos($row['description'], '>')+1);
	if (strpos($str, ':')) {
		$arr = explode(':', $str);
		$str = null;
	}
	if ($str) {
		if (trim($str) == "") continue;
		$descriptions[] = $str;
	}
	else 
		foreach ($arr as $v) $descriptions[] = $v;
}
//sort($descriptions);
foreach ($descriptions as $d) {
	$sql = "select s.school_name, u.user_id, u.last, u.first from schools as s, users as u 
			where u.school_id = s.school_id 
			and u.user_id = $d";
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_assoc($result);
	echo $row['school_name'] . ", " . $row['user_id'] . ", " . $row['last'] . ", " . $row['first'] . "<br />";
	$ids[] = $d;
}
echo "<br />total: " . count($descriptions) . "<br /><br />";
/*
sort($ids);
foreach ($ids as $id) echo $id . "<br />";
echo "total: " . count($ids) . "<br />";
*/
echo "<br />Errors:<br />";
foreach ($errors as $error) echo $error;
echo "total errors: " . count($errors) . "<br />";

/*
$success = 0;
foreach ($ids as $id) {
	$sql = "update users set add_on_one = 1 where user_id = $id";
	//echo $sql;
	if ($result = mysql_query($sql)) $success++;
}
echo "<br />Success: " . $success . "<br />";
*/
?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
