<?php
echo "DESTROYING SESSION<br />";
session_start();
$_SESSION['camp_id'] = "";
header( 'Location: http://www.mashpia.com/admin.php' ) ;
?>