<?php
session_start();
$school = $_SESSION['school'];
unset($_SESSION['admin_id']);
unset($_SESSION['user_id']);
unset($_SESSION['school']);
unset($_SESSION['name']);
unset($_SESSION['grade']);
unset($_SESSION['photo']);
unset($_SESSION['subject']);
if ($school == 'Beis Rivka HS') header("Location: /achos/mobile");
else header("Location: /achos/mobile/fc");
exit;
?>