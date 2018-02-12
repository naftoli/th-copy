<?php
if (!isset($_SESSION))
	session_start();
if (isset($_SESSION['admin_id']) || isset($_POST['admin_id']))
	$admin_id = isset($_POST['admin_id']) ? $_POST['admin_id'] : $_SESSION["admin_id"];	
else 
	header("Location: http://www.mashpia.com/admin.php");
?>