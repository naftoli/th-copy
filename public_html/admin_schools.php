<?
require_once( __DIR__ . "/classes/admin.php" );
require_once( __DIR__ . "/classes/school.php" );
require_once( __DIR__ . "/classes/school_class.php" );
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new admin($row);
$admin->get_school_id(); 
$admin->get_schools();
?>