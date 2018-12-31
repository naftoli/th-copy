<?php
$admin_auth = ['school'];
require 'header.php';
include("classes/admin.php");
$sql = "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$admin = new \classes\admin($row);
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf8" />
    <link href="admin_styles.css" rel="stylesheet" type="text/css">
    <title>HQ Links</title>
  </head>
  <body>
    <?php
    include('admin_header.php');
    echo "<h1>HQ Links</h1>";
    if ($admin->auth == 'super') {
      echo "<H2>" . T_('Admin Menu') . "</H2>";
      $menu_type = 'super';
      include('admin_inc.php');
    }
    ?>
  </body>
</html>