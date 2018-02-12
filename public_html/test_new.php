<?
require '/home/mashpia/includes/globals.php';
$link = mysql_connect('localhost', $global_db_user, $global_db_pass) or trigger_error_server('Failed to connect to mysql', E_USER_ERROR);
mysql_query('SET NAMES utf8');
mysql_query('SET CHARACTER_SET utf8');
mysql_select_db('mashpiadb') or trigger_error_server('Failed to select db', E_USER_ERROR);

$sql = "SELECT * FROM line_campaigns";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
var_dump($row);
}
?>