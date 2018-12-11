<?
session_start();
if (!isset($_SESSION['school']) || !isset($_SESSION['grade']) || !isset($_SESSION['name']) 
	|| !isset($_SESSION['admin_id']) || !isset($_SESSION['photo']) || !isset($_SESSION['user_id'])) {
	header("Location: index.php");
	exit;
}