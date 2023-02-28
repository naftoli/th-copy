<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$chidon_id = mysql_real_escape_string($_POST['id']);
$value = mysql_real_escape_string(intval($_POST['val']));
$field = mysql_real_escape_string($_POST['field']);
$type = mysql_real_escape_string($_POST['typeOfField']);

if ($type == 'int') $sql = "update th_chidon set $field = $value where th_chidon_id = $chidon_id";
else if ($type == 'varchar' && $value != 'Please Choose') $sql = "update th_chidon set $field = '$value' where th_chidon_id = $chidon_id";
if (mysql_query($sql)) echo 1;
else echo 0;