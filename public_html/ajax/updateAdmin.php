<?
require_once '../db.php';

foreach ($_POST as $k => $v) {
    $$k = mysql_real_escape_string(trim($v));
}

$sql = "update admins set  
        title = '$title', 
        first = '$first', 
        last = '$last', 
        admin_email = '$email' 
        where admin_id = $admin";

if (mysql_query($sql)) {
	echo 1;
} else {
	echo 0;
}
?>