<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';

$chidon_id = mysql_real_escape_string($_POST['id']);
$value = mysql_real_escape_string($_POST['val']);
$field = mysql_real_escape_string($_POST['field']);

$teams = ['Sefer Hamitzvos', 'Mishna Torah', 'Moreh Nevuchim', 'Pirush Hamishnayos', 'Igeres Horambam'];

if (strpos($field, 'team') !== false) {
    if (intval($value) > 0) {
        $val = $teams[$value - 1];
        $sql = "update th_chidon set $field = '$val' where th_chidon_id = " . $chidon_id;
    }
    else $sql = "update th_chidon set $field = '' where th_chidon_id = " . $chidon_id;
}
else $sql = "update th_chidon set $field = $value where th_chidon_id = " . $chidon_id;
if (isset($sql) && mysql_query($sql)) echo 1;
else echo 0;