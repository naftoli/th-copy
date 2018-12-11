<?php

$ids = array(

);

include('db.php');
$num = 0;

foreach($ids as $id) {
$sql = "delete from date_tasks where date_task_id = $id";
if (mysql_query($sql))
	$num++;
}

echo "records deleted: $num";

?>
