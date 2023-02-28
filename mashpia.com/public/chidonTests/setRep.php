<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$chidon_id = mysql_real_escape_string($_POST['id']);
$value = mysql_real_escape_string(intval($_POST['val']));
$field = mysql_real_escape_string($_POST['field']);
$type = mysql_real_escape_string($_POST['typeOfField']);

$teams = ['Sefer Hamitzvos', 'Mishna Torah', 'Moreh Nevuchim', 'Pirush Hamishnayos', 'Igeres Horambam'];

if (strpos($field, '_') !== false) $sql = "update th_chidon set $field = $value where th_chidon_id = " . $chidon_id;
else {
    if ($value) $val = $teams[$value - 1];
    $sql = "update th_chidon set $field = '$val' where th_chidon_id = " . $chidon_id;
}
if (isset($sql) && mysql_query($sql)) echo 1;
else echo 0;