<?php
$admin_auth = ['user'];
require_once $_SERVER['DOCUMENT_ROOT'] . "/header.php";

$user_id = mysql_real_escape_string($_POST['user_id']);
$heName = mysql_real_escape_string($_POST['name']);

// first split he name into first / last
$nameInfo = explode(' ', $heName);
$end = count($nameInfo) - 1;
$last = $nameInfo[$end];
$first = '';
for ($i = 0; $i < $end; $i++) {
    $first .= $nameInfo[$i] . ' ';
}
$first = trim($first);

$sql = "update users set first_he = '" . $first . "', last_he = '" . $last . "' where user_id = " . $user_id;
$result = mysql_query($sql);
echo intval($result);