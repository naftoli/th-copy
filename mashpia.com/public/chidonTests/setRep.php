<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$chidon_id = mysql_real_escape_string($_POST['id']);
$value = mysql_real_escape_string($_POST['val']);
$field = mysql_real_escape_string($_POST['field']);
$type = mysql_real_escape_string($_POST['typeOfField']);

if ($type == 'int') {
    $value = intval($value);
    $sql = "update th_chidon set $field = $value where th_chidon_id = $chidon_id";
} else if ($type == 'varchar') {
    $value = json_decode($value);
    if ($value != 'Please Choose') $sql = "update th_chidon set $field = '$value' where th_chidon_id = $chidon_id";
}
if (isset($sql) && mysql_query($sql)) echo 1;
else echo 0;