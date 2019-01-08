<?php
$admin_auth = array( 'school');
require('header.php'); 
include("classes/admin.php");

$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
?>
<!DOCTYPE html>
<html>
  <head>
    <TITLE><?=T_('Admin Menu'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
  </head>
  <body>
    <? include('admin_header.php'); ?>
    <h1>HQ Links</h1>
      <?php
      if ($admin->auth == 'super') {
        echo "<H2>" . T_('Admin Menu') . "</H2>";
        $menu_type = 'super';
        include('admin_inc.php');
      }
      ?>
  </body>
</html>