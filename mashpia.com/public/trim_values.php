<?
require_once 'db.php';

$sql = "select date_task_id, name, description, quantity from date_tasks where name is not null and description is not null and quantity is not null";
$result = mysql_query($sql) or die(mysql_error());

$success = 0;
$errors = array();
$i = 0;
while ($row = mysql_fetch_assoc($result)) {
	$sql2 = "update date_tasks set name = '" . mysql_real_escape_string(trim($row['name'])) .  "', 
		description = '" . mysql_real_escape_string(trim($row['description'])) .  "', 
		quantity = " . mysql_real_escape_string(trim($row['quantity'])) . " where date_task_id = " . $row['date_task_id'];
	
	//echo $sql . "<br />";
	$result2 = mysql_query($sql2);
	if ($result2) $success++;
	else $errors[$i++] = $row['date_task_id'];
}
echo "Success: " . $success;
if (count($errors) > 0) {
	foreach ($errors as $error) echo "Error with id: " . $error;
}
