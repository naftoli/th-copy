<?php
session_start();
$id = $_POST['user_id'];
$addon = $_POST['school_add_on_id'];
$size = $_POST['size'];
$action = $_POST['action'];

include ("db.php");
if ($action == 'add') {
    $_SESSION['addon'][$id][$addon] = $size;
    $sql = "UPDATE user_add_ons SET size='". $size . "' WHERE user_id=" . $id . " AND school_add_on_id=" . $addon;	
} else if ($action == 'delete') {
    unset($_SESSION['addon'][$id][$addon]);
    $sql = "DELETE FROM user_add_ons WHERE user_id=" . $id . " AND school_add_on_id=" . $addon;
}
$query = mysql_query($sql);
?>