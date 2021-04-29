<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$chidon_id = $_POST['chidon_id'];
$checked = $_POST['checked'];

$sql = "update th_chidon set edit_prizes = $checked where th_chidon_id = $chidon_id";
if (mysql_query($sql)) echo 1;
else echo 0;