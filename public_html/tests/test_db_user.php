<?php
$link = mysql_connect('localhost', 'mashpia_cth', 'UlqKsfnTUq2A') or trigger_error_server('Failed to connect to mysql', E_USER_ERROR);
mysql_query('SET NAMES utf8');
mysql_query('SET CHARACTER_SET utf8');
mysql_select_db('mashpiadb') or trigger_error_server('Failed to select db', E_USER_ERROR);

$users = array();
$sql = "select * from users limit 5";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $users[] = $row;
}

echo "<pre>";
print_r($users);
echo "</pre>";