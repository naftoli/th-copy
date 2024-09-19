<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$sql = "select raffle_id from raffles where date_ran < NOW() and show_for_kids = 1 order by date_ran desc limit 1";
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$raffle_id = $row['raffle_id'];

echo $raffle_id;