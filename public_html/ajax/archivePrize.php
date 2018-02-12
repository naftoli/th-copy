<?php
require '../db.php';

$prize_id = mysql_real_escape_string($_POST['prize_id']);
$checked = mysql_real_escape_string($_POST['checked']);
if ($checked == 'true') $value = 1;
else $value = 0;

$sql = "update prizes_auction set archived = " . $value . " where prize_id = " . $prize_id;
mysql_query($sql);