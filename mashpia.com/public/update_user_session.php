<?php
session_start();
$id = $_POST['user_id'];
$fee = $_POST['fee'];
$_SESSION['toEnroll'][$id] = $fee;
?>