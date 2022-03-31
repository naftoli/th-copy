<?php
chdir('../../../');
require 'db.php';
require_once 'class.globalSettings.php';

$year = GlobalSettings::getChidonYear();
$serial = mysql_real_escape_string( $_POST['id'] );

$sql = "update th_chidon set invite_used = 1 where year = $year and user_id = (
    select user_id from users where user_serial = $serial
)";
if (mysql_query($sql)) echo 1;
else echo 0;