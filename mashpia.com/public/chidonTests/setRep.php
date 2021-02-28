<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$id = mysql_real_escape_string($_POST['id']);
$state = mysql_real_escape_string(intval($_POST['state']));
$field = mysql_real_escape_string($_POST['field']);

$sql = "update th_chidon set $field = $state where th_chidon_id = $id";
if (mysql_query($sql)) echo 1;
else echo 0;