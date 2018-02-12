<?php
session_start();
$id = $_GET['user_id'];
$addon = $_GET['school_add_on_id'];
unset($_SESSION['addon'][$id][$addon]);
echo json_encode('1');
?>