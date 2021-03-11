<?php
require __DIR__ . '/../../../db.php';
require __DIR__ . '/../../../class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$user_id = mysql_real_escape_string($_POST['user']);

$prizes = [];
$sql = "select * from chidon_user_prizes where year = " . $year . " and user_id = " . $user_id;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[] = $row;
}
echo json_encode($prizes);